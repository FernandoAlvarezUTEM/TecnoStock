
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de usuario - TecnoStock</title>

    <link rel="stylesheet" href="../assets/css/estilos.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .registro-contenedor {
            width: 100%;
            max-width: 460px;
        }

        .registro-tarjeta {
            background: white;
            padding: 38px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.08);
        }

        .registro-logo {
            text-align: center;
            font-size: 27px;
            font-weight: 800;
            color: #172554;
            margin-bottom: 28px;
        }

        .registro-logo span {
            color: #2563eb;
        }

        .registro-icono {
            width: 58px;
            height: 58px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dbeafe;
            color: #2563eb;
            border-radius: 14px;
            font-size: 27px;
        }

        .registro-titulo {
            text-align: center;
            font-size: 25px;
            margin: 0 0 9px;
        }

        .registro-descripcion {
            text-align: center;
            color: #64748b;
            margin: 0 0 28px;
            line-height: 1.6;
        }

        .registro-formulario .formulario-grupo {
            margin-bottom: 20px;
        }

        .registro-formulario input {
            padding: 13px;
        }

        .registro-boton {
            width: 100%;
            padding: 13px;
            font-size: 14px;
            margin-top: 5px;
        }

        .registro-mensaje {
            padding: 13px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
        }

        .registro-exito {
            background: #dcfce7;
            color: #166534;
        }

        .registro-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .registro-login {
            text-align: center;
            color: #64748b;
            margin-top: 25px;
        }

        .registro-login a {
            font-weight: 700;
        }

        .registro-volver {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #64748b;
            font-size: 13px;
        }

        .registro-volver:hover {
            color: #2563eb;
        }

        @media (max-width: 480px) {
            .registro-tarjeta {
                padding: 27px 21px;
            }
        }
    </style>
</head>

<body>

    <main class="registro-contenedor">

        <div class="registro-tarjeta">

            <div class="registro-logo">
                Tecno<span>Stock</span>
            </div>

            <div class="registro-icono">＋</div>

            <h1 class="registro-titulo">Crear una cuenta</h1>

            <p class="registro-descripcion">
                Completa tus datos para registrarte en TecnoStock.
            </p>

            <?php if (!empty($mensaje)): ?>
                <div class="registro-mensaje <?php echo $registro_exitoso ? 'registro-exito' : 'registro-error'; ?>">
                    <?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?>
                </div>
            <?php endif; ?>

            <?php if ($registro_exitoso): ?>

                <a href="login.php" class="boton registro-boton">
                    Iniciar sesión
                </a>

            <?php else: ?>

                <form class="registro-formulario" method="POST" action="">

                    <div class="formulario-grupo">
                        <label for="nombre">Nombre completo</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            placeholder="Ingresa tu nombre"
                            value="<?php echo htmlspecialchars($_POST["nombre"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
                            required
                        >
                    </div>

                    <div class="formulario-grupo">
                        <label for="correo">Correo electrónico</label>
                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            placeholder="ejemplo@correo.com"
                            value="<?php echo htmlspecialchars($_POST["correo"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
                            required
                        >
                    </div>

                    <div class="formulario-grupo">
                        <label for="clave">Contraseña</label>
                        <input
                            type="password"
                            id="clave"
                            name="clave"
                            placeholder="Crea una contraseña"
                            required
                        >
                    </div>

                    <button type="submit" class="boton registro-boton">
                        Crear cuenta
                    </button>

                </form>

            <?php endif; ?>

            <div class="registro-login">
                ¿Ya tienes una cuenta?
                <a href="login.php">Inicia sesión aquí</a>
            </div>

        </div>

        <a class="registro-volver" href="../index.php">
            ← Volver al inicio
        </a>

    </main>

</body>
</html>
