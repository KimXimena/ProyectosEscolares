<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

$sql = "
    SELECT 
        g.*,
        b.numero_boleto,
        b.folio_boleto,
        r.titulo AS titulo_rifa,
        c.folio_compra,
        comp.nombre AS nombre_comprador,
        comp.whatsapp
    FROM ganadores g
    INNER JOIN boletos b ON b.id = g.boleto_id
    INNER JOIN rifas r ON r.id = b.rifa_id
    LEFT JOIN compras c ON c.id = b.compra_id
    LEFT JOIN compradores comp ON comp.id = c.comprador_id
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
    <title>Ganadores</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Ganadores</h1>
    <p>Administra ganadores anteriores</p>
</header>

<main class="contenedor">

    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="rifas.php">Rifas</a>
        <a href="compras.php">Compras</a>
        <a href="ganadores.php">Ganadores</a>
        <a href="../index.php">Ver sitio</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <section class="seleccion-boletos">
        <h2>Listado de ganadores</h2>

        <a href="crear_ganador.php" class="btn" style="margin-bottom: 20px;">
            Registrar ganador
        </a>

        <div class="tabla-responsive">
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>Rifa</th>
                        <th>Boleto</th>
                        <th>Ganador</th>
                        <th>WhatsApp</th>
                        <th>Fecha</th>
                        <th>Foto</th>
                        <th>Video</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ganadores as $ganador): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ganador["titulo_rifa"]); ?></td>
                            <td><?php echo str_pad($ganador["numero_boleto"], 3, "0", STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($ganador["nombre_ganador"] ?? $ganador["nombre_comprador"] ?? ""); ?></td>
                            <td><?php echo htmlspecialchars($ganador["whatsapp"] ?? ""); ?></td>
                            <td><?php echo htmlspecialchars($ganador["fecha"]); ?></td>
                            <td>
                                <?php if (!empty($ganador["foto_url"])): ?>
                                    <a class="btn-tabla" href="../uploads/ganadores/<?php echo htmlspecialchars($ganador["foto_url"]); ?>" target="_blank">
                                        Ver foto
                                    </a>
                                <?php else: ?>
                                    Sin foto
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($ganador["video_url"])): ?>
                                    <a class="btn-tabla" href="<?php echo htmlspecialchars($ganador["video_url"]); ?>" target="_blank">
                                        Ver video
                                    </a>
                                <?php else: ?>
                                    Sin video
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($ganadores) === 0): ?>
                        <tr>
                            <td colspan="7">No hay ganadores registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

</body>
</html>