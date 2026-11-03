<?php
require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

// Consultar el historial de movimientos
$consulta = $conexion->query("
    SELECT
        m.id_movimiento,
        m.fecha_hora,
        m.tipo,
        m.cantidad,
        m.observacion,
        p.codigo,
        p.nombre AS producto,
        u.nombre AS usuario
    FROM movimientos m
    INNER JOIN productos p
        ON m.id_producto = p.id_producto
    INNER JOIN usuarios u
        ON m.id_usuario = u.id_usuario
    ORDER BY m.fecha_hora DESC, m.id_movimiento DESC
");

$movimientos = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de movimientos - TecnoStock</title>
</head>
<body>

    <h1>Historial de movimientos</h1>

    <?php if (count($movimientos) > 0): ?>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Tipo</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Observación</th>
                    <th>Usuario</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($movimientos as $movimiento): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($movimiento["fecha_hora"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars(ucfirst($movimiento["tipo"])); ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $movimiento["codigo"] . " - " .
                                $movimiento["producto"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($movimiento["cantidad"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($movimiento["observacion"] ?? "Sin observación"); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($movimiento["usuario"]); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <p>No hay movimientos registrados todavía.</p>

    <?php endif; ?>

    <br>

    <a href="../prueba_panel.php">Volver al panel</a>

</body>
</html>