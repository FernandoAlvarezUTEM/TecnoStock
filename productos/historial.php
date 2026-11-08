
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$movimientos = [];
$productos = [];
$error = "";

// Obtener filtros
$tipo = $_GET["tipo"] ?? "";

if (!in_array($tipo, ["entrada", "salida"], true)) {
    $tipo = "";
}

$id_producto = filter_input(INPUT_GET, "id_producto", FILTER_VALIDATE_INT);

if (!$id_producto || $id_producto <= 0) {
    $id_producto = null;
}

try {

    // Obtener productos que tienen movimientos
    // Incluye también los productos inactivos
    $consulta_productos = $conexion->query("
        SELECT DISTINCT
            p.id_producto,
            p.codigo,
            p.nombre
        FROM productos p
        INNER JOIN movimientos m
            ON p.id_producto = m.id_producto
        ORDER BY p.nombre ASC
    ");

    $productos = $consulta_productos->fetchAll(PDO::FETCH_ASSOC);

    // Construir consulta del historial
    $sql = "
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
        WHERE 1 = 1
    ";

    $parametros = [];

    // Aplicar filtro por tipo
    if ($tipo !== "") {
        $sql .= " AND m.tipo = ?";
        $parametros[] = $tipo;
    }

    // Aplicar filtro por producto
    if ($id_producto !== null) {
        $sql .= " AND m.id_producto = ?";
        $parametros[] = $id_producto;
    }

    $sql .= "
        ORDER BY m.fecha_hora DESC, m.id_movimiento DESC
    ";

    $consulta = $conexion->prepare($sql);
    $consulta->execute($parametros);

    $movimientos = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = "No se pudo cargar el historial de movimientos.";

}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de movimientos - TecnoStock</title>

    <link rel="stylesheet" href="../assets/css/estilos.css">

    <style>
        .encabezado-pagina {
            margin-bottom: 25px;
        }

        .encabezado-pagina h1 {
            color: #1e293b;
            margin-bottom: 8px;
        }

        .encabezado-pagina p {
            color: #64748b;
            margin: 0;
        }

        .filtros {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 25px;
        }

        .filtros h2 {
            color: #1e293b;
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .filtros-formulario {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .grupo-campo label {
            display: block;
            color: #334155;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .grupo-campo select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: white;
            color: #334155;
            font-size: 14px;
        }

        .acciones-filtros {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .boton-filtrar {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 11px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .boton-filtrar:hover {
            background: #1d4ed8;
        }

        .boton-limpiar {
            background: #e2e8f0;
            color: #334155;
            border-radius: 8px;
            padding: 11px 18px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .boton-limpiar:hover {
            background: #cbd5e1;
        }

        .seccion-historial {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .seccion-historial h2 {
            color: #1e293b;
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .contador-movimientos {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .etiqueta-entrada {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .etiqueta-salida {
            display: inline-block;
            background: #fee2e2;
            color: #b91c1c;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .cantidad-entrada {
            color: #16a34a;
            font-weight: 700;
        }

        .cantidad-salida {
            color: #dc2626;
            font-weight: 700;
        }

        .mensaje-vacio {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 35px 20px;
            text-align: center;
            color: #64748b;
        }

        .mensaje-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 13px;
            margin-bottom: 20px;
        }

        .volver-panel {
            display: inline-block;
            margin-top: 25px;
        }

        @media (max-width: 650px) {
            .filtros-formulario {
                grid-template-columns: 1fr;
            }

            .filtros,
            .seccion-historial {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

    <main class="contenedor">

        <div class="encabezado-pagina">
            <h1>Historial de movimientos</h1>
            <p>Consulta y revisa las entradas y salidas de productos del inventario.</p>
        </div>

        <?php if ($error !== ""): ?>

            <div class="mensaje-error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
            </div>

        <?php else: ?>

            <section class="filtros">

                <h2>Filtrar movimientos</h2>

                <form action="historial.php" method="GET">

                    <div class="filtros-formulario">

                        <div class="grupo-campo">

                            <label for="tipo">Tipo de movimiento</label>

                            <select name="tipo" id="tipo">

                                <option value="">Todos los movimientos</option>

                                <option value="entrada"
                                    <?php echo $tipo === "entrada" ? "selected" : ""; ?>>
                                    Entradas
                                </option>

                                <option value="salida"
                                    <?php echo $tipo === "salida" ? "selected" : ""; ?>>
                                    Salidas
                                </option>

                            </select>

                        </div>

                        <div class="grupo-campo">

                            <label for="id_producto">Producto</label>

                            <select name="id_producto" id="id_producto">

                                <option value="">Todos los productos</option>

                                <?php foreach ($productos as $producto): ?>

                                    <option
                                        value="<?php echo (int) $producto["id_producto"]; ?>"
                                        <?php echo $id_producto === (int) $producto["id_producto"] ? "selected" : ""; ?>
                                    >
                                        <?php
                                        echo htmlspecialchars(
                                            $producto["codigo"] . " - " . $producto["nombre"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                    <div class="acciones-filtros">

                        <button type="submit" class="boton-filtrar">
                            Aplicar filtros
                        </button>

                        <a href="historial.php" class="boton-limpiar">
                            Limpiar filtros
                        </a>

                    </div>

                </form>

            </section>

            <section class="seccion-historial">

                <h2>Movimientos registrados</h2>

                <p class="contador-movimientos">
                    Se encontraron <?php echo count($movimientos); ?> movimientos.
                </p>

                <?php if (count($movimientos) > 0): ?>

                    <div class="tabla-contenedor">

                        <table>

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
                                            <?php echo htmlspecialchars($movimiento["fecha_hora"], ENT_QUOTES, "UTF-8"); ?>
                                        </td>

                                        <td>

                                            <?php if ($movimiento["tipo"] === "entrada"): ?>

                                                <span class="etiqueta-entrada">
                                                    Entrada
                                                </span>

                                            <?php else: ?>

                                                <span class="etiqueta-salida">
                                                    Salida
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $movimiento["codigo"] . " - " . $movimiento["producto"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>
                                        </td>

                                        <td>

                                            <?php if ($movimiento["tipo"] === "entrada"): ?>

                                                <span class="cantidad-entrada">
                                                    +<?php echo (int) $movimiento["cantidad"]; ?>
                                                </span>

                                            <?php else: ?>

                                                <span class="cantidad-salida">
                                                    -<?php echo (int) $movimiento["cantidad"]; ?>
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $movimiento["observacion"] ?? "Sin observación",
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($movimiento["usuario"], ENT_QUOTES, "UTF-8"); ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="mensaje-vacio">
                        No se encontraron movimientos con los filtros seleccionados.
                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>

        <a href="../prueba_panel.php" class="boton-secundario volver-panel">
            Volver al panel
        </a>

    </main>

</body>
</html>
