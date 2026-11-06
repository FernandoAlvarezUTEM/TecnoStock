
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$error = "";
$mensaje = "";

// Obtener el ID del producto
$id_producto = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_producto || $id_producto <= 0) {
    die("Producto no válido.");
}

// Consultar el producto
$consulta = $conexion->prepare("
    SELECT *
    FROM productos
    WHERE id_producto = ? AND estado = 1
");

$consulta->execute([$id_producto]);
$producto = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("El producto no existe o está inactivo.");
}

// Guardar cambios
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $codigo = trim($_POST["codigo"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");

    $precio = filter_input(INPUT_POST, "precio", FILTER_VALIDATE_FLOAT);
    $stock_minimo = filter_input(INPUT_POST, "stock_minimo", FILTER_VALIDATE_INT);
    $id_categoria = filter_input(INPUT_POST, "id_categoria", FILTER_VALIDATE_INT);

    if (
        $codigo === "" ||
        $nombre === "" ||
        $precio === false || $precio === null || $precio < 0 ||
        $stock_minimo === false || $stock_minimo === null || $stock_minimo < 0 ||
        $id_categoria === false || $id_categoria === null || $id_categoria <= 0
    ) {

        $error = "Completa correctamente todos los campos obligatorios.";

    } else {

        try {

            // Comprobar que la categoría exista y esté activa
            $consultaCategoria = $conexion->prepare("
                SELECT id_categoria
                FROM categorias
                WHERE id_categoria = ? AND estado = 1
            ");

            $consultaCategoria->execute([$id_categoria]);

            if (!$consultaCategoria->fetch()) {

                $error = "La categoría seleccionada no existe o está inactiva.";

            } else {

                // Actualizar el producto
                $actualizar = $conexion->prepare("
                    UPDATE productos
                    SET codigo = ?,
                        nombre = ?,
                        descripcion = ?,
                        precio = ?,
                        stock_minimo = ?,
                        id_categoria = ?
                    WHERE id_producto = ? AND estado = 1
                ");

                $actualizar->execute([
                    $codigo,
                    $nombre,
                    $descripcion,
                    $precio,
                    $stock_minimo,
                    $id_categoria,
                    $id_producto
                ]);

                $mensaje = "Producto actualizado correctamente.";

                // Actualizar los datos mostrados
                $consulta->execute([$id_producto]);
                $producto = $consulta->fetch(PDO::FETCH_ASSOC);

            }

        } catch (PDOException $e) {

            if ($e->getCode() === "23000") {
                $error = "Ese código de producto ya está registrado.";
            } else {
                $error = "Ocurrió un error al actualizar el producto.";
            }
        }
    }
}

// Consultar categorías activas
$consulta_categorias = $conexion->query("
    SELECT id_categoria, nombre
    FROM categorias
    WHERE estado = 1
    ORDER BY nombre ASC
");

$categorias = $consulta_categorias->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar producto - TecnoStock</title>
</head>

<body>

    <h1>Editar producto</h1>

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

        <label>Código:</label><br>
        <input type="text" name="codigo"
            value="<?php echo htmlspecialchars($producto["codigo"]); ?>"
            required>

        <br><br>

        <label>Nombre:</label><br>
        <input type="text" name="nombre"
            value="<?php echo htmlspecialchars($producto["nombre"]); ?>"
            required>

        <br><br>

        <label>Descripción:</label><br>
        <textarea name="descripcion"><?php
            echo htmlspecialchars($producto["descripcion"] ?? "");
        ?></textarea>

        <br><br>

        <label>Precio:</label><br>
        <input type="number" name="precio" min="0" step="0.01"
            value="<?php echo htmlspecialchars($producto["precio"]); ?>"
            required>

        <br><br>

        <label>Stock mínimo:</label><br>
        <input type="number" name="stock_minimo" min="0" step="1"
            value="<?php echo htmlspecialchars($producto["stock_minimo"]); ?>"
            required>

        <br><br>

        <label>Categoría:</label><br>

        <select name="id_categoria" required>

            <option value="">Selecciona una categoría</option>

            <?php foreach ($categorias as $categoria): ?>

                <option value="<?php echo $categoria["id_categoria"]; ?>"
                    <?php
                    echo $categoria["id_categoria"] == $producto["id_categoria"]
                        ? "selected"
                        : "";
                    ?>>

                    <?php echo htmlspecialchars($categoria["nombre"]); ?>

                </option>

            <?php endforeach; ?>

        </select>

        <br><br>

        <button type="submit">Guardar cambios</button>

    </form>

    <br>

    <a href="listar.php">Volver al listado</a>

</body>

</html>
