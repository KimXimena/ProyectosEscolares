<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

$folio = $_GET["folio"] ?? "";

if (empty($folio)) {
    die("Folio no válido.");
}

$sql = "
    SELECT 
        c.*,
        comp.nombre,
        comp.whatsapp,
        comp.email
    FROM compras c
    INNER JOIN compradores comp ON comp.id = c.comprador_id
    WHERE c.folio_compra = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$folio]);
$compra = $stmt->fetch();

if (!$compra) {
    die("Compra no encontrada.");
}

$sqlBoletos = "
    SELECT 
        b.numero_boleto,
        b.folio_boleto,
        b.estado AS estado_boleto,
        r.titulo AS titulo_rifa
    FROM boletos b
    INNER JOIN rifas r ON r.id = b.rifa_id
    WHERE b.compra_id = ?
    ORDER BY b.numero_boleto ASC
";

$stmt = $pdo->prepare($sqlBoletos);
$stmt->execute([$compra["id"]]);
$boletos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver compra</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Detalle de compra</h1>
    <p><?php echo htmlspecialchars($compra["folio_compra"]); ?></p>
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

        <h2>Datos del comprador</h2>

        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($compra["nombre"]); ?></p>
        <p><strong>WhatsApp:</strong> <?php echo htmlspecialchars($compra["whatsapp"]); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($compra["email"] ?? ""); ?></p>
        <p><strong>Folio:</strong> <?php echo htmlspecialchars($compra["folio_compra"]); ?></p>
        <p><strong>Total:</strong> $<?php echo number_format($compra["total_pagado"], 2); ?></p>
        <p>
            <strong>Estado de pago:</strong>
            <span class="estado estado-<?php echo htmlspecialchars($compra["estado_pago"]); ?>">
                <?php echo htmlspecialchars($compra["estado_pago"]); ?>
            </span>
        </p>

        <h3>Boletos</h3>

        <div class="resumen-boletos">
            <?php foreach ($boletos as $boleto): ?>
                <span class="boleto-resumen">
                    <?php echo str_pad($boleto["numero_boleto"], 3, "0", STR_PAD_LEFT); ?>
                </span>
            <?php endforeach; ?>
        </div>

        <h3>Comprobante</h3>

        <?php if (!empty($compra["comprobante"])): ?>
            <p>
                <a class="btn" href="../uploads/comprobantes/<?php echo htmlspecialchars($compra["comprobante"]); ?>" target="_blank">
                    Ver comprobante
                </a>
            </p>
        <?php else: ?>
            <p>No se ha subido comprobante.</p>
        <?php endif; ?>

        <a href="../boleto.php?folio=<?php echo urlencode($compra["folio_compra"]); ?>" class="btn" target="_blank">
            Ver boleto digital
        </a>

        <h3>Actualizar estado de pago</h3>

        <form action="actualizar_pago.php" method="POST" class="formulario-compra">
            <input type="hidden" name="folio" value="<?php echo htmlspecialchars($compra["folio_compra"]); ?>">

            <label>Nuevo estado</label>
            <select name="estado_pago" required>
                <option value="pendiente" <?php echo $compra["estado_pago"] === "pendiente" ? "selected" : ""; ?>>
                    Pendiente
                </option>
                <option value="aprobado" <?php echo $compra["estado_pago"] === "aprobado" ? "selected" : ""; ?>>
                    Aprobado
                </option>
                <option value="rechazado" <?php echo $compra["estado_pago"] === "rechazado" ? "selected" : ""; ?>>
                    Rechazado
                </option>
                <option value="cancelado" <?php echo $compra["estado_pago"] === "cancelado" ? "selected" : ""; ?>>
                    Cancelado
                </option>
            </select>

            <button type="submit" class="btn">
                Guardar cambios
            </button>
        </form>

    </section>

</main>

</body>
</html>