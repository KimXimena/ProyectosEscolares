<?php
require __DIR__ . "/db.php";

function h($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$rfc    = trim($_GET['rfc'] ?? '');
$curp   = trim($_GET['curp'] ?? '');
$nombre = trim($_GET['nombre'] ?? '');

$where = [];
$params = [];

if ($rfc !== '') {
  $where[] = "t.rfc LIKE :rfc";
  $params[':rfc'] = "%$rfc%";
}

if ($curp !== '') {
  $where[] = "t.curp LIKE :curp";
  $params[':curp'] = "%$curp%";
}

if ($nombre !== '') {
  $where[] = "t.nombre_completo LIKE :nombre";
  $params[':nombre'] = "%$nombre%";
}

$sql = "
  SELECT
    t.rfc,
    t.curp,
    t.nombre_completo,
    t.sostenimiento,
    t.centro_trabajo,
    t.clave_centro_trabajo,
    t.puesto,
    t.estatus AS estatus_trabajador,
    n.quincena,
    n.percepciones,
    n.deducciones_ley,
    n.sueldo_neto,
    n.estatus AS estatus_nomina
  FROM trabajadores t
  LEFT JOIN nomina_simulada n
    ON n.trabajador_id = t.id_trabajador
";

if ($where) {
  $sql .= " WHERE " . implode(" OR ", $where);
}

$sql .= " ORDER BY t.nombre_completo ASC, n.quincena DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Consulta de Nómina</title>
<style>
body{font-family:"Segoe UI",system-ui;background:#f3f4f6;margin:0;padding:1.5rem}
.page{max-width:1250px;margin:auto}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:4px;padding:1rem;margin-bottom:1rem}
h1{margin-top:0}
table{width:100%;border-collapse:collapse;font-size:.82rem}
th,td{padding:.5rem;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:middle}
thead{background:#f1f5f9}
.muted{color:#6b7280}
</style>
</head>

<body>
<div class="page">

<h1>Consulta de nómina simulada</h1>

<div class="card">
  <strong>Búsqueda:</strong>
  <?= h($rfc ?: $curp ?: $nombre ?: 'Mostrando todos los trabajadores') ?>
</div>

<div class="card" style="overflow-x:auto;">
<table>
<thead>
<tr>
  <th>RFC</th>
  <th>CURP</th>
  <th>Nombre</th>
  <th>Sostenimiento</th>
  <th>Centro trabajo</th>
  <th>Puesto</th>
  <th>Quincena</th>
  <th>Percepciones</th>
  <th>Deducciones ley</th>
  <th>Sueldo neto</th>
  <th>Estado</th>
</tr>
</thead>
<tbody>
<?php if (!$rows): ?>
<tr>
  <td colspan="11" class="muted">No se encontró información de nómina.</td>
</tr>
<?php else: ?>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= h($r['rfc']) ?></td>
    <td><?= h($r['curp']) ?></td>
    <td><?= h($r['nombre_completo']) ?></td>
    <td><?= h($r['sostenimiento']) ?></td>
    <td><?= h($r['centro_trabajo']) ?></td>
    <td><?= h($r['puesto']) ?></td>
    <td><?= h($r['quincena'] ?: '—') ?></td>
    <td>$<?= number_format((float)$r['percepciones'], 2) ?></td>
    <td>$<?= number_format((float)$r['deducciones_ley'], 2) ?></td>
    <td>$<?= number_format((float)$r['sueldo_neto'], 2) ?></td>
    <td><?= h($r['estatus_nomina'] ?: $r['estatus_trabajador']) ?></td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>

</div>
</body>
</html>