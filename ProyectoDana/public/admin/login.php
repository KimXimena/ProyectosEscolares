<?php
session_start();
require_once __DIR__ . "/../../config/conexion.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($usuario === "" || $password === "") {
        $error = "Ingresa usuario y contraseña.";
    } else {
        $stmt = $pdo->prepare("
            SELECT *
            FROM administradores
            WHERE usuario = ?
            AND activo = 1
            LIMIT 1
        ");
        $stmt->execute([$usuario]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin["password_hash"])) {
            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_nombre"] = $admin["nombre"];
            $_SESSION["admin_usuario"] = $admin["usuario"];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Login</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Panel de Administración</h1>
    <p>Inicia sesión para continuar</p>
</header>

<main class="contenedor">
    <section class="seleccion-boletos login-box">
        <h2>Iniciar sesión</h2>

        <?php if ($error): ?>
            <p class="mensaje-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" class="formulario-compra">
            <label>Usuario</label>
            <input type="text" name="usuario" required>

            <label>Contraseña</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn">
                Entrar
            </button>
        </form>
    </section>
</main>

</body>
</html>