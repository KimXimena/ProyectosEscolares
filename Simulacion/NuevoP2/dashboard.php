<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$matricula = $_SESSION["usuario"]["matricula"];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM historial_estudiante WHERE MATRICULA = ?");
$stmt->execute([$matricula]);
$total = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT AVG(CALIFICACION) FROM historial_estudiante WHERE MATRICULA = ?");
$stmt->execute([$matricula]);
$promedio = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM historial_estudiante WHERE MATRICULA = ? AND CALIFICACION >= 75");
$stmt->execute([$matricula]);
$nivelEsperado = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM historial_estudiante WHERE MATRICULA = ? AND CALIFICACION < 75");
$stmt->execute([$matricula]);
$requiereApoyo = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT TIPO_TEST, COUNT(*) AS total 
    FROM historial_estudiante 
    WHERE MATRICULA = ?
    GROUP BY TIPO_TEST
");
$stmt->execute([$matricula]);
$tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT ID_RECORD, TIPO_TEST, FECHA_HORA_REALIZA, CALIFICACION
    FROM historial_estudiante
    WHERE MATRICULA = ?
    ORDER BY FECHA_HORA_REALIZA DESC
    LIMIT 10
");
$stmt->execute([$matricula]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

$promedio = $promedio ?: 0;

function nombreTipo($tipo) {
    if ($tipo === "final") {
        return "Evaluación diagnóstica";
    }

    if ($tipo === "practica") {
        return "Instrumento de práctica";
    }

    return ucfirst($tipo);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de seguimiento académico</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", Arial, sans-serif;
}

body {
    background: #eef2f7;
    color: #0f172a;
}

.topbar {
    height: 80px;
    background: linear-gradient(135deg, #061f45, #082f63);
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    box-shadow: 0 4px 18px rgba(0,0,0,.18);
}

.brand h1 {
    font-size: 24px;
}

.brand p {
    font-size: 14px;
    opacity: .85;
}

.usuario {
    display: flex;
    align-items: center;
    gap: 18px;
}

.btn-salir {
    background: #dc2626;
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
}

.layout {
    display: flex;
    min-height: calc(100vh - 80px);
}

.sidebar {
    width: 240px;
    background: white;
    padding: 28px 18px;
    border-right: 1px solid #dbe3ef;
}

.nav {
    display: block;
    padding: 14px 18px;
    margin-bottom: 12px;
    border-radius: 10px;
    text-decoration: none;
    color: #334155;
    font-weight: 600;
}

.nav.activo {
    background: #eaf2ff;
    color: #0b63ce;
}

.main {
    flex: 1;
    padding: 32px;
}

.titulo {
    margin-bottom: 25px;
}

.titulo h2 {
    font-size: 30px;
    color: #061f45;
}

.titulo p {
    color: #64748b;
    margin-top: 5px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.card {
    background: white;
    padding: 22px;
    border-radius: 14px;
    border: 1px solid #dbe3ef;
    box-shadow: 0 6px 18px rgba(15,23,42,.07);
}

.card small {
    color: #64748b;
    font-weight: bold;
    display: block;
    margin-bottom: 10px;
}

.card h3 {
    font-size: 32px;
    color: #061f45;
}

.card.esperado {
    border-left: 6px solid #16a34a;
}

.card.apoyo {
    border-left: 6px solid #dc2626;
}

.card.promedio {
    border-left: 6px solid #0b63ce;
}

.card.total {
    border-left: 6px solid #f59e0b;
}

.panel {
    background: white;
    border-radius: 14px;
    border: 1px solid #dbe3ef;
    box-shadow: 0 6px 18px rgba(15,23,42,.07);
    padding: 24px;
    margin-bottom: 25px;
}

.panel h3 {
    margin-bottom: 18px;
    color: #061f45;
}

.tipo-item {
    display: flex;
    justify-content: space-between;
    padding: 14px;
    background: #f8fafc;
    border-radius: 10px;
    margin-bottom: 10px;
    font-weight: 600;
}

.tipo-item span:last-child {
    color: #0b63ce;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    background: #f1f5f9;
    padding: 13px;
    color: #334155;
}

td {
    padding: 13px;
    border-bottom: 1px solid #e5e7eb;
}

.estado-ok {
    color: #16a34a;
    font-weight: bold;
}

.estado-no {
    color: #dc2626;
    font-weight: bold;
}

.btn-volver {
    display: inline-block;
    background: #061f45;
    color: white;
    padding: 13px 22px;
    border-radius: 9px;
    text-decoration: none;
    font-weight: bold;
}

@media(max-width: 1000px) {
    .grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .sidebar {
        display: none;
    }
}

@media(max-width: 600px) {
    .grid {
        grid-template-columns: 1fr;
    }

    .topbar {
        padding: 0 20px;
    }

    .usuario {
        display: none;
    }
}
</style>
</head>

<body>

<header class="topbar">
    <div class="brand">
        <h1>SisAT - Diagnóstico Académico</h1>
        <p>Panel personal de seguimiento académico</p>
    </div>

    <div class="usuario">
        <strong><?php echo htmlspecialchars($_SESSION["usuario"]["nombre"]); ?></strong>
        <a class="btn-salir" href="logout.php">Cerrar sesión</a>
    </div>
</header>

<div class="layout">

    <aside class="sidebar">
        <a class="nav" href="menu.php">Mis evaluaciones</a>
        <a class="nav" href="iniciar_examen.php?tipo=practica">Instrumento de práctica</a>
        <a class="nav" href="iniciar_examen.php?tipo=final">Evaluación diagnóstica</a>
        <a class="nav activo" href="dashboard.php">Panel de seguimiento</a>
    </aside>

    <main class="main">

        <div class="titulo">
            <h2>Seguimiento de <?php echo htmlspecialchars($_SESSION["usuario"]["nombre"]); ?></h2>
            <p>Resumen individual de aplicaciones, resultados y desempeño académico.</p>
        </div>

        <div class="grid">
            <div class="card total">
                <small>Total de aplicaciones</small>
                <h3><?php echo $total; ?></h3>
            </div>

            <div class="card promedio">
                <small>Resultado promedio</small>
                <h3><?php echo number_format($promedio, 2); ?>%</h3>
            </div>

            <div class="card esperado">
                <small>Aplicaciones en nivel esperado</small>
                <h3><?php echo $nivelEsperado; ?></h3>
            </div>

            <div class="card apoyo">
                <small>Aplicaciones que requieren apoyo</small>
                <h3><?php echo $requiereApoyo; ?></h3>
            </div>
        </div>

        <div class="panel">
            <h3>Mis aplicaciones por tipo de instrumento</h3>

            <?php if (count($tipos) > 0): ?>
                <?php foreach ($tipos as $t): ?>
                    <div class="tipo-item">
                        <span><?php echo htmlspecialchars(nombreTipo($t["TIPO_TEST"])); ?></span>
                        <span><?php echo $t["total"]; ?> aplicación(es)</span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aún no tienes aplicaciones registradas.</p>
            <?php endif; ?>
        </div>

        <div class="panel">
            <h3>Mis últimos resultados</h3>

            <?php if (count($historial) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Instrumento</th>
                            <th>Fecha</th>
                            <th>Resultado</th>
                            <th>Nivel</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($historial as $h): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(nombreTipo($h["TIPO_TEST"])); ?></td>
                                <td><?php echo $h["FECHA_HORA_REALIZA"]; ?></td>
                                <td><?php echo number_format($h["CALIFICACION"], 2); ?>%</td>
                                <td>
                                    <?php if ($h["CALIFICACION"] >= 75): ?>
                                        <span class="estado-ok">NIVEL ESPERADO</span>
                                    <?php else: ?>
                                        <span class="estado-no">REQUIERE APOYO</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aún no tienes historial registrado.</p>
            <?php endif; ?>
        </div>

        <a class="btn-volver" href="menu.php">Volver a mis evaluaciones</a>

    </main>

</div>

</body>
</html>