
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

$nombre_usuario = htmlspecialchars($_SESSION["nombre_usuario"], ENT_QUOTES, "UTF-8");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar producto - TecnoStock</title>

    <link rel="stylesheet" href="../assets/css/estilos.css">

    <style>
        .pagina {
            max-width: 1100px;
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
            line-height: 1.6;
        }

        .formulario-panel {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 13px;
            padding: 28px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .formulario-panel h2 {
            font-size: 19px;
            margin: 0 0 8px;
        }

        .formulario-ayuda {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 25px;
        }

        .campos-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 22px;
        }

        .campo-completo {
            grid-column: 1 / -1;
        }

        .formulario-panel textarea {
            min-height: 105px;
            resize: vertical;
        }

        .campo-obligatorio {
            color: #dc2626;
        }

        .mensaje-formulario {
            padding: 13px 16px;
            margin-bottom: 22px;
            border-radius: 8px;
            font-weight: 500;
        }

        .mensaje-exito {
            background: #dcfce7;
            color: #166534;
        }

        .mensaje-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .botones-formulario {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .pie {
            margin-top: 22px;
        }

        @media (max-width: 650px) {
            .pagina {
                padding: 18px 12px;
            }

            .campos-grid {
                grid-template-columns: 1fr;
            }

            .campo-completo {
                grid-column: auto;
            }

            .formulario-panel {
                padding: 20px;
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

            <h1>Editar producto</h1>

            <div class="usuario">
                Usuario: <?php echo $nombre_usuario; ?>
            </div>

        </header>

        <section class="introduccion">

            <h2>Modificar información</h2>

            <p>
                Actualiza los datos del producto seleccionado.
                Los cambios se guardarán en el inventario de TecnoStock.
            </p>

        </section>

        <section class="formulario-panel">

            <h2>Información del producto</h2>

            <p class="formulario-ayuda">
                Los campos marcados con <span class="campo-obligatorio">*</span>
                son obligatorios.
            </p>

            <?php if ($mensaje !== ""): ?>

                <div class="mensaje-formulario mensaje-exito">
                    <?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?>
                </div>

            <?php endif; ?>

            <?php if ($error !== ""): ?>

                <div class="mensaje-formulario mensaje-error">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="campos-grid">

                    <div class="formulario-grupo">
                        <label for="codigo">
                            Código <span class="campo-obligatorio">*</span>
                        </label>

                        <input
                            type="text"
                            id="codigo"
                            name="codigo"
                            value="<?php echo htmlspecialchars($producto["codigo"], ENT_QUOTES, "UTF-8"); ?>"
                            required
                        >
                    </div>

                    <div class="formulario-grupo">
                        <label for="nombre">
                            Nombre <span class="campo-obligatorio">*</span>
                        </label>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="<?php echo htmlspecialchars($producto["nombre"], ENT_QUOTES, "UTF-8"); ?>"
                            required
                        >
                    </div>

                    <div class="formulario-grupo campo-completo">
                        <label for="descripcion">Descripción</label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                        ><?php echo htmlspecialchars($producto["descripcion"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>
                    </div>

                    <div class="formulario-grupo">
                        <label for="precio">
                            Precio <span class="campo-obligatorio">*</span>
                        </label>

                        <input
                            type="number"
                            id="precio"
                            name="precio"
                            min="0"
                            step="0.01"
                            value="<?php echo htmlspecialchars($producto["precio"], ENT_QUOTES, "UTF-8"); ?>"
                            required
                        >
                    </div>

                    <div class="formulario-grupo">
                        <label for="stock_minimo">
                            Stock mínimo <span class="campo-obligatorio">*</span>
                        </label>

                        <input
                            type="number"
                            id="stock_minimo"
                            name="stock_minimo"
                            min="0"
                            step="1"
                            value="<?php echo htmlspecialchars($producto["stock_minimo"], ENT_QUOTES, "UTF-8"); ?>"
                            required
                        >
                    </div>

                    <div class="formulario-grupo">
                        <label for="id_categoria">
                            Categoría <span class="campo-obligatorio">*</span>
                        </label>

                        <select id="id_categoria" name="id_categoria" required>

                            <option value="">Selecciona una categoría</option>

                            <?php foreach ($categorias as $categoria): ?>

                                <option
                                    value="<?php echo $categoria["id_categoria"]; ?>"
                                    <?php
                                    echo $categoria["id_categoria"] == $producto["id_categoria"]
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    <?php echo htmlspecialchars($categoria["nombre"], ENT_QUOTES, "UTF-8"); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>

                </div>

                <div class="botones-formulario">

                    <button type="submit" class="boton">
                        Guardar cambios
                    </button>

                    <a href="listar.php" class="boton boton-secundario">
                        Volver al listado
                    </a>

                </div>

            </form>

        </section>

        <div class="pie">
            <a href="../prueba_panel.php" class="boton boton-secundario">
                ← Volver al panel principal
            </a>
        </div>

    </main>

</body>
</html>
