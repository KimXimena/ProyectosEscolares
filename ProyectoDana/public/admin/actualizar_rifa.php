<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

function convertirFecha($fecha) {
    if (empty($fecha)) {
        return null;
    }

    return str_replace("T", " ", $fecha) . ":00";
}

$id = $_POST["id"] ?? null;
$codigoRifa = trim($_POST["codigo_rifa"] ?? "");
$slug = trim($_POST["slug"] ?? "");
$titulo = trim($_POST["titulo"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$premioTipo = $_POST["premio_tipo"] ?? "efectivo";
$premioNombre = trim($_POST["premio_nombre"] ?? "");
$premioMonto = $_POST["premio_monto"] ?? null;
$precioBoleto = $_POST["precio_boleto"] ?? 0;
$fechaInicio = convertirFecha($_POST["fecha_inicio"] ?? null);
$fechaCierre = convertirFecha($_POST["fecha_cierre"] ?? null);
$fechaSorteo = convertirFecha($_POST["fecha_sorteo"] ?? null);
$metodoGanador = trim($_POST["metodo_ganador"] ?? "");
$baseSorteo = trim($_POST["base_sorteo"] ?? "");
$estado = $_POST["estado"] ?? "borrador";

if (!$id || $codigoRifa === "" || $slug === "" || $titulo === "" || $descripcion === "") {
    die("Faltan datos obligatorios.");
}

if ($precioBoleto <= 0) {
    die("El precio del boleto debe ser mayor a 0.");
}

$estadosPermitidos = ["borrador", "activa", "cerrada", "cancelada", "finalizada"];

if (!in_array($estado, $estadosPermitidos)) {
    die("Estado no válido.");
}

$tiposPermitidos = ["efectivo", "producto", "servicio", "mixto"];

if (!in_array($premioTipo, $tiposPermitidos)) {
    die("Tipo de premio no válido.");
}

$stmt = $pdo->prepare("
    SELECT imagen
    FROM rifas
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);
$rifaActual = $stmt->fetch();

if (!$rifaActual) {
    die("Rifa no encontrada.");
}

$nombreImagen = $rifaActual["imagen"];

if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
    $archivo = $_FILES["imagen"];
    $extension = strtolower(pathinfo($archivo["name"], PATHINFO_EXTENSION));
    $permitidas = ["jpg", "jpeg", "png", "webp"];

    if (!in_array($extension, $permitidas)) {
        die("Formato de imagen no permitido.");
    }

    $carpeta = __DIR__ . "/../uploads/rifas/";

    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $nombreImagen = "rifa_" . time() . "_" . rand(1000, 9999) . "." . $extension;
    $rutaFinal = $carpeta . $nombreImagen;

    if (!move_uploaded_file($archivo["tmp_name"], $rutaFinal)) {
        die("No se pudo guardar la imagen.");
    }
}

try {
    $sql = "
        UPDATE rifas
        SET
            codigo_rifa = :codigo_rifa,
            slug = :slug,
            titulo = :titulo,
            descripcion = :descripcion,
            premio_tipo = :premio_tipo,
            premio_nombre = :premio_nombre,
            imagen = :imagen,
            precio_boleto = :precio_boleto,
            premio_monto = :premio_monto,
            fecha_inicio = :fecha_inicio,
            fecha_cierre = :fecha_cierre,
            fecha_sorteo = :fecha_sorteo,
            metodo_ganador = :metodo_ganador,
            base_sorteo = :base_sorteo,
            estado = :estado,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":codigo_rifa" => $codigoRifa,
        ":slug" => $slug,
        ":titulo" => $titulo,
        ":descripcion" => $descripcion,
        ":premio_tipo" => $premioTipo,
        ":premio_nombre" => $premioNombre ?: $titulo,
        ":imagen" => $nombreImagen,
        ":precio_boleto" => $precioBoleto,
        ":premio_monto" => $premioMonto ?: null,
        ":fecha_inicio" => $fechaInicio,
        ":fecha_cierre" => $fechaCierre,
        ":fecha_sorteo" => $fechaSorteo,
        ":metodo_ganador" => $metodoGanador ?: null,
        ":base_sorteo" => $baseSorteo ?: null,
        ":estado" => $estado,
        ":id" => $id
    ]);

    header("Location: rifas.php");
    exit;

} catch (Exception $e) {
    echo "Error al actualizar la rifa: " . htmlspecialchars($e->getMessage());
}