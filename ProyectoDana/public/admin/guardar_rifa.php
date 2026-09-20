<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

function convertirFecha($fecha) {
    if (empty($fecha)) {
        return null;
    }

    return str_replace("T", " ", $fecha) . ":00";
}

$codigoRifa = trim($_POST["codigo_rifa"] ?? "");
$slug = trim($_POST["slug"] ?? "");
$titulo = trim($_POST["titulo"] ?? "");
$descripcion = trim($_POST["descripcion"] ?? "");
$precioBoleto = $_POST["precio_boleto"] ?? 0;
$totalBoletos = $_POST["total_boletos"] ?? 0;
$premioMonto = $_POST["premio_monto"] ?? null;
$premioTipo = $_POST["premio_tipo"] ?? "efectivo";
$premioNombre = trim($_POST["premio_nombre"] ?? "");
$fechaInicio = convertirFecha($_POST["fecha_inicio"] ?? null);
$fechaCierre = convertirFecha($_POST["fecha_cierre"] ?? null);
$fechaSorteo = convertirFecha($_POST["fecha_sorteo"] ?? null);
$metodoGanador = trim($_POST["metodo_ganador"] ?? "");
$baseSorteo = trim($_POST["base_sorteo"] ?? "");
$estado = $_POST["estado"] ?? "borrador";

if ($codigoRifa === "" || $slug === "" || $titulo === "" || $descripcion === "") {
    die("Faltan datos obligatorios.");
}

if ($precioBoleto <= 0 || $totalBoletos <= 0) {
    die("El precio y total de boletos deben ser mayores a 0.");
}

$estadosPermitidos = ["borrador", "activa", "cerrada", "cancelada", "finalizada"];

if (!in_array($estado, $estadosPermitidos)) {
    die("Estado no válido.");
}

$nombreImagen = null;

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
    $pdo->beginTransaction();

$sql = "
    INSERT INTO rifas (
        codigo_rifa,
        slug,
        titulo,
        descripcion,
        premio_tipo,
        premio_nombre,
        imagen,
        precio_boleto,
        total_boletos,
        premio_monto,
        fecha_inicio,
        fecha_cierre,
        fecha_sorteo,
        metodo_ganador,
        base_sorteo,
        estado
    ) VALUES (
        :codigo_rifa,
        :slug,
        :titulo,
        :descripcion,
        :premio_tipo,
        :premio_nombre,
        :imagen,
        :precio_boleto,
        :total_boletos,
        :premio_monto,
        :fecha_inicio,
        :fecha_cierre,
        :fecha_sorteo,
        :metodo_ganador,
        :base_sorteo,
        :estado
    )
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
    ":total_boletos" => $totalBoletos,
    ":premio_monto" => $premioMonto ?: null,
    ":fecha_inicio" => $fechaInicio,
    ":fecha_cierre" => $fechaCierre,
    ":fecha_sorteo" => $fechaSorteo,
    ":metodo_ganador" => $metodoGanador ?: null,
    ":base_sorteo" => $baseSorteo ?: null,
    ":estado" => $estado
]);

    $rifaId = $pdo->lastInsertId();

    $sqlBoleto = "
        INSERT INTO boletos (
            rifa_id,
            numero_boleto,
            folio_boleto,
            estado
        ) VALUES (
            :rifa_id,
            :numero_boleto,
            :folio_boleto,
            'disponible'
        )
    ";

    $stmtBoleto = $pdo->prepare($sqlBoleto);

    for ($i = 1; $i <= $totalBoletos; $i++) {
        $folioBoleto = strtoupper(str_replace("-", "", $codigoRifa)) . "-" . str_pad($i, 4, "0", STR_PAD_LEFT);

        $stmtBoleto->execute([
            ":rifa_id" => $rifaId,
            ":numero_boleto" => $i,
            ":folio_boleto" => $folioBoleto
        ]);
    }

    $cantidades = $_POST["paquete_cantidad"] ?? [];
    $precios = $_POST["paquete_precio"] ?? [];
    $etiquetas = $_POST["paquete_etiqueta"] ?? [];

    $sqlPaquete = "
        INSERT INTO paquetes (
            rifa_id,
            cantidad_boletos,
            precio,
            etiqueta,
            activo
        ) VALUES (
            :rifa_id,
            :cantidad_boletos,
            :precio,
            :etiqueta,
            1
        )
    ";

    $stmtPaquete = $pdo->prepare($sqlPaquete);

    for ($i = 0; $i < count($cantidades); $i++) {
        $cantidad = $cantidades[$i] ?? "";
        $precio = $precios[$i] ?? "";
        $etiqueta = trim($etiquetas[$i] ?? "");

        if ($cantidad !== "" && $precio !== "") {
            $stmtPaquete->execute([
                ":rifa_id" => $rifaId,
                ":cantidad_boletos" => $cantidad,
                ":precio" => $precio,
                ":etiqueta" => $etiqueta ?: $cantidad . " boletos por $" . $precio
            ]);
        }
    }

    $pdo->commit();

    header("Location: rifas.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();

    echo "Error al crear la rifa: " . htmlspecialchars($e->getMessage());
}