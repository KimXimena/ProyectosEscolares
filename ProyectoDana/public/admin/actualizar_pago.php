<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

$folio = $_POST["folio"] ?? "";
$estadoPago = $_POST["estado_pago"] ?? "";

$estadosPermitidos = ["pendiente", "aprobado", "rechazado", "cancelado"];

if (empty($folio) || !in_array($estadoPago, $estadosPermitidos)) {
    die("Datos no válidos.");
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT *
        FROM compras
        WHERE folio_compra = ?
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute([$folio]);
    $compra = $stmt->fetch();

    if (!$compra) {
        throw new Exception("Compra no encontrada.");
    }

    $compraId = $compra["id"];

    $stmt = $pdo->prepare("
        UPDATE compras
        SET estado_pago = :estado_pago,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = :id
    ");

    $stmt->execute([
        ":estado_pago" => $estadoPago,
        ":id" => $compraId
    ]);

    if ($estadoPago === "aprobado") {
        $stmt = $pdo->prepare("
            UPDATE boletos
            SET estado = 'vendido',
                updated_at = CURRENT_TIMESTAMP
            WHERE compra_id = ?
        ");
        $stmt->execute([$compraId]);
    }

    if ($estadoPago === "pendiente") {
        $stmt = $pdo->prepare("
            UPDATE boletos
            SET estado = 'apartado',
                updated_at = CURRENT_TIMESTAMP
            WHERE compra_id = ?
        ");
        $stmt->execute([$compraId]);
    }

    if ($estadoPago === "rechazado" || $estadoPago === "cancelado") {
        $stmt = $pdo->prepare("
            UPDATE boletos
            SET estado = 'disponible',
                compra_id = NULL,
                reservado_hasta = NULL,
                updated_at = CURRENT_TIMESTAMP
            WHERE compra_id = ?
        ");
        $stmt->execute([$compraId]);
    }

    $pdo->commit();

    header("Location: ver_compra.php?folio=" . urlencode($folio));
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error al actualizar pago: " . htmlspecialchars($e->getMessage());
}