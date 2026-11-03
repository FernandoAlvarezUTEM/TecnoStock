<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

// Comprobar que se recibió el ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: inactivos.php");
    exit;
}

$id_producto = (int) $_GET["id"];

// Buscar el producto inactivo
$consulta = $conexion->prepare("
    SELECT id_producto, codigo, nombre
    FROM productos
    WHERE id_producto = ? AND estado = 0
");

$consulta->execute([$id_producto]);
$producto = $consulta->fetch(PDO::FETCH_ASSOC);
// Reactivar el producto cuando se confirma
if ($producto && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirmar"])) {

    try {
        $actualizar = $conexion->prepare("
            UPDATE productos
            SET estado = 1
            WHERE id_producto = ? AND estado = 0
        ");

        $actualizar->execute([$id_producto]);

        header("Location: inactivos.php?reactivado=1");
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() === "23000") {
            die("No se pudo reactivar el producto porque su código ya está siendo utilizado por otro producto.");
        }

        throw $e;
    }
}

if (!$producto) {
    header("Location: inactivos.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reactivar producto - TecnoStock</title>
</head>
<body>

    <h1>Reactivar producto</h1>

    <p>
        ¿Estás seguro de que deseas reactivar este producto?
    </p>

    <p>
        Código: <?php echo htmlspecialchars($producto["codigo"]); ?>
    </p>

    <p>
        Nombre: <?php echo htmlspecialchars($producto["nombre"]); ?>
    </p>

    <form action="reactivar.php?id=<?php echo $id_producto; ?>" method="POST">
        <button type="submit" name="confirmar">Sí, reactivar</button>
    </form>

    <br>

    <a href="inactivos.php">Cancelar y volver</a>

</body>
</html>