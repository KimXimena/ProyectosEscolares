<?php
require_once "conexion.php";

if (!isset($_SESSION["usuario"]) || !isset($_SESSION["examen"])) {
    header("Location: login.php");
    exit;
}

$examen = $_SESSION["examen"];
$idUsuario = $_SESSION["usuario"]["id"];

$correctas = $examen["correctas"];
$total = $examen["total"];
$valor = $examen["valor"];
$tipo = $examen["tipo"];

$calificacion = $correctas * $valor;
$porcentaje = ($correctas / $total) * 100;
$estado = ($porcentaje >= 75) ? "APROBADO" : "NO APROBADO";

if ($tipo === "practica") {
    $stmt = $pdo->prepare("UPDATE Usuarios SET intentos_simulador = intentos_simulador + 1 WHERE id_usuario = ?");
    $stmt->execute([$idUsuario]);
} else {
    $stmt = $pdo->prepare("UPDATE Usuarios SET intentos_final = intentos_final + 1 WHERE id_usuario = ?");
    $stmt->execute([$idUsuario]);
}

unset($_SESSION["examen"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
        }

        .contenedor {
            width: 420px;
            background: rgba(255,255,255,0.13);
            padding: 40px;
            border-radius: 22px;
            color: white;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }

        h2 {
            margin-bottom: 25px;
            font-size: 30px;
        }

        .estado {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 30px;
            font-weight: bold;
            margin-bottom: 20px;
            background: <?php echo ($estado === "APROBADO") ? "#00c853" : "#ff3d00"; ?>;
        }

        .resultado {
            background: rgba(255,255,255,0.18);
            padding: 18px;
            border-radius: 15px;
            margin-bottom: 20px;
            text-align: left;
        }

        .resultado p {
            margin: 12px 0;
            font-size: 16px;
        }

        .boton {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            transition: 0.3s;
        }

        .boton:hover {
            transform: scale(1.04);
        }
    </style>
</head>

<body>

<div class="contenedor">
    <h2>Resultado del examen</h2>

    <div class="estado">
        <?php echo $estado; ?>
    </div>

    <div class="resultado">
        <p><strong>Tipo:</strong> <?php echo htmlspecialchars($tipo); ?></p>
        <p><strong>Respuestas correctas:</strong> <?php echo $correctas; ?> de <?php echo $total; ?></p>
        <p><strong>Calificación:</strong> <?php echo number_format($calificacion, 2); ?></p>
        <p><strong>Porcentaje:</strong> <?php echo number_format($porcentaje, 2); ?>%</p>
    </div>

    <a class="boton" href="menu.php">Volver al menú</a>
</div>

</body>
</html>