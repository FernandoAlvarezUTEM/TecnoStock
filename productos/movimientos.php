
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$mensaje = "";
$error = "";

// Registrar movimiento
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tipo = $_POST["tipo"] ?? "";
    $id_producto = filter_input(INPUT_POST, "id_producto", FILTER_VALIDATE_INT);
    $cantidad = filter_input(INPUT_POST, "cantidad", FILTER_VALIDATE_INT);
    $observacion = trim($_POST["observacion"] ?? "");
    $id_usuario = $_SESSION["id_usuario"];

    if (!in_array($tipo, ["entrada", "salida"], true)) {
        $error = "Selecciona un tipo de movimiento válido.";
    } elseif (
        $id_producto === false || $id_producto === null || $id_producto <= 0 ||
        $cantidad === false || $cantidad === null || $cantidad <= 0
    ) {
        $error = "Selecciona un producto y una cantidad entera válida mayor que cero.";
    } else {

        try {
            $conexion->beginTransaction();

            // Consultar y bloquear el producto
            $consulta = $conexion->prepare("
                SELECT stock_actual
                FROM productos
                WHERE id_producto = ? AND estado = 1
                FOR UPDATE
            ");

            $consulta->execute([$id_producto]);
            $producto = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception(
                    "El producto seleccionado no existe o está inactivo."
                );
            }

            $stock_actual = (int) $producto["stock_actual"];

            // Validar que una salida no supere el stock
            if ($tipo === "salida" && $cantidad > $stock_actual) {
                throw new Exception(
                    "No hay stock suficiente para realizar esta salida."
                );
            }

            // Calcular nuevo stock
            if ($tipo === "entrada") {
                $nuevo_stock = $stock_actual + $cantidad;
            } else {
                $nuevo_stock = $stock_actual - $cantidad;
            }

            // Registrar movimiento
            $insertar = $conexion->prepare("
                INSERT INTO movimientos
                (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario)
                VALUES (NOW(), ?, ?, ?, ?, ?)
            ");

            $insertar->execute([
                $tipo,
                $cantidad,
                $observacion,
                $id_producto,
                $id_usuario
            ]);

            // Actualizar stock
            $actualizar = $conexion->prepare("
                UPDATE productos
                SET stock_actual = ?
                WHERE id_producto = ? AND estado = 1
            ");

            $actualizar->execute([
                $nuevo_stock,
                $id_producto
            ]);

            if ($actualizar->rowCount() !== 1) {
                throw new Exception(
                    "No se pudo actualizar el stock del producto."
                );
            }

            $conexion->commit();

            $mensaje = "Movimiento registrado correctamente. Nuevo stock: " . $nuevo_stock;

        } catch (PDOException $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $error = "Ocurrió un error al registrar el movimiento. Inténtalo nuevamente.";

        } catch (Exception $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}

// Cargar productos activos
$consulta = $conexion->query("
    SELECT id_producto, codigo, nombre, stock_actual
    FROM productos
    WHERE estado = 1
    ORDER BY nombre ASC
");

$productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos de inventario - TecnoStock</title>

    <link rel="stylesheet" href="../assets/css/estilos.css">

    <style>
        .formulario-movimiento {
            max-width: 650px;
            margin: 35px auto;
            padding: 30px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
        }

        .encabezado-formulario {
            margin-bottom: 25px;
        }

        .encabezado-formulario h1 {
            color: #1e293b;
            margin-bottom: 8px;
        }

        .encabezado-formulario p {
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }

        .grupo-campo {
            margin-bottom: 20px;
        }

        .grupo-campo label {
            display: block;
            color: #334155;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .grupo-campo input,
        .grupo-campo select,
        .grupo-campo textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            color: #334155;
            background: white;
        }

        .grupo-campo input:focus,
        .grupo-campo select:focus,
        .grupo-campo textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .grupo-campo textarea {
            resize: vertical;
            min-height: 100px;
        }

        .ayuda-campo {
            display: block;
            color: #94a3b8;
            font-size: 12px;
            margin-top: 6px;
        }

        .boton-registrar {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .boton-registrar:hover {
            background: #1d4ed8;
        }

        .mensaje-exito {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .mensaje-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .volver-panel {
            display: inline-block;
            margin-top: 22px;
        }

        @media (max-width: 600px) {
            .formulario-movimiento {
                padding: 20px;
                margin: 20px auto;
            }
        }
    </style>
</head>

<body>

    <main class="contenedor">

        <section class="formulario-movimiento">

            <div class="encabezado-formulario">
                <h1>Movimientos de inventario</h1>
                <p>
                    Registra las entradas y salidas de productos
                    para mantener actualizado el inventario.
                </p>
            </div>

            <?php if ($mensaje !== ""): ?>

                <div class="mensaje-exito">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>

            <?php endif; ?>

            <?php if ($error !== ""): ?>

                <div class="mensaje-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="grupo-campo">
                    <label for="tipo">Tipo de movimiento</label>

                    <select name="tipo" id="tipo" required>
                        <option value="">Selecciona una opción</option>
                        <option value="entrada">Entrada de productos</option>
                        <option value="salida">Salida de productos</option>
                    </select>
                </div>

                <div class="grupo-campo">
                    <label for="id_producto">Producto</label>

                    <select name="id_producto" id="id_producto" required>
                        <option value="">Selecciona un producto</option>

                        <?php foreach ($productos as $producto): ?>

                            <option value="<?php echo (int) $producto["id_producto"]; ?>">
                                <?php
                                echo htmlspecialchars(
                                    $producto["codigo"] . " - " .
                                    $producto["nombre"] . " (Stock: " .
                                    $producto["stock_actual"] . ")"
                                );
                                ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="grupo-campo">
                    <label for="cantidad">Cantidad</label>

                    <input
                        type="number"
                        name="cantidad"
                        id="cantidad"
                        min="1"
                        step="1"
                        placeholder="Ingresa la cantidad"
                        required
                    >

                    <span class="ayuda-campo">
                        La cantidad debe ser un número entero mayor que cero.
                    </span>
                </div>

                <div class="grupo-campo">
                    <label for="observacion">Observación</label>

                    <textarea
                        name="observacion"
                        id="observacion"
                        placeholder="Escribe una observación (opcional)"
                    ></textarea>
                </div>

                <button type="submit" class="boton-registrar">
                    Registrar movimiento
                </button>

            </form>

            <a href="../prueba_panel.php" class="boton-secundario volver-panel">
                Volver al panel
            </a>

        </section>

    </main>

</body>
</html>
