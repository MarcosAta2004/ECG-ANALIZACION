-- =====================================================
-- Actualizacion incremental: modulo de usuarios y roles
-- Ejecutar sobre una base bd_arritmias ya existente.
-- =====================================================

USE bd_arritmias;

INSERT IGNORE INTO roles (role_id, nombre, descripcion, estado, created_at, updated_at) VALUES
(1, 'Administrador', 'Acceso completo al sistema', 1, NOW(), NOW()),
(2, 'Medico', 'Gestion clinica de analisis ECG', 1, NOW(), NOW()),
(3, 'Operador', 'Carga y consulta de analisis ECG', 1, NOW(), NOW());

UPDATE roles
SET nombre = CASE role_id
        WHEN 1 THEN 'Administrador'
        WHEN 2 THEN 'Medico'
        WHEN 3 THEN 'Operador'
        ELSE nombre
    END,
    descripcion = CASE role_id
        WHEN 1 THEN 'Acceso completo al sistema'
        WHEN 2 THEN 'Gestion clinica de analisis ECG'
        WHEN 3 THEN 'Carga y consulta de analisis ECG'
        ELSE descripcion
    END,
    estado = CASE role_id
        WHEN 1 THEN 1
        WHEN 2 THEN 1
        WHEN 3 THEN 1
        ELSE estado
    END,
    updated_at = NOW()
WHERE role_id IN (1, 2, 3);

SET @add_role_id = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN role_id INT NULL AFTER id',
        'SELECT 1'
    )
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'users'
      AND column_name = 'role_id'
);
PREPARE stmt FROM @add_role_id;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_estado = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD COLUMN estado BOOLEAN NOT NULL DEFAULT TRUE AFTER remember_token',
        'SELECT 1'
    )
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'users'
      AND column_name = 'estado'
);
PREPARE stmt FROM @add_estado;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE users
SET role_id = 1
WHERE role_id IS NULL
  AND id > 0;

SET @add_users_role_index = (
    SELECT IF(
        COUNT(*) = 0,
        'CREATE INDEX idx_users_role_id ON users(role_id)',
        'SELECT 1'
    )
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'users'
      AND index_name = 'idx_users_role_id'
);
PREPARE stmt FROM @add_users_role_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @add_users_role_fk = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE users ADD CONSTRAINT fk_users_roles FOREIGN KEY (role_id) REFERENCES roles(role_id) ON UPDATE CASCADE ON DELETE SET NULL',
        'SELECT 1'
    )
    FROM information_schema.referential_constraints
    WHERE constraint_schema = DATABASE()
      AND constraint_name = 'fk_users_roles'
);
PREPARE stmt FROM @add_users_role_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
