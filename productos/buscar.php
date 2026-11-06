
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$busqueda = "";
$id_categoria = "";
$productos = [];
$categorias = [];
$error = "";

try {

    // Obtener las categorías activas
    $sqlCategorias = "SELECT id_categoria, nombre
                     FROM categorias
                     WHERE estado = 1
                     ORDER BY nombre ASC";

    $consultaCategorias = $conexion->query($sqlCategorias);
    $categorias = $consultaCategorias->fetchAll(PDO::FETCH_ASSOC);

    // Comprobar si el usuario presionó Buscar
    if (isset($_GET["buscar"])) {

        $busqueda = trim($_GET["busqueda"] ?? "");
        $id_categoria = $_GET["id_categoria"] ?? "";

        // Validar que la categoría sea un número entero positivo
        if ($id_categoria !== "") {

            if (
                !ctype_digit((string)$id_categoria) ||
                (int)$id_categoria <= 0
            ) {
                $error = "La categoría seleccionada no es válida.";
            } else {

                // Comprobar que la categoría exista y esté activa
                $sqlValidar = "SELECT id_categoria
                               FROM categorias
                               WHERE id_categoria = ?
                               AND estado = 1";

                $consultaValidar = $conexion->prepare($sqlValidar);
                $consultaValidar->execute([$id_categoria]);

                if (!$consultaValidar->fetch()) {
                    $error = "La categoría seleccionada no existe o está inactiva.";
                }
            }
        }

        // Realizar búsqueda si no hay errores
        if ($error === "") {

            $sql = "SELECT
                        p.id_producto,
                        p.codigo,
                        p.nombre,
                        p.descripcion,
                        p.precio,
                        p.stock_actual,
                        p.stock_minimo,
                        c.nombre AS categoria
                    FROM productos p
                    INNER JOIN categorias c
                        ON p.id_categoria = c.id_categoria
                    WHERE p.estado = 1
                    AND c.estado = 1";

            $parametros = [];

            // Buscar por nombre o código
            if ($busqueda !== "") {

                $sql .= " AND (
                            p.nombre LIKE :busqueda
                            OR p.codigo LIKE :busqueda
                          )";

                $parametros[":busqueda"] = "%" . $busqueda . "%";
            }

            // Filtrar por categoría
            if ($id_categoria !== "") {

                $sql .= " AND p.id_categoria = :id_categoria";

                $parametros[":id_categoria"] = $id_categoria;
            }

            $sql .= " ORDER BY p.nombre ASC";

            $consulta = $conexion->prepare($sql);
            $consulta->execute($parametros);

            $productos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        }
    }

} catch (PDOException $e) {

    $error = "Ocurrió un error al buscar los productos.";

}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Buscar productos - TecnoStock</title>
</head>

<body>

    <h1>Buscar productos</h1>

    <?php if ($error !== ""): ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <form method="GET" action="">

        <label>Nombre o código:</label>

        <input
            type="text"
            name="busqueda"
            value="<?php echo htmlspecialchars($busqueda); ?>"
            placeholder="Ejemplo: Mouse o TEC001"
        >

        <br><br>

        <label>Categoría:</label>

        <select name="id_categoria">

            <option value="">Todas las categorías</option>

            <?php foreach ($categorias as $categoria): ?>

                <option
                    value="<?php echo $categoria["id_categoria"]; ?>"
                    <?php echo ($id_categoria == $categoria["id_categoria"]) ? "selected" : ""; ?>
                >
                    <?php echo htmlspecialchars($categoria["nombre"]); ?>
                </option>

            <?php endforeach; ?>

        </select>

        <br><br>

        <button type="submit" name="buscar" value="1">Buscar</button>

        <a href="buscar.php">Limpiar búsqueda</a>

    </form>

    <hr>

    <h2>Resultados</h2>

    <?php if (count($productos) > 0): ?>

        <table border="1" cellpadding="8">

            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock actual</th>
                    <th>Stock mínimo</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($productos as $producto): ?>

                    <tr>
                        <td><?php echo htmlspecialchars($producto["codigo"]); ?></td>
                        <td><?php echo htmlspecialchars($producto["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($producto["descripcion"] ?? ""); ?></td>
                        <td><?php echo htmlspecialchars($producto["categoria"]); ?></td>
                        <td>$<?php echo number_format($producto["precio"], 0, ",", "."); ?></td>
                        <td><?php echo $producto["stock_actual"]; ?></td>
                        <td><?php echo $producto["stock_minimo"]; ?></td>
                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php elseif ($error === ""): ?>

        <p>No se encontraron productos.</p>

    <?php endif; ?>

    <br>

    <a href="listar.php">Volver al listado</a>

    <br><br>

    <a href="../prueba_panel.php">Volver al panel</a>

</body>

</html>
