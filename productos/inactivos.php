
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

try {

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
        AND c.estado = 1
        ORDER BY p.nombre ASC
    ");

    $productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Error al consultar los productos inactivos.");

}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos inactivos - TecnoStock</title>

    <link rel="stylesheet" href="../assets/css/estilos.css">

    <style>
        .encabezado-pagina {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .encabezado-pagina h1 {
            color: #1e293b;
            margin-bottom: 5px;
        }

        .encabezado-pagina p {
            color: #64748b;
            margin: 0;
        }

        .contador {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        .estado-inactivo {
            display: inline-block;
            background: #fee2e2;
            color: #b91c1c;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .boton-reactivar {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 8px 13px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .boton-reactivar:hover {
            background: #1d4ed8;
        }

        .mensaje-vacio {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 35px 20px;
            text-align: center;
            color: #64748b;
        }

        .mensaje-vacio strong {
            display: block;
            color: #334155;
            font-size: 17px;
            margin-bottom: 8px;
        }

        .acciones-inferiores {
            margin-top: 25px;
        }
    </style>
</head>

<body>

    <main class="contenedor">

        <section class="panel">

            <div class="encabezado-pagina">

                <div>
                    <h1>Productos inactivos</h1>
                    <p>Consulta y administra los productos que han sido desactivados.</p>
                </div>

                <span class="contador">
                    <?php echo count($productos); ?>
                    productos inactivos
                </span>

            </div>

            <?php if (count($productos) > 0): ?>

                <div class="tabla-contenedor">

                    <table>

                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock actual</th>
                                <th>Stock mínimo</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($productos as $producto): ?>

                                <tr>

                                    <td>
                                        <?php echo htmlspecialchars($producto["codigo"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($producto["nombre"]); ?>
                                    </td>

                                    <td>
                                        $<?php echo number_format((float) $producto["precio"], 0, ",", "."); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($producto["stock_actual"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($producto["stock_minimo"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($producto["categoria"]); ?>
                                    </td>

                                    <td>
                                        <span class="estado-inactivo">Inactivo</span>
                                    </td>

                                    <td>
                                        <a
                                            href="reactivar.php?id=<?php echo (int) $producto['id_producto']; ?>"
                                            class="boton-reactivar">
                                            Reactivar
                                        </a>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="mensaje-vacio">
                    <strong>No hay productos inactivos</strong>
                    Actualmente no existen productos desactivados para mostrar.
                </div>

            <?php endif; ?>

            <div class="acciones-inferiores">
                <a href="../prueba_panel.php" class="boton-secundario">
                    Volver al panel
                </a>
            </div>

        </section>

    </main>

</body>
</html>
