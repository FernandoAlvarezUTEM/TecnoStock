
<?php

require_once "../config/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"] ?? "");
    $clave = $_POST["clave"] ?? "";

    if (empty($correo) || empty($clave)) {

        $mensaje = "Debes completar todos los campos.";

    } else {

        try {

            $sql = "SELECT * FROM usuarios
                    WHERE correo = :correo
                    AND estado = 1";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                ":correo" => $correo
            ]);

            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($clave, $usuario["clave_hash"])) {

                session_start();
                session_regenerate_id(true);

                $_SESSION["id_usuario"] = $usuario["id_usuario"];
                $_SESSION["nombre_usuario"] = $usuario["nombre"];
                $_SESSION["rol"] = $usuario["rol"];

                header("Location: ../prueba_panel.php");
                exit;

            } else {

                $mensaje = "Correo o contraseña incorrectos.";

            }

        } catch (PDOException $e) {

            $mensaje = "Ocurrió un error al iniciar sesión.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicio de sesión - TecnoStock</title>
</head>

<body>

    <h1>Inicio de sesión</h1>

    <p>
        <?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?>
    </p>

    <form method="POST" action="">

        <label>Correo electrónico:</label>
        <input type="email" name="correo" required>

        <br><br>

        <label>Contraseña:</label>
        <input type="password" name="clave" required>

        <br><br>

        <button type="submit">Iniciar sesión</button>

    </form>

    <p>
        ¿No tienes una cuenta?
        <a href="registrar.php">Regístrate aquí</a>
    </p>

</body>
</html>
