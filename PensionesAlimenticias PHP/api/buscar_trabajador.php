<?php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../db.php';

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode([
        "ok" => false,
        "msg" => "Escribe RFC, CURP o nombre del trabajador."
    ]);
    exit;
}

try {
    $sql = "
        SELECT
            t.id_trabajador,
            t.rfc,
            t.curp,
            t.nombre_completo,
            t.sostenimiento,
            t.centro_trabajo,
            t.clave_centro_trabajo,
            t.puesto,
            t.estatus,
            n.quincena,
            n.percepciones,
            n.deducciones_ley,
            n.sueldo_neto
        FROM trabajadores t
        LEFT JOIN nomina_simulada n
            ON n.trabajador_id = t.id_trabajador
        WHERE
            t.rfc LIKE :q_rfc
            OR t.curp LIKE :q_curp
            OR t.nombre_completo LIKE :q_nombre
        ORDER BY n.quincena DESC, t.id_trabajador DESC
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $busqueda = "%$q%";

    $stmt->execute([
        ':q_rfc' => $busqueda,
        ':q_curp' => $busqueda,
        ':q_nombre' => $busqueda
    ]);

    $trabajador = $stmt->fetch();

    if (!$trabajador) {
        echo json_encode([
            "ok" => false,
            "msg" => "No se encontró trabajador con ese dato."
        ]);
        exit;
    }

    echo json_encode([
        "ok" => true,
        "trabajador" => $trabajador
    ]);

} catch (Throwable $e) {
    echo json_encode([
        "ok" => false,
        "msg" => "Error al buscar trabajador: " . $e->getMessage()
    ]);
}