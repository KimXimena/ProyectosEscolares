<?php
require_once __DIR__ . "/../config/conexion.php";

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
        r.titulo AS titulo_rifa,
        r.slug
    FROM boletos b
    INNER JOIN rifas r ON r.id = b.rifa_id
    WHERE b.compra_id = ?
    ORDER BY b.numero_boleto ASC
";

$stmt = $pdo->prepare($sqlBoletos);
$stmt->execute([$compra["id"]]);
$boletos = $stmt->fetchAll();

$sqlCuenta = "
    SELECT *
    FROM cuentas_pago
    WHERE activo = 1
    LIMIT 1
";

$stmt = $pdo->prepare($sqlCuenta);
$stmt->execute();
$cuenta = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir comprobante</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Boletos apartados</h1>
    <p>Sube tu comprobante para validar tu compra</p>
</header>

<main class="contenedor">

    <section class="seleccion-boletos">
        <h2>Resumen de apartado</h2>

        <p><strong>Folio de compra:</strong> <?php echo htmlspecialchars($compra["folio_compra"]); ?></p>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($compra["nombre"]); ?></p>
        <p><strong>WhatsApp:</strong> <?php echo htmlspecialchars($compra["whatsapp"]); ?></p>
        <p><strong>Estado de pago:</strong> <?php echo htmlspecialchars($compra["estado_pago"]); ?></p>

        <p class="premio">
            Total a pagar: $<?php echo number_format($compra["total_pagado"], 2); ?>
        </p>

        <h3>Boletos apartados</h3>

        <div class="resumen-boletos">
            <?php foreach ($boletos as $boleto): ?>
                <span class="boleto-resumen">
                    <?php echo str_pad($boleto["numero_boleto"], 3, "0", STR_PAD_LEFT); ?>
                </span>
            <?php endforeach; ?>
        </div>

        <?php if ($cuenta): ?>
            <h3>Datos de pago</h3>

            <div class="datos-pago">
                <p><strong>Banco:</strong> <?php echo htmlspecialchars($cuenta["banco"]); ?></p>
                <p><strong>Titular:</strong> <?php echo htmlspecialchars($cuenta["titular"]); ?></p>

                <?php if (!empty($cuenta["numero_cuenta"])): ?>
                    <p><strong>Número de cuenta:</strong> <?php echo htmlspecialchars($cuenta["numero_cuenta"]); ?></p>
                <?php endif; ?>

                <?php if (!empty($cuenta["clabe"])): ?>
                    <p><strong>CLABE:</strong> <?php echo htmlspecialchars($cuenta["clabe"]); ?></p>
                <?php endif; ?>

                <?php if (!empty($cuenta["tarjeta"])): ?>
                    <p><strong>Tarjeta:</strong> <?php echo htmlspecialchars($cuenta["tarjeta"]); ?></p>
                <?php endif; ?>

                <p><?php echo nl2br(htmlspecialchars($cuenta["instrucciones"])); ?></p>
            </div>
        <?php endif; ?>

        <h3>Subir comprobante</h3>

        <?php if (!empty($compra["comprobante"])): ?>
            <p>Ya subiste un comprobante. Está pendiente de revisión.</p>
        <?php else: ?>
            <form action="subir_comprobante.php" method="POST" enctype="multipart/form-data" class="formulario-compra">
                <input type="hidden" name="folio" value="<?php echo htmlspecialchars($compra["folio_compra"]); ?>">

                <label>Comprobante de pago</label>
                <input type="file" name="comprobante" accept="image/*,.pdf" required>

                <button type="submit" class="btn">
                    Subir comprobante
                </button>
            </form>
        <?php endif; ?>

        <a href="boleto.php?folio=<?php echo urlencode($compra["folio_compra"]); ?>" class="btn" target="_blank">
            Ver boleto digital
        </a>

        <a href="index.php" class="btn">
            Volver al inicio
        </a>
    </section>

</main>

</body>
</html>