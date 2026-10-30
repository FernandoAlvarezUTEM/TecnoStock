<?php

session_start();

// Comprobar si existe una sesión iniciada
if (!isset($_SESSION["id_usuario"])) {

    // Si no hay sesión, volver al inicio de sesión
   header("Location: /TecnoStock/usuarios/login.php");
    exit;
}

?>