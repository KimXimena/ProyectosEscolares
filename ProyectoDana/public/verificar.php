<?php
require_once __DIR__ . "/../config/conexion.php";

$busqueda = trim($_GET["q"] ?? "");
$resultados = [];

if ($busqueda !== "") {
    $sql = "
        SELECT 
            c.folio_compra,
            c.estado_pago,
            c.total_pagado,
            c.created_at,
            comp.nombre,
            comp.whatsapp,
            comp.email,
            b.numero_boleto,
            b.folio_boleto,
            b.estado AS estado_boleto,
            r.titulo AS titulo_rifa,
            r.slug
        FROM compras c
        INNER JOIN compradores comp ON comp.id = c.comprador_id
        INNER JOIN boletos b ON b.compra_id = c.id
        INNER JOIN rifas r ON r.id = b.rifa_id
        WHERE 
            c.folio_compra = :folio_compra
            OR b.folio_boleto = :folio_boleto
            OR comp.whatsapp = :whatsapp
        ORDER BY c.created_at DESC, b.numero_boleto ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":folio_compra" => $busqueda,
        ":folio_boleto" => $busqueda,
        ":whatsapp" => $busqueda
    ]);

    $resultados = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar boleto</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Verificar boleto</h1>
    <p>Consulta el estado de tus boletos</p>
</header>

<main class="contenedor">

    <a href="index.php" class="link-volver">← Volver al inicio</a>

    <section class="seleccion-boletos">
        <h2>Buscar boleto</h2>

        <form method="GET" class="formulario-compra">
            <label>Ingresa tu folio de compra, folio de boleto o WhatsApp</label>
            <input 
                type="text" 
                name="q" 
                value="<?php echo htmlspecialchars($busqueda); ?>" 
                placeholder="Ejemplo: COMP-20260607202942-4059"
                required
            >

            <button type="submit" class="btn">
                Verificar
            </button>
        </form>
    </section>

    <?php if ($busqueda !== ""): ?>
        <section class="seleccion-boletos" style="margin-top: 25px;">
            <h2>Resultado de búsqueda</h2>

            <?php if (count($resultados) > 0): ?>
                <div class="tabla-responsive">
                    <table class="tabla-admin">
                        <thead>
                            <tr>
                                <th>Rifa</th>
                                <th>Boleto</th>
                                <th>Folio boleto</th>
                                <th>Folio compra</th>
                                <th>Nombre</th>
                                <th>WhatsApp</th>
                                <th>Estado boleto</th>
                                <th>Estado pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["titulo_rifa"]); ?></td>
                                    <td>
                                        <?php echo str_pad($row["numero_boleto"], 3, "0", STR_PAD_LEFT); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row["folio_boleto"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["folio_compra"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["nombre"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["whatsapp"]); ?></td>
                                    <td>
                                        <span class="estado estado-<?php echo htmlspecialchars($row["estado_boleto"]); ?>">
                                            <?php echo htmlspecialchars($row["estado_boleto"]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="estado estado-<?php echo htmlspecialchars($row["estado_pago"]); ?>">
                                            <?php echo htmlspecialchars($row["estado_pago"]); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No se encontraron boletos con ese dato.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

</main>

</body>
</html>