<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$rolSesion = strtolower(trim($_SESSION["usuario"]["rol"] ?? ""));

if ($rolSesion !== "admin") {
    header("Location: menu.php");
    exit;
}

/* TOTAL DE ALUMNOS */
$totalAlumnos = $pdo->query("
    SELECT COUNT(*) 
    FROM estudiante
")->fetchColumn();

/* TOTAL DE APLICACIONES */
$totalAplicaciones = $pdo->query("
    SELECT COUNT(*) 
    FROM historial_estudiante
")->fetchColumn();

/* RESULTADO PROMEDIO GENERAL */
$promedioGeneral = $pdo->query("
    SELECT AVG(CALIFICACION) 
    FROM historial_estudiante
")->fetchColumn();

/* NIVEL ESPERADO */
$nivelEsperado = $pdo->query("
    SELECT COUNT(*) 
    FROM historial_estudiante
    WHERE CALIFICACION >= 75
")->fetchColumn();

/* REQUIERE APOYO */
$requiereApoyo = $pdo->query("
    SELECT COUNT(*) 
    FROM historial_estudiante
    WHERE CALIFICACION < 75
")->fetchColumn();

/* TABLA GENERAL */
$alumnos = $pdo->query("
    SELECT 
        e.MATRICULA,
        e.NOMBRE,
        e.PATERNO,
        e.MATERNO,
        e.EMAIL,
        COUNT(h.ID_RECORD) AS aplicaciones,
        AVG(h.CALIFICACION) AS promedio
    FROM estudiante e
    LEFT JOIN historial_estudiante h
        ON e.MATRICULA = h.MATRICULA
    GROUP BY 
        e.MATRICULA,
        e.NOMBRE,
        e.PATERNO,
        e.MATERNO,
        e.EMAIL
    ORDER BY promedio IS NULL, promedio DESC
")->fetchAll(PDO::FETCH_ASSOC);

$promedioGeneral = $promedioGeneral ?: 0;
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Panel institucional</title>

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
    height:80px;
    background:#0b2a4a;
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 35px;
}

.topbar h1{
    font-size:24px;
}

.topbar p{
    font-size:13px;
    opacity:.85;
    margin-top:3px;
}

.usuario{
    display:flex;
    align-items:center;
    gap:18px;
    font-weight:bold;
}

.btn-salir{
    background:#dc2626;
    color:white;
    padding:10px 16px;
    border-radius:8px;
    text-decoration:none;
}

.main{
    padding:30px;
}

.titulo{
    margin-bottom:25px;
}

.titulo h2{
    color:#0b2a4a;
    font-size:30px;
}

.titulo p{
    color:#64748b;
    margin-top:5px;
}

.grid{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:18px;
    margin-bottom:30px;
}

.card{
    background:white;
    padding:22px;
    border-radius:14px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    border-left:6px solid #2563eb;
}

.card.total{
    border-left-color:#f59e0b;
}

.card.promedio{
    border-left-color:#2563eb;
}

.card.esperado{
    border-left-color:#16a34a;
}

.card.apoyo{
    border-left-color:#dc2626;
}

.card small{
    color:#64748b;
    font-weight:bold;
}

.card h3{
    font-size:32px;
    margin-top:8px;
    color:#0b2a4a;
}

.panel{
    background:white;
    padding:25px;
    border-radius:14px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    overflow-x:auto;
}

.panel h3{
    margin-bottom:20px;
    color:#0b2a4a;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#f1f5f9;
    padding:14px;
    text-align:left;
    color:#334155;
}

td{
    padding:14px;
    border-bottom:1px solid #e5e7eb;
}

.estado-ok{
    color:#16a34a;
    font-weight:bold;
}

.estado-no{
    color:#dc2626;
    font-weight:bold;
}

.estado-neutro{
    color:#64748b;
    font-weight:bold;
}

.badge{
    background:#0b2a4a;
    color:white;
    padding:6px 10px;
    border-radius:8px;
    font-size:13px;
    display:inline-block;
}

@media(max-width:1200px){
    .grid{
        grid-template-columns:repeat(2,1fr);
    }
}

@media(max-width:700px){
    .grid{
        grid-template-columns:1fr;
    }

    .topbar{
        height:auto;
        padding:20px;
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }

    .usuario{
        width:100%;
        justify-content:space-between;
    }
}

</style>

</head>

<body>

<header class="topbar">

    <div>
        <h1>SisAT - Panel institucional</h1>
        <p>Seguimiento general de aplicaciones diagnósticas</p>
    </div>

    <div class="usuario">

        <?php echo htmlspecialchars($_SESSION["usuario"]["nombre"]); ?>

        <a class="btn-salir" href="logout.php">
            Cerrar sesión
        </a>

    </div>

</header>

<main class="main">

<div class="titulo">

    <h2>Dashboard general</h2>

    <p>
        Estadísticas generales de estudiantes, aplicaciones y resultados académicos.
    </p>

</div>

<div class="grid">

    <div class="card total">
        <small>Total de alumnos</small>
        <h3><?php echo $totalAlumnos; ?></h3>
    </div>

    <div class="card">
        <small>Total de aplicaciones</small>
        <h3><?php echo $totalAplicaciones; ?></h3>
    </div>

    <div class="card promedio">
        <small>Resultado promedio</small>
        <h3><?php echo number_format($promedioGeneral, 2); ?>%</h3>
    </div>

    <div class="card esperado">
        <small>Nivel esperado</small>
        <h3><?php echo $nivelEsperado; ?></h3>
    </div>

    <div class="card apoyo">
        <small>Requiere apoyo</small>
        <h3><?php echo $requiereApoyo; ?></h3>
    </div>

</div>

<div class="panel">

    <h3>Seguimiento de estudiantes</h3>

    <table>

        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Alumno</th>
                <th>Email</th>
                <th>Aplicaciones</th>
                <th>Resultado promedio</th>
                <th>Nivel</th>
            </tr>
        </thead>

        <tbody>

        <?php foreach($alumnos as $a): ?>

            <tr>

                <td><?php echo htmlspecialchars($a["MATRICULA"]); ?></td>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $a["NOMBRE"] . " " .
                        $a["PATERNO"] . " " .
                        $a["MATERNO"]
                    );
                    ?>
                </td>

                <td><?php echo htmlspecialchars($a["EMAIL"]); ?></td>

                <td>
                    <span class="badge">
                        <?php echo $a["aplicaciones"]; ?>
                    </span>
                </td>

                <td>
                    <?php
                    if ($a["promedio"] !== null) {
                        echo number_format($a["promedio"], 2) . "%";
                    } else {
                        echo "Sin datos";
                    }
                    ?>
                </td>

                <td>
                    <?php if ($a["promedio"] === null): ?>

                        <span class="estado-neutro">SIN APLICACIONES</span>

                    <?php elseif ($a["promedio"] >= 75): ?>

                        <span class="estado-ok">NIVEL ESPERADO</span>

                    <?php else: ?>

                        <span class="estado-no">REQUIERE APOYO</span>

                    <?php endif; ?>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

</main>

</body>
</html>