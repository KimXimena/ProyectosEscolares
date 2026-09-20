<?php
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idPreguntaActual = $examen["preguntas"][$indice];
    $idOpcion = isset($_POST["opcion"]) ? (int)$_POST["opcion"] : 0;

    if ($idOpcion > 0) {
        $stmt = $pdo->prepare("SELECT es_correcta FROM Opciones WHERE id_opcion = ? AND id_pregunta = ?");
        $stmt->execute([$idOpcion, $idPreguntaActual]);
        $opcion = $stmt->fetch();

        if ($opcion && $opcion["es_correcta"] == 1) {
            $examen["correctas"]++;
        }
    }

    $examen["indice"]++;

    if ($examen["indice"] >= $examen["total"]) {
        header("Location: resultado.php");
        exit;
    }

    header("Location: examen.php");
    exit;
}

$idPregunta = $examen["preguntas"][$indice];

$stmt = $pdo->prepare("SELECT * FROM Preguntas WHERE id_pregunta = ?");
$stmt->execute([$idPregunta]);
$pregunta = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM Opciones WHERE id_pregunta = ?");
$stmt->execute([$idPregunta]);
$opciones = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Examen</title>

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
            width: 650px;
            background: rgba(255,255,255,0.13);
            padding: 35px;
            border-radius: 20px;
            color: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }

        .encabezado {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .badge {
            background: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 12px;
            font-weight: bold;
        }

        .tiempo {
            background: rgba(255, 75, 43, 0.85);
        }

        .pregunta {
            font-size: 20px;
            margin: 20px 0;
            line-height: 1.4;
        }

        img {
            display: block;
            max-width: 280px;
            width: 100%;
            margin: 15px auto;
            border-radius: 15px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.3);
        }

        .opcion {
            display: block;
            background: rgba(255,255,255,0.18);
            padding: 14px;
            border-radius: 12px;
            margin: 12px 0;
            cursor: pointer;
            transition: 0.3s;
        }

        .opcion:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.02);
        }

        input[type="radio"] {
            margin-right: 10px;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 20px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: scale(1.03);
        }
    </style>

    <script>
        let tiempo = 60;

        function actualizarTemporizador() {
            const reloj = document.getElementById("tiempo");
            reloj.textContent = tiempo;

            if (tiempo <= 0) {
                document.getElementById("formExamen").submit();
            } else {
                tiempo--;
                setTimeout(actualizarTemporizador, 1000);
            }
        }

        window.onload = actualizarTemporizador;
    </script>
</head>

<body>

<div class="contenedor">

    <div class="encabezado">
        <div class="badge">
            <?php echo strtoupper($examen["tipo"]); ?>
        </div>

        <div class="badge">
            Pregunta <?php echo $indice + 1; ?> de <?php echo $total; ?>
        </div>

        <div class="badge tiempo">
            Tiempo: <span id="tiempo">60</span>s
        </div>
    </div>

    <form method="POST" id="formExamen">
        <p class="pregunta">
            <strong><?php echo htmlspecialchars($pregunta["pregunta"]); ?></strong>
        </p>

        <?php if (!empty($pregunta["imagen"])): ?>
            <img src="<?php echo htmlspecialchars($pregunta["imagen"]); ?>">
        <?php endif; ?>

        <?php foreach ($opciones as $op): ?>
            <label class="opcion">
                <input type="radio" name="opcion" value="<?php echo $op["id_opcion"]; ?>">
                <?php echo htmlspecialchars($op["texto_opcion"]); ?>
            </label>
        <?php endforeach; ?>

        <button type="submit">Siguiente</button>
    </form>

</div>

</body>
</html>