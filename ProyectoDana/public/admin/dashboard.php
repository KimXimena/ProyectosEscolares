<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM rifas");
$totalRifas = $stmt->fetch()["total"];

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM compras");
$totalCompras = $stmt->fetch()["total"];

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM compras
    WHERE estado_pago = 'pendiente'
");
$comprasPendientes = $stmt->fetch()["total"];

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM compras
    WHERE estado_pago = 'aprobado'
");
$comprasAprobadas = $stmt->fetch()["total"];

$stmt = $pdo->query("
    SELECT COALESCE(SUM(total_pagado), 0) AS total
    FROM compras
    WHERE estado_pago = 'aprobado'
");
$totalRecaudado = $stmt->fetch()["total"];

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM boletos
    WHERE estado = 'vendido'
");
$boletosVendidos = $stmt->fetch()["total"];

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM boletos
    WHERE estado = 'apartado'
");
$boletosApartados = $stmt->fetch()["total"];

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM boletos
    WHERE estado = 'disponible'
");
$boletosDisponibles = $stmt->fetch()["total"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Dashboard</h1>
    <p>Bienvenida, <?php echo htmlspecialchars($_SESSION["admin_nombre"]); ?></p>
</header>

<main class="contenedor">

    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="rifas.php">Rifas</a>
        <a href="compras.php">Compras</a>
        <a href="ganadores.php">Ganadores</a>
        <a href="cuentas_pago.php">Cuentas de pago</a>
        <a href="liberar_apartados.php">Liberar apartados</a>
        <a href="../index.php">Ver sitio</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <section class="admin-grid">
        <div class="admin-card">
            <h3>Rifas</h3>
            <p><?php echo $totalRifas; ?></p>
        </div>

        <div class="admin-card">
            <h3>Compras totales</h3>
            <p><?php echo $totalCompras; ?></p>
        </div>

        <div class="admin-card">
            <h3>Pagos pendientes</h3>
            <p><?php echo $comprasPendientes; ?></p>
        </div>

        <div class="admin-card">
            <h3>Pagos aprobados</h3>
            <p><?php echo $comprasAprobadas; ?></p>
        </div>

        <div class="admin-card">
            <h3>Total recaudado</h3>
            <p>$<?php echo number_format($totalRecaudado, 2); ?></p>
        </div>

        <div class="admin-card">
            <h3>Boletos vendidos</h3>
            <p><?php echo $boletosVendidos; ?></p>
        </div>

        <div class="admin-card">
            <h3>Boletos apartados</h3>
            <p><?php echo $boletosApartados; ?></p>
        </div>

        <div class="admin-card">
            <h3>Boletos disponibles</h3>
            <p><?php echo $boletosDisponibles; ?></p>
        </div>
    </section>

</main>

</body>
</html>