DROP DATABASE IF EXISTS pensiones_alimenticias;
SELECT VERSION();

CREATE DATABASE pensiones_alimenticias
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE pensiones_alimenticias;

CREATE TABLE beneficiarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombres        VARCHAR(80)  NOT NULL,
  ap_paterno     VARCHAR(80)  NOT NULL,
  ap_materno     VARCHAR(80)  NULL,
  rfc            VARCHAR(13)  NULL,
  celular        VARCHAR(15)  NULL,
  -- cómo se aplicará el descuento
  forma_aplicacion VARCHAR(30) NOT NULL,  -- PORC_PENSION | IMP_FIJO_PENSION | JUICIO_MERCANTIL
  monto_descuento  DECIMAL(12,2) NOT NULL,
  -- vigencia del descuento por quincena
  quincena_inicio VARCHAR(10) NOT NULL,
  quincena_fin    VARCHAR(10) NOT NULL,

  nombre_abogado  VARCHAR(150) NULL,
  celular_abogado VARCHAR(15)  NULL,
  cct_pago_beneficiario VARCHAR(20) NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE movimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  estatus VARCHAR(30) NOT NULL DEFAULT 'NUEVO',
  tipo_movimiento_clase VARCHAR(20) NOT NULL DEFAULT 'pension',  -- pension | juicio
  tipo_movimiento       VARCHAR(20) NOT NULL,                    -- alta | cambio | baja | reintegro
  tipo_tramite          VARCHAR(20) NOT NULL,                    -- pension | juicio (tipo de oficio)
  rfc_trabajador    VARCHAR(13)  NOT NULL,
  curp_trabajador   VARCHAR(18)  NULL,
  nombre_trabajador VARCHAR(150) NOT NULL,
  beneficiario_id INT NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_mov_beneficiario
    FOREIGN KEY (beneficiario_id)
    REFERENCES beneficiarios(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- índices, para búsqueda y filtros comunes
CREATE INDEX idx_mov_rfc     ON movimientos (rfc_trabajador);
CREATE INDEX idx_mov_tipo    ON movimientos (tipo_movimiento);
CREATE INDEX idx_mov_tramite ON movimientos (tipo_tramite);
CREATE INDEX idx_mov_estatus ON movimientos (estatus);
CREATE INDEX idx_mov_creado  ON movimientos (creado_en);
CREATE INDEX idx_mov_benef   ON movimientos (beneficiario_id);

CREATE INDEX idx_ben_rfc       ON beneficiarios (rfc);
CREATE INDEX idx_ben_quincenas ON beneficiarios (quincena_inicio, quincena_fin);
CREATE INDEX idx_ben_cct       ON beneficiarios (cct_pago_beneficiario);


SHOW TABLES;

SHOW CREATE TABLE beneficiarios;
SHOW CREATE TABLE movimientos;

SELECT COUNT(*) FROM beneficiarios;
SELECT COUNT(*) FROM movimientos;

INSERT INTO movimientos (tipo_movimiento, tipo_tramite, rfc_trabajador, nombre_trabajador, beneficiario_id)
VALUES ('alta','pension','AAAA000000AAA','PRUEBA',999999);

USE pensiones_alimenticias;
SELECT * FROM beneficiarios ORDER BY id DESC LIMIT 3;
SELECT * FROM movimientos   ORDER BY id DESC LIMIT 3;

