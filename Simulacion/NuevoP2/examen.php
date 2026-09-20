<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION["examen"])) {
    header("Location: menu.php");
    exit;
}

$examen = &$_SESSION["examen"];
$indice = $examen["indice"];
$total = $examen["total"];
$matricula = $_SESSION["usuario"]["matricula"];

$tipoVisual = ($examen["tipo"] === "final")
    ? "Evaluación diagnóstica"
    : "Instrumento de práctica";

$porcentajeAvance = (($indice + 1) / $total) * 100;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $idPregunta = $examen["preguntas"][$indice];
    $idRespuesta = isset($_POST["respuesta"]) ? (int)$_POST["respuesta"] : 0;

    $texto = "Sin responder";

    if ($idRespuesta > 0) {

        $stmt = $pdo->prepare("
            SELECT OPCION, OK
            FROM respuestas
            WHERE ID_PREGUNTA = ? AND ID_RESPUESTA = ?
        ");

        $stmt->execute([$idPregunta, $idRespuesta]);

        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($r) {

            $texto = $r["OPCION"];

            if ($r["OK"] == 1) {
                $examen["correctas"]++;
            }
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO examen_estudiante
        (ID_PREGUNTA, MATRICULA, RESPUESTA)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $idPregunta,
        $matricula,
        $texto
    ]);

    $examen["indice"]++;

    if ($examen["indice"] >= $examen["total"]) {
        header("Location: resultado.php");
        exit;
    }

    header("Location: examen.php");
    exit;
}

$idPreguntaActual = $examen["preguntas"][$indice];

$stmt = $pdo->prepare("
    SELECT p.*, b.RUTA
    FROM preguntas p
    LEFT JOIN banco_imagenes b
    ON p.CODIGO_IMAGEN = b.CODIGO_IMAGEN
    WHERE p.ID_PREGUNTA = ?
");

$stmt->execute([$idPreguntaActual]);

$pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT *
    FROM respuestas
    WHERE ID_PREGUNTA = ?
");

$stmt->execute([$idPreguntaActual]);

$respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>SisAT - Instrumento Diagnóstico</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Segoe UI", Arial, sans-serif;
}

body{
    background:#eef2f7;
    color:#0f172a;
}

.topbar{
    height:72px;
    background:#0b2a4a;
    color:white;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 30px;
}

.topbar h1{
    font-size:20px;
}

.topbar span{
    font-size:13px;
    opacity:.85;
}

.usuario{
    font-weight:bold;
}

.layout{
    display:flex;
}

.sidebar{
    width:240px;
    background:white;
    padding:20px;
    border-right:1px solid #e5e7eb;
    min-height:calc(100vh - 72px);
}

.nav{
    display:block;
    padding:12px;
    margin-bottom:10px;
    border-radius:8px;
    text-decoration:none;
    color:#334155;
}

.nav.activo{
    background:#eaf2ff;
    color:#2563eb;
    font-weight:bold;
}

.main{
    flex:1;
    padding:30px;
}

.card{
    background:white;
    border-radius:16px;
    padding:28px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    max-width:900px;
    margin:0 auto;
}

.exam-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
    gap:15px;
    flex-wrap:wrap;
}

.badge{
    background:#eaf2ff;
    color:#0b63ce;
    padding:8px 12px;
    border-radius:9px;
    font-weight:bold;
    font-size:14px;
}

.tiempo{
    background:#ecfdf5;
    color:#16a34a;
    padding:8px 12px;
    border-radius:9px;
    font-weight:bold;
}

.progreso{
    width:100%;
    height:10px;
    background:#e5e7eb;
    border-radius:20px;
    overflow:hidden;
    margin-bottom:25px;
}

.progreso-barra{
    height:100%;
    background:#2563eb;
    width:<?php echo $porcentajeAvance; ?>%;
    transition:.3s;
}

.instruccion{
    text-align:center;
    color:#64748b;
    margin-bottom:16px;
    font-size:15px;
}

.pregunta{
    text-align:center;
    font-size:24px;
    margin-bottom:25px;
    color:#0b2a4a;
}

.img{
    display:block;
    margin:0 auto 22px;
    max-width:450px;
    width:100%;
    border-radius:12px;
    border:1px solid #dbe3ef;
}

.opcion{
    display:flex;
    align-items:center;
    gap:12px;
    padding:16px;
    border:1px solid #d1d5db;
    border-radius:12px;
    margin-bottom:14px;
    cursor:pointer;
    background:#f8fafc;
    transition:.2s;
}

.opcion:hover{
    border-color:#2563eb;
    background:#f1f5ff;
}

.opcion input{
    width:18px;
    height:18px;
    accent-color:#2563eb;
}

.opcion:has(input:checked){
    border:2px solid #2563eb;
    background:#eef4ff;
}

.footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:24px;
    gap:15px;
}

.btn{
    padding:13px 22px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
    text-decoration:none;
    display:inline-block;
}

.btn-main{
    background:#0b2a4a;
    color:white;
}

.btn-main:hover{
    background:#123d69;
}

.btn-secundario{
    background:#e2e8f0;
    color:#334155;
}

.btn-secundario:hover{
    background:#cbd5e1;
}

@media(max-width:800px){
    .layout{
        display:block;
    }

    .sidebar{
        display:none;
    }

    .main{
        padding:18px;
    }

    .card{
        padding:22px;
    }

    .pregunta{
        font-size:21px;
    }
}

</style>

<script>

let t = 60;
let tiempoAgotado = false;

function timer(){

    document.getElementById("t").innerText =
    "00:" + String(t).padStart(2, "0");

    if(t <= 0){

        tiempoAgotado = true;
        document.getElementById("form").submit();

    }else{

        t--;
        setTimeout(timer, 1000);

    }
}

window.onload = timer;

</script>

</head>

<body>

<header class="topbar">

    <div>
        <h1>SisAT - Diagnóstico Académico</h1>
        <span>Aplicación de instrumentos diagnósticos</span>
    </div>

    <div class="usuario">
        <?php echo htmlspecialchars($_SESSION["usuario"]["nombre"]); ?>
    </div>

</header>

<div class="layout">

<aside class="sidebar">

    <a class="nav activo">
        Instrumento diagnóstico
    </a>

    <a class="nav" href="dashboard.php">
        Panel de seguimiento
    </a>

    <a class="nav" href="menu.php">
        Mis evaluaciones
    </a>

</aside>

<main class="main">

<div class="card">

<div class="exam-header">

    <div class="badge">
        <?php echo htmlspecialchars($tipoVisual); ?>
    </div>

    <div class="badge">
        Reactivo <?php echo $indice + 1; ?> de <?php echo $total; ?>
    </div>

    <div class="tiempo" id="t">
        01:00
    </div>

</div>

<div class="progreso">
    <div class="progreso-barra"></div>
</div>

<p class="instruccion">
    Lee cuidadosamente el reactivo y selecciona la opción que consideres correcta.
</p>

<h2 class="pregunta">
    <?php echo htmlspecialchars($pregunta["REACTIVO"]); ?>
</h2>

<?php if (!empty($pregunta["RUTA"])): ?>

    <img class="img" src="<?php echo $pregunta["RUTA"]; ?>">

<?php endif; ?>

<form method="POST" id="form">

<?php foreach($respuestas as $r): ?>

<label class="opcion">

    <input 
        type="radio"
        name="respuesta"
        value="<?php echo $r["ID_RESPUESTA"]; ?>"
    >

    <?php echo htmlspecialchars($r["OPCION"]); ?>

</label>

<?php endforeach; ?>

<div class="footer">

    <a class="btn btn-secundario" href="menu.php">
        Salir del instrumento
    </a>

    <button class="btn btn-main" type="submit">
        Siguiente →
    </button>

</div>

</form>

</div>

</main>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.getElementById("form").addEventListener("submit", function(e){

    let opcion = document.querySelector('input[name="respuesta"]:checked');

    if(!opcion && !tiempoAgotado){

        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Respuesta pendiente',
            text: 'Debes seleccionar una respuesta antes de continuar.',
            confirmButtonText: 'DE ACUERDO',
            confirmButtonColor: '#2563eb'
        });

    }

});

</script>

</body>
</html>