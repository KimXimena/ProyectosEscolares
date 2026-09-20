<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$matricula = $_SESSION["usuario"]["matricula"];

/* Aplicaciones de práctica */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM historial_estudiante
    WHERE MATRICULA = ?
    AND TIPO_TEST = 'practica'
");
$stmt->execute([$matricula]);
$intentosPractica = $stmt->fetchColumn();

/* Aplicaciones diagnósticas */
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM historial_estudiante
    WHERE MATRICULA = ?
    AND TIPO_TEST = 'final'
");
$stmt->execute([$matricula]);
$intentosFinal = $stmt->fetchColumn();

$restantesPractica = 6 - $intentosPractica;
$restantesFinal = 3 - $intentosFinal;
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <title>SisAT - Diagnóstico Académico</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:"Segoe UI", Arial, sans-serif;
        }

        body{
            background:#eef2f7;
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
        }

        .contenedor{
            width:100%;
            max-width:560px;
            background:white;
            padding:35px;
            border-radius:18px;
            box-shadow:0 8px 24px rgba(0,0,0,.08);
        }

        .encabezado{
            text-align:center;
            margin-bottom:25px;
        }

        .encabezado h1{
            color:#0b2a4a;
            font-size:26px;
            margin-bottom:8px;
        }

        .encabezado p{
            color:#64748b;
            font-size:15px;
        }

        h2{
            text-align:center;
            margin-bottom:20px;
            color:#0b2a4a;
            font-size:22px;
        }

        .info{
            background:#f8fafc;
            border:1px solid #dbe3ef;
            border-radius:14px;
            padding:18px;
            margin-bottom:24px;
        }

        .info h3{
            color:#0b2a4a;
            font-size:17px;
            margin-bottom:12px;
        }

        .info p{
            margin-bottom:10px;
            font-size:15px;
            color:#334155;
        }

        .info strong{
            color:#0b63ce;
        }

        .evaluacion{
            background:#ffffff;
            border:1px solid #dbe3ef;
            border-radius:14px;
            padding:18px;
            margin-bottom:16px;
            box-shadow:0 4px 12px rgba(15,23,42,.05);
        }

        .evaluacion h3{
            color:#0b2a4a;
            font-size:18px;
            margin-bottom:6px;
        }

        .evaluacion p{
            color:#64748b;
            font-size:14px;
            margin-bottom:14px;
            line-height:1.4;
        }

        .boton{
            display:block;
            width:100%;
            text-align:center;
            background:#0b2a4a;
            color:white;
            padding:14px;
            border-radius:10px;
            text-decoration:none;
            font-weight:bold;
            transition:.2s;
        }

        .boton:hover{
            background:#123d69;
        }

        .boton-secundario{
            background:#2563eb;
            margin-top:14px;
        }

        .boton-secundario:hover{
            background:#1d4ed8;
        }

        .boton-salir{
            background:#dc2626;
            margin-top:14px;
        }

        .boton-salir:hover{
            background:#b91c1c;
        }

        .agotado{
            background:#94a3b8 !important;
            cursor:not-allowed;
            pointer-events:none;
        }

        .badge{
            display:inline-block;
            background:#eaf2ff;
            color:#0b63ce;
            padding:6px 10px;
            border-radius:8px;
            font-size:13px;
            font-weight:bold;
            margin-bottom:10px;
        }

    </style>

</head>

<body>

<div class="contenedor">

    <div class="encabezado">
        <h1>SisAT - Diagnóstico Académico</h1>
        <p>Prototipo de aplicación de instrumentos diagnósticos</p>
    </div>

    <h2>
        Bienvenido, <?php echo htmlspecialchars($_SESSION["usuario"]["nombre"]); ?>
    </h2>

    <div class="info">

        <h3>Resumen de aplicaciones</h3>

        <p>
            Instrumentos de práctica realizados:
            <strong>
                <?php echo $intentosPractica; ?> / 6
            </strong>
        </p>

        <p>
            Aplicaciones de práctica restantes:
            <strong>
                <?php echo $restantesPractica; ?>
            </strong>
        </p>

        <br>

        <p>
            Evaluaciones diagnósticas realizadas:
            <strong>
                <?php echo $intentosFinal; ?> / 3
            </strong>
        </p>

        <p>
            Evaluaciones diagnósticas restantes:
            <strong>
                <?php echo $restantesFinal; ?>
            </strong>
        </p>

    </div>

    <div class="evaluacion">
        <span class="badge">Instrumento de práctica</span>
        <h3>Exploración de habilidades básicas</h3>
        <p>
            Actividad de práctica orientada a familiarizar al estudiante con el formato de los reactivos diagnósticos.
        </p>

        <?php if($restantesPractica > 0): ?>

            <a class="boton" href="iniciar_examen.php?tipo=practica">
                Iniciar práctica
            </a>

        <?php else: ?>

            <a class="boton agotado">
                Práctica agotada
            </a>

        <?php endif; ?>
    </div>

    <div class="evaluacion">
        <span class="badge">Evaluación diagnóstica</span>
        <h3>Aplicación formal del instrumento</h3>
        <p>
            Evaluación orientada a registrar resultados para el seguimiento académico del estudiante.
        </p>

        <?php if($restantesFinal > 0): ?>

            <a class="boton" href="iniciar_examen.php?tipo=final">
                Iniciar evaluación diagnóstica
            </a>

        <?php else: ?>

            <a class="boton agotado">
                Evaluación diagnóstica agotada
            </a>

        <?php endif; ?>
    </div>

    <a class="boton boton-secundario" href="dashboard.php">
        Ver panel de seguimiento
    </a>

    <a class="boton boton-salir" href="logout.php">
        Cerrar sesión
    </a>

</div>

</body>
</html>