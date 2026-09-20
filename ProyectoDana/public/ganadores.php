<?php
require_once __DIR__ . "/../config/conexion.php";

$sql = "
    SELECT 
        g.*,
        b.numero_boleto,
        b.folio_boleto,
        r.titulo AS titulo_rifa,
        r.premio_tipo,
        r.premio_nombre,
        r.premio_monto
    FROM ganadores g
    INNER JOIN boletos b ON b.id = g.boleto_id
    INNER JOIN rifas r ON r.id = b.rifa_id
    ORDER BY g.fecha DESC, g.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$ganadores = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ganadores anteriores</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Ganadores anteriores</h1>
    <p>Consulta los ganadores de rifas pasadas</p>
</header>

<main class="contenedor">

    <a href="index.php" class="link-volver">← Volver al inicio</a>

    <section class="ganadores-grid">
        <?php foreach ($ganadores as $ganador): ?>
            <div class="ganador-card">
                <div class="ganador-img">
                    <?php if (!empty($ganador["foto_url"])): ?>
                        <img src="uploads/ganadores/<?php echo htmlspecialchars($ganador["foto_url"]); ?>" alt="Ganador">
                    <?php else: ?>
                        <div class="sin-imagen">Sin foto</div>
                    <?php endif; ?>
                </div>

                <div class="rifa-info">
                    <h3><?php echo htmlspecialchars($ganador["titulo_rifa"]); ?></h3>

                    <p class="premio">
                        Ganador: <?php echo htmlspecialchars($ganador["nombre_ganador"]); ?>
                    </p>

                    <p>
                        Boleto ganador:
                        <strong><?php echo str_pad($ganador["numero_boleto"], 3, "0", STR_PAD_LEFT); ?></strong>
                    </p>

                    <p>
                        Premio:
                        <strong>
                            <?php if ($ganador["premio_tipo"] === "efectivo"): ?>
                                $<?php echo number_format($ganador["premio_monto"], 2); ?>
                            <?php else: ?>
                                <?php echo htmlspecialchars($ganador["premio_nombre"]); ?>
                            <?php endif; ?>
                        </strong>
                    </p>

                    <p>Fecha: <?php echo htmlspecialchars($ganador["fecha"]); ?></p>

                    <?php if (!empty($ganador["descripcion"])): ?>
                        <p><?php echo nl2br(htmlspecialchars($ganador["descripcion"])); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($ganador["video_url"])): ?>
                        <a class="btn" href="<?php echo htmlspecialchars($ganador["video_url"]); ?>" target="_blank">
                            Ver video
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($ganadores) === 0): ?>
            <section class="seleccion-boletos">
                <p>Aún no hay ganadores registrados.</p>
            </section>
        <?php endif; ?>
    </section>

</main>

</body>
</html>