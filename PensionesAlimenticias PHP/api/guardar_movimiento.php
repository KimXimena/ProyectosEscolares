<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../db.php';

function s($k) {
    if (!isset($_POST[$k])) return null;
    $v = trim((string)$_POST[$k]);
    return $v === '' ? null : $v;
}

function req($k) {
    $v = s($k);
    if ($v === null) {
        echo json_encode(["ok" => false, "msg" => "Falta llenar: $k"]);
        exit;
    }
    return $v;
}

function dec($k) {
    $v = s($k);
    if ($v === null) return null;
    if (!is_numeric($v)) return null;
    return (float)$v;
}

/* =========================
   1) DATOS DEL MOVIMIENTO
========================= */

$tipo_movimiento_clase = req('tipo_movimiento_clase');
$tipo_movimiento       = req('tipo_movimiento');
$tipo_tramite          = req('tipo_tramite');

/* =========================
   2) DATOS DEL TRABAJADOR
========================= */

$rfc_trabajador    = req('rfc_trabajador');
$nombre_trabajador = req('nombre_trabajador');
$curp_trabajador   = s('curp_trabajador');

$sostenimiento     = s('sostenimiento');
$centro_trabajo    = s('centro_trabajo');

/* =========================
   3) DATOS DEL BENEFICIARIO
========================= */

$ben_nombres    = req('ben_nombres');
$ben_ap_paterno = req('ben_ap_paterno');
$ben_ap_materno = s('ben_ap_materno');

$rfc_beneficiario     = s('rfc_beneficiario');
$celular_beneficiario = s('celular_beneficiario');

$forma_aplicacion = req('forma_aplicacion');
$monto_descuento  = dec('monto_descuento');

if ($monto_descuento === null) {
    echo json_encode(["ok" => false, "msg" => "Falta llenar o inválido: monto_descuento"]);
    exit;
}

$quincena_inicio = req('quincena_inicio');
$quincena_fin    = req('quincena_fin');

$cct_pago_beneficiario = req('cct_pago_beneficiario');

$nombre_abogado  = s('nombre_abogado');
$celular_abogado = s('celular_abogado');

/* =========================
   4) DATOS DEL OFICIO
========================= */

$numero_oficio     = s('numero_oficio');
$fecha_oficio      = s('fecha_oficio');
$fecha_recibido    = s('fecha_recibido');
$numero_expediente = s('numero_expediente');
$juzgado           = s('juzgado');
$municipio_pago    = s('municipio_pago');

$archivo_oficio = null;

/* =========================
   5) VALIDACIONES
========================= */

$validClase = ['pension', 'juicio'];
$validMov   = ['alta', 'cambio', 'baja', 'reintegro'];
$validTram  = ['pension', 'juicio'];
$validForma = ['PORC_PENSION', 'IMP_FIJO_PENSION', 'JUICIO_MERCANTIL'];

if (!in_array($tipo_movimiento_clase, $validClase, true)) {
    echo json_encode(["ok" => false, "msg" => "tipo_movimiento_clase inválido"]);
    exit;
}

if (!in_array($tipo_movimiento, $validMov, true)) {
    echo json_encode(["ok" => false, "msg" => "tipo_movimiento inválido"]);
    exit;
}

if (!in_array($tipo_tramite, $validTram, true)) {
    echo json_encode(["ok" => false, "msg" => "tipo_tramite inválido"]);
    exit;
}

if ($tipo_movimiento_clase !== $tipo_tramite) {
    echo json_encode([
        "ok" => false,
        "msg" => "Inconsistencia: la clase interna no coincide con el tipo de trámite legal"
    ]);
    exit;
}

if (!in_array($forma_aplicacion, $validForma, true)) {
    echo json_encode(["ok" => false, "msg" => "forma_aplicacion inválida"]);
    exit;
}

/* =========================
   6) GUARDADO CON TRANSACCIÓN
========================= */

try {
    $pdo->beginTransaction();

    /*
      A) Buscar trabajador por RFC.
      Si no existe, se registra.
      Si ya existe, se reutiliza.
    */
    $stmtBuscarTrab = $pdo->prepare("
        SELECT id_trabajador
        FROM trabajadores
        WHERE rfc = ?
        LIMIT 1
    ");
    $stmtBuscarTrab->execute([$rfc_trabajador]);
    $trabajador = $stmtBuscarTrab->fetch();

    if ($trabajador) {
        $trabajador_id = (int)$trabajador['id_trabajador'];
    } else {
        $stmtTrab = $pdo->prepare("
            INSERT INTO trabajadores
            (
                rfc,
                curp,
                nombre_completo,
                sostenimiento,
                centro_trabajo
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmtTrab->execute([
            $rfc_trabajador,
            $curp_trabajador,
            $nombre_trabajador,
            $sostenimiento,
            $centro_trabajo
        ]);

        $trabajador_id = (int)$pdo->lastInsertId();
    }

    /*
      B) Insertar beneficiario
    */
    $stmtBen = $pdo->prepare("
        INSERT INTO beneficiarios
        (
            nombres,
            ap_paterno,
            ap_materno,
            rfc,
            celular,
            cct_pago_beneficiario,
            forma_aplicacion,
            monto_descuento,
            quincena_inicio,
            quincena_fin,
            nombre_abogado,
            celular_abogado
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmtBen->execute([
        $ben_nombres,
        $ben_ap_paterno,
        $ben_ap_materno,
        $rfc_beneficiario,
        $celular_beneficiario,
        $cct_pago_beneficiario,
        $forma_aplicacion,
        $monto_descuento,
        $quincena_inicio,
        $quincena_fin,
        $nombre_abogado,
        $celular_abogado
    ]);

    $beneficiario_id = (int)$pdo->lastInsertId();

    /*
      C) Insertar movimiento
    */
    $stmtMov = $pdo->prepare("
        INSERT INTO movimientos
        (
            trabajador_id,
            beneficiario_id,
            tipo_movimiento_clase,
            tipo_movimiento,
            tipo_tramite,
            estatus
        )
        VALUES (?, ?, ?, ?, ?, 'Capturado')
    ");

    $stmtMov->execute([
        $trabajador_id,
        $beneficiario_id,
        $tipo_movimiento_clase,
        $tipo_movimiento,
        $tipo_tramite
    ]);

    $movimiento_id = (int)$pdo->lastInsertId();

    /*
      D) Insertar oficio
    */
    $stmtOficio = $pdo->prepare("
        INSERT INTO oficios
        (
            movimiento_id,
            numero_oficio,
            fecha_oficio,
            fecha_recibido,
            archivo_oficio,
            numero_expediente,
            juzgado,
            municipio_pago
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmtOficio->execute([
        $movimiento_id,
        $numero_oficio,
        $fecha_oficio,
        $fecha_recibido,
        $archivo_oficio,
        $numero_expediente,
        $juzgado,
        $municipio_pago
    ]);

    /*
      E) Insertar historial
      Como todavía no tenemos login real, usuario_id queda NULL.
    */
    $stmtHist = $pdo->prepare("
        INSERT INTO historial_movimientos
        (
            movimiento_id,
            usuario_id,
            accion,
            descripcion
        )
        VALUES (?, NULL, ?, ?)
    ");

    $stmtHist->execute([
        $movimiento_id,
        'Captura',
        'Se registró un nuevo movimiento en el sistema.'
    ]);

    $pdo->commit();

    echo json_encode([
        "ok" => true,
        "id_trabajador" => $trabajador_id,
        "id_beneficiario" => $beneficiario_id,
        "id_movimiento" => $movimiento_id,
        "msg" => "Movimiento guardado correctamente"
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        "ok" => false,
        "msg" => "Error al guardar: " . $e->getMessage()
    ]);
}