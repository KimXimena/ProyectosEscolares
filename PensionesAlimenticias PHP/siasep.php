<?php
require __DIR__ . "/db.php";

function h($s){
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$quincena = trim($_GET['quincena'] ?? '');
$mov      = strtolower(trim($_GET['mov'] ?? ''));

$allowed = ['', 'alta', 'cambio', 'baja', 'reintegro'];

if (!in_array($mov, $allowed, true)) {
  $mov = '';
}

$where  = [];
$params = [];

/* =========================================
   FILTRO POR QUINCENA
========================================= */

if ($quincena !== '') {

  $where[] = "b.quincena_inicio = :quincena";
$params[':quincena'] = $quincena;
}

/* =========================================
   FILTRO POR MOVIMIENTO
========================================= */

if ($mov !== '') {

  $where[] = "m.tipo_movimiento = :mov";

  $params[':mov'] = $mov;
}

/* =========================================
   CONSULTA
========================================= */

$sql = "
  SELECT

    m.id_movimiento,
    m.tipo_movimiento,
    m.tipo_tramite,
    m.estatus,

    t.rfc AS rfc_trabajador,
    t.curp AS curp_trabajador,
    t.nombre_completo AS nombre_trabajador,

    b.quincena_inicio,
    b.quincena_fin,

    CONCAT_WS(
      ' ',
      b.ap_paterno,
      b.ap_materno,
      b.nombres
    ) AS nombre_beneficiario,

    b.rfc AS rfc_beneficiario

  FROM movimientos m

  INNER JOIN trabajadores t
    ON t.id_trabajador = m.trabajador_id

  INNER JOIN beneficiarios b
    ON b.id_beneficiario = m.beneficiario_id
";

/* =========================================
   WHERE DINÁMICO
========================================= */

if ($where) {

  $sql .= " WHERE " . implode(" AND ", $where);
}

/* =========================================
   ORDER
========================================= */

$sql .= "
  ORDER BY m.id_movimiento DESC
";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   FUNCIONES VISUALES
========================================= */

function movTxt($m){

  return $m === 'alta'
    ? 'ALTA'
    : (
      $m === 'cambio'
        ? 'CAMBIO'
        : (
          $m === 'baja'
            ? 'BAJA'
            : (
              $m === 'reintegro'
                ? 'REINTEGRO'
                : '—'
            )
        )
    );
}

function tramTxt($t){

  return $t === 'pension'
    ? 'Pensión alimenticia'
    : (
      $t === 'juicio'
        ? 'Juicio mercantil'
        : '—'
    );
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>
SIAPSEP · Consulta por Quincena
</title>

<style>

body{
  margin:0;
  font-family:"Segoe UI",system-ui;
  background:#f3f4f6;
}

.page{
  max-width:1400px;
  margin:0 auto;
  padding:1.5rem;
}

header{
  border-bottom:2px solid #e5e7eb;
  margin-bottom:1rem;
}

h1{
  margin:0;
  font-size:1.9rem;
  letter-spacing:.03em;
}

.card{
  background:#fff;
  border:1px solid #e5e7eb;
  border-radius:4px;
  padding:1rem 1.25rem;
  margin-bottom:1rem;
}

label{
  font-size:.78rem;
  font-weight:700;
  text-transform:uppercase;
  color:#6b7280;
}

input{
  width:100%;
  padding:.45rem;
  border:1px solid #cbd5e1;
  border-radius:3px;
}

.actions{
  text-align:right;
  margin-top:.75rem;
}

.btn{
  border:none;
  border-radius:3px;
  padding:.45rem .9rem;
  font-size:.82rem;
  font-weight:700;
  cursor:pointer;
  text-decoration:none;
  display:inline-block;
}

.btn-primary{
  background:#007bff;
  color:#fff;
}

.btn-ghost{
  background:#f3f4f6;
  border:1px solid #d1d5db;
  color:#111827;
}

.filters{
  display:flex;
  gap:.5rem;
  flex-wrap:wrap;
}

table{
  width:100%;
  border-collapse:collapse;
  font-size:.82rem;
}

th,td{
  border-bottom:1px solid #e5e7eb;
  padding:.55rem;
  text-align:left;
  vertical-align:top;
}

thead{
  background:#f1f5f9;
}

tbody tr:nth-child(even){
  background:#f9fafb;
}

.muted{
  color:#6b7280;
}

.nowrap{
  white-space:nowrap;
}

</style>

</head>

<body>

<div class="page">

<header>

<h1>
SIAPSEP · Consulta por Quincena
</h1>

</header>

<!-- =====================================
     BUSCADOR
===================================== -->

<div class="card">

<form method="GET">

<label>
Quincena de Proceso
</label>

<input
type="text"
name="quincena"
value="<?=h($quincena)?>"
placeholder="Ej. 2025-01"
>

<div class="actions">

<button class="btn btn-primary">
Buscar
</button>

</div>

</form>

</div>

<!-- =====================================
     FILTROS
===================================== -->

<div class="card">

<strong>
Quincena:
</strong>

<?= $quincena !== '' ? h($quincena) : 'TODAS' ?>

<div
class="filters"
style="margin-top:.7rem;"
>

<a
class="btn <?= $mov===''?'btn-primary':'btn-ghost' ?>"
href="?quincena=<?=h($quincena)?>"
>
TODOS
</a>

<a
class="btn <?= $mov==='alta'?'btn-primary':'btn-ghost' ?>"
href="?quincena=<?=h($quincena)?>&mov=alta"
>
ALTA
</a>

<a
class="btn <?= $mov==='cambio'?'btn-primary':'btn-ghost' ?>"
href="?quincena=<?=h($quincena)?>&mov=cambio"
>
CAMBIOS
</a>

<a
class="btn <?= $mov==='baja'?'btn-primary':'btn-ghost' ?>"
href="?quincena=<?=h($quincena)?>&mov=baja"
>
BAJA
</a>

<a
class="btn <?= $mov==='reintegro'?'btn-primary':'btn-ghost' ?>"
href="?quincena=<?=h($quincena)?>&mov=reintegro"
>
REINTEGRO
</a>

</div>

</div>

<!-- =====================================
     TABLA
===================================== -->

<div
class="card"
style="overflow-x:auto;"
>

<table>

<thead>

<tr>

<th>ID</th>
<th>Movimiento</th>
<th>Trámite</th>
<th>Beneficiario</th>
<th>RFC Beneficiario</th>
<th>RFC Trabajador</th>
<th>CURP</th>
<th>Nombre Trabajador</th>
<th>Qna Inicio</th>
<th>Qna Fin</th>
<th>Estatus</th>

</tr>

</thead>

<tbody>

<?php if (!$rows): ?>

<tr>

<td colspan="11" class="muted">
No hay registros encontrados.
</td>

</tr>

<?php else: ?>

<?php foreach ($rows as $r): ?>

<tr>

<td>
<?=h($r['id_movimiento'])?>
</td>

<td class="nowrap">
<?=h(movTxt($r['tipo_movimiento']))?>
</td>

<td class="nowrap">
<?=h(tramTxt($r['tipo_tramite']))?>
</td>

<td>
<?=h($r['nombre_beneficiario'])?>
</td>

<td>
<?=h($r['rfc_beneficiario'])?>
</td>

<td>
<?=h($r['rfc_trabajador'])?>
</td>

<td>
<?=h($r['curp_trabajador'])?>
</td>

<td>
<?=h($r['nombre_trabajador'])?>
</td>

<td class="nowrap">
<?=h($r['quincena_inicio'])?>
</td>

<td class="nowrap">
<?=h($r['quincena_fin'])?>
</td>

<td class="nowrap">
<?=h($r['estatus'])?>
</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</body>
</html>