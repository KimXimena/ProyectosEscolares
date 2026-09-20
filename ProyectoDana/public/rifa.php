<?php
require_once __DIR__ . "/../config/conexion.php";

$slug = $_GET["slug"] ?? "";

if (empty($slug)) {
    die("Rifa no encontrada.");
}

$sql = "
    SELECT *
    FROM rifas
    WHERE slug = :slug
    AND estado = 'activa'
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([":slug" => $slug]);
$rifa = $stmt->fetch();

if (!$rifa) {
    die("Rifa no encontrada o no está activa.");
}

$rifaId = $rifa["id"];

$sqlPaquetes = "
    SELECT *
    FROM paquetes
    WHERE rifa_id = :rifa_id
    AND activo = 1
    ORDER BY cantidad_boletos ASC
";

$stmt = $pdo->prepare($sqlPaquetes);
$stmt->execute([":rifa_id" => $rifaId]);
$paquetes = $stmt->fetchAll();

$sqlBoletos = "
    SELECT *
    FROM boletos
    WHERE rifa_id = :rifa_id
    ORDER BY numero_boleto ASC
";

$stmt = $pdo->prepare($sqlBoletos);
$stmt->execute([":rifa_id" => $rifaId]);
$boletos = $stmt->fetchAll();

$totalBoletos = count($boletos);
$ocupados = 0;

foreach ($boletos as $boleto) {
    if ($boleto["estado"] !== "disponible") {
        $ocupados++;
    }
}

$disponibles = $totalBoletos - $ocupados;
$porcentaje = $totalBoletos > 0 ? ($ocupados / $totalBoletos) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($rifa["titulo"]); ?></title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header class="header">
    <h1><?php echo htmlspecialchars($rifa["titulo"]); ?></h1>
    <p>Selecciona tus boletos y participa</p>
</header>

<main class="contenedor">

    <a href="index.php" class="link-volver">← Volver a rifas</a>

    <section class="detalle-rifa">

        <div class="detalle-imagen">
            <?php
                $rutaImagen = __DIR__ . "/uploads/rifas/" . $rifa["imagen"];
                $urlImagen = "uploads/rifas/" . htmlspecialchars($rifa["imagen"]);
            ?>

            <?php if (!empty($rifa["imagen"]) && file_exists($rutaImagen)): ?>
                <img src="<?php echo $urlImagen; ?>" alt="Premio">
            <?php else: ?>
                <div class="sin-imagen">Imagen del premio</div>
            <?php endif; ?>
        </div>

        <div class="detalle-info">
            <h2><?php echo htmlspecialchars($rifa["titulo"]); ?></h2>

            <p class="premio">
                Premio:
                <?php if ($rifa["premio_tipo"] === "efectivo"): ?>
                    $<?php echo number_format($rifa["premio_monto"], 2); ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars($rifa["premio_nombre"]); ?>
                        <?php endif; ?>
                    </p>
                    <?php if ($rifa["premio_tipo"] !== "efectivo" && !empty($rifa["premio_monto"])): ?>
                        <p>
                            Valor estimado:
                            <strong>$<?php echo number_format($rifa["premio_monto"], 2); ?></strong>
                        </p>
                        <?php endif; ?>

            <p>
                <?php echo nl2br(htmlspecialchars($rifa["descripcion"])); ?>
            </p>

            <p>
                Precio por boleto:
                <strong>$<?php echo number_format($rifa["precio_boleto"], 2); ?></strong>
            </p>

            <div class="contador-box">
                <p>Cierra en:</p>
                <div 
                class="contador-regresivo" 
                data-fecha="<?php echo htmlspecialchars($rifa["fecha_cierre"] ?? ""); ?>">
                Cargando...
            </div>
        </div>

            <p>
                Boletos ocupados: <?php echo $ocupados; ?> de <?php echo $totalBoletos; ?>
            </p>

            <p>
                Boletos disponibles: <?php echo $disponibles; ?>
            </p>

            <div class="barra">
                <div class="progreso" style="width: <?php echo $porcentaje; ?>%;"></div>
            </div>

            <h3>Paquetes disponibles</h3>

            <div class="paquetes">
                <?php foreach ($paquetes as $paquete): ?>
                    <div class="paquete">
                        <strong><?php echo htmlspecialchars($paquete["etiqueta"]); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </section>

    <section class="seleccion-boletos">
        <h2>Selecciona tus boletos</h2>

        <p>
            Boletos seleccionados:
            <strong id="contadorSeleccionados">0</strong>
        </p>

        <div class="grid-boletos">
            <?php foreach ($boletos as $boleto): ?>
                <?php
                    $clase = $boleto["estado"] === "disponible" ? "boleto disponible" : "boleto ocupado";
                    $disabled = $boleto["estado"] === "disponible" ? "" : "disabled";
                ?>

                <button 
                    type="button"
                    class="<?php echo $clase; ?>"
                    data-id="<?php echo $boleto["id"]; ?>"
                    data-numero="<?php echo $boleto["numero_boleto"]; ?>"
                    <?php echo $disabled; ?>
                >
                    <?php echo str_pad($boleto["numero_boleto"], 3, "0", STR_PAD_LEFT); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <form id="formCompra" action="comprar.php" method="GET">
            <input type="hidden" name="rifa_id" value="<?php echo $rifa["id"]; ?>">
            <input type="hidden" name="boletos" id="boletosSeleccionados">

            <button type="submit" class="btn comprar-btn">
                Apartar boletos seleccionados
            </button>
        </form>
    </section>

</main>

<script src="js/rifa.js"></script>
<script src="js/contador.js"></script>

</body>
</html>