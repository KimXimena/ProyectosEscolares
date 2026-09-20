<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$tipo = $_GET["tipo"] ?? "practica";

/*
    Se conservan los valores internos:
    - practica
    - final

    Esto se hace para no modificar la base de datos ni romper
    la lógica que ya utiliza historial_estudiante.TIPO_TEST.
*/

if ($tipo === "final") {
    // Evaluación diagnóstica formal
    $limite = 40;
    $valor = 2.5;
} else {
    // Instrumento de práctica
    $tipo = "practica";
    $limite = 20;
    $valor = 5;
}

// Seleccionar reactivos aleatorios sin repetir
$sql = "SELECT ID_PREGUNTA FROM preguntas ORDER BY RAND() LIMIT $limite";
$preguntas = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);

// Validar que existan reactivos disponibles
if (count($preguntas) === 0) {
    header("Location: menu.php");
    exit;
}

/*
    Se mantiene el nombre $_SESSION["examen"]
    porque examen.php y resultado.php ya lo utilizan.
*/
$_SESSION["examen"] = [
    "tipo" => $tipo,
    "preguntas" => $preguntas,
    "indice" => 0,
    "correctas" => 0,
    "total" => count($preguntas),
    "valor" => $valor
];

header("Location: examen.php");
exit;