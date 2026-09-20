<?php
require_once "conexion.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT intentos_simulador, intentos_final FROM Usuarios WHERE id_usuario = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario"]["id"]]);
$datos = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú</title>

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
        }

        .contenedor {
            width: 420px;
            background: rgba(255,255,255,0.12);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            color: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }

        h2 {
            margin-bottom: 25px;
        }

        .info {
            background: rgba(255,255,255,0.18);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: left;
        }

        .info p {
            margin: 10px 0;
        }

        .boton {
            display: block;
            width: 100%;
            padding: 13px;
            margin: 12px 0;
            border-radius: 10px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            transition: 0.3s;
        }

        .boton:hover {
            transform: scale(1.04);
        }

        .cerrar {
            background: linear-gradient(to right, #ff416c, #ff4b2b);
        }
    </style>
</head>

<body>

<div class="contenedor">
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION["usuario"]["nombre"]); ?></h2>

    <div class="info">
        <p>Intentos práctica usados: 
            <strong><?php echo $datos["intentos_simulador"]; ?> / 6</strong>
        </p>

        <p>Intentos final usados: 
            <strong><?php echo $datos["intentos_final"]; ?> / 3</strong>
        </p>
    </div>

    <a class="boton" href="iniciar_examen.php?tipo=practica">
        Iniciar simulador de práctica
    </a>

    <a class="boton" href="iniciar_examen.php?tipo=final">
        Iniciar examen final
    </a>

    <a class="boton cerrar" href="logout.php">
        Cerrar sesión
    </a>
</div>

</body>
</html>