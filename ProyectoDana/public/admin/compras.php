<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

$sql = "
    SELECT 
        c.*,
        comp.nombre,
        comp.whatsapp,
        comp.email,
        GROUP_CONCAT(DISTINCT r.titulo SEPARATOR ', ') AS rifas,
        GROUP_CONCAT(LPAD(b.numero_boleto, 3, '0') ORDER BY b.numero_boleto SEPARATOR ', ') AS boletos
    FROM compras c
    INNER JOIN compradores comp ON comp.id = c.comprador_id
    LEFT JOIN boletos b ON b.compra_id = c.id
    LEFT JOIN rifas r ON r.id = b.rifa_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$compras = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compras</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Compras</h1>
    <p>Revisa pagos, comprobantes y boletos apartados</p>
</header>

<main class="contenedor">

    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="rifas.php">Rifas</a>
        <a href="compras.php">Compras</a>
        <a href="../index.php">Ver sitio</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <section class="seleccion-boletos">
        <h2>Lista de compras</h2>

        <div class="tabla-responsive">
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>WhatsApp</th>
                        <th>Rifa</th>
                        <th>Boletos</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Comprobante</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($compras as $compra): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($compra["folio_compra"]); ?></td>
                            <td><?php echo htmlspecialchars($compra["nombre"]); ?></td>
                            <td><?php echo htmlspecialchars($compra["whatsapp"]); ?></td>
                            <td><?php echo htmlspecialchars($compra["rifas"] ?? ""); ?></td>
                            <td><?php echo htmlspecialchars($compra["boletos"] ?? ""); ?></td>
                            <td>$<?php echo number_format($compra["total_pagado"], 2); ?></td>
                            <td>
                                <span class="estado estado-<?php echo htmlspecialchars($compra["estado_pago"]); ?>">
                                    <?php echo htmlspecialchars($compra["estado_pago"]); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($compra["comprobante"])): ?>
                                    Sí
                                <?php else: ?>
                                    No
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="btn-tabla" href="ver_compra.php?folio=<?php echo urlencode($compra["folio_compra"]); ?>">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($compras) === 0): ?>
                        <tr>
                            <td colspan="9">No hay compras registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

</body>
</html>