<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

// Consultar productos inactivos
$consulta = $conexion->query("
    SELECT
        p.id_producto,
        p.codigo,
        p.nombre,
        p.precio,
        p.stock_actual,
        p.stock_minimo,
        c.nombre AS categoria
    FROM productos p
    INNER JOIN categorias c
        ON p.id_categoria = c.id_categoria
    WHERE p.estado = 0
    ORDER BY p.nombre ASC
");

$productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos inactivos - TecnoStock</title>
</head>
<body>

    <h1>Productos inactivos</h1>

    <?php if (count($productos) > 0): ?>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Stock actual</th>
                    <th>Stock mínimo</th>
                    <th>Categoría</th>
                    <th>Acción</th>

                </tr>
            </thead>

            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto["codigo"]); ?></td>
                        <td><?php echo htmlspecialchars($producto["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($producto["precio"]); ?></td>
                        <td><?php echo htmlspecialchars($producto["stock_actual"]); ?></td>
                        <td><?php echo htmlspecialchars($producto["stock_minimo"]); ?></td>
                        <td><?php echo htmlspecialchars($producto["categoria"]); ?></td>
                        <td>
    <a href="reactivar.php?id=<?php echo $producto['id_producto']; ?>">
        Reactivar
    </a>
</td>
                        
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <p>No hay productos inactivos.</p>

    <?php endif; ?>

    <br>

    <a href="../prueba_panel.php">Volver al panel</a>

</body>
</html>