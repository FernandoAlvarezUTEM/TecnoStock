
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - TecnoStock</title>

    <link rel="stylesheet" href="../assets/css/estilos.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-contenedor {
            width: 100%;
            max-width: 440px;
        }

        .login-tarjeta {
            background: white;
            padding: 38px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.08);
        }

        .login-logo {
            text-align: center;
            font-size: 27px;
            font-weight: 800;
            color: #172554;
            margin-bottom: 28px;
        }

        .login-logo span {
            color: #2563eb;
        }

        .login-icono {
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

        .login-titulo {
            text-align: center;
            font-size: 25px;
            margin: 0 0 9px;
        }

        .login-descripcion {
            text-align: center;
            color: #64748b;
            margin: 0 0 28px;
            line-height: 1.6;
        }

        .login-formulario .formulario-grupo {
            margin-bottom: 20px;
        }

        .login-formulario input {
            padding: 13px;
        }

        .login-boton {
            width: 100%;
            padding: 13px;
            font-size: 14px;
            margin-top: 5px;
        }

        .login-mensaje {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
            font-weight: 500;
            text-align: center;
        }

        .login-registro {
            text-align: center;
            color: #64748b;
            margin-top: 25px;
        }

        .login-registro a {
            font-weight: 700;
        }

        .login-volver {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #64748b;
            font-size: 13px;
        }

        .login-volver:hover {
            color: #2563eb;
        }

        @media (max-width: 480px) {
            .login-tarjeta {
                padding: 27px 21px;
            }
        }
    </style>
</head>

<body>

    <main class="login-contenedor">

        <div class="login-tarjeta">

            <div class="login-logo">
                Tecno<span>Stock</span>
            </div>

            <div class="login-icono">↪</div>

            <h1 class="login-titulo">¡Bienvenido!</h1>

            <p class="login-descripcion">
                Ingresa tus datos para acceder al sistema de inventario.
            </p>

            <?php if (!empty($mensaje)): ?>
                <div class="login-mensaje">
                    <?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?>
                </div>
            <?php endif; ?>

            <form class="login-formulario" method="POST" action="">

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
                        placeholder="Ingresa tu contraseña"
                        required
                    >
                </div>

                <button type="submit" class="boton login-boton">
                    Iniciar sesión
                </button>

            </form>

            <div class="login-registro">
                ¿No tienes una cuenta?
                <a href="registrar.php">Regístrate aquí</a>
            </div>

        </div>

        <a class="login-volver" href="../index.php">
            ← Volver al inicio
        </a>

    </main>

</body>
</html>
