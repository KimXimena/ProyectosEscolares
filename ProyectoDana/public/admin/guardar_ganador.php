<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

$rifaId = $_POST["rifa_id"] ?? null;
$numeroBoleto = $_POST["numero_boleto"] ?? null;
$nombreGanador = trim($_POST["nombre_ganador"] ?? "");
$videoUrl = trim($_POST["video_url"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$fecha = $_POST["fecha"] ?? "";

if (!$rifaId || !$numeroBoleto || empty($fecha)) {
    die("Faltan datos obligatorios.");
}

$nombreFoto = null;

if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === UPLOAD_ERR_OK) {
    $archivo = $_FILES["foto"];
    $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
    $permitidas = ["jpg", "jpeg", "png", "webp"];

    if (!in_array($extension, $permitidas)) {
        die("Formato de imagen no permitido.");
    }

    $carpeta = __DIR__ . "/../uploads/ganadores/";

    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $nombreFoto = "ganador_" . time() . "_" . rand(1000, 9999) . "." . $extension;
    $rutaFinal = $carpeta . $nombreFoto;

    if (!move_uploaded_file($archivo["tmp_name"], $rutaFinal)) {
        die("No se pudo guardar la foto.");
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            c.estado_pago,
            comp.nombre AS nombre_comprador
        FROM boletos b
        LEFT JOIN compras c ON c.id = b.compra_id
        LEFT JOIN compradores comp ON comp.id = c.comprador_id
        WHERE b.rifa_id = ?
        AND b.numero_boleto = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$rifaId, $numeroBoleto]);
    $boleto = $stmt->fetch();

    if (!$boleto) {
        throw new Exception("No existe ese boleto en la rifa seleccionada.");
    }

    if ($boleto["estado"] !== "vendido" || $boleto["estado_pago"] !== "aprobado") {
        throw new Exception("El boleto ganador debe estar vendido y con pago aprobado.");
    }

    $nombreFinal = $nombreGanador ?: $boleto["nombre_comprador"];

    $stmt = $pdo->prepare("
        INSERT INTO ganadores (
            boleto_id,
            nombre_ganador,
            foto_url,
            video_url,
            descripcion,
            fecha
        ) VALUES (
            :boleto_id,
            :nombre_ganador,
            :foto_url,
            :video_url,
            :descripcion,
            :fecha
        )
    ");

    $stmt->execute([
        ":boleto_id" => $boleto["id"],
        ":nombre_ganador" => $nombreFinal,
        ":foto_url" => $nombreFoto,
        ":video_url" => $videoUrl ?: null,
        ":descripcion" => $descripcion ?: null,
        ":fecha" => $fecha
    ]);

    $stmt = $pdo->prepare("
        UPDATE rifas
        SET estado = 'finalizada',
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    $stmt->execute([$rifaId]);

    $pdo->commit();

    header("Location: ganadores.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error al guardar ganador: " . htmlspecialchars($e->getMessage());
}