<?php
require_once "conexion.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["tipo"])) {
    die("Tipo de examen no especificado.");
}

$tipo = $_GET["tipo"];

if ($tipo !== "practica" && $tipo !== "final") {
    die("Tipo de examen inválido.");
}

$idUsuario = $_SESSION["usuario"]["id"];

$stmt = $pdo->prepare("SELECT intentos_simulador, intentos_final FROM Usuarios WHERE id_usuario = ?");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch();

if ($tipo === "practica") {
    if ($usuario["intentos_simulador"] >= 6) {
        die("Ya alcanzaste el máximo de 6 intentos de práctica.");
    }
    $limitePreguntas = 20;
    $valorPregunta = 5;
} else {
    if ($usuario["intentos_final"] >= 3) {
        die("Ya alcanzaste el máximo de 3 intentos del examen final.");
    }
    $limitePreguntas = 40;
    $valorPregunta = 2.5;
}

$sql = "SELECT id_pregunta FROM Preguntas ORDER BY RAND() LIMIT $limitePreguntas";
$preguntas = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);

$_SESSION["examen"] = [
    "tipo" => $tipo,
    "preguntas" => $preguntas,
    "indice" => 0,
    "correctas" => 0,
    "valor" => $valorPregunta,
    "total" => $limitePreguntas
];

header("Location: examen.php");
exit;
?>