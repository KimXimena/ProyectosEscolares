<?php
require_once __DIR__ . "/../config/conexion.php";

$sql = "
    SELECT 
        r.*,
        COUNT(b.id) AS boletos_generados,
        SUM(CASE WHEN b.estado IN ('apartado', 'vendido') THEN 1 ELSE 0 END) AS boletos_ocupados,
        SUM(CASE WHEN b.estado = 'disponible' THEN 1 ELSE 0 END) AS boletos_disponibles
    FROM rifas r
    LEFT JOIN boletos b ON b.rifa_id = r.id
    WHERE r.estado = 'activa'
    GROUP BY r.id
    ORDER BY r.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rifas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Rifas Online</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Rifas Online</h1>
    <p>Participa y gana grandes premios</p>
</header>

<main class="contenedor">
    <h2>Rifas Activas</h2>

    <div class="rifas-grid">
        <?php foreach ($rifas as $rifa): ?>
            <?php
                $ocupados = (int) $rifa["boletos_ocupados"];
                $total = (int) $rifa["total_boletos"];
                $porcentaje = $total > 0 ? ($ocupados / $total) * 100 : 0;
            ?>

            <div class="rifa-card">
                <div class="imagen-rifa">
                    <?php if (!empty($rifa["imagen"])): ?>
                        <img src="uploads/rifas/<?php echo htmlspecialchars($rifa["imagen"]); ?>" alt="Imagen de la rifa">
                    <?php else: ?>
                        <div class="sin-imagen">Sin imagen</div>
                    <?php endif; ?>
                </div>

                <div class="rifa-info">
                    <h3><?php echo htmlspecialchars($rifa["titulo"]); ?></h3>

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
                        Boletos vendidos/apartados:
                        <?php echo $ocupados; ?> de <?php echo $total; ?>
                    </p>

                    <div class="barra">
                        <div class="progreso" style="width: <?php echo $porcentaje; ?>%;"></div>
                    </div>

                    <a class="btn" href="rifa.php?slug=<?php echo urlencode($rifa["slug"]); ?>">
                        Comprar boletos
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<a href="https://wa.me/528443197173" class="whatsapp" target="_blank">
    WhatsApp
</a>

<a href="verificar.php" class="verificar-flotante">
    Verificar boleto
</a>

<a href="ganadores.php" class="ganadores-flotante">
    Ganadores
</a>

<a href="transparencia.php" class="transparencia-flotante">
    Transparencia
</a>

<a href="terminos.php" class="terminos-flotante">
    Términos
</a>

<script src="js/contador.js"></script>

</body>
</html>