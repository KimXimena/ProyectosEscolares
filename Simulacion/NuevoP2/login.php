<?php
session_start();
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $login = trim($_POST["login"] ?? "");
    $contrasena = trim($_POST["contrasena"] ?? "");

    if ($login === "" || $contrasena === "") {

        $mensaje = "Completa todos los campos.";

    } elseif (filter_var($login, FILTER_VALIDATE_EMAIL)) {

        // LOGIN DE ESTUDIANTE
        $sql = "
            SELECT 
                MATRICULA,
                NOMBRE,
                EMAIL,
                CONTRASENA
            FROM estudiante
            WHERE LOWER(TRIM(EMAIL)) = LOWER(?)
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {

            $hashGuardado = trim($usuario["CONTRASENA"] ?? "");

            if (password_verify($contrasena, $hashGuardado)) {

                session_regenerate_id(true);

                $_SESSION["usuario"] = [
                    "matricula" => $usuario["MATRICULA"],
                    "nombre" => $usuario["NOMBRE"],
                    "rol" => "alumno"
                ];

                header("Location: menu.php");
                exit;

            } else {
                $mensaje = "Correo o contraseña incorrectos.";
            }

        } else {
            $mensaje = "Correo o contraseña incorrectos.";
        }

    } else {

        // LOGIN DE ADMINISTRADOR / USUARIO INTERNO
        $sql = "
            SELECT 
                u.USUARIO,
                u.PWD,
                r.NOMBRE_ROL
            FROM usuario u
            INNER JOIN rol r
                ON u.ID_ROL = r.ID_ROL
            WHERE TRIM(u.USUARIO) = ?
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {

            $hashGuardado = trim($usuario["PWD"] ?? "");
            $rol = strtolower(trim($usuario["NOMBRE_ROL"] ?? ""));

            if (password_verify($contrasena, $hashGuardado)) {

                session_regenerate_id(true);

                $_SESSION["usuario"] = [
                    "matricula" => null,
                    "nombre" => trim($usuario["USUARIO"]),
                    "rol" => $rol
                ];

                if ($rol === "admin") {
                    header("Location: admin.php");
                } else {
                    header("Location: menu.php");
                }

                exit;

            } else {
                $mensaje = "Usuario o contraseña incorrectos.";
            }

        } else {
            $mensaje = "Usuario o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SisAT - Acceso al sistema</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

<div class="contenedor contenedor-chico">

    <h2>SisAT - Diagnóstico Académico</h2>

    <p style="text-align:center; color:#64748b;">
        Acceso al sistema de instrumentos diagnósticos
    </p>

    <form method="POST">

        <input 
            type="text" 
            name="login" 
            placeholder="Correo institucional o usuario"
            required
        >

        <input 
            type="password" 
            name="contrasena" 
            placeholder="Contraseña" 
            required
        >

        <button type="submit">
            Iniciar sesión
        </button>

    </form>

    <p class="mensaje">
        <?php echo htmlspecialchars($mensaje); ?>
    </p>

    <a class="boton boton-secundario" href="registro.php">
        Registrar estudiante
    </a>

</div>

</body>
</html>