<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

$stmt = $pdo->query("
    SELECT id, titulo, codigo_rifa
    FROM rifas
    ORDER BY created_at DESC
");

$rifas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar ganador</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Registrar ganador</h1>
    <p>Selecciona la rifa y el boleto ganador</p>
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
        <h2>Datos del ganador</h2>

        <form action="guardar_ganador.php" method="POST" enctype="multipart/form-data" class="formulario-compra">

            <label>Rifa</label>
            <select name="rifa_id" required>
                <option value="">Selecciona una rifa</option>
                <?php foreach ($rifas as $rifa): ?>
                    <option value="<?php echo $rifa["id"]; ?>">
                        <?php echo htmlspecialchars($rifa["codigo_rifa"] . " - " . $rifa["titulo"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Número de boleto ganador</label>
            <input type="number" name="numero_boleto" min="1" required>

            <label>Nombre del ganador</label>
            <input type="text" name="nombre_ganador" placeholder="Si lo dejas vacío, se tomará del comprador">

            <label>Foto del ganador o entrega</label>
            <input type="file" name="foto" accept="image/*">

            <label>URL del video</label>
            <input type="url" name="video_url" placeholder="https://...">

            <label>Descripción</label>
            <textarea name="descripcion" rows="4" placeholder="Ejemplo: Ganador del sorteo realizado el día..."></textarea>

            <label>Fecha</label>
            <input type="date" name="fecha" required>

            <button type="submit" class="btn">
                Guardar ganador
            </button>
        </form>
    </section>

</main>

</body>
</html>