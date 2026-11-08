
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
            AND c.estado = 1
            ORDER BY p.nombre ASC";

    $consulta = $conexion->query($sql);
    $productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Error al consultar los productos.");

}

$nombre_usuario = htmlspecialchars($_SESSION["nombre_usuario"], ENT_QUOTES, "UTF-8");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - TecnoStock</title>

    <link rel="stylesheet" href="../assets/css/estilos.css">

    <style>
        .pagina {
            max-width: 1500px;
            margin: 0 auto;
            padding: 30px;
        }

        .barra-superior {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        .barra-superior h1 {
            margin: 0;
            font-size: 26px;
        }

        .usuario {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            padding: 11px 16px;
            color: #475569;
            font-weight: 600;
        }

        .introduccion {
            margin-bottom: 25px;
        }

        .introduccion p {
            color: #64748b;
            margin-top: 7px;
        }

        .tabla-panel {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .tabla-encabezado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .tabla-encabezado h2 {
            font-size: 18px;
            margin: 0;
        }

        .cantidad-productos {
            color: #64748b;
            font-size: 13px;
        }

        .tabla-contenedor {
            overflow-x: auto;
        }

        .tabla-productos {
            min-width: 950px;
        }

        .tabla-productos td {
            vertical-align: middle;
        }

        .codigo {
            color: #475569;
            font-weight: 600;
        }

        .nombre-producto {
            font-weight: 600;
            color: #1e293b;
        }

        .stock-bajo {
            display: inline-block;
            margin-left: 6px;
            padding: 5px 8px;
            border-radius: 6px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 10px;
            font-weight: 700;
        }

        .stock-normal {
            color: #15803d;
            font-weight: 600;
        }

        .acciones {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .accion {
            display: inline-block;
            padding: 7px 10px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
        }

        .accion-editar {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .accion-editar:hover {
            background: #bfdbfe;
            color: #1e40af;
        }

        .accion-desactivar {
            background: #fee2e2;
            color: #b91c1c;
        }

        .accion-desactivar:hover {
            background: #fecaca;
            color: #991b1b;
        }

        .sin-productos {
            padding: 35px 15px;
            text-align: center;
            color: #64748b;
        }

        .pie {
            margin-top: 25px;
        }

        @media (max-width: 600px) {
            .pagina {
                padding: 18px 12px;
            }

            .tabla-panel {
                padding: 14px;
            }

            .barra-superior h1 {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

    <main class="pagina">

        <header class="barra-superior">

            <h1>Listado de productos</h1>

            <div class="usuario">
                Usuario: <?php echo $nombre_usuario; ?>
            </div>

        </header>

        <section class="introduccion">

            <h2>Inventario de TecnoStock</h2>

            <p>
                Consulta los productos registrados, revisa sus existencias
                y administra la información del inventario.
            </p>

        </section>

        <section class="tabla-panel">

            <div class="tabla-encabezado">

                <h2>Productos registrados</h2>

                <span class="cantidad-productos">
                    <?php echo count($productos); ?>
                    productos encontrados
                </span>

            </div>

            <?php if (count($productos) > 0): ?>

                <div class="tabla-contenedor">

                    <table class="tabla-productos">

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

                                    <td class="codigo">
                                        <?php echo htmlspecialchars($producto["codigo"], ENT_QUOTES, "UTF-8"); ?>
                                    </td>

                                    <td class="nombre-producto">
                                        <?php echo htmlspecialchars($producto["nombre"], ENT_QUOTES, "UTF-8"); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($producto["descripcion"] ?? "", ENT_QUOTES, "UTF-8"); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($producto["categoria"], ENT_QUOTES, "UTF-8"); ?>
                                    </td>

                                    <td>
                                        $<?php echo number_format((float)$producto["precio"], 0, ",", "."); ?>
                                    </td>

                                    <td>

                                        <?php echo htmlspecialchars($producto["stock_actual"], ENT_QUOTES, "UTF-8"); ?>

                                        <?php if ($producto["stock_actual"] < $producto["stock_minimo"]): ?>

                                            <span class="stock-bajo">
                                                STOCK BAJO
                                            </span>

                                        <?php else: ?>

                                            <span class="stock-normal">
                                                Normal
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($producto["stock_minimo"], ENT_QUOTES, "UTF-8"); ?>
                                    </td>

                                    <td>

                                        <div class="acciones">

                                            <a
                                                class="accion accion-editar"
                                                href="editar.php?id=<?php echo (int)$producto['id_producto']; ?>"
                                            >
                                                Editar
                                            </a>

                                            <a
                                                class="accion accion-desactivar"
                                                href="desactivar.php?id=<?php echo (int)$producto['id_producto']; ?>"
                                            >
                                                Desactivar
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="sin-productos">
                    <h3>No hay productos registrados</h3>
                    <p>Cuando registres productos, aparecerán en este listado.</p>
                </div>

            <?php endif; ?>

        </section>

        <div class="pie">
            <a href="../prueba_panel.php" class="boton boton-secundario">
                ← Volver al panel principal
            </a>
        </div>

    </main>

</body>
</html>
