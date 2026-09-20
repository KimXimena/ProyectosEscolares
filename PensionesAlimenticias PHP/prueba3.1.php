<?php
require __DIR__ . "/db.php";

$q       = trim($_GET['q'] ?? '');
$campo   = $_GET['campo'] ?? 'rfc_trabajador';
$estatus = trim($_GET['estatus'] ?? '');
$limit   = (int)($_GET['limit'] ?? 50);

if (!in_array($limit, [10, 25, 50], true)) {
  $limit = 50;
}

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

$labels = [
  'rfc_trabajador'      => 'RFC del trabajador',
  'nombre_trabajador'   => 'Nombre del trabajador',
  'nombre_beneficiario' => 'Nombre del beneficiario',
  'tipo_tramite'        => 'Tipo de trámite',
  'tipo_movimiento'     => 'Tipo de movimiento',
];

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
    m.id_movimiento,
    m.estatus,
    m.tipo_movimiento_clase,
    m.tipo_movimiento,
    m.tipo_tramite,

    t.rfc AS rfc_trabajador,
    t.curp AS curp_trabajador,
    t.nombre_completo AS nombre_trabajador,

    CONCAT_WS(' ', b.ap_paterno, b.ap_materno, b.nombres) AS nombre_beneficiario,
    b.rfc AS rfc_beneficiario,

    m.creado_en

  FROM movimientos m

  INNER JOIN trabajadores t
    ON t.id_trabajador = m.trabajador_id

  INNER JOIN beneficiarios b
    ON b.id_beneficiario = m.beneficiario_id
";

if ($where) {
  $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY m.id_movimiento DESC LIMIT $limit";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($s) {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function etiquetaProceso($v) {

  $v = strtolower(trim((string)$v));

  switch ($v) {

    case 'alta':
      return 'Alta (A)';

    case 'cambio':
    case 'cambi':
      return 'Cambio (B)';

    case 'baja':
      return 'Baja (C)';

    case 'reintegro':
    case 'reint':
      return 'Reintegro (D)';

    default:
      return $v !== '' ? $v : '—';
  }
}

function etiquetaTramite($v) {

  return ($v === 'juicio')
    ? 'Juicio mercantil'
    : (($v === 'pension')
      ? 'Pensión alimenticia'
      : ($v ?: '—'));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Seguimiento de Movimientos</title>

<style>
* {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: "Segoe UI", system-ui, sans-serif;
  background: #f3f4f6;
  color: #333;
}

.page {
  width: 100%;
  max-width: 1250px;
  margin: 0 auto;
  padding: 1.5rem;
}

header {
  padding-bottom: .75rem;
  border-bottom: 2px solid #e5e7eb;
  margin-bottom: 1rem;
}

.title-main {
  font-size: 1.8rem;
  font-weight: 700;
  text-transform: uppercase;
}

.title-main span {
  color: #ff007f;
}

.subtitle {
  font-size: .85rem;
  color: #6b7280;
}

.card {
  background: #fff;
  border-radius: 6px;
  padding: 1rem 1.25rem;
  border: 1px solid #e5e7eb;
  margin-bottom: 1rem;
  width: 100%;
}

.top-row {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 1.25rem;
  align-items: start;
}

.search-bar {
  display: flex;
  flex-direction: column;
  gap: .7rem;
  width: 100%;
}

.table-panel {
  width: 100%;
  min-width: 0;
}

label {
  display: block;
  font-size: .85rem;
  font-weight: 600;
  margin-bottom: .25rem;
}

input[type="text"],
select {
  width: 100%;
  padding: .4rem .5rem;
  border-radius: 3px;
  border: 1px solid #cbd5e1;
  font-family: inherit;
  font-size: .85rem;
}

.btn {
  border: none;
  border-radius: 3px;
  padding: .4rem .9rem;
  font-size: .85rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
}

.btn-primary {
  background: #007bff;
  color: #fff;
}

.btn-secondary {
  background: #f3f4f6;
  color: #111827;
  border: 1px solid #d1d5db;
  text-decoration: none;
  display: inline-block;
}

.table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: .25rem 0 1rem;
  gap: .75rem;
}

.table-controls-left {
  display: flex;
  gap: .5rem;
  align-items: center;
  flex-wrap: wrap;
}

.btn-excel {
  background: #e2e8f0;
  border: 1px solid #cbd5e1;
  padding: .3rem .8rem;
  border-radius: 3px;
  cursor: pointer;
  font-weight: 600;
}

.table-wrap {
  width: 100%;
  overflow-x: auto;
}

table {
  width: 100%;
  min-width: 950px;
  border-collapse: collapse;
  font-size: .78rem;
}

thead {
  background: #f1f5f9;
}

th,
td {
  padding: .5rem .45rem;
  border-bottom: 1px solid #e5e7eb;
  text-align: left;
  vertical-align: middle;
}

th {
  font-weight: 700;
  white-space: nowrap;
}

tbody tr:nth-child(even) {
  background: #f9fafb;
}

.col-btn {
  width: 140px;
  white-space: nowrap;
}

.btn-seguimiento {
  background: #007bff;
  color: #fff;
  border: none;
  border-radius: 16px;
  padding: .25rem .75rem;
  cursor: pointer;
  font-size: .75rem;
  white-space: nowrap;
}

.nowrap {
  white-space: nowrap;
}

.muted {
  color: #6b7280;
}

.top-actions {
  display: flex;
  justify-content: flex-end;
  gap: .5rem;
  margin-top: 1rem;
}

@media (max-width: 900px) {
  .top-row {
    grid-template-columns: 1fr;
  }

  .page {
    padding: 1rem;
  }

  table {
    min-width: 900px;
  }
}
</style>
</head>

<body>

<div class="page">

<header>
  <div class="title-main">
    REINTEGROS DE NÓMINA. <span>SEGUIMIENTO</span>
  </div>

  <div class="subtitle">
    Listado de movimientos capturados
  </div>
</header>

<div class="card">

<div class="top-row">

<form class="search-bar" method="GET">

<div>
<label>Buscar por</label>

<select id="criterioBusqueda" name="campo">
<?php foreach ($allowedCampos as $c): ?>
<option value="<?= h($c) ?>" <?= $campo === $c ? 'selected' : '' ?>>
<?= h($labels[$c] ?? $c) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div>
<label>Búsqueda</label>

<input
type="text"
name="q"
value="<?= h($q) ?>"
placeholder="Escriba RFC, nombre, etc."
>
</div>

<input type="hidden" name="estatus" value="<?= h($estatus) ?>">
<input type="hidden" name="limit" value="<?= h($limit) ?>">

<div>
<button class="btn btn-primary" type="submit" style="width:100%;">
Buscar
</button>
</div>

</form>

<div class="table-panel">

<div class="table-controls">

<div class="table-controls-left">

<span>Mostrar</span>

<select id="pageSize">
<option value="10" <?= $limit === 10 ? 'selected' : '' ?>>10</option>
<option value="25" <?= $limit === 25 ? 'selected' : '' ?>>25</option>
<option value="50" <?= $limit === 50 ? 'selected' : '' ?>>50</option>
</select>

<span>registros</span>

<button class="btn-excel" type="button" id="btnExcel">
Excel
</button>

</div>
</div>

<div style="overflow-x:auto;">

<table>

<thead>
<tr>
<th class="col-btn">Dar seguimiento</th>
<th>Estatus</th>
<th>Tipo movimiento</th>
<th>Tipo trámite</th>
<th>RFC trabajador</th>
<th>CURP</th>
<th>Nombre trabajador</th>
<th>Nombre beneficiario</th>
<th>RFC beneficiario</th>
<th>Fecha captura</th>
</tr>
</thead>

<tbody>

<?php if (!$rows): ?>

<tr>
<td colspan="10" class="muted">
No hay registros con esos filtros.
</td>
</tr>

<?php else: ?>

<?php foreach ($rows as $r): ?>

<tr>

<td class="col-btn">
<button
class="btn-seguimiento"
data-id="<?= h($r['id_movimiento']) ?>"
>
Movimiento <?= str_pad((int)$r['id_movimiento'], 4, '0', STR_PAD_LEFT) ?>
</button>
</td>

<td class="nowrap"><?= h($r['estatus']) ?></td>

<td class="nowrap">
<?= h(etiquetaProceso($r['tipo_movimiento'])) ?>
</td>

<td class="nowrap">
<?= h(etiquetaTramite($r['tipo_tramite'])) ?>
</td>

<td><?= h($r['rfc_trabajador']) ?></td>
<td><?= h($r['curp_trabajador']) ?></td>
<td><?= h($r['nombre_trabajador']) ?></td>
<td><?= h($r['nombre_beneficiario']) ?></td>
<td><?= h($r['rfc_beneficiario']) ?></td>
<td class="nowrap"><?= h($r['creado_en']) ?></td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>
</table>

</div>

<div class="top-actions">

<a
href="siasep.php"
target="_blank"
rel="noopener"
class="btn btn-secondary"
>
Ir a SIAPSEP
</a>

</div>

</div>
</div>
</div>
</div>

<script>
document.getElementById('pageSize').addEventListener('change', (e) => {

  const url = new URL(window.location.href);

  url.searchParams.set('limit', e.target.value);

  window.location.href = url.toString();
});

document.querySelectorAll('.btn-seguimiento').forEach(btn => {

  btn.addEventListener('click', () => {

    const id = btn.dataset.id;

    window.location.href =
      'prueba3.1.php?idMovimiento=' + encodeURIComponent(id);
  });
});

document.getElementById("btnExcel").addEventListener("click", () => {

  const params = new URLSearchParams(window.location.search);

  const url = "exportacion.php?" + params.toString();

  const a = document.createElement("a");

  a.href = url;
  a.download = "reintegros_nomina.xls";

  document.body.appendChild(a);

  a.click();

  a.remove();
});
</script>

</body>
</html>