
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $codigo = trim($_POST["codigo"]);
    $nombre = trim($_POST["nombre"]);
    $descripcion = trim($_POST["descripcion"]);
    $precio = $_POST["precio"];
    $stock_actual = $_POST["stock_actual"];
    $stock_minimo = $_POST["stock_minimo"];
    $id_categoria = $_POST["id_categoria"];

    if (
        empty($codigo) ||
        empty($nombre) ||
        $precio === "" ||
        $stock_actual === "" ||
        $stock_minimo === "" ||
        empty($id_categoria)
    ) {

        $mensaje = "Debes completar todos los campos obligatorios.";

    } elseif (
        !is_numeric($precio) ||
        !is_numeric($stock_actual) ||
        !is_numeric($stock_minimo) ||
        $precio < 0 ||
        $stock_actual < 0 ||
        $stock_minimo < 0 ||
        !ctype_digit((string)$stock_actual) ||
        !ctype_digit((string)$stock_minimo)
    ) {

        $mensaje = "El precio y las cantidades deben ser valores válidos y no negativos.";

    } else {

        try {

            $sql = "INSERT INTO productos
                    (codigo, nombre, descripcion, precio,
                     stock_actual, stock_minimo, id_categoria)
                    VALUES
                    (:codigo, :nombre, :descripcion, :precio,
                     :stock_actual, :stock_minimo, :id_categoria)";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                ":codigo" => $codigo,
                ":nombre" => $nombre,
                ":descripcion" => $descripcion,
                ":precio" => $precio,
                ":stock_actual" => $stock_actual,
                ":stock_minimo" => $stock_minimo,
                ":id_categoria" => $id_categoria
            ]);

            $mensaje = "¡Producto registrado correctamente!";

        } catch (PDOException $e) {

            if ($e->getCode() == "23000") {
                $mensaje = "El código ya existe o la categoría seleccionada no es válida.";
            } else {
                $mensaje = "Ocurrió un error al registrar el producto.";
            }
        }
    }
}

// Obtener las categorías activas
$sqlCategorias = "SELECT id_categoria, nombre
                  FROM categorias
                  WHERE estado = 1
                  ORDER BY nombre ASC";

$consultaCategorias = $conexion->query($sqlCategorias);
$categorias = $consultaCategorias->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar producto - TecnoStock</title>
</head>

<body>

    <h1>Registrar producto</h1>

    <p><?php echo htmlspecialchars($mensaje); ?></p>

    <form method="POST" action="">

        <label>Código:</label>
        <input type="text" name="codigo" required>

        <br><br>

        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <br><br>

        <label>Descripción:</label>
        <textarea name="descripcion"></textarea>

        <br><br>

        <label>Precio:</label>
        <input type="number" name="precio" min="0" step="0.01" required>

        <br><br>

        <label>Stock actual:</label>
        <input type="number" name="stock_actual" min="0" step="1" required>

        <br><br>

        <label>Stock mínimo:</label>
        <input type="number" name="stock_minimo" min="0" step="1" required>

        <br><br>

        <label>Categoría:</label>

        <select name="id_categoria" required>
            <option value="">Selecciona una categoría</option>

            <?php foreach ($categorias as $categoria): ?>

                <option value="<?php echo $categoria["id_categoria"]; ?>">
                    <?php echo htmlspecialchars($categoria["nombre"]); ?>
                </option>

            <?php endforeach; ?>

        </select>

        <br><br>

        <button type="submit">Registrar producto</button>

    </form>

    <br>

    <a href="listar.php">Ver productos</a>

    <br><br>

    <a href="../prueba_panel.php">Volver al panel</a>

</body>

</html>
