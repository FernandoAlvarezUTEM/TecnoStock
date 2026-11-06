
<?php

require_once "../config/conexion.php";

$mensaje = "";
$registro_exitoso = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $clave = $_POST["clave"] ?? "";

    if (empty($nombre) || empty($correo) || empty($clave)) {

        $mensaje = "Todos los campos son obligatorios.";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "El correo electrónico no es válido.";

    } else {

        $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

        try {

            $sql = "INSERT INTO usuarios (nombre, correo, clave_hash)
                    VALUES (:nombre, :correo, :clave_hash)";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                ":nombre" => $nombre,
                ":correo" => $correo,
                ":clave_hash" => $clave_hash
            ]);

            $mensaje = "Usuario registrado correctamente.";
            $registro_exitoso = true;

        } catch (PDOException $e) {

            if ($e->getCode() == "23000") {

                $mensaje = "Este correo ya está registrado.";

            } else {

                $mensaje = "Error al registrar el usuario.";

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro de usuario - TecnoStock</title>
</head>

<body>

    <h1>Registro de usuario</h1>

    <p>
        <?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?>
    </p>

    <?php if ($registro_exitoso): ?>

        <br>

        <a href="login.php">
            <button type="button">Iniciar sesión</button>
        </a>

        <br><br>

        <a href="../index.php">Volver al inicio</a>

    <?php endif; ?>

    <form method="POST" action="">

        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <br><br>

        <label>Correo electrónico:</label>
        <input type="email" name="correo" required>

        <br><br>

        <label>Contraseña:</label>
        <input type="password" name="clave" required>

        <br><br>

        <button type="submit">Registrar usuario</button>

    </form>

    <p>
        ¿Ya tienes una cuenta?
        <a href="login.php">Inicia sesión aquí</a>
    </p>

</body>
</html>
