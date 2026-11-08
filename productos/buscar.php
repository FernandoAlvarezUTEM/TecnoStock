
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
                            p.nombre LIKE :nombre   
                            OR p.codigo LIKE :codigo
                          )";

                $parametros[":nombre"] = "%" . $busqueda . "%";
                $parametros[":codigo"] = "%" . $busqueda . "%";

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar productos - TecnoStock</title>

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

        .tarjeta-busqueda {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 25px;
        }

        .tarjeta-busqueda h2,
        .resultados h2 {
            color: #1e293b;
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .formulario-busqueda {
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

        .grupo-campo input,
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

        .grupo-campo input:focus,
        .grupo-campo select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .acciones-busqueda {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .boton-buscar {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 11px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .boton-buscar:hover {
            background: #1d4ed8;
        }

        .boton-limpiar {
            background: #e2e8f0;
            color: #334155;
            border-radius: 8px;
            padding: 11px 20px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .boton-limpiar:hover {
            background: #cbd5e1;
        }

        .resultados {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .contador-resultados {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .mensaje-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 13px;
            margin-bottom: 20px;
        }

        .mensaje-vacio {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 30px 20px;
            text-align: center;
            color: #64748b;
        }

        .stock-bajo {
            color: #dc2626;
            font-weight: 700;
        }

        .stock-normal {
            color: #16a34a;
            font-weight: 600;
        }

        .volver {
            display: inline-block;
            margin-top: 25px;
            margin-right: 10px;
        }

        @media (max-width: 650px) {
            .formulario-busqueda {
                grid-template-columns: 1fr;
            }

            .tarjeta-busqueda,
            .resultados {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

    <main class="contenedor">

        <div class="encabezado-pagina">
            <h1>Buscar productos</h1>
            <p>Encuentra productos del inventario por nombre, código o categoría.</p>
        </div>

        <?php if ($error !== ""): ?>

            <div class="mensaje-error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
            </div>

        <?php endif; ?>

        <section class="tarjeta-busqueda">

            <h2>Filtros de búsqueda</h2>

            <form method="GET" action="">

                <div class="formulario-busqueda">

                    <div class="grupo-campo">

                        <label for="busqueda">Nombre o código</label>

                        <input
                            type="text"
                            name="busqueda"
                            id="busqueda"
                            value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8"); ?>"
                            placeholder="Ejemplo: Mouse o TEC001"
                        >

                    </div>

                    <div class="grupo-campo">

                        <label for="id_categoria">Categoría</label>

                        <select name="id_categoria" id="id_categoria">

                            <option value="">Todas las categorías</option>

                            <?php foreach ($categorias as $categoria): ?>

                                <option
                                    value="<?php echo (int) $categoria["id_categoria"]; ?>"
                                    <?php echo ($id_categoria == $categoria["id_categoria"]) ? "selected" : ""; ?>
                                >
                                    <?php echo htmlspecialchars($categoria["nombre"], ENT_QUOTES, "UTF-8"); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

                <div class="acciones-busqueda">

                    <button type="submit" name="buscar" value="1" class="boton-buscar">
                        Buscar productos
                    </button>

                    <a href="buscar.php" class="boton-limpiar">
                        Limpiar búsqueda
                    </a>

                </div>

            </form>

        </section>

        <section class="resultados">

            <h2>Resultados de búsqueda</h2>

            <?php if (count($productos) > 0): ?>

                <p class="contador-resultados">
                    Se encontraron <?php echo count($productos); ?> productos.
                </p>

                <div class="tabla-contenedor">

                    <table>

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

                                    <td>
                                        <?php echo htmlspecialchars($producto["codigo"], ENT_QUOTES, "UTF-8"); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($producto["nombre"], ENT_QUOTES, "UTF-8"); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($producto["descripcion"] ?? "", ENT_QUOTES, "UTF-8"); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($producto["categoria"], ENT_QUOTES, "UTF-8"); ?>
                                    </td>

                                    <td>
                                        $<?php echo number_format((float) $producto["precio"], 0, ",", "."); ?>
                                    </td>

                                    <td>
                                        <?php if ((int) $producto["stock_actual"] < (int) $producto["stock_minimo"]): ?>

                                            <span class="stock-bajo">
                                                <?php echo (int) $producto["stock_actual"]; ?>
                                            </span>

                                        <?php else: ?>

                                            <span class="stock-normal">
                                                <?php echo (int) $producto["stock_actual"]; ?>
                                            </span>

                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php echo (int) $producto["stock_minimo"]; ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php elseif ($error === ""): ?>

                <div class="mensaje-vacio">
                    No se encontraron productos con los criterios seleccionados.
                </div>

            <?php endif; ?>

        </section>

        <a href="listar.php" class="boton-secundario volver">
            Volver al listado
        </a>

        <a href="../prueba_panel.php" class="boton-secundario volver">
            Volver al panel
        </a>

    </main>

</body>
</html>
