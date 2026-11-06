
<?php

// Iniciar sesión solamente si todavía no existe una sesión activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Comprobar que exista un ID de usuario válido
if (
    !isset($_SESSION["id_usuario"]) ||
    !filter_var($_SESSION["id_usuario"], FILTER_VALIDATE_INT) ||
    $_SESSION["id_usuario"] <= 0
) {

    // Cerrar la sesión si existe
    $_SESSION = [];

    session_destroy();

    // Redirigir al inicio de sesión
    header("Location: /TecnoStock/usuarios/login.php");
    exit;
}

?>
