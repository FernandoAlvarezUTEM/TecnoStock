
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$error = "";

// Obtener el ID del producto
$id_producto = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_producto || $id_producto <= 0) {
    die("Producto no válido.");
}

// Procesar la desactivación
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {

        $consulta = $conexion->prepare("
            UPDATE productos
            SET estado = 0
            WHERE id_producto = ? AND estado = 1
        ");

        $consulta->execute([$id_producto]);

        if ($consulta->rowCount() === 1) {

            header("Location: listar.php?desactivado=1");
            exit;

        } else {

            $error = "El producto no existe o ya está desactivado.";

        }

    } catch (PDOException $e) {

        $error = "No se pudo desactivar el producto.";

    }
}

// Consultar el producto
$consulta = $conexion->prepare("
    SELECT codigo, nombre
    FROM productos
    WHERE id_producto = ? AND estado = 1
");

$consulta->execute([$id_producto]);
$producto = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$producto && $error === "") {
    die("El producto no existe o ya está desactivado.");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desactivar producto - TecnoStock</title>

    <link rel="stylesheet" href="../assets/css/estilos.css">

    <style>
        .confirmacion {
            max-width: 550px;
            margin: 50px auto;
            padding: 35px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .icono-advertencia {
            width: 65px;
            height: 65px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #fef2f2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
        }

        .confirmacion h1 {
            color: #1e293b;
            margin-bottom: 12px;
        }

        .confirmacion p {
            color: #64748b;
            line-height: 1.6;
        }

        .datos-producto {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px;
            margin: 25px 0;
            text-align: left;
        }

        .dato {
            margin-bottom: 12px;
            color: #334155;
        }

        .dato:last-child {
            margin-bottom: 0;
        }

        .acciones {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .boton-peligro {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 11px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .boton-peligro:hover {
            background: #b91c1c;
        }

        .boton-cancelar {
            display: inline-block;
            background: #e2e8f0;
            color: #334155;
            padding: 11px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .boton-cancelar:hover {
            background: #cbd5e1;
        }

        .mensaje-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <main class="confirmacion">

        <div class="icono-advertencia">!</div>

        <h1>Desactivar producto</h1>

        <p>
            ¿Estás seguro de que deseas desactivar este producto?
            Ya no aparecerá en el listado de productos activos,
            pero podrás reactivarlo posteriormente.
        </p>

        <?php if ($error !== ""): ?>

            <div class="mensaje-error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <?php if ($producto): ?>

            <div class="datos-producto">

                <div class="dato">
                    <strong>Código:</strong>
                    <?php echo htmlspecialchars($producto["codigo"]); ?>
                </div>

                <div class="dato">
                    <strong>Nombre:</strong>
                    <?php echo htmlspecialchars($producto["nombre"]); ?>
                </div>

            </div>

            <form method="POST">

                <div class="acciones">

                    <a href="listar.php" class="boton-cancelar">
                        Cancelar
                    </a>

                    <button type="submit" class="boton-peligro">
                        Sí, desactivar producto
                    </button>

                </div>

            </form>

        <?php else: ?>

            <div class="acciones">
                <a href="listar.php" class="boton-cancelar">
                    Volver al listado
                </a>
            </div>

        <?php endif; ?>

    </main>

</body>
</html>
