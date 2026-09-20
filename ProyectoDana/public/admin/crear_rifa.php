<?php
require_once __DIR__ . "/auth.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear rifa</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Crear nueva rifa</h1>
    <p>Registra una rifa y genera sus boletos</p>
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

        <form action="guardar_rifa.php" method="POST" enctype="multipart/form-data" class="formulario-compra">

            <label>Código de rifa</label>
            <input type="text" name="codigo_rifa" placeholder="Ejemplo: RIFA-002" required>

            <label>Slug para URL</label>
            <input type="text" name="slug" placeholder="Ejemplo: rifa-iphone-15" required>

            <label>Título</label>
            <input type="text" name="titulo" required>

            <label>Descripción</label>
            <textarea name="descripcion" rows="5" required></textarea>

            <label>Imagen del premio</label>
            <input type="file" name="imagen" accept="image/*">

            <label>Precio por boleto</label>
            <input type="number" name="precio_boleto" step="0.01" min="0" required>

            <label>Total de boletos</label>
            <input type="number" name="total_boletos" min="1" required>

            <label>Tipo de premio</label>
            <select name="premio_tipo" required>
                <option value="efectivo">Efectivo</option>
                <option value="producto">Producto</option>
                <option value="servicio">Servicio</option>
                <option value="mixto">Mixto</option>
            </select>
            
            <label>Nombre del premio</label>
            <input type="text" name="premio_nombre" placeholder="Ejemplo: Botellas Don Julio, iPhone 15, Moto Italika">
            
            <label>Monto o valor estimado del premio</label>
            <input type="number" name="premio_monto" step="0.01" min="0" placeholder="Ejemplo: 2500">

            <label>Fecha de inicio</label>
            <input type="datetime-local" name="fecha_inicio">

            <label>Fecha de cierre</label>
            <input type="datetime-local" name="fecha_cierre">

            <label>Fecha de sorteo</label>
            <input type="datetime-local" name="fecha_sorteo">

            <label>Método para elegir ganador</label>
            <input type="text" name="metodo_ganador" placeholder="Ejemplo: Lotería Nacional">

            <label>Base del sorteo</label>
            <input type="text" name="base_sorteo" placeholder="Ejemplo: Últimas 3 cifras del premio mayor">

            <label>Estado</label>
            <select name="estado" required>
                <option value="borrador">Borrador</option>
                <option value="activa">Activa</option>
                <option value="cerrada">Cerrada</option>
                <option value="cancelada">Cancelada</option>
                <option value="finalizada">Finalizada</option>
            </select>

            <h3>Paquetes</h3>

            <div class="paquete-form">
                <input type="number" name="paquete_cantidad[]" placeholder="Cantidad boletos" min="1">
                <input type="number" name="paquete_precio[]" placeholder="Precio paquete" step="0.01" min="0">
                <input type="text" name="paquete_etiqueta[]" placeholder="Etiqueta: 5 boletos por $90">
            </div>

            <div class="paquete-form">
                <input type="number" name="paquete_cantidad[]" placeholder="Cantidad boletos" min="1">
                <input type="number" name="paquete_precio[]" placeholder="Precio paquete" step="0.01" min="0">
                <input type="text" name="paquete_etiqueta[]" placeholder="Etiqueta: 10 boletos por $170">
            </div>

            <div class="paquete-form">
                <input type="number" name="paquete_cantidad[]" placeholder="Cantidad boletos" min="1">
                <input type="number" name="paquete_precio[]" placeholder="Precio paquete" step="0.01" min="0">
                <input type="text" name="paquete_etiqueta[]" placeholder="Etiqueta: 20 boletos por $320">
            </div>

            <button type="submit" class="btn">
                Guardar rifa y generar boletos
            </button>
        </form>
    </section>

</main>

</body>
</html>