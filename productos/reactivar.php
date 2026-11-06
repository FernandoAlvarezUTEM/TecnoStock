
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$error = "";

// Validar el ID del producto
$id_producto = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_producto || $id_producto <= 0) {
    header("Location: inactivos.php");
    exit;
}

try {

    // Buscar el producto inactivo
    $consulta = $conexion->prepare("
        SELECT id_producto, codigo, nombre
        FROM productos
        WHERE id_producto = ? AND estado = 0
    ");

    $consulta->execute([$id_producto]);
    $producto = $consulta->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Error al consultar el producto.");

}

// Comprobar si existe el producto
if (!$producto) {
    header("Location: inactivos.php");
    exit;
}

// Procesar la reactivación
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirmar"])) {

    try {

        $actualizar = $conexion->prepare("
            UPDATE productos
            SET estado = 1
            WHERE id_producto = ? AND estado = 0
        ");

        $actualizar->execute([$id_producto]);

        if ($actualizar->rowCount() === 1) {

            header("Location: inactivos.php?reactivado=1");
            exit;

        } else {

            $error = "No se pudo reactivar el producto. Comprueba su estado.";

        }

    } catch (PDOException $e) {

        if ($e->getCode() === "23000") {

            $error = "No se pudo reactivar el producto porque su código ya está siendo utilizado.";

        } else {

            $error = "Ocurrió un error al intentar reactivar el producto.";

        }

    }
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

    <?php if ($error !== ""): ?>

        <p style="color: red;">
            <?php echo htmlspecialchars($error); ?>
        </p>

    <?php endif; ?>

    <p>
        ¿Estás seguro de que deseas reactivar este producto?
    </p>

    <p>
        <strong>Código:</strong>
        <?php echo htmlspecialchars($producto["codigo"]); ?>
    </p>

    <p>
        <strong>Nombre:</strong>
        <?php echo htmlspecialchars($producto["nombre"]); ?>
    </p>

    <form action="reactivar.php?id=<?php echo $id_producto; ?>" method="POST">

        <button type="submit" name="confirmar">
            Sí, reactivar
        </button>

    </form>

    <br>

    <a href="inactivos.php">Cancelar y volver</a>

</body>
</html>
