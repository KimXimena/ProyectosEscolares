<?php
require_once __DIR__ . "/../config/conexion.php";

$folio = $_POST["folio"] ?? "";

if (empty($folio)) {
    die("Folio no válido.");
}

if (!isset($_FILES["comprobante"]) || $_FILES["comprobante"]["error"] !== UPLOAD_ERR_OK) {
    die("Error al subir el comprobante.");
}

$archivo = $_FILES["comprobante"];

// Tamaño máximo: 5 MB
$tamanoMaximo = 5 * 1024 * 1024;

if ($archivo["size"] > $tamanoMaximo) {
    die("El archivo es demasiado grande. Máximo permitido: 5 MB.");
}

// Validar extensión sin usar mime_content_type()
$nombreOriginal = $archivo["name"];
$extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

$extensionesPermitidas = ["jpg", "jpeg", "png", "webp", "pdf"];

if (!in_array($extension, $extensionesPermitidas)) {
    die("Formato no permitido. Solo se permiten JPG, JPEG, PNG, WEBP o PDF.");
}

// Verificar que la compra exista
$sql = "
    SELECT id
    FROM compras
    WHERE folio_compra = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$folio]);
$compra = $stmt->fetch();

if (!$compra) {
    die("Compra no encontrada.");
}

// Crear carpeta si no existe
$carpetaDestino = __DIR__ . "/uploads/comprobantes/";

if (!is_dir($carpetaDestino)) {
    mkdir($carpetaDestino, 0777, true);
}

// Nombre seguro del archivo
$nombreArchivo = "comprobante_" . preg_replace("/[^A-Za-z0-9\-]/", "_", $folio) . "_" . time() . "." . $extension;
$rutaFinal = $carpetaDestino . $nombreArchivo;

// Mover archivo
if (!move_uploaded_file($archivo["tmp_name"], $rutaFinal)) {
    die("No se pudo guardar el comprobante.");
}

// Guardar en la base de datos
$sql = "
    UPDATE compras
    SET comprobante = :comprobante,
        updated_at = CURRENT_TIMESTAMP
    WHERE folio_compra = :folio
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":comprobante" => $nombreArchivo,
    ":folio" => $folio
]);

header("Location: comprobante.php?folio=" . urlencode($folio));
exit;