<?php

require_once __DIR__ . "/config/verificar_sesion.php";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de prueba - TecnoStock</title>
</head>
<body>

    <h1>Bienvenido a TecnoStock</h1>

    <p>Hola, <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>.</p>

    <p>Has iniciado sesión correctamente y puedes acceder al sistema.</p>

    <a href="usuarios/cerrar_sesion.php">Cerrar sesión</a>

</body>
</html>