-- =====================================================
-- Actualizacion incremental: modulo de auditoria
-- Ejecutar sobre una base bd_arritmias ya existente.
-- No almacena direccion IP.
-- =====================================================

USE bd_arritmias;

CREATE TABLE IF NOT EXISTS auditorias (
    auditoria_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    accion VARCHAR(100) NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    entidad VARCHAR(100) NULL,
    entidad_id VARCHAR(100) NULL,
    descripcion TEXT NULL,
    valores_anteriores JSON NULL,
    valores_nuevos JSON NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditorias_users
        FOREIGN KEY (usuario_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

SET @add_auditorias_usuario_index = (
    SELECT IF(
        COUNT(*) = 0,
        'CREATE INDEX idx_auditorias_usuario_id ON auditorias(usuario_id)',
        'SELECT 1'
    )
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'auditorias'
      AND index_name = 'idx_auditorias_usuario_id'
);
PREPARE stmt FROM @add_auditorias_usuario_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_auditorias_modulo_index = (
    SELECT IF(
        COUNT(*) = 0,
        'CREATE INDEX idx_auditorias_modulo_accion ON auditorias(modulo, accion)',
        'SELECT 1'
    )
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'auditorias'
      AND index_name = 'idx_auditorias_modulo_accion'
);
PREPARE stmt FROM @add_auditorias_modulo_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_auditorias_created_index = (
    SELECT IF(
        COUNT(*) = 0,
        'CREATE INDEX idx_auditorias_created_at ON auditorias(created_at)',
        'SELECT 1'
    )
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'auditorias'
      AND index_name = 'idx_auditorias_created_at'
);
PREPARE stmt FROM @add_auditorias_created_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
