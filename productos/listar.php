
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

try {

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
            ORDER BY p.nombre ASC";

    $consulta = $conexion->query($sql);

    $productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Error al consultar los productos.");

}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Productos - TecnoStock</title>
</head>

<body>

    <h1>Listado de productos</h1>

    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>.</p>

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
                    <th>Acciones</th>

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
                        <td>
                        <a href="editar.php?id=<?php echo $producto['id_producto']; ?>">
        Editar
    </a>

    |

    <a href="desactivar.php?id=<?php echo $producto['id_producto']; ?>">
        Desactivar
    </a>
</td>
                        
                         </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p>No hay productos registrados todavía.</p>

    <?php endif; ?>

    <br>

    <a href="../prueba_panel.php">Volver al panel</a>

</body>

</html>
