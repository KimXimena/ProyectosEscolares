<?php
session_start();
require_once "conexion.php";

$mensaje = "";
$tipoMensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST["nombre"] ?? "");
    $pwd = trim($_POST["contrasena"] ?? "");
    $confirmarPwd = trim($_POST["confirmar_contrasena"] ?? "");

    $regexPassword = "/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.#_-])[A-Za-z\d@$!%*?&.#_-]{8,}$/";

    if ($usuario === "" || $pwd === "" || $confirmarPwd === "") {

        $mensaje = "Necesitas completar todos los campos.";
        $tipoMensaje = "error";

    } elseif ($pwd !== $confirmarPwd) {

        $mensaje = "Las contraseñas no coinciden.";
        $tipoMensaje = "error";

    } elseif (!preg_match($regexPassword, $pwd)) {

        $mensaje = "La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial.";
        $tipoMensaje = "error";

    } else {

        try {

            // Buscar el ID del rol admin
            $stmtRol = $pdo->prepare("
                SELECT ID_ROL
                FROM rol
                WHERE LOWER(TRIM(NOMBRE_ROL)) = 'admin'
                LIMIT 1
            ");
            $stmtRol->execute();
            $rol = $stmtRol->fetch(PDO::FETCH_ASSOC);

            if (!$rol) {

                $mensaje = "No existe el rol admin en la tabla rol.";
                $tipoMensaje = "error";

            } else {

                $idRolAdmin = $rol["ID_ROL"];

                // Verificar si el usuario ya existe
                $stmtExiste = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM usuario
                    WHERE LOWER(TRIM(USUARIO)) = LOWER(?)
                ");
                $stmtExiste->execute([$usuario]);
                $existe = $stmtExiste->fetchColumn();

                if ($existe > 0) {

                    $mensaje = "Ese usuario ya existe.";
                    $tipoMensaje = "error";

                } else {

                    // Insertar usuario administrador
                    $stmt = $pdo->prepare("
                        INSERT INTO usuario 
                        (USUARIO, PWD, ID_ROL)
                        VALUES (?, ?, ?)
                    ");

                    $stmt->execute([
                        $usuario,
                        password_hash($pwd, PASSWORD_DEFAULT),
                        $idRolAdmin
                    ]);

                    $mensaje = "Administrador registrado correctamente. Ya puedes iniciar sesión.";
                    $tipoMensaje = "exito";
                }
            }

        } catch (PDOException $e) {

            $mensaje = "No se pudo registrar el usuario.";
            $tipoMensaje = "error";

            // Para ver el error real mientras estás depurando:
            // $mensaje = "Error SQL: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <title>Registro de administrador</title>

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
            max-width:450px;
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
    <p class="subtitulo">Registro de administrador</p>

    <form method="POST" id="formRegistro">

        <input 
            type="text" 
            name="nombre" 
            placeholder="Usuario administrador"
            required
        >

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
            Registrar administrador
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.getElementById("formRegistro").addEventListener("submit", function(e){

    let usuario = document.querySelector("[name='nombre']").value.trim();
    let contrasena = document.querySelector("[name='contrasena']").value.trim();
    let confirmar = document.querySelector("[name='confirmar_contrasena']").value.trim();

    let regexPassword =
    /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.#_-])[A-Za-z\d@$!%*?&.#_-]{8,}$/;

    if(usuario === "" || contrasena === "" || confirmar === ""){

        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Campos incompletos',
            text: 'Necesitas completar todos los campos.',
            confirmButtonText: 'DE ACUERDO',
            confirmButtonColor: '#2563eb'
        });

        return;
    }

    if(contrasena !== confirmar){

        e.preventDefault();

        Swal.fire({
            icon: 'error',
            title: 'Contraseñas diferentes',
            text: 'Las contraseñas no coinciden.',
            confirmButtonText: 'DE ACUERDO',
            confirmButtonColor: '#dc2626'
        });

        return;
    }

    if(!regexPassword.test(contrasena)){

        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Contraseña insegura',
            text: 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial.',
            confirmButtonText: 'DE ACUERDO',
            confirmButtonColor: '#2563eb'
        });

        return;
    }

});

</script>

</body>
</html>