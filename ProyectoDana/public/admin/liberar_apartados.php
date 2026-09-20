<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../../config/conexion.php";

try {
    $pdo->beginTransaction();

    // Buscar compras pendientes vencidas y sin comprobante
    $stmt = $pdo->prepare("
        SELECT id, folio_compra
        FROM compras
        WHERE estado_pago = 'pendiente'
        AND comprobante IS NULL
        AND fecha_expiracion_apartado IS NOT NULL
        AND fecha_expiracion_apartado < NOW()
        FOR UPDATE
    ");

    $stmt->execute();
    $comprasVencidas = $stmt->fetchAll();

    $totalCompras = count($comprasVencidas);
    $totalBoletosLiberados = 0;

    foreach ($comprasVencidas as $compra) {
        $compraId = $compra["id"];

        // Liberar boletos
        $stmtBoletos = $pdo->prepare("
            UPDATE boletos
            SET 
                estado = 'disponible',
                compra_id = NULL,
                reservado_hasta = NULL,
                updated_at = CURRENT_TIMESTAMP
            WHERE compra_id = ?
            AND estado = 'apartado'
        ");

        $stmtBoletos->execute([$compraId]);
        $totalBoletosLiberados += $stmtBoletos->rowCount();

        // Cancelar compra
        $stmtCompra = $pdo->prepare("
            UPDATE compras
            SET 
                estado_pago = 'cancelado',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmtCompra->execute([$compraId]);
    }

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error al liberar apartados: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Liberar apartados</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<header class="header">
    <h1>Apartados liberados</h1>
    <p>Proceso terminado correctamente</p>
</header>

<main class="contenedor">
    <section class="seleccion-boletos">
        <h2>Resultado</h2>

        <p>
            Compras vencidas canceladas:
            <strong><?php echo $totalCompras; ?></strong>
        </p>

        <p>
            Boletos liberados:
            <strong><?php echo $totalBoletosLiberados; ?></strong>
        </p>

        <a href="dashboard.php" class="btn">
            Volver al dashboard
        </a>
    </section>
</main>

</body>
</html>