<?php
// database/seed.php

require_once __DIR__ . "/../config/conexion.php";

try {
    $pdo->beginTransaction();

    // =========================
    // 1. CREAR ADMIN DE PRUEBA
    // =========================
    $passwordHash = password_hash("admin123", PASSWORD_DEFAULT);

    $sqlAdmin = "
        INSERT INTO administradores (
            nombre,
            usuario,
            password_hash,
            rol,
            activo
        ) VALUES (
            :nombre,
            :usuario,
            :password_hash,
            :rol,
            :activo
        )
        ON DUPLICATE KEY UPDATE
            nombre = VALUES(nombre),
            password_hash = VALUES(password_hash),
            rol = VALUES(rol),
            activo = VALUES(activo)
    ";

    $stmt = $pdo->prepare($sqlAdmin);
    $stmt->execute([
        ":nombre" => "Administrador",
        ":usuario" => "admin",
        ":password_hash" => $passwordHash,
        ":rol" => "admin",
        ":activo" => 1
    ]);

    // =========================
    // 2. CREAR RIFA DE PRUEBA
    // =========================
    $sqlRifa = "
        INSERT INTO rifas (
            codigo_rifa,
            slug,
            titulo,
            descripcion,
            imagen,
            precio_boleto,
            total_boletos,
            premio_monto,
            fecha_inicio,
            fecha_cierre,
            fecha_sorteo,
            metodo_ganador,
            base_sorteo,
            estado
        ) VALUES (
            :codigo_rifa,
            :slug,
            :titulo,
            :descripcion,
            :imagen,
            :precio_boleto,
            :total_boletos,
            :premio_monto,
            NOW(),
            DATE_ADD(NOW(), INTERVAL 15 DAY),
            DATE_ADD(NOW(), INTERVAL 16 DAY),
            :metodo_ganador,
            :base_sorteo,
            :estado
        )
        ON DUPLICATE KEY UPDATE
            titulo = VALUES(titulo),
            descripcion = VALUES(descripcion),
            imagen = VALUES(imagen),
            precio_boleto = VALUES(precio_boleto),
            total_boletos = VALUES(total_boletos),
            premio_monto = VALUES(premio_monto),
            fecha_cierre = VALUES(fecha_cierre),
            fecha_sorteo = VALUES(fecha_sorteo),
            metodo_ganador = VALUES(metodo_ganador),
            base_sorteo = VALUES(base_sorteo),
            estado = VALUES(estado)
    ";

    $stmt = $pdo->prepare($sqlRifa);
    $stmt->execute([
        ":codigo_rifa" => "RIFA-001",
        ":slug" => "rifa-premio-10000",
        ":titulo" => "Rifa de $10,000 en efectivo",
        ":descripcion" => "Participa en nuestra rifa especial y gana $10,000 en efectivo. El ganador será elegido con base en los resultados oficiales indicados en la sección de transparencia.",
        ":imagen" => "premio-demo.jpg",
        ":precio_boleto" => 20.00,
        ":total_boletos" => 700,
        ":premio_monto" => 10000.00,
        ":metodo_ganador" => "Resultado oficial",
        ":base_sorteo" => "Lotería Nacional / Tris",
        ":estado" => "activa"
    ]);

    // =========================
    // 3. OBTENER ID DE LA RIFA
    // =========================
    $stmt = $pdo->prepare("
        SELECT id 
        FROM rifas 
        WHERE codigo_rifa = :codigo_rifa
        LIMIT 1
    ");

    $stmt->execute([
        ":codigo_rifa" => "RIFA-001"
    ]);

    $rifa = $stmt->fetch();

    if (!$rifa) {
        throw new Exception("No se pudo obtener la rifa creada.");
    }

    $rifaId = $rifa["id"];

    // =========================
    // 4. CREAR PAQUETES
    // =========================
    $paquetes = [
        [
            "cantidad" => 1,
            "precio" => 20.00,
            "etiqueta" => "1 boleto por $20"
        ],
        [
            "cantidad" => 5,
            "precio" => 90.00,
            "etiqueta" => "5 boletos por $90"
        ],
        [
            "cantidad" => 10,
            "precio" => 170.00,
            "etiqueta" => "10 boletos por $170"
        ],
        [
            "cantidad" => 20,
            "precio" => 320.00,
            "etiqueta" => "20 boletos por $320"
        ]
    ];

    $sqlPaquete = "
        INSERT INTO paquetes (
            rifa_id,
            cantidad_boletos,
            precio,
            etiqueta,
            activo
        ) VALUES (
            :rifa_id,
            :cantidad_boletos,
            :precio,
            :etiqueta,
            1
        )
        ON DUPLICATE KEY UPDATE
            precio = VALUES(precio),
            etiqueta = VALUES(etiqueta),
            activo = VALUES(activo)
    ";

    $stmtPaquete = $pdo->prepare($sqlPaquete);

    foreach ($paquetes as $paquete) {
        $stmtPaquete->execute([
            ":rifa_id" => $rifaId,
            ":cantidad_boletos" => $paquete["cantidad"],
            ":precio" => $paquete["precio"],
            ":etiqueta" => $paquete["etiqueta"]
        ]);
    }

    // =========================
    // 5. CREAR 700 BOLETOS
    // =========================
    $sqlBoleto = "
        INSERT IGNORE INTO boletos (
            rifa_id,
            compra_id,
            numero_boleto,
            folio_boleto,
            estado
        ) VALUES (
            :rifa_id,
            NULL,
            :numero_boleto,
            :folio_boleto,
            'disponible'
        )
    ";

    $stmtBoleto = $pdo->prepare($sqlBoleto);

    for ($i = 1; $i <= 700; $i++) {
        $folioBoleto = "RIFA001-" . str_pad($i, 4, "0", STR_PAD_LEFT);

        $stmtBoleto->execute([
            ":rifa_id" => $rifaId,
            ":numero_boleto" => $i,
            ":folio_boleto" => $folioBoleto
        ]);
    }

    // =========================
    // 6. CREAR CUENTA DE PAGO
    // =========================
    $sqlCuenta = "
        INSERT INTO cuentas_pago (
            alias_cuenta,
            banco,
            titular,
            numero_cuenta,
            clabe,
            tarjeta,
            instrucciones,
            activo
        ) VALUES (
            :alias_cuenta,
            :banco,
            :titular,
            :numero_cuenta,
            :clabe,
            :tarjeta,
            :instrucciones,
            1
        )
        ON DUPLICATE KEY UPDATE
            banco = VALUES(banco),
            titular = VALUES(titular),
            numero_cuenta = VALUES(numero_cuenta),
            clabe = VALUES(clabe),
            tarjeta = VALUES(tarjeta),
            instrucciones = VALUES(instrucciones),
            activo = VALUES(activo)
    ";

    $stmt = $pdo->prepare($sqlCuenta);
    $stmt->execute([
        ":alias_cuenta" => "principal",
        ":banco" => "Banco de prueba",
        ":titular" => "Titular de prueba",
        ":numero_cuenta" => "0000000000",
        ":clabe" => "000000000000000000",
        ":tarjeta" => "0000 0000 0000 0000",
        ":instrucciones" => "Realiza tu transferencia o depósito y sube el comprobante en el sistema."
    ]);

    $pdo->commit();

    echo "Datos de prueba creados correctamente.\n";
    echo "Usuario admin: admin\n";
    echo "Contraseña admin: admin123\n";
    echo "Rifa creada: RIFA-001\n";
    echo "Boletos generados: 700\n";

} catch (Exception $e) {
    $pdo->rollBack();

    echo "Error al crear datos de prueba:\n";
    echo $e->getMessage() . "\n";
}