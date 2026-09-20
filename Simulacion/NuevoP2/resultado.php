<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["usuario"]) || !isset($_SESSION["examen"])) {
    header("Location: login.php");
    exit;
}

$examen = $_SESSION["examen"];
$matricula = $_SESSION["usuario"]["matricula"];

$correctas = $examen["correctas"];
$total = $examen["total"];
$valor = $examen["valor"];
$tipo = $examen["tipo"];

$porcentaje = ($total > 0) ? ($correctas / $total) * 100 : 0;

$estado = ($porcentaje >= 75) ? "NIVEL ESPERADO" : "REQUIERE APOYO";

$tipoVisual = ($tipo === "final")
    ? "Evaluación diagnóstica"
    : "Instrumento de práctica";

// Guardar en historial
$stmt = $pdo->prepare("
    INSERT INTO historial_estudiante
    (MATRICULA, TIPO_TEST, FECHA_HORA_REALIZA, CALIFICACION)
    VALUES (?, ?, NOW(), ?)
");
$stmt->execute([$matricula, $tipo, $porcentaje]);

unset($_SESSION["examen"]);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resultado diagnóstico</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

<div class="contenedor contenedor-chico">

    <h2>Resultado del instrumento</h2>

    <p>
        <strong>Tipo de aplicación:</strong>
        <?php echo htmlspecialchars($tipoVisual); ?>
    </p>

    <p>
        <strong>Reactivos correctos:</strong>
        <?php echo $correctas; ?> / <?php echo $total; ?>
    </p>

    <p>
        <strong>Resultado obtenido:</strong>
        <?php echo number_format($porcentaje, 2); ?>%
    </p>

    <p class="<?php echo ($estado == 'NIVEL ESPERADO') ? 'resultado-aprobado' : 'resultado-no'; ?>">
        <?php echo $estado; ?>
    </p>

    <p style="text-align:center; color:#64748b;">
        Este resultado forma parte del seguimiento académico del estudiante.
    </p>

    <a class="boton" href="dashboard.php">
        Ver panel de seguimiento
    </a>

    <a class="boton boton-secundario" href="menu.php">
        Volver a mis evaluaciones
    </a>

</div>

</body>
</html>