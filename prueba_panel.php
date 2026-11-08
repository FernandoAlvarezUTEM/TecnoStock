
<?php

require_once __DIR__ . "/config/verificar_sesion.php";

$nombre_usuario = htmlspecialchars($_SESSION["nombre_usuario"], ENT_QUOTES, "UTF-8");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - TecnoStock</title>

    <link rel="stylesheet" href="assets/css/estilos.css">

    <style>
        body {
            min-height: 100vh;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #172554;
            color: white;
            padding: 28px 18px;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .logo {
            font-size: 23px;
            font-weight: 800;
            padding: 0 12px 35px;
            color: white;
        }

        .logo span {
            color: #60a5fa;
        }

        .menu-titulo {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 0 12px;
            margin-bottom: 12px;
        }

        .menu a {
            display: block;
            color: #dbeafe;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: 0.2s;
        }

        .menu a:hover {
            background: #1e40af;
            color: white;
        }

        .cerrar-sesion {
            margin-top: auto;
            display: block;
            padding: 12px;
            border-radius: 8px;
            background: #1e3a8a;
            color: white;
            text-align: center;
        }

        .cerrar-sesion:hover {
            background: #1d4ed8;
            color: white;
        }

        .contenido {
            flex: 1;
            min-width: 0;
            padding: 35px;
        }

        .barra-superior {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 35px;
            flex-wrap: wrap;
        }

        .barra-superior h1 {
            margin: 0;
            font-size: 25px;
        }

        .usuario {
            background: white;
            padding: 11px 17px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-weight: 600;
        }

        .bienvenida {
            background: linear-gradient(120deg, #2563eb, #1e40af);
            color: white;
            padding: 30px;
            border-radius: 14px;
            margin-bottom: 30px;
        }

        .bienvenida h2 {
            color: white;
            font-size: 25px;
            margin: 0 0 10px;
        }

        .bienvenida p {
            color: #dbeafe;
            margin: 0;
            line-height: 1.7;
        }

        .seccion-titulo {
            margin-bottom: 18px;
            font-size: 19px;
        }

        .opciones {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .opcion {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 23px;
            transition: 0.2s;
        }

        .opcion:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 18px rgba(15, 23, 42, 0.08);
        }

        .opcion h3 {
            font-size: 16px;
            margin: 0 0 9px;
        }

        .opcion p {
            color: #64748b;
            line-height: 1.6;
            margin: 0 0 18px;
            min-height: 44px;
        }

        .opcion a {
            font-weight: 600;
        }

        .pie {
            color: #94a3b8;
            font-size: 12px;
            margin-top: 35px;
        }

        @media (max-width: 700px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .logo {
                padding-bottom: 20px;
            }

            .menu {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 5px;
            }

            .cerrar-sesion {
                margin-top: 18px;
            }

            .contenido {
                padding: 20px 15px;
            }
        }
    </style>
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <div class="logo">Tecno<span>Stock</span></div>

        <div class="menu-titulo">MENÚ PRINCIPAL</div>

        <nav class="menu">
            <a href="productos/registrar.php">＋ Registrar producto</a>
            <a href="productos/listar.php">▤ Listar productos</a>
            <a href="productos/inactivos.php">◷ Productos inactivos</a>
            <a href="productos/buscar.php">⌕ Buscar productos</a>
            <a href="productos/movimientos.php">↕ Registrar movimiento</a>
            <a href="productos/historial.php">▧ Historial de movimientos</a>
        </nav>

        <a class="cerrar-sesion" href="usuarios/cerrar_sesion.php">
            Cerrar sesión
        </a>

    </aside>

    <main class="contenido">

        <header class="barra-superior">
            <h1>Panel principal</h1>

            <div class="usuario">
                Usuario: <?php echo $nombre_usuario; ?>
            </div>
        </header>

        <section class="bienvenida">

            <h2>¡Hola, <?php echo $nombre_usuario; ?>!</h2>

            <p>
                Has iniciado sesión correctamente.
                Desde aquí puedes administrar los productos y controlar
                los movimientos de inventario de TecnoStock.
            </p>

        </section>

        <h2 class="seccion-titulo">Gestión de inventario</h2>

        <section class="opciones">

            <article class="opcion">
                <h3>Registrar producto</h3>
                <p>Incorpora nuevos productos al inventario.</p>
                <a href="productos/registrar.php">Acceder →</a>
            </article>

            <article class="opcion">
                <h3>Listar productos</h3>
                <p>Consulta los productos disponibles y sus existencias.</p>
                <a href="productos/listar.php">Acceder →</a>
            </article>

            <article class="opcion">
                <h3>Productos inactivos</h3>
                <p>Consulta y administra los productos desactivados.</p>
                <a href="productos/inactivos.php">Acceder →</a>
            </article>

            <article class="opcion">
                <h3>Buscar productos</h3>
                <p>Encuentra productos mediante los filtros de búsqueda.</p>
                <a href="productos/buscar.php">Acceder →</a>
            </article>

            <article class="opcion">
                <h3>Registrar movimiento</h3>
                <p>Registra entradas y salidas de productos.</p>
                <a href="productos/movimientos.php">Acceder →</a>
            </article>

            <article class="opcion">
                <h3>Historial de movimientos</h3>
                <p>Revisa los movimientos registrados en el inventario.</p>
                <a href="productos/historial.php">Acceder →</a>
            </article>

        </section>

        <footer class="pie">
            TecnoStock · Sistema de gestión de inventario
        </footer>

    </main>

</div>

</body>
</html>
