<?php
require __DIR__ . "/db.php";

$q       = trim($_GET['q'] ?? '');
$campo   = $_GET['campo'] ?? 'rfc_trabajador';
$estatus = trim($_GET['estatus'] ?? '');

$allowedCampos = [
  'rfc_trabajador',
  'nombre_trabajador',
  'nombre_beneficiario',
  'tipo_tramite',
  'tipo_movimiento'
];

if (!in_array($campo, $allowedCampos, true)) {
  $campo = 'rfc_trabajador';
}

$where  = [];
$params = [];

if ($estatus !== '') {
  $where[] = "m.estatus = :estatus";
  $params[':estatus'] = $estatus;
}

if ($q !== '') {
  switch ($campo) {
    case 'rfc_trabajador':
      $where[] = "t.rfc LIKE :q";
      break;

    case 'nombre_trabajador':
      $where[] = "t.nombre_completo LIKE :q";
      break;

    case 'nombre_beneficiario':
      $where[] = "CONCAT_WS(' ', b.ap_paterno, b.ap_materno, b.nombres) LIKE :q";
      break;

    case 'tipo_tramite':
      $where[] = "m.tipo_tramite LIKE :q";
      break;

    case 'tipo_movimiento':
      $where[] = "m.tipo_movimiento LIKE :q";
      break;

    default:
      $where[] = "t.rfc LIKE :q";
      break;
  }

  $params[':q'] = "%$q%";
}

$sql = "
  SELECT
    m.id_movimiento AS ID,
    m.estatus AS Estatus,
    m.tipo_movimiento AS 'Tipo Movimiento',
    m.tipo_tramite AS 'Tipo Trámite',

    t.rfc AS 'RFC Trabajador',
    t.curp AS CURP,
    t.nombre_completo AS 'Nombre Trabajador',

    CONCAT_WS(' ', b.ap_paterno, b.ap_materno, b.nombres) AS 'Nombre Beneficiario',
    b.rfc AS 'RFC Beneficiario',

    m.creado_en AS 'Fecha Captura'

  FROM movimientos m

  INNER JOIN trabajadores t
    ON t.id_trabajador = m.trabajador_id

  INNER JOIN beneficiarios b
    ON b.id_beneficiario = m.beneficiario_id
";

if ($where) {
  $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY m.id_movimiento DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reintegros_nomina.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<meta charset='utf-8'>";
echo "<table border='1'>";

if (!$rows) {
  echo "<tr><td>No hay registros para exportar.</td></tr>";
  echo "</table>";
  exit;
}

echo "<tr>";
foreach (array_keys($rows[0]) as $columna) {
  echo "<th>" . htmlspecialchars($columna, ENT_QUOTES, 'UTF-8') . "</th>";
}
echo "</tr>";

foreach ($rows as $r) {
  echo "<tr>";
  foreach ($r as $value) {
    echo "<td>" . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . "</td>";
  }
  echo "</tr>";
}

echo "</table>";
exit;