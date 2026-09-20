<?php
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $correo = trim($_POST["correo"]);
    $contrasena = trim($_POST["contrasena"]);

    if ($nombre === "" || $correo === "" || $contrasena === "") {
        $mensaje = "Todos los campos son obligatorios.";
    } else {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO Usuarios (nombre, correo, contraseña) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $correo, $hash]);
            $mensaje = "Usuario registrado correctamente. <a href='login.php'>Inicia sesión aquí</a>";
        } catch (PDOException $e) {
            $mensaje = "Error: ese correo ya existe.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>

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
            width: 380px;
            background: rgba(255, 255, 255, 0.12);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            color: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }

        h2 {
            margin-bottom: 20px;
            font-size: 28px;
        }

        input {
            width: 100%;
            padding: 13px;
            margin: 10px 0;
            border: none;
            border-radius: 10px;
            outline: none;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 12px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            color: white;
            font-weight: bold;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: scale(1.04);
        }

        .mensaje {
            margin-top: 15px;
            color: #ffdddd;
        }

        .mensaje a {
            color: white;
            font-weight: bold;
        }

        .volver {
            display: block;
            margin-top: 18px;
            color: #ddd;
            text-decoration: none;
        }

        .volver:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="contenedor">
    <h2>Registro</h2>

    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="correo" placeholder="Correo" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <button type="submit">Registrarse</button>
    </form>

    <?php if ($mensaje): ?>
        <p class="mensaje"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <a class="volver" href="index.php">Volver</a>
</div>

</body>
</html>