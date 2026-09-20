<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aliasCuenta = trim($_POST["alias_cuenta"] ?? "principal");
    $banco = trim($_POST["banco"] ?? "");
    $titular = trim($_POST["titular"] ?? "");
    $numeroCuenta = trim($_POST["numero_cuenta"] ?? "");
    $clabe = trim($_POST["clabe"] ?? "");
    $tarjeta = trim($_POST["tarjeta"] ?? "");
    $instrucciones = trim($_POST["instrucciones"] ?? "");
    $activo = isset($_POST["activo"]) ? 1 : 0;

    if ($aliasCuenta === "" || $banco === "" || $titular === "") {
        $mensaje = "Alias, banco y titular son obligatorios.";
    } else {
        $sql = "
            INSERT INTO cuentas_pago (
                alias_cuenta,
                banco,
                titular,
                numero_cuenta,
                clabe,
                tarjeta,
                instrucciones,
                activo
            ) VALUES (
                :alias_cuenta,
                :banco,
                :titular,
                :numero_cuenta,
                :clabe,
                :tarjeta,
                :instrucciones,
                :activo
            )
            ON DUPLICATE KEY UPDATE
                banco = VALUES(banco),
                titular = VALUES(titular),
                numero_cuenta = VALUES(numero_cuenta),
                clabe = VALUES(clabe),
                tarjeta = VALUES(tarjeta),
                instrucciones = VALUES(instrucciones),
                activo = VALUES(activo),
                updated_at = CURRENT_TIMESTAMP
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":alias_cuenta" => $aliasCuenta,
            ":banco" => $banco,
            ":titular" => $titular,
            ":numero_cuenta" => $numeroCuenta ?: null,
            ":clabe" => $clabe ?: null,
            ":tarjeta" => $tarjeta ?: null,
            ":instrucciones" => $instrucciones ?: null,
            ":activo" => $activo
        ]);

        $mensaje = "Datos de pago guardados correctamente.";
    }
}

$stmt = $pdo->prepare("
    SELECT *
    FROM cuentas_pago
    WHERE alias_cuenta = 'principal'
    LIMIT 1
");

$stmt->execute();
$cuenta = $stmt->fetch();

if (!$cuenta) {
    $cuenta = [
        "alias_cuenta" => "principal",
        "banco" => "",
        "titular" => "",
        "numero_cuenta" => "",
        "clabe" => "",
        "tarjeta" => "",
        "instrucciones" => "",
        "activo" => 1
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuentas de pago</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Cuentas de pago</h1>
    <p>Configura los datos bancarios que verá el cliente</p>
</header>

<main class="contenedor">

    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="rifas.php">Rifas</a>
        <a href="compras.php">Compras</a>
        <a href="ganadores.php">Ganadores</a>
        <a href="cuentas_pago.php">Cuentas de pago</a>
        <a href="liberar_apartados.php">Liberar apartados</a>
        <a href="../index.php">Ver sitio</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <section class="seleccion-boletos">

        <h2>Cuenta principal</h2>

        <?php if ($mensaje): ?>
            <p class="mensaje-exito">
                <?php echo htmlspecialchars($mensaje); ?>
            </p>
        <?php endif; ?>

        <form method="POST" class="formulario-compra">

            <label>Alias de cuenta</label>
            <input 
                type="text" 
                name="alias_cuenta" 
                value="<?php echo htmlspecialchars($cuenta["alias_cuenta"]); ?>" 
                required
            >

            <label>Banco</label>
            <input 
                type="text" 
                name="banco" 
                value="<?php echo htmlspecialchars($cuenta["banco"]); ?>" 
                required
            >

            <label>Titular</label>
            <input 
                type="text" 
                name="titular" 
                value="<?php echo htmlspecialchars($cuenta["titular"]); ?>" 
                required
            >

            <label>Número de cuenta</label>
            <input 
                type="text" 
                name="numero_cuenta" 
                value="<?php echo htmlspecialchars($cuenta["numero_cuenta"] ?? ""); ?>"
            >

            <label>CLABE</label>
            <input 
                type="text" 
                name="clabe" 
                value="<?php echo htmlspecialchars($cuenta["clabe"] ?? ""); ?>"
            >

            <label>Tarjeta</label>
            <input 
                type="text" 
                name="tarjeta" 
                value="<?php echo htmlspecialchars($cuenta["tarjeta"] ?? ""); ?>"
            >

            <label>Instrucciones de pago</label>
            <textarea name="instrucciones" rows="5"><?php echo htmlspecialchars($cuenta["instrucciones"] ?? ""); ?></textarea>

            <label>
                <input 
                    type="checkbox" 
                    name="activo" 
                    <?php echo $cuenta["activo"] ? "checked" : ""; ?>
                >
                Cuenta activa
            </label>

            <button type="submit" class="btn">
                Guardar datos de pago
            </button>

        </form>

    </section>

</main>

</body>
</html>