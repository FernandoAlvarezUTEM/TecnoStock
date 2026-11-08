
<?php

require_once "../config/verificar_sesion.php";
require_once "../config/conexion.php";

$error = "";

// Validar el ID del producto
$id_producto = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_producto || $id_producto <= 0) {
    header("Location: inactivos.php");
    exit;
}

try {

    // Buscar el producto inactivo
    $consulta = $conexion->prepare("
        SELECT id_producto, codigo, nombre
        FROM productos
        WHERE id_producto = ? AND estado = 0
    ");

    $consulta->execute([$id_producto]);
    $producto = $consulta->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Error al consultar el producto.");

}

// Comprobar si existe el producto
if (!$producto) {
    header("Location: inactivos.php");
    exit;
}

// Procesar la reactivación
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirmar"])) {

    try {

        $actualizar = $conexion->prepare("
            UPDATE productos
            SET estado = 1
            WHERE id_producto = ? AND estado = 0
        ");

        $actualizar->execute([$id_producto]);

        if ($actualizar->rowCount() === 1) {

            header("Location: inactivos.php?reactivado=1");
            exit;

        } else {

            $error = "No se pudo reactivar el producto. Comprueba su estado.";

        }

    } catch (PDOException $e) {

        if ($e->getCode() === "23000") {

            $error = "No se pudo reactivar el producto porque su código ya está siendo utilizado.";

        } else {

            $error = "Ocurrió un error al intentar reactivar el producto.";

        }

    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reactivar producto - TecnoStock</title>

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

        .icono-reactivar {
            width: 65px;
            height: 65px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #dcfce7;
            color: #16a34a;
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

        .boton-reactivar {
            display: inline-block;
            background: #16a34a;
            color: white;
            padding: 11px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .boton-reactivar:hover {
            background: #15803d;
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

        <div class="icono-reactivar">✓</div>

        <h1>Reactivar producto</h1>

        <p>
            ¿Estás seguro de que deseas reactivar este producto?
            Volverá a aparecer en el listado de productos activos
            y estará disponible para su gestión.
        </p>

        <?php if ($error !== ""): ?>

            <div class="mensaje-error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

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

        <form action="reactivar.php?id=<?php echo (int) $id_producto; ?>" method="POST">

            <div class="acciones">

                <a href="inactivos.php" class="boton-cancelar">
                    Cancelar
                </a>

                <button type="submit" name="confirmar" class="boton-reactivar">
                    Sí, reactivar producto
                </button>

            </div>

        </form>

    </main>

</body>
</html>
