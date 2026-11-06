
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$error = "";

// Obtener el ID del producto
$id_producto = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_producto || $id_producto <= 0) {
    die("Producto no válido.");
}

// Procesar la desactivación
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {

        $consulta = $conexion->prepare("
            UPDATE productos
            SET estado = 0
            WHERE id_producto = ? AND estado = 1
        ");

        $consulta->execute([$id_producto]);

        if ($consulta->rowCount() === 1) {

            header("Location: listar.php?desactivado=1");
            exit;

        } else {

            $error = "El producto no existe o ya está desactivado.";

        }

    } catch (PDOException $e) {

        $error = "No se pudo desactivar el producto.";

    }
}

// Consultar el producto
$consulta = $conexion->prepare("
    SELECT codigo, nombre
    FROM productos
    WHERE id_producto = ? AND estado = 1
");

$consulta->execute([$id_producto]);
$producto = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$producto && $error === "") {
    die("El producto no existe o ya está desactivado.");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Desactivar producto - TecnoStock</title>
</head>

<body>

    <h1>Desactivar producto</h1>

    <?php if ($error !== ""): ?>

        <p style="color: red;">
            <?php echo htmlspecialchars($error); ?>
        </p>

    <?php endif; ?>

    <?php if ($producto): ?>

        <p>¿Estás seguro de que deseas desactivar este producto?</p>

        <p>
            <strong>Código:</strong>
            <?php echo htmlspecialchars($producto["codigo"]); ?>
        </p>

        <p>
            <strong>Nombre:</strong>
            <?php echo htmlspecialchars($producto["nombre"]); ?>
        </p>

        <form method="POST">
            <button type="submit">Sí, desactivar producto</button>
        </form>

    <?php endif; ?>

    <br>

    <a href="listar.php">Cancelar y volver al listado</a>

</body>
</html>
