<?php

require_once "config/conexion.php";

try {
    $consulta = $conexion->query("SHOW TABLES");

    echo "<h2>Tablas de TecnoStock</h2>";

    while ($tabla = $consulta->fetch(PDO::FETCH_NUM)) {
        echo $tabla[0] . "<br>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>