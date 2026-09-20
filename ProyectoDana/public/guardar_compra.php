<?php
require_once __DIR__ . "/../config/conexion.php";

$rifaId = $_POST["rifa_id"] ?? null;
$boletosParam = $_POST["boletos"] ?? "";
$nombre = trim($_POST["nombre"] ?? "");
$whatsapp = trim($_POST["whatsapp"] ?? "");
$email = trim($_POST["email"] ?? "");

if (!$rifaId || empty($boletosParam) || empty($nombre) || empty($whatsapp)) {
    die("Faltan datos obligatorios.");
}

$boletosIds = array_filter(explode(",", $boletosParam));

if (count($boletosIds) === 0) {
    die("No hay boletos seleccionados.");
}

try {
    $pdo->beginTransaction();

    // 1. Buscar rifa
    $stmt = $pdo->prepare("
        SELECT *
        FROM rifas
        WHERE id = ?
        AND estado = 'activa'
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute([$rifaId]);
    $rifa = $stmt->fetch();

    if (!$rifa) {
        throw new Exception("La rifa no existe o ya no está activa.");
    }

    // 2. Bloquear boletos seleccionados para evitar doble venta
    $placeholders = implode(",", array_fill(0, count($boletosIds), "?"));

    $sqlBoletos = "
        SELECT *
        FROM boletos
        WHERE id IN ($placeholders)
        AND rifa_id = ?
        FOR UPDATE
    ";

    $params = array_merge($boletosIds, [$rifaId]);

    $stmt = $pdo->prepare($sqlBoletos);
    $stmt->execute($params);
    $boletos = $stmt->fetchAll();

    if (count($boletos) !== count($boletosIds)) {
        throw new Exception("Uno o más boletos no pertenecen a esta rifa.");
    }

    foreach ($boletos as $boleto) {
        if ($boleto["estado"] !== "disponible") {
            throw new Exception("El boleto " . $boleto["numero_boleto"] . " ya no está disponible.");
        }
    }

    // 3. Crear o actualizar comprador
    $sqlComprador = "
        INSERT INTO compradores (nombre, whatsapp, email)
        VALUES (:nombre, :whatsapp, :email)
        ON DUPLICATE KEY UPDATE
            nombre = VALUES(nombre),
            email = VALUES(email),
            updated_at = CURRENT_TIMESTAMP
    ";

    $stmt = $pdo->prepare($sqlComprador);
    $stmt->execute([
        ":nombre" => $nombre,
        ":whatsapp" => $whatsapp,
        ":email" => $email
    ]);

    // 4. Obtener comprador
    $stmt = $pdo->prepare("
        SELECT id
        FROM compradores
        WHERE whatsapp = ?
        LIMIT 1
    ");
    $stmt->execute([$whatsapp]);
    $comprador = $stmt->fetch();

    if (!$comprador) {
        throw new Exception("No se pudo registrar el comprador.");
    }

    $compradorId = $comprador["id"];

    // 5. Crear compra
    $cantidad = count($boletos);
    $total = $cantidad * $rifa["precio_boleto"];
    $folioCompra = "COMP-" . date("YmdHis") . "-" . rand(1000, 9999);

    $sqlCompra = "
        INSERT INTO compras (
            folio_compra,
            comprador_id,
            cantidad_boletos,
            total_pagado,
            estado_pago,
            fecha_expiracion_apartado
        ) VALUES (
            :folio_compra,
            :comprador_id,
            :cantidad_boletos,
            :total_pagado,
            'pendiente',
            DATE_ADD(NOW(), INTERVAL 24 HOUR)
        )
    ";

    $stmt = $pdo->prepare($sqlCompra);
    $stmt->execute([
        ":folio_compra" => $folioCompra,
        ":comprador_id" => $compradorId,
        ":cantidad_boletos" => $cantidad,
        ":total_pagado" => $total
    ]);

    $compraId = $pdo->lastInsertId();

    // 6. Apartar boletos
    $sqlActualizarBoleto = "
        UPDATE boletos
        SET 
            compra_id = :compra_id,
            estado = 'apartado',
            reservado_hasta = DATE_ADD(NOW(), INTERVAL 24 HOUR),
            updated_at = CURRENT_TIMESTAMP
        WHERE id = :boleto_id
        AND estado = 'disponible'
    ";

    $stmtActualizar = $pdo->prepare($sqlActualizarBoleto);

    foreach ($boletos as $boleto) {
        $stmtActualizar->execute([
            ":compra_id" => $compraId,
            ":boleto_id" => $boleto["id"]
        ]);

        if ($stmtActualizar->rowCount() !== 1) {
            throw new Exception("No se pudo apartar el boleto " . $boleto["numero_boleto"]);
        }
    }

    $pdo->commit();

    header("Location: comprobante.php?folio=" . urlencode($folioCompra));
    exit;

} catch (Exception $e) {
    $pdo->rollBack();

    echo "Error al registrar la compra: " . htmlspecialchars($e->getMessage());
}