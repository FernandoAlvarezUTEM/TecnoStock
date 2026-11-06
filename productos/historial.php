
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
    <title>Historial de movimientos - TecnoStock</title>
</head>

<body>

    <h1>Historial de movimientos</h1>

    <?php if ($error !== ""): ?>

        <p style="color: red;">
            <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
        </p>

    <?php else: ?>

        <h2>Filtrar movimientos</h2>

        <form action="historial.php" method="GET">

            <label for="tipo">Tipo de movimiento:</label>

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

            <br><br>

            <label for="id_producto">Producto:</label>

            <select name="id_producto" id="id_producto">

                <option value="">Todos los productos</option>

                <?php foreach ($productos as $producto): ?>

                    <option
                        value="<?php echo (int) $producto["id_producto"]; ?>"
                        <?php echo (int) $id_producto === (int) $producto["id_producto"] ? "selected" : ""; ?>
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

            <br><br>

            <button type="submit">Aplicar filtros</button>

            <a href="historial.php">Limpiar filtros</a>

        </form>

        <hr>

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
                                <?php echo htmlspecialchars($movimiento["fecha_hora"], ENT_QUOTES, "UTF-8"); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(ucfirst($movimiento["tipo"]), ENT_QUOTES, "UTF-8"); ?>
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
                                <?php echo htmlspecialchars((string) $movimiento["cantidad"], ENT_QUOTES, "UTF-8"); ?>
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

        <?php else: ?>

            <p>No se encontraron movimientos con los filtros seleccionados.</p>

        <?php endif; ?>

    <?php endif; ?>

    <br>

    <a href="../prueba_panel.php">Volver al panel</a>

</body>
</html>
