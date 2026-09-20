<?php
require_once __DIR__ . "/../config/conexion.php";

$folio = $_GET["folio"] ?? "";

if (empty($folio)) {
    die("Folio no válido.");
}

$sql = "
    SELECT 
        c.folio_compra,
        c.estado_pago,
        c.total_pagado,
        c.created_at,
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
        r.titulo,
        r.codigo_rifa,
        r.premio_tipo,
        r.premio_nombre,
        r.premio_monto,
        r.fecha_sorteo,
        r.metodo_ganador,
        r.base_sorteo
    FROM boletos b
    INNER JOIN rifas r ON r.id = b.rifa_id
    WHERE b.compra_id = (
        SELECT id FROM compras WHERE folio_compra = ? LIMIT 1
    )
    ORDER BY b.numero_boleto ASC
";

$stmt = $pdo->prepare($sqlBoletos);
$stmt->execute([$folio]);
$boletos = $stmt->fetchAll();

if (count($boletos) === 0) {
    die("No hay boletos asociados a esta compra.");
}

$rifa = $boletos[0];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleto <?php echo htmlspecialchars($compra["folio_compra"]); ?></title>
    <link rel="stylesheet" href="css/estilos.css">

    <style>
        body {
            background: #f4f4f4;
        }

        .boleto-digital {
            max-width: 850px;
            margin: 40px auto;
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.18);
            border: 2px solid #111;
        }

        .boleto-header {
            background: #111;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .boleto-header h1 {
            margin-bottom: 8px;
        }

        .boleto-body {
            padding: 30px;
        }

        .boleto-datos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 25px;
        }

        .dato-box {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 10px;
        }

        .dato-box strong {
            display: block;
            margin-bottom: 5px;
        }

        .numeros-boleto {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }

        .numero-ticket {
            background: #111;
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 20px;
            font-weight: bold;
        }

        .estado-ticket {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 30px;
            font-weight: bold;
            margin-top: 10px;
        }

        .estado-ticket.aprobado {
            background: #d4edda;
            color: #155724;
        }

        .estado-ticket.pendiente {
            background: #fff3cd;
            color: #856404;
        }

        .estado-ticket.rechazado,
        .estado-ticket.cancelado {
            background: #f8d7da;
            color: #721c24;
        }

        .boleto-footer {
            border-top: 1px dashed #999;
            padding: 20px 30px;
            font-size: 14px;
            color: #555;
        }

        .acciones-boleto {
            max-width: 850px;
            margin: 20px auto;
            display: flex;
            gap: 10px;
        }

        @media print {
            .acciones-boleto,
            .whatsapp,
            .verificar-flotante,
            .ganadores-flotante {
                display: none !important;
            }

            body {
                background: white;
            }

            .boleto-digital {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .boleto-datos {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="boleto-digital">

    <div class="boleto-header">
        <h1>Boleto de Rifa</h1>
        <p><?php echo htmlspecialchars($rifa["titulo"]); ?></p>
    </div>

    <div class="boleto-body">

        <div class="boleto-datos">
            <div class="dato-box">
                <strong>Folio de compra</strong>
                <?php echo htmlspecialchars($compra["folio_compra"]); ?>
            </div>

            <div class="dato-box">
                <strong>Estado de pago</strong>
                <span class="estado-ticket <?php echo htmlspecialchars($compra["estado_pago"]); ?>">
                    <?php echo htmlspecialchars($compra["estado_pago"]); ?>
                </span>
            </div>

            <div class="dato-box">
                <strong>Nombre</strong>
                <?php echo htmlspecialchars($compra["nombre"]); ?>
            </div>

            <div class="dato-box">
                <strong>WhatsApp</strong>
                <?php echo htmlspecialchars($compra["whatsapp"]); ?>
            </div>

            <div class="dato-box">
                <strong>Premio</strong>
                <?php if ($rifa["premio_tipo"] === "efectivo"): ?>
                    $<?php echo number_format($rifa["premio_monto"], 2); ?>
                <?php else: ?>
                    <?php echo htmlspecialchars($rifa["premio_nombre"]); ?>
                    <?php if (!empty($rifa["premio_monto"])): ?>
                        <br>Valor estimado: $<?php echo number_format($rifa["premio_monto"], 2); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="dato-box">
                <strong>Fecha de sorteo</strong>
                <?php echo !empty($rifa["fecha_sorteo"]) ? htmlspecialchars($rifa["fecha_sorteo"]) : "Por definir"; ?>
            </div>
        </div>

        <h2>Números de boleto</h2>

        <div class="numeros-boleto">
            <?php foreach ($boletos as $boleto): ?>
                <span class="numero-ticket">
                    <?php echo str_pad($boleto["numero_boleto"], 3, "0", STR_PAD_LEFT); ?>
                </span>
            <?php endforeach; ?>
        </div>

        <h3>Folios de boleto</h3>

        <?php foreach ($boletos as $boleto): ?>
            <p>
                <strong><?php echo str_pad($boleto["numero_boleto"], 3, "0", STR_PAD_LEFT); ?>:</strong>
                <?php echo htmlspecialchars($boleto["folio_boleto"]); ?>
            </p>
        <?php endforeach; ?>

        <?php if ($compra["estado_pago"] !== "aprobado"): ?>
            <p style="color:#c1121f; font-weight:bold; margin-top:20px;">
                Este boleto aún no está confirmado. Será válido cuando el pago sea aprobado por administración.
            </p>
        <?php endif; ?>

    </div>

    <div class="boleto-footer">
        <p>
            Método de selección:
            <?php echo htmlspecialchars($rifa["metodo_ganador"] ?? "Por definir"); ?>
        </p>

        <p>
            Base del sorteo:
            <?php echo htmlspecialchars($rifa["base_sorteo"] ?? "Por definir"); ?>
        </p>

        <p>
            Conserva este boleto como comprobante de participación.
        </p>
    </div>

</div>

<div class="acciones-boleto">
    <button onclick="window.print()" class="btn">
        Imprimir / Guardar como PDF
    </button>

    <a href="index.php" class="btn">
        Volver al inicio
    </a>
</div>

</body>
</html>