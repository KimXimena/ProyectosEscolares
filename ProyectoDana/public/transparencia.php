<?php
require_once __DIR__ . "/../config/conexion.php";

$sql = "
    SELECT 
        titulo,
        codigo_rifa,
        slug,
        premio_tipo,
        premio_nombre,
        premio_monto,
        fecha_sorteo,
        metodo_ganador,
        base_sorteo,
        estado
    FROM rifas
    ORDER BY created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rifas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Transparencia del sorteo</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Transparencia</h1>
    <p>Conoce cómo se eligen los ganadores</p>
</header>

<main class="contenedor">

    <a href="index.php" class="link-volver">← Volver al inicio</a>

    <section class="seleccion-boletos">
        <h2>¿Cómo se elige al ganador?</h2>

        <p>
            Cada rifa puede tener un método de selección definido por administración.
            El método puede basarse en resultados oficiales, sorteos transmitidos en vivo
            o cualquier mecanismo anunciado previamente en la descripción de la rifa.
        </p>

        <p>
            Para mayor confianza, cada rifa muestra su método de selección, fecha de sorteo
            y base utilizada para determinar al ganador.
        </p>
    </section>

    <section class="seleccion-boletos" style="margin-top: 25px;">
        <h2>Rifas registradas</h2>

        <div class="tabla-responsive">
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>Rifa</th>
                        <th>Premio</th>
                        <th>Fecha sorteo</th>
                        <th>Método</th>
                        <th>Base del sorteo</th>
                        <th>Estado</th>
                        <th>Ver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rifas as $rifa): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($rifa["codigo_rifa"]); ?><br>
                                <?php echo htmlspecialchars($rifa["titulo"]); ?>
                            </td>

                            <td>
                                <?php if ($rifa["premio_tipo"] === "efectivo"): ?>
                                    $<?php echo number_format($rifa["premio_monto"], 2); ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($rifa["premio_nombre"]); ?>
                                    <?php if (!empty($rifa["premio_monto"])): ?>
                                        <br>
                                        Valor estimado: $<?php echo number_format($rifa["premio_monto"], 2); ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php echo !empty($rifa["fecha_sorteo"]) ? htmlspecialchars($rifa["fecha_sorteo"]) : "Por definir"; ?>
                            </td>

                            <td>
                                <?php echo !empty($rifa["metodo_ganador"]) ? htmlspecialchars($rifa["metodo_ganador"]) : "Por definir"; ?>
                            </td>

                            <td>
                                <?php echo !empty($rifa["base_sorteo"]) ? htmlspecialchars($rifa["base_sorteo"]) : "Por definir"; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($rifa["estado"]); ?>
                            </td>

                            <td>
                                <a class="btn-tabla" href="rifa.php?slug=<?php echo urlencode($rifa["slug"]); ?>">
                                    Ver rifa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($rifas) === 0): ?>
                        <tr>
                            <td colspan="7">No hay rifas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

</body>
</html>