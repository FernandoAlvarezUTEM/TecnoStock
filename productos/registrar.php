
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$mensaje = "";
$registro_exitoso = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $codigo = trim($_POST["codigo"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = $_POST["precio"] ?? "";
    $stock_actual = $_POST["stock_actual"] ?? "";
    $stock_minimo = $_POST["stock_minimo"] ?? "";
    $id_categoria = $_POST["id_categoria"] ?? "";

    if (
        $codigo === "" ||
        $nombre === "" ||
        $precio === "" ||
        $stock_actual === "" ||
        $stock_minimo === "" ||
        $id_categoria === ""
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
        !ctype_digit((string)$stock_minimo) ||
        !ctype_digit((string)$id_categoria) ||
        (int)$id_categoria <= 0
    ) {

        $mensaje = "El precio y las cantidades deben ser valores válidos y no negativos.";

    } else {

        try {

            // Comprobar que la categoría exista y esté activa
            $sqlCategoria = "SELECT id_categoria
                             FROM categorias
                             WHERE id_categoria = :id_categoria
                             AND estado = 1";

            $consultaCategoria = $conexion->prepare($sqlCategoria);

            $consultaCategoria->execute([
                ":id_categoria" => $id_categoria
            ]);

            if (!$consultaCategoria->fetch()) {

                $mensaje = "La categoría seleccionada no existe o está inactiva.";

            } else {

                // Registrar el producto
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
                $registro_exitoso = true;
            }

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

$nombre_usuario = htmlspecialchars($_SESSION["nombre_usuario"], ENT_QUOTES, "UTF-8");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar producto - TecnoStock</title>

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

            <h1>Registrar producto</h1>

            <div class="usuario">
                Usuario: <?php echo $nombre_usuario; ?>
            </div>

        </header>

        <section class="introduccion">

            <h2>Agregar al inventario</h2>

            <p>
                Completa la información del producto para incorporarlo
                al inventario de TecnoStock.
            </p>

        </section>

        <section class="formulario-panel">

            <h2>Información del producto</h2>

            <p class="formulario-ayuda">
                Los campos marcados con <span class="campo-obligatorio">*</span>
                son obligatorios.
            </p>

            <?php if (!empty($mensaje)): ?>

                <div class="mensaje-formulario <?php echo $registro_exitoso ? 'mensaje-exito' : 'mensaje-error'; ?>">
                    <?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="">

                <div class="campos-grid">

                    <div class="formulario-grupo">
                        <label for="codigo">
                            Código <span class="campo-obligatorio">*</span>
                        </label>

                        <input
                            type="text"
                            id="codigo"
                            name="codigo"
                            placeholder="Ej: TEC003"
                            value="<?php echo htmlspecialchars($_POST["codigo"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
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
                            placeholder="Nombre del producto"
                            value="<?php echo htmlspecialchars($_POST["nombre"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
                            required
                        >
                    </div>

                    <div class="formulario-grupo campo-completo">
                        <label for="descripcion">Descripción</label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            placeholder="Describe las características del producto..."
                        ><?php echo htmlspecialchars($_POST["descripcion"] ?? "", ENT_QUOTES, "UTF-8"); ?></textarea>
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
                            placeholder="Ej: 15990"
                            value="<?php echo htmlspecialchars($_POST["precio"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
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
                                    <?php echo (($_POST["id_categoria"] ?? "") == $categoria["id_categoria"]) ? "selected" : ""; ?>
                                >
                                    <?php echo htmlspecialchars($categoria["nombre"], ENT_QUOTES, "UTF-8"); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="formulario-grupo">
                        <label for="stock_actual">
                            Stock actual <span class="campo-obligatorio">*</span>
                        </label>

                        <input
                            type="number"
                            id="stock_actual"
                            name="stock_actual"
                            min="0"
                            step="1"
                            placeholder="Cantidad disponible"
                            value="<?php echo htmlspecialchars($_POST["stock_actual"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
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
                            placeholder="Cantidad mínima"
                            value="<?php echo htmlspecialchars($_POST["stock_minimo"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
                            required
                        >
                    </div>

                </div>

                <div class="botones-formulario">

                    <button type="submit" class="boton">
                        Registrar producto
                    </button>

                    <a href="listar.php" class="boton boton-secundario">
                        Ver productos
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
