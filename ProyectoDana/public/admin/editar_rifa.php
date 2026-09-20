<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    die("Rifa no válida.");
}

$stmt = $pdo->prepare("
    SELECT *
    FROM rifas
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);
$rifa = $stmt->fetch();

if (!$rifa) {
    die("Rifa no encontrada.");
}

function fechaInput($fecha) {
    if (empty($fecha)) {
        return "";
    }

    return date("Y-m-d\TH:i", strtotime($fecha));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar rifa</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Editar rifa</h1>
    <p><?php echo htmlspecialchars($rifa["titulo"]); ?></p>
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
        <h2>Datos de la rifa</h2>

        <form action="actualizar_rifa.php" method="POST" enctype="multipart/form-data" class="formulario-compra">

            <input type="hidden" name="id" value="<?php echo $rifa["id"]; ?>">

            <label>Código de rifa</label>
            <input 
                type="text" 
                name="codigo_rifa" 
                value="<?php echo htmlspecialchars($rifa["codigo_rifa"]); ?>" 
                required
            >

            <label>Slug para URL</label>
            <input 
                type="text" 
                name="slug" 
                value="<?php echo htmlspecialchars($rifa["slug"]); ?>" 
                required
            >

            <label>Título</label>
            <input 
                type="text" 
                name="titulo" 
                value="<?php echo htmlspecialchars($rifa["titulo"]); ?>" 
                required
            >

            <label>Descripción</label>
            <textarea name="descripcion" rows="5" required><?php echo htmlspecialchars($rifa["descripcion"]); ?></textarea>

            <label>Tipo de premio</label>
            <select name="premio_tipo" required>
                <option value="efectivo" <?php echo $rifa["premio_tipo"] === "efectivo" ? "selected" : ""; ?>>Efectivo</option>
                <option value="producto" <?php echo $rifa["premio_tipo"] === "producto" ? "selected" : ""; ?>>Producto</option>
                <option value="servicio" <?php echo $rifa["premio_tipo"] === "servicio" ? "selected" : ""; ?>>Servicio</option>
                <option value="mixto" <?php echo $rifa["premio_tipo"] === "mixto" ? "selected" : ""; ?>>Mixto</option>
            </select>

            <label>Nombre del premio</label>
            <input 
                type="text" 
                name="premio_nombre" 
                value="<?php echo htmlspecialchars($rifa["premio_nombre"] ?? ""); ?>"
                placeholder="Ejemplo: Botellas Don Julio, iPhone 15, Moto Italika"
            >

            <label>Monto o valor estimado del premio</label>
            <input 
                type="number" 
                name="premio_monto" 
                step="0.01" 
                min="0"
                value="<?php echo htmlspecialchars($rifa["premio_monto"] ?? ""); ?>"
            >

            <label>Imagen actual</label>

            <?php if (!empty($rifa["imagen"])): ?>
                <p><?php echo htmlspecialchars($rifa["imagen"]); ?></p>
                <img 
                    src="../uploads/rifas/<?php echo htmlspecialchars($rifa["imagen"]); ?>" 
                    alt="Imagen actual" 
                    style="max-width: 250px; border-radius: 10px;"
                >
            <?php else: ?>
                <p>No tiene imagen.</p>
            <?php endif; ?>

            <label>Nueva imagen del premio</label>
            <input type="file" name="imagen" accept="image/*">

            <label>Precio por boleto</label>
            <input 
                type="number" 
                name="precio_boleto" 
                step="0.01" 
                min="0" 
                value="<?php echo htmlspecialchars($rifa["precio_boleto"]); ?>" 
                required
            >

            <label>Total de boletos</label>
            <input 
                type="number" 
                value="<?php echo htmlspecialchars($rifa["total_boletos"]); ?>" 
                disabled
            >

            <small>El total de boletos no se edita desde aquí para evitar problemas con boletos ya generados.</small>

            <label>Fecha de inicio</label>
            <input 
                type="datetime-local" 
                name="fecha_inicio" 
                value="<?php echo fechaInput($rifa["fecha_inicio"]); ?>"
            >

            <label>Fecha de cierre</label>
            <input 
                type="datetime-local" 
                name="fecha_cierre" 
                value="<?php echo fechaInput($rifa["fecha_cierre"]); ?>"
            >

            <label>Fecha de sorteo</label>
            <input 
                type="datetime-local" 
                name="fecha_sorteo" 
                value="<?php echo fechaInput($rifa["fecha_sorteo"]); ?>"
            >

            <label>Método para elegir ganador</label>
            <input 
                type="text" 
                name="metodo_ganador" 
                value="<?php echo htmlspecialchars($rifa["metodo_ganador"] ?? ""); ?>"
            >

            <label>Base del sorteo</label>
            <input 
                type="text" 
                name="base_sorteo" 
                value="<?php echo htmlspecialchars($rifa["base_sorteo"] ?? ""); ?>"
            >

            <label>Estado</label>
            <select name="estado" required>
                <option value="borrador" <?php echo $rifa["estado"] === "borrador" ? "selected" : ""; ?>>Borrador</option>
                <option value="activa" <?php echo $rifa["estado"] === "activa" ? "selected" : ""; ?>>Activa</option>
                <option value="cerrada" <?php echo $rifa["estado"] === "cerrada" ? "selected" : ""; ?>>Cerrada</option>
                <option value="cancelada" <?php echo $rifa["estado"] === "cancelada" ? "selected" : ""; ?>>Cancelada</option>
                <option value="finalizada" <?php echo $rifa["estado"] === "finalizada" ? "selected" : ""; ?>>Finalizada</option>
            </select>

            <button type="submit" class="btn">
                Guardar cambios
            </button>
        </form>
    </section>

</main>

</body>
</html>