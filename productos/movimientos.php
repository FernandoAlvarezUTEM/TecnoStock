
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$mensaje = "";
$error = "";

// Registrar movimiento
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tipo = $_POST["tipo"] ?? "";
    $id_producto = filter_input(INPUT_POST, "id_producto", FILTER_VALIDATE_INT);
    $cantidad = filter_input(INPUT_POST, "cantidad", FILTER_VALIDATE_INT);
    $observacion = trim($_POST["observacion"] ?? "");
    $id_usuario = $_SESSION["id_usuario"];

    if (!in_array($tipo, ["entrada", "salida"], true)) {
        $error = "Selecciona un tipo de movimiento válido.";
    } elseif (
        $id_producto === false || $id_producto === null || $id_producto <= 0 ||
        $cantidad === false || $cantidad === null || $cantidad <= 0
    ) {
        $error = "Selecciona un producto y una cantidad entera válida mayor que cero.";
    } else {

        try {
            $conexion->beginTransaction();

            // Consultar y bloquear el producto
            $consulta = $conexion->prepare("
                SELECT stock_actual
                FROM productos
                WHERE id_producto = ? AND estado = 1
                FOR UPDATE
            ");

            $consulta->execute([$id_producto]);
            $producto = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception(
                    "El producto seleccionado no existe o está inactivo."
                );
            }

            $stock_actual = (int) $producto["stock_actual"];

            // Validar que una salida no supere el stock
            if ($tipo === "salida" && $cantidad > $stock_actual) {
                throw new Exception(
                    "No hay stock suficiente para realizar esta salida."
                );
            }

            // Calcular nuevo stock
            if ($tipo === "entrada") {
                $nuevo_stock = $stock_actual + $cantidad;
            } else {
                $nuevo_stock = $stock_actual - $cantidad;
            }

            // Registrar movimiento
            $insertar = $conexion->prepare("
                INSERT INTO movimientos
                (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario)
                VALUES (NOW(), ?, ?, ?, ?, ?)
            ");

            $insertar->execute([
                $tipo,
                $cantidad,
                $observacion,
                $id_producto,
                $id_usuario
            ]);

            // Actualizar stock
            $actualizar = $conexion->prepare("
                UPDATE productos
                SET stock_actual = ?
                WHERE id_producto = ? AND estado = 1
            ");

            $actualizar->execute([
                $nuevo_stock,
                $id_producto
            ]);

            if ($actualizar->rowCount() !== 1) {
                throw new Exception(
                    "No se pudo actualizar el stock del producto."
                );
            }

            $conexion->commit();

            $mensaje = "Movimiento registrado correctamente. Nuevo stock: " . $nuevo_stock;

        } catch (PDOException $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $error = "Ocurrió un error al registrar el movimiento. Inténtalo nuevamente.";

        } catch (Exception $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}

// Cargar productos activos
$consulta = $conexion->query("
    SELECT id_producto, codigo, nombre, stock_actual
    FROM productos
    WHERE estado = 1
    ORDER BY nombre ASC
");

$productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimientos de inventario - TecnoStock</title>
</head>
<body>

    <h1>Movimientos de inventario</h1>

    <?php if ($mensaje !== ""): ?>
        <p style="color: green;">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Tipo de movimiento:</label>
        <select name="tipo" required>
            <option value="">Selecciona una opción</option>
            <option value="entrada">Entrada</option>
            <option value="salida">Salida</option>
        </select>

        <br><br>

        <label>Producto:</label>
        <select name="id_producto" required>
            <option value="">Selecciona un producto</option>

            <?php foreach ($productos as $producto): ?>
                <option value="<?php echo $producto["id_producto"]; ?>">
                    <?php
                    echo htmlspecialchars(
                        $producto["codigo"] . " - " .
                        $producto["nombre"] . " (Stock: " .
                        $producto["stock_actual"] . ")"
                    );
                    ?>
                </option>
            <?php endforeach; ?>

        </select>

        <br><br>

        <label>Cantidad:</label>
        <input type="number" name="cantidad" min="1" step="1" required>

        <br><br>

        <label>Observación:</label>
        <textarea name="observacion"></textarea>

        <br><br>

        <button type="submit">Registrar movimiento</button>

    </form>

    <br>

    <a href="../prueba_panel.php">Volver al panel</a>

</body>
</html>
