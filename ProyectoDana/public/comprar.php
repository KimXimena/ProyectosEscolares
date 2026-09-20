<?php
require_once __DIR__ . "/../config/conexion.php";

$rifaId = $_GET["rifa_id"] ?? null;
$boletosParam = $_GET["boletos"] ?? "";

if (!$rifaId || empty($boletosParam)) {
    die("No seleccionaste boletos.");
}

$boletosIds = array_filter(explode(",", $boletosParam));

if (count($boletosIds) === 0) {
    die("No seleccionaste boletos.");
}

$placeholders = implode(",", array_fill(0, count($boletosIds), "?"));

$sql = "
    SELECT *
    FROM rifas
    WHERE id = ?
    AND estado = 'activa'
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$rifaId]);
$rifa = $stmt->fetch();

if (!$rifa) {
    die("Rifa no encontrada.");
}

$sqlBoletos = "
    SELECT *
    FROM boletos
    WHERE id IN ($placeholders)
    AND rifa_id = ?
    AND estado = 'disponible'
    ORDER BY numero_boleto ASC
";

$params = array_merge($boletosIds, [$rifaId]);

$stmt = $pdo->prepare($sqlBoletos);
$stmt->execute($params);
$boletos = $stmt->fetchAll();

if (count($boletos) !== count($boletosIds)) {
    die("Uno o más boletos ya no están disponibles.");
}

$cantidad = count($boletos);
$total = $cantidad * $rifa["precio_boleto"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar compra</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Confirmar apartado</h1>
    <p>Completa tus datos para apartar tus boletos</p>
</header>

<main class="contenedor">

    <a href="rifa.php?slug=<?php echo urlencode($rifa["slug"]); ?>" class="link-volver">
        ← Volver a la rifa
    </a>

    <section class="seleccion-boletos">
        <h2><?php echo htmlspecialchars($rifa["titulo"]); ?></h2>

        <p>
            Boletos seleccionados:
            <strong><?php echo $cantidad; ?></strong>
        </p>

        <div class="resumen-boletos">
            <?php foreach ($boletos as $boleto): ?>
                <span class="boleto-resumen">
                    <?php echo str_pad($boleto["numero_boleto"], 3, "0", STR_PAD_LEFT); ?>
                </span>
            <?php endforeach; ?>
        </div>

        <p class="premio">
            Total a pagar: $<?php echo number_format($total, 2); ?>
        </p>

        <form action="guardar_compra.php" method="POST" class="formulario-compra">
            <input type="hidden" name="rifa_id" value="<?php echo htmlspecialchars($rifaId); ?>">
            <input type="hidden" name="boletos" value="<?php echo htmlspecialchars($boletosParam); ?>">

            <label>Nombre completo</label>
            <input type="text" name="nombre" required>

            <label>WhatsApp</label>
            <input type="text" name="whatsapp" required>

            <label>Correo electrónico</label>
            <input type="email" name="email">

            <button type="submit" class="btn">
                Confirmar apartado
            </button>
        </form>
    </section>

</main>

</body>
</html>