<?php
require_once "conexion.php";

if (isset($_SESSION['usuario'])) {
    header("Location: menu.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Simulador de Examen</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .contenedor {
            text-align: center;
            color: white;
            background: rgba(255,255,255,0.12);
            padding: 50px;
            border-radius: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }

        h1 {
            font-size: 32px;
            margin-bottom: 30px;
        }

        .botones a {
            display: inline-block;
            margin: 10px;
            padding: 14px 25px;
            border-radius: 12px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            transition: 0.3s;
        }

        .botones a:hover {
            transform: scale(1.08);
        }
    </style>
</head>

<body>

<div class="contenedor">
    <h1>Simulador de Examen de Manejo</h1>

    <div class="botones">
        <a href="registro.php">Registrarse</a>
        <a href="login.php">Iniciar sesión</a>
    </div>
</div>

</body>
</html>