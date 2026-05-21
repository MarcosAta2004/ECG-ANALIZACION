<?php

$sqlitePath = __DIR__ . '/database.sqlite';
if (! is_file($sqlitePath) || filesize($sqlitePath) === 0) {
    fwrite(
        STDERR,
        "No se encontro un SQLite valido en sistema/database/database.sqlite.\n" .
        "Este generador solo sirve para migrar datos antiguos desde SQLite; " .
        "si ya estas usando MySQL, no necesitas ejecutarlo.\n"
    );
    exit(1);
}

$sqlite = new PDO('sqlite:' . $sqlitePath);
$sqlite->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

function sql_value($value): string
{
    if ($value === null || $value === '') {
        return $value === '' ? "''" : 'NULL';
    }

    return "'" . str_replace("'", "''", (string) $value) . "'";
}

function sql_bool($value): string
{
    return $value ? '1' : '0';
}

function values_line(array $values): string
{
    return '(' . implode(', ', $values) . ')';
}

$users = $sqlite->query('SELECT * FROM users ORDER BY id')->fetchAll();
$analyses = $sqlite->query('SELECT * FROM ecg_analyses ORDER BY id')->fetchAll();
$sessions = $sqlite->query('SELECT * FROM sessions ORDER BY id')->fetchAll();

$ritmos = [
    'NORM' => [1, 1, 1, 'Ritmo Sinusal Normal', 'ECG dentro de limites normales.'],
    '1AVB' => [2, 2, 2, 'Bloqueo AV de primer grado', 'Retraso de conduccion auriculoventricular.'],
    'WPW' => [2, 2, 2, 'Sindrome de Wolff-Parkinson-White', 'Patron de preexcitacion ventricular.'],
    'PVC' => [3, 2, 2, 'Complejo ventricular prematuro', 'Latido ventricular ectopico prematuro.'],
    'PAC' => [3, 2, 2, 'Complejo auricular prematuro', 'Latido auricular ectopico prematuro.'],
    'AFIB' => [4, 3, 2, 'Fibrilacion Auricular', 'Ritmo auricular irregular compatible con fibrilacion.'],
    'STACH' => [1, 2, 2, 'Taquicardia Sinusal', 'Frecuencia sinusal elevada.'],
    'SARRH' => [1, 1, 1, 'Arritmia Sinusal', 'Variabilidad fisiologica del ritmo sinusal.'],
    'SBRAD' => [1, 1, 1, 'Bradicardia Sinusal', 'Frecuencia sinusal disminuida.'],
    'SVARR' => [4, 2, 2, 'Arritmia Supraventricular', 'Alteracion del ritmo de origen supraventricular.'],
    'BIGU' => [3, 2, 2, 'Bigeminismo', 'Patron bigeminal de origen supraventricular o ventricular.'],
    'AFLT' => [4, 3, 2, 'Flutter Auricular', 'Ritmo auricular compatible con flutter.'],
    'PSVT' => [4, 2, 2, 'Taquicardia supraventricular paroxistica', 'Taquicardia supraventricular de inicio paroxistico.'],
];

$ritmoIds = [];
$i = 1;
foreach (array_keys($ritmos) as $code) {
    $ritmoIds[$code] = $i++;
}

function rhythm_code_from_label(?string $label): string
{
    $label = mb_strtolower((string) $label, 'UTF-8');
    return match (true) {
        str_contains($label, 'fibril') => 'AFIB',
        str_contains($label, 'flutter') => 'AFLT',
        str_contains($label, 'taquicardia') => 'STACH',
        str_contains($label, 'normal'), str_contains($label, 'sinusal') => 'NORM',
        default => 'NORM',
    };
}

$sql = [];
$sql[] = '-- =====================================================';
$sql[] = '-- BASE DE DATOS: Sistema de Clasificacion de Arritmias';
$sql[] = '-- Motor: MySQL / MariaDB';
$sql[] = '-- Generado desde sistema/database/database.sqlite';
$sql[] = '-- =====================================================';
$sql[] = '';
$sql[] = 'SET NAMES utf8mb4;';
$sql[] = 'SET FOREIGN_KEY_CHECKS = 0;';
$sql[] = '';
$sql[] = 'CREATE DATABASE IF NOT EXISTS bd_arritmias CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$sql[] = 'USE bd_arritmias;';
$sql[] = '';
$sql[] = 'DROP TABLE IF EXISTS reportes, diagnosticos, predicciones, imagenes, estudios, pacientes, codigo_pacientes, ritmo_cardiacos, clasificacion_arritmias, nivel_gravedades, grupo_cardiacos, auditorias, usuarios, roles;';
$sql[] = 'DROP TABLE IF EXISTS ecg_analyses, users, password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs, migrations;';
$sql[] = '';
$sql[] = 'SET FOREIGN_KEY_CHECKS = 1;';
$sql[] = '';

$sql[] = <<<'SQL'
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_roles FOREIGN KEY (role_id) REFERENCES roles(role_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabla de usuarios usada por Laravel y por el modulo de gestion.
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_users_roles FOREIGN KEY (role_id) REFERENCES roles(role_id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE auditorias (
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
    CONSTRAINT fk_auditorias_users FOREIGN KEY (usuario_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE grupo_cardiacos (
    grupo_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE nivel_gravedades (
    nivel_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE clasificacion_arritmias (
    clasificacion_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE ritmo_cardiacos (
    ritmo_id INT AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT NOT NULL,
    nivel_id INT NOT NULL,
    clasificacion_id INT NOT NULL,
    label VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ritmo_grupo FOREIGN KEY (grupo_id) REFERENCES grupo_cardiacos(grupo_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ritmo_nivel FOREIGN KEY (nivel_id) REFERENCES nivel_gravedades(nivel_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ritmo_clasificacion FOREIGN KEY (clasificacion_id) REFERENCES clasificacion_arritmias(clasificacion_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE codigo_pacientes (
    codigo_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pacientes (
    paciente_id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_id INT NOT NULL,
    codigo_generado VARCHAR(100) NOT NULL UNIQUE,
    fecha_nacimiento DATE,
    edad INT,
    sexo CHAR(1),
    peso DECIMAL(6,2),
    usuario_id BIGINT UNSIGNED NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pacientes_codigo FOREIGN KEY (codigo_id) REFERENCES codigo_pacientes(codigo_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pacientes_users FOREIGN KEY (usuario_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_pacientes_sexo CHECK (sexo IN ('M', 'F'))
) ENGINE=InnoDB;

CREATE TABLE estudios (
    estudio_id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    usuario_id BIGINT UNSIGNED NULL,
    legacy_analysis_id BIGINT UNSIGNED NULL,
    observaciones TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudios_pacientes FOREIGN KEY (paciente_id) REFERENCES pacientes(paciente_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_estudios_users FOREIGN KEY (usuario_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE imagenes (
    imagen_id INT AUTO_INCREMENT PRIMARY KEY,
    estudio_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    formato VARCHAR(50),
    resolucion VARCHAR(50),
    tamano_kb INT,
    hash VARCHAR(255),
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_imagenes_estudios FOREIGN KEY (estudio_id) REFERENCES estudios(estudio_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE predicciones (
    prediccion_id INT AUTO_INCREMENT PRIMARY KEY,
    imagen_id INT NOT NULL,
    ritmo_id INT NOT NULL,
    probabilidad DECIMAL(5,4) NOT NULL,
    tiempo_ms INT,
    top_predicciones JSON NULL,
    label_detectado VARCHAR(200) NULL,
    label_code VARCHAR(50) NULL,
    tipo VARCHAR(20) NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_predicciones_imagenes FOREIGN KEY (imagen_id) REFERENCES imagenes(imagen_id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_predicciones_ritmos FOREIGN KEY (ritmo_id) REFERENCES ritmo_cardiacos(ritmo_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_predicciones_probabilidad CHECK (probabilidad >= 0 AND probabilidad <= 1)
) ENGINE=InnoDB;

CREATE TABLE diagnosticos (
    diagnostico_id INT AUTO_INCREMENT PRIMARY KEY,
    estudio_id INT NOT NULL,
    ritmo_id INT NOT NULL,
    concordancia BOOLEAN,
    resultado VARCHAR(20) NULL,
    doctor_label VARCHAR(200) NULL,
    observacion TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_diagnosticos_estudios FOREIGN KEY (estudio_id) REFERENCES estudios(estudio_id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_diagnosticos_ritmos FOREIGN KEY (ritmo_id) REFERENCES ritmo_cardiacos(ritmo_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE reportes (
    reporte_id INT AUTO_INCREMENT PRIMARY KEY,
    estudio_id INT NOT NULL,
    resumen TEXT,
    ruta_pdf VARCHAR(255),
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reportes_estudios FOREIGN KEY (estudio_id) REFERENCES estudios(estudio_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ecg_analyses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL,
    patient_identifier VARCHAR(100) NULL,
    patient_age DOUBLE NOT NULL,
    patient_sex TINYINT NOT NULL,
    patient_weight DOUBLE NOT NULL,
    label VARCHAR(255) NOT NULL,
    label_code VARCHAR(50) NOT NULL,
    type VARCHAR(50) NOT NULL,
    confidence DOUBLE NOT NULL,
    top_predictions JSON NULL,
    doctor_result VARCHAR(50) NULL,
    doctor_label VARCHAR(255) NULL,
    doctor_notes TEXT NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_ecg_analyses_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL
) ENGINE=InnoDB;

CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
) ENGINE=InnoDB;

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
) ENGINE=InnoDB;
SQL;

$sql[] = '';
$sql[] = "INSERT INTO roles (role_id, nombre, descripcion, estado, created_at, updated_at) VALUES
(1, 'Administrador', 'Acceso completo al sistema', 1, NOW(), NOW()),
(2, 'Medico', 'Gestion clinica de analisis ECG', 1, NOW(), NOW()),
(3, 'Operador', 'Carga y consulta de analisis ECG', 1, NOW(), NOW());";

if ($users) {
    $rows = [];
    foreach ($users as $user) {
        $rows[] = values_line([
            sql_value($user['id']),
            '1',
            sql_value($user['name']),
            sql_value($user['email']),
            sql_value($user['password']),
            '1',
            sql_value($user['created_at']),
            sql_value($user['updated_at']),
        ]);
    }
    $sql[] = 'INSERT INTO usuarios (usuario_id, role_id, nombre, email, password, estado, created_at, updated_at) VALUES';
    $sql[] = implode(",\n", $rows) . ';';
}

$sql[] = "INSERT INTO grupo_cardiacos (grupo_id, nombre, descripcion) VALUES
(1, 'Sinusal', 'Ritmos de origen sinusal'),
(2, 'Conduccion', 'Alteraciones de conduccion cardiaca'),
(3, 'Ectopias', 'Complejos prematuros o patrones ectopicos'),
(4, 'Supraventricular', 'Arritmias de origen supraventricular');";
$sql[] = "INSERT INTO nivel_gravedades (nivel_id, nombre) VALUES (1, 'Baja'), (2, 'Moderada'), (3, 'Alta');";
$sql[] = "INSERT INTO clasificacion_arritmias (clasificacion_id, nombre) VALUES (1, 'Normal'), (2, 'Arritmia');";

$rows = [];
foreach ($ritmos as $code => $data) {
    [$grupo, $nivel, $clasificacion, $nombre, $descripcion] = $data;
    $rows[] = values_line([
        (string) $ritmoIds[$code],
        (string) $grupo,
        (string) $nivel,
        (string) $clasificacion,
        sql_value($code),
        sql_value($nombre),
        sql_value($descripcion),
        '1',
        'NOW()',
        'NOW()',
    ]);
}
$sql[] = 'INSERT INTO ritmo_cardiacos (ritmo_id, grupo_id, nivel_id, clasificacion_id, label, nombre, descripcion, estado, created_at, updated_at) VALUES';
$sql[] = implode(",\n", $rows) . ';';

$sql[] = "INSERT INTO codigo_pacientes (codigo_id, nombre, descripcion, estado, created_at, updated_at) VALUES (1, 'PACIENTE', 'Codigo generado por el sistema', 1, NOW(), NOW());";

if ($users) {
    $rows = [];
    foreach ($users as $user) {
        $rows[] = values_line([
            sql_value($user['id']),
            '1',
            sql_value($user['name']),
            sql_value($user['email']),
            sql_value($user['email_verified_at']),
            sql_value($user['password']),
            sql_value($user['remember_token']),
            '1',
            sql_value($user['created_at']),
            sql_value($user['updated_at']),
        ]);
    }
    $sql[] = 'INSERT INTO users (id, role_id, name, email, email_verified_at, password, remember_token, estado, created_at, updated_at) VALUES';
    $sql[] = implode(",\n", $rows) . ';';
}

if ($analyses) {
    $rows = [];
    foreach ($analyses as $row) {
        $rows[] = values_line([
            sql_value($row['id']),
            sql_value($row['user_id']),
            sql_value($row['filename']),
            sql_value($row['patient_identifier']),
            sql_value($row['patient_age']),
            sql_value($row['patient_sex']),
            sql_value($row['patient_weight']),
            sql_value($row['label']),
            sql_value($row['label_code']),
            sql_value($row['type']),
            sql_value($row['confidence']),
            sql_value($row['top_predictions']),
            sql_value($row['doctor_result']),
            sql_value($row['doctor_label']),
            sql_value($row['doctor_notes']),
            sql_value($row['reviewed_at']),
            sql_value($row['created_at']),
            sql_value($row['updated_at']),
        ]);
    }
    $sql[] = 'INSERT INTO ecg_analyses (id, user_id, filename, patient_identifier, patient_age, patient_sex, patient_weight, label, label_code, type, confidence, top_predictions, doctor_result, doctor_label, doctor_notes, reviewed_at, created_at, updated_at) VALUES';
    $sql[] = implode(",\n", $rows) . ';';

    $rows = [];
    foreach ($analyses as $row) {
        $rows[] = values_line([
            sql_value($row['id']),
            '1',
            sql_value($row['patient_identifier']),
            'NULL',
            sql_value((int) round((float) $row['patient_age'])),
            sql_value(((int) $row['patient_sex']) === 1 ? 'M' : 'F'),
            sql_value($row['patient_weight']),
            sql_value($row['user_id']),
            '1',
            sql_value($row['created_at']),
            sql_value($row['updated_at']),
        ]);
    }
    $sql[] = 'INSERT INTO pacientes (paciente_id, codigo_id, codigo_generado, fecha_nacimiento, edad, sexo, peso, usuario_id, estado, created_at, updated_at) VALUES';
    $sql[] = implode(",\n", $rows) . ';';

    $rows = [];
    foreach ($analyses as $row) {
        $rows[] = values_line([
            sql_value($row['id']),
            sql_value($row['id']),
            sql_value($row['user_id']),
            sql_value($row['id']),
            sql_value('Migrado desde ecg_analyses. Archivo: ' . $row['filename']),
            '1',
            sql_value($row['created_at']),
            sql_value($row['updated_at']),
        ]);
    }
    $sql[] = 'INSERT INTO estudios (estudio_id, paciente_id, usuario_id, legacy_analysis_id, observaciones, estado, created_at, updated_at) VALUES';
    $sql[] = implode(",\n", $rows) . ';';

    $rows = [];
    foreach ($analyses as $row) {
        $format = strtolower(pathinfo((string) $row['filename'], PATHINFO_EXTENSION)) ?: 'pdf';
        $rows[] = values_line([
            sql_value($row['id']),
            sql_value($row['id']),
            sql_value($row['filename']),
            sql_value($format),
            'NULL',
            'NULL',
            'NULL',
            '1',
            sql_value($row['created_at']),
            sql_value($row['updated_at']),
        ]);
    }
    $sql[] = 'INSERT INTO imagenes (imagen_id, estudio_id, ruta, formato, resolucion, tamano_kb, hash, estado, created_at, updated_at) VALUES';
    $sql[] = implode(",\n", $rows) . ';';

    $rows = [];
    foreach ($analyses as $row) {
        $code = $row['label_code'] ?: rhythm_code_from_label($row['label']);
        $rows[] = values_line([
            sql_value($row['id']),
            sql_value($row['id']),
            (string) ($ritmoIds[$code] ?? $ritmoIds['NORM']),
            number_format(((float) $row['confidence']) / 100, 4, '.', ''),
            'NULL',
            sql_value($row['top_predictions']),
            sql_value($row['label']),
            sql_value($row['label_code']),
            sql_value($row['type']),
            '1',
            sql_value($row['created_at']),
            sql_value($row['updated_at']),
        ]);
    }
    $sql[] = 'INSERT INTO predicciones (prediccion_id, imagen_id, ritmo_id, probabilidad, tiempo_ms, top_predicciones, label_detectado, label_code, tipo, estado, created_at, updated_at) VALUES';
    $sql[] = implode(",\n", $rows) . ';';

    $rows = [];
    foreach ($analyses as $row) {
        if ($row['doctor_result'] === null) {
            continue;
        }
        $code = rhythm_code_from_label($row['doctor_label']);
        $rows[] = values_line([
            sql_value($row['id']),
            sql_value($row['id']),
            (string) ($ritmoIds[$code] ?? $ritmoIds['NORM']),
            sql_bool($row['type'] === $row['doctor_result']),
            sql_value($row['doctor_result']),
            sql_value($row['doctor_label']),
            sql_value($row['doctor_notes']),
            '1',
            sql_value($row['reviewed_at']),
            sql_value($row['reviewed_at'] ?: $row['updated_at']),
            sql_value($row['updated_at']),
        ]);
    }
    if ($rows) {
        $sql[] = 'INSERT INTO diagnosticos (diagnostico_id, estudio_id, ritmo_id, concordancia, resultado, doctor_label, observacion, estado, reviewed_at, created_at, updated_at) VALUES';
        $sql[] = implode(",\n", $rows) . ';';
    }
}

if ($sessions) {
    $rows = [];
    foreach ($sessions as $row) {
        $rows[] = values_line([
            sql_value($row['id']),
            sql_value($row['user_id']),
            sql_value($row['ip_address']),
            sql_value($row['user_agent']),
            sql_value($row['payload']),
            sql_value($row['last_activity']),
        ]);
    }
    $sql[] = 'INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity) VALUES';
    $sql[] = implode(",\n", $rows) . ';';
}

$sql[] = <<<'SQL'
DELIMITER $$

CREATE TRIGGER trg_ecg_analyses_ai
AFTER INSERT ON ecg_analyses
FOR EACH ROW
BEGIN
    INSERT INTO pacientes (paciente_id, codigo_id, codigo_generado, edad, sexo, peso, usuario_id, estado, created_at, updated_at)
    VALUES (
        NEW.id,
        1,
        COALESCE(NEW.patient_identifier, CONCAT('PACIENTE_', NEW.id)),
        ROUND(NEW.patient_age),
        IF(NEW.patient_sex = 1, 'M', 'F'),
        NEW.patient_weight,
        NEW.user_id,
        1,
        COALESCE(NEW.created_at, NOW()),
        COALESCE(NEW.updated_at, NOW())
    )
    ON DUPLICATE KEY UPDATE
        codigo_generado = VALUES(codigo_generado),
        edad = VALUES(edad),
        sexo = VALUES(sexo),
        peso = VALUES(peso),
        usuario_id = VALUES(usuario_id),
        updated_at = VALUES(updated_at);

    INSERT INTO estudios (estudio_id, paciente_id, usuario_id, legacy_analysis_id, observaciones, estado, created_at, updated_at)
    VALUES (
        NEW.id,
        NEW.id,
        NEW.user_id,
        NEW.id,
        CONCAT('Migrado desde ecg_analyses. Archivo: ', NEW.filename),
        1,
        COALESCE(NEW.created_at, NOW()),
        COALESCE(NEW.updated_at, NOW())
    )
    ON DUPLICATE KEY UPDATE
        usuario_id = VALUES(usuario_id),
        observaciones = VALUES(observaciones),
        updated_at = VALUES(updated_at);

    INSERT INTO imagenes (imagen_id, estudio_id, ruta, formato, estado, created_at, updated_at)
    VALUES (
        NEW.id,
        NEW.id,
        NEW.filename,
        LOWER(SUBSTRING_INDEX(NEW.filename, '.', -1)),
        1,
        COALESCE(NEW.created_at, NOW()),
        COALESCE(NEW.updated_at, NOW())
    )
    ON DUPLICATE KEY UPDATE
        ruta = VALUES(ruta),
        formato = VALUES(formato),
        updated_at = VALUES(updated_at);

    INSERT INTO predicciones (prediccion_id, imagen_id, ritmo_id, probabilidad, top_predicciones, label_detectado, label_code, tipo, estado, created_at, updated_at)
    VALUES (
        NEW.id,
        NEW.id,
        COALESCE((SELECT ritmo_id FROM ritmo_cardiacos WHERE label = NEW.label_code LIMIT 1), 1),
        LEAST(GREATEST(NEW.confidence / 100, 0), 1),
        NEW.top_predictions,
        NEW.label,
        NEW.label_code,
        NEW.type,
        1,
        COALESCE(NEW.created_at, NOW()),
        COALESCE(NEW.updated_at, NOW())
    )
    ON DUPLICATE KEY UPDATE
        ritmo_id = VALUES(ritmo_id),
        probabilidad = VALUES(probabilidad),
        top_predicciones = VALUES(top_predicciones),
        label_detectado = VALUES(label_detectado),
        label_code = VALUES(label_code),
        tipo = VALUES(tipo),
        updated_at = VALUES(updated_at);
END$$

CREATE TRIGGER trg_ecg_analyses_au
AFTER UPDATE ON ecg_analyses
FOR EACH ROW
BEGIN
    UPDATE pacientes
    SET codigo_generado = COALESCE(NEW.patient_identifier, CONCAT('PACIENTE_', NEW.id)),
        edad = ROUND(NEW.patient_age),
        sexo = IF(NEW.patient_sex = 1, 'M', 'F'),
        peso = NEW.patient_weight,
        usuario_id = NEW.user_id,
        updated_at = COALESCE(NEW.updated_at, NOW())
    WHERE paciente_id = NEW.id;

    UPDATE estudios
    SET usuario_id = NEW.user_id,
        observaciones = CONCAT('Migrado desde ecg_analyses. Archivo: ', NEW.filename),
        updated_at = COALESCE(NEW.updated_at, NOW())
    WHERE estudio_id = NEW.id;

    UPDATE imagenes
    SET ruta = NEW.filename,
        formato = LOWER(SUBSTRING_INDEX(NEW.filename, '.', -1)),
        updated_at = COALESCE(NEW.updated_at, NOW())
    WHERE imagen_id = NEW.id;

    UPDATE predicciones
    SET ritmo_id = COALESCE((SELECT ritmo_id FROM ritmo_cardiacos WHERE label = NEW.label_code LIMIT 1), 1),
        probabilidad = LEAST(GREATEST(NEW.confidence / 100, 0), 1),
        top_predicciones = NEW.top_predictions,
        label_detectado = NEW.label,
        label_code = NEW.label_code,
        tipo = NEW.type,
        updated_at = COALESCE(NEW.updated_at, NOW())
    WHERE prediccion_id = NEW.id;

    IF NEW.doctor_result IS NULL THEN
        DELETE FROM diagnosticos WHERE diagnostico_id = NEW.id;
    ELSE
        INSERT INTO diagnosticos (diagnostico_id, estudio_id, ritmo_id, concordancia, resultado, doctor_label, observacion, estado, reviewed_at, created_at, updated_at)
        VALUES (
            NEW.id,
            NEW.id,
            COALESCE((
                SELECT ritmo_id
                FROM ritmo_cardiacos
                WHERE nombre = NEW.doctor_label
                   OR (NEW.doctor_label LIKE '%Fibrilacion%' AND label = 'AFIB')
                   OR (NEW.doctor_label LIKE '%Flutter%' AND label = 'AFLT')
                   OR (NEW.doctor_label LIKE '%Taquicardia%' AND label = 'STACH')
                   OR (NEW.doctor_label LIKE '%Normal%' AND label = 'NORM')
                LIMIT 1
            ), 1),
            NEW.type = NEW.doctor_result,
            NEW.doctor_result,
            NEW.doctor_label,
            NEW.doctor_notes,
            1,
            NEW.reviewed_at,
            COALESCE(NEW.reviewed_at, NEW.updated_at, NOW()),
            COALESCE(NEW.updated_at, NOW())
        )
        ON DUPLICATE KEY UPDATE
            ritmo_id = VALUES(ritmo_id),
            concordancia = VALUES(concordancia),
            resultado = VALUES(resultado),
            doctor_label = VALUES(doctor_label),
            observacion = VALUES(observacion),
            reviewed_at = VALUES(reviewed_at),
            updated_at = VALUES(updated_at);
    END IF;
END$$

CREATE TRIGGER trg_ecg_analyses_ad
AFTER DELETE ON ecg_analyses
FOR EACH ROW
BEGIN
    DELETE FROM estudios WHERE estudio_id = OLD.id;
    DELETE FROM pacientes WHERE paciente_id = OLD.id;
END$$

DELIMITER ;

CREATE INDEX idx_usuarios_role_id ON usuarios(role_id);
CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_auditorias_usuario_id ON auditorias(usuario_id);
CREATE INDEX idx_auditorias_modulo_accion ON auditorias(modulo, accion);
CREATE INDEX idx_auditorias_created_at ON auditorias(created_at);
CREATE INDEX idx_ritmo_grupo_id ON ritmo_cardiacos(grupo_id);
CREATE INDEX idx_ritmo_nivel_id ON ritmo_cardiacos(nivel_id);
CREATE INDEX idx_ritmo_clasificacion_id ON ritmo_cardiacos(clasificacion_id);
CREATE INDEX idx_pacientes_codigo_id ON pacientes(codigo_id);
CREATE INDEX idx_pacientes_usuario_id ON pacientes(usuario_id);
CREATE INDEX idx_estudios_paciente_id ON estudios(paciente_id);
CREATE INDEX idx_estudios_usuario_id ON estudios(usuario_id);
CREATE INDEX idx_imagenes_estudio_id ON imagenes(estudio_id);
CREATE INDEX idx_predicciones_imagen_id ON predicciones(imagen_id);
CREATE INDEX idx_predicciones_ritmo_id ON predicciones(ritmo_id);
CREATE INDEX idx_diagnosticos_estudio_id ON diagnosticos(estudio_id);
CREATE INDEX idx_diagnosticos_ritmo_id ON diagnosticos(ritmo_id);
CREATE INDEX idx_reportes_estudio_id ON reportes(estudio_id);

CREATE INDEX ecg_analyses_user_created_at_idx ON ecg_analyses(user_id, created_at);
CREATE INDEX ecg_analyses_user_type_idx ON ecg_analyses(user_id, type);
CREATE INDEX ecg_analyses_user_doctor_result_idx ON ecg_analyses(user_id, doctor_result);
CREATE INDEX ecg_analyses_user_reviewed_at_idx ON ecg_analyses(user_id, reviewed_at);
CREATE INDEX sessions_user_id_index ON sessions(user_id);
CREATE INDEX sessions_last_activity_index ON sessions(last_activity);
CREATE INDEX cache_expiration_index ON cache(expiration);
CREATE INDEX cache_locks_expiration_index ON cache_locks(expiration);
CREATE INDEX jobs_queue_index ON jobs(queue);

SET FOREIGN_KEY_CHECKS = 1;
SQL;

file_put_contents(__DIR__ . '/bd_arritmias_mysql.sql', implode("\n", $sql) . "\n");

echo 'SQL generado: ' . __DIR__ . "/bd_arritmias_mysql.sql\n";
