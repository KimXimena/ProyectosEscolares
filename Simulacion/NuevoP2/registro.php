<?php
session_start();
require_once "conexion.php";

$mensaje = "";
$tipoMensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $matricula = trim($_POST["matricula"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $paterno = trim($_POST["paterno"] ?? "");
    $materno = trim($_POST["materno"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $contrasena = trim($_POST["contrasena"] ?? "");
    $confirmarContrasena = trim($_POST["confirmar_contrasena"] ?? "");

    $regexPassword = "/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.#_-])[A-Za-z\d@$!%*?&.#_-]{8,}$/";

    if (
        $matricula === "" ||
        $nombre === "" ||
        $email === "" ||
        $contrasena === "" ||
        $confirmarContrasena === ""
    ) {

        $mensaje = "Necesitas completar los campos obligatorios.";
        $tipoMensaje = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "El correo electrónico no tiene un formato válido.";
        $tipoMensaje = "error";

    } elseif ($contrasena !== $confirmarContrasena) {

        $mensaje = "Las contraseñas no coinciden.";
        $tipoMensaje = "error";

    } elseif (!preg_match($regexPassword, $contrasena)) {

        $mensaje = "La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial.";
        $tipoMensaje = "error";

    } else {

        try {

            $sql = "
                INSERT INTO estudiante 
                (MATRICULA, NOMBRE, PATERNO, MATERNO, EMAIL, TELEFONO, CONTRASENA)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $matricula,
                $nombre,
                $paterno,
                $materno,
                $email,
                $telefono,
                password_hash($contrasena, PASSWORD_DEFAULT)
            ]);

            $mensaje = "Estudiante registrado correctamente. Ya puedes iniciar sesión.";
            $tipoMensaje = "exito";

        } catch (PDOException $e) {

            $mensaje = "No se pudo registrar. La matrícula o el correo ya existen.";
            $tipoMensaje = "error";

            // SOLO PARA DEPURAR, si quieres ver el error real descomenta esta línea:
            // $mensaje = "Error SQL: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <title>Registro de estudiante</title>

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
            position:relative;
        }

        .volver{
            position:absolute;
            top:25px;
            left:25px;
            background:white;
            color:#0b2a4a;
            padding:12px 18px;
            border-radius:10px;
            text-decoration:none;
            font-weight:bold;
            box-shadow:0 4px 12px rgba(0,0,0,.08);
            transition:.2s;
        }

        .volver:hover{
            background:#e2e8f0;
        }

        .contenedor{
            width:100%;
            max-width:460px;
            background:white;
            padding:35px;
            border-radius:16px;
            box-shadow:0 8px 20px rgba(0,0,0,.08);
        }

        h2{
            text-align:center;
            margin-bottom:8px;
            color:#0b2a4a;
        }

        .subtitulo{
            text-align:center;
            color:#64748b;
            margin-bottom:25px;
            font-size:14px;
        }

        form{
            display:flex;
            flex-direction:column;
            gap:14px;
        }

        input{
            padding:14px;
            border:1px solid #d1d5db;
            border-radius:10px;
            font-size:15px;
            outline:none;
        }

        input:focus{
            border-color:#2563eb;
        }

        .info-pass{
            color:#64748b;
            font-size:13px;
            margin-top:-6px;
            margin-bottom:6px;
            line-height:1.4;
        }

        button{
            background:#0b2a4a;
            color:white;
            padding:14px;
            border:none;
            border-radius:10px;
            cursor:pointer;
            font-size:15px;
            font-weight:bold;
            transition:.2s;
        }

        button:hover{
            background:#123d69;
        }

        .mensaje{
            margin-top:18px;
            text-align:center;
            font-weight:600;
            line-height:1.4;
        }

        .mensaje.error{
            color:#dc2626;
        }

        .mensaje.exito{
            color:#16a34a;
        }

        .mensaje a{
            color:#2563eb;
            text-decoration:none;
            font-weight:bold;
        }

        .mensaje a:hover{
            text-decoration:underline;
        }

    </style>

</head>

<body>

<a class="volver" href="login.php">
    ← Volver al login
</a>

<div class="contenedor">

    <h2>SisAT - Diagnóstico Académico</h2>
    <p class="subtitulo">Registro de estudiante</p>

    <form method="POST" id="formRegistro">

        <input type="text" name="matricula" placeholder="Matrícula" required>

        <input type="text" name="nombre" placeholder="Nombre" required>

        <input type="text" name="paterno" placeholder="Apellido paterno">

        <input type="text" name="materno" placeholder="Apellido materno">

        <input type="email" name="email" placeholder="Correo electrónico" required>

        <input type="text" name="telefono" placeholder="Teléfono">

        <input 
            type="password" 
            name="contrasena" 
            id="contrasena" 
            placeholder="Contraseña"
            required
        >

        <input 
            type="password" 
            name="confirmar_contrasena" 
            id="confirmar_contrasena"
            placeholder="Confirmar contraseña"
            required
        >

        <small class="info-pass">
            La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial.
            Ejemplo: Admin123$
        </small>

        <button type="submit">
            Registrar estudiante
        </button>

    </form>

    <?php if ($mensaje !== ""): ?>
        <p class="mensaje <?php echo htmlspecialchars($tipoMensaje); ?>">
            <?php echo htmlspecialchars($mensaje); ?>

            <?php if ($tipoMensaje === "exito"): ?>
                <br>
                <a href="login.php">Iniciar sesión aquí</a>
            <?php endif; ?>
        </p>
    <?php endif; ?>

</div>

</body>
</html>