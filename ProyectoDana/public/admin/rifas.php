<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

$stmt = $pdo->query("
    SELECT 
        r.*,
        COUNT(b.id) AS boletos_generados
    FROM rifas r
    LEFT JOIN boletos b ON b.rifa_id = r.id
    GROUP BY r.id
    ORDER BY r.created_at DESC
");

$rifas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Rifas</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Rifas</h1>
    <p>Administra tus rifas activas</p>
</header>

<main class="contenedor">

    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="rifas.php">Rifas</a>
        <a href="compras.php">Compras</a>
        <a href="../index.php">Ver sitio</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>

    <section class="seleccion-boletos">
        <h2>Listado de rifas</h2>

        <a href="crear_rifa.php" class="btn" style="margin-bottom: 20px;">
            Crear nueva rifa
        </a>

        <div class="tabla-responsive">
            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Título</th>
                        <th>Precio</th>
                        <th>Total boletos</th>
                        <th>Boletos generados</th>
                        <th>Estado</th>
                        <th>URL</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rifas as $rifa): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rifa["codigo_rifa"]); ?></td>
                            <td><?php echo htmlspecialchars($rifa["titulo"]); ?></td>
                            <td>$<?php echo number_format($rifa["precio_boleto"], 2); ?></td>
                            <td><?php echo $rifa["total_boletos"]; ?></td>
                            <td><?php echo $rifa["boletos_generados"]; ?></td>
                            <td><?php echo htmlspecialchars($rifa["estado"]); ?></td>
                            <td>
                                <a class="btn-tabla" href="../rifa.php?slug=<?php echo urlencode($rifa["slug"]); ?>" target="_blank">
                                    Ver
                                </a>
                            </td>
                            
                            <td>
                                <a class="btn-tabla" href="editar_rifa.php?id=<?php echo $rifa["id"]; ?>">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($rifas) === 0): ?>
                        <tr>
                            <td colspan="8">No hay rifas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

</body>
</html>