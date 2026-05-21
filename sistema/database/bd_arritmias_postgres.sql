-- =====================================================
-- BASE DE DATOS: Sistema de Clasificacion de Arritmias
-- Compatibilidad: PostgreSQL / ElephantSQL / Supabase
-- Ubicacion actual del sistema Laravel: sistema/
-- =====================================================

-- Limpiar tablas si existen (equivalente a DROP TABLE IF EXISTS ... CASCADE)
DROP TABLE IF EXISTS reportes CASCADE;
DROP TABLE IF EXISTS diagnosticos CASCADE;
DROP TABLE IF EXISTS predicciones CASCADE;
DROP TABLE IF EXISTS imagenes CASCADE;
DROP TABLE IF EXISTS estudios CASCADE;
DROP TABLE IF EXISTS pacientes CASCADE;
DROP TABLE IF EXISTS codigo_pacientes CASCADE;
DROP TABLE IF EXISTS ritmo_cardiacos CASCADE;
DROP TABLE IF EXISTS clasificacion_arritmias CASCADE;
DROP TABLE IF EXISTS nivel_gravedades CASCADE;
DROP TABLE IF EXISTS grupo_cardiacos CASCADE;
DROP TABLE IF EXISTS auditorias CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS roles CASCADE;

DROP TABLE IF EXISTS ecg_analyses CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS password_reset_tokens CASCADE;
DROP TABLE IF EXISTS sessions CASCADE;
DROP TABLE IF EXISTS cache CASCADE;
DROP TABLE IF EXISTS cache_locks CASCADE;
DROP TABLE IF EXISTS jobs CASCADE;
DROP TABLE IF EXISTS job_batches CASCADE;
DROP TABLE IF EXISTS failed_jobs CASCADE;
DROP TABLE IF EXISTS migrations CASCADE;

-- =====================================================
-- Creacion de Tablas
-- =====================================================

CREATE TABLE roles (
    role_id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
    usuario_id SERIAL PRIMARY KEY,
    role_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_roles FOREIGN KEY (role_id) REFERENCES roles(role_id) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Tabla de usuarios usada por Laravel
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
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
);

CREATE TABLE auditorias (
    auditoria_id BIGSERIAL PRIMARY KEY,
    usuario_id BIGINT NULL,
    accion VARCHAR(100) NOT NULL,
    modulo VARCHAR(100) NOT NULL,
    entidad VARCHAR(100) NULL,
    entidad_id VARCHAR(100) NULL,
    descripcion TEXT NULL,
    valores_anteriores JSONB NULL,
    valores_nuevos JSONB NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditorias_users FOREIGN KEY (usuario_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE grupo_cardiacos (
    grupo_id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE nivel_gravedades (
    nivel_id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clasificacion_arritmias (
    clasificacion_id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ritmo_cardiacos (
    ritmo_id SERIAL PRIMARY KEY,
    grupo_id INT NOT NULL,
    nivel_id INT NOT NULL,
    clasificacion_id INT NOT NULL,
    label VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ritmo_grupo FOREIGN KEY (grupo_id) REFERENCES grupo_cardiacos(grupo_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ritmo_nivel FOREIGN KEY (nivel_id) REFERENCES nivel_gravedades(nivel_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ritmo_clasificacion FOREIGN KEY (clasificacion_id) REFERENCES clasificacion_arritmias(clasificacion_id) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE codigo_pacientes (
    codigo_id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pacientes (
    paciente_id SERIAL PRIMARY KEY,
    codigo_id INT NOT NULL,
    codigo_generado VARCHAR(100) NOT NULL UNIQUE,
    fecha_nacimiento DATE,
    edad INT,
    sexo CHAR(1),
    peso DECIMAL(6,2),
    usuario_id BIGINT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pacientes_codigo FOREIGN KEY (codigo_id) REFERENCES codigo_pacientes(codigo_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pacientes_users FOREIGN KEY (usuario_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_pacientes_sexo CHECK (sexo IN ('M', 'F'))
);

CREATE TABLE estudios (
    estudio_id SERIAL PRIMARY KEY,
    paciente_id INT NOT NULL,
    usuario_id BIGINT NULL,
    legacy_analysis_id BIGINT NULL,
    observaciones TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudios_pacientes FOREIGN KEY (paciente_id) REFERENCES pacientes(paciente_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_estudios_users FOREIGN KEY (usuario_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE imagenes (
    imagen_id SERIAL PRIMARY KEY,
    estudio_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    formato VARCHAR(50),
    resolucion VARCHAR(50),
    tamano_kb INT,
    hash VARCHAR(255),
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_imagenes_estudios FOREIGN KEY (estudio_id) REFERENCES estudios(estudio_id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE predicciones (
    prediccion_id SERIAL PRIMARY KEY,
    imagen_id INT NOT NULL,
    ritmo_id INT NOT NULL,
    probabilidad DECIMAL(5,4) NOT NULL,
    tiempo_ms INT,
    top_predicciones JSONB NULL,
    label_detectado VARCHAR(200) NULL,
    label_code VARCHAR(50) NULL,
    tipo VARCHAR(20) NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_predicciones_imagenes FOREIGN KEY (imagen_id) REFERENCES imagenes(imagen_id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_predicciones_ritmos FOREIGN KEY (ritmo_id) REFERENCES ritmo_cardiacos(ritmo_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_predicciones_probabilidad CHECK (probabilidad >= 0 AND probabilidad <= 1)
);

CREATE TABLE diagnosticos (
    diagnostico_id SERIAL PRIMARY KEY,
    estudio_id INT NOT NULL,
    ritmo_id INT NOT NULL,
    concordancia BOOLEAN,
    resultado VARCHAR(20) NULL,
    doctor_label VARCHAR(200) NULL,
    observacion TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_diagnosticos_estudios FOREIGN KEY (estudio_id) REFERENCES estudios(estudio_id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_diagnosticos_ritmos FOREIGN KEY (ritmo_id) REFERENCES ritmo_cardiacos(ritmo_id) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE reportes (
    reporte_id SERIAL PRIMARY KEY,
    estudio_id INT NOT NULL,
    resumen TEXT,
    ruta_pdf VARCHAR(255),
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reportes_estudios FOREIGN KEY (estudio_id) REFERENCES estudios(estudio_id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE ecg_analyses (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    patient_identifier VARCHAR(100) NULL,
    patient_age DOUBLE PRECISION NOT NULL,
    patient_sex SMALLINT NOT NULL,
    patient_weight DOUBLE PRECISION NOT NULL,
    label VARCHAR(255) NOT NULL,
    label_code VARCHAR(50) NOT NULL,
    type VARCHAR(50) NOT NULL,
    confidence DOUBLE PRECISION NOT NULL,
    top_predictions JSONB NULL,
    doctor_result VARCHAR(50) NULL,
    doctor_label VARCHAR(255) NULL,
    doctor_notes TEXT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_ecg_analyses_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- Tablas auxiliares de soporte para Laravel
-- =====================================================

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL
);

CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INT NOT NULL
);

CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
);

CREATE TABLE jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INT NULL,
    available_at INT NOT NULL,
    created_at INT NOT NULL
);

CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
);

CREATE TABLE failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
);

-- =====================================================
-- Insercion de datos semilla
-- =====================================================

INSERT INTO roles (role_id, nombre, descripcion, estado, created_at, updated_at) VALUES
(1, 'Administrador', 'Acceso completo al sistema', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 'Medico', 'Gestion clinica de analisis ECG', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 'Operador', 'Carga y consulta de analisis ECG', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO grupo_cardiacos (grupo_id, nombre, descripcion) VALUES
(1, 'Sinusal', 'Ritmos de origen sinusal'),
(2, 'Conduccion', 'Alteraciones de conduccion cardiaca'),
(3, 'Ectopias', 'Complejos prematuros o patrones ectopicos'),
(4, 'Supraventricular', 'Arritmias de origen supraventricular');

INSERT INTO nivel_gravedades (nivel_id, nombre) VALUES
(1, 'Baja'),
(2, 'Moderada'),
(3, 'Alta');

INSERT INTO clasificacion_arritmias (clasificacion_id, nombre) VALUES
(1, 'Normal'),
(2, 'Arritmia');

INSERT INTO ritmo_cardiacos (ritmo_id, grupo_id, nivel_id, clasificacion_id, label, nombre, descripcion, estado, created_at, updated_at) VALUES
(1, 1, 1, 1, 'NORM', 'Ritmo Sinusal Normal', 'ECG dentro de limites normales.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 2, 2, 2, '1AVB', 'Bloqueo AV de primer grado', 'Retraso de conduccion auriculoventricular.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 2, 2, 2, 'WPW', 'Sindrome de Wolff-Parkinson-White', 'Patron de preexcitacion ventricular.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(4, 3, 2, 2, 'PVC', 'Complejo ventricular prematuro', 'Latido ventricular ectopico prematuro.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(5, 3, 2, 2, 'PAC', 'Complejo auricular prematuro', 'Latido auricular ectopico prematuro.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(6, 4, 3, 2, 'AFIB', 'Fibrilacion Auricular', 'Ritmo auricular irregular compatible con fibrilacion.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(7, 1, 2, 2, 'STACH', 'Taquicardia Sinusal', 'Frecuencia sinusal elevada.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(8, 1, 1, 1, 'SARRH', 'Arritmia Sinusal', 'Variabilidad fisiologica del ritmo sinusal.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(9, 1, 1, 1, 'SBRAD', 'Bradicardia Sinusal', 'Frecuencia sinusal disminuida.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(10, 4, 2, 2, 'SVARR', 'Arritmia Supraventricular', 'Alteracion del ritmo de origen supraventricular.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(11, 3, 2, 2, 'BIGU', 'Bigeminismo', 'Patron bigeminal de origen supraventricular o ventricular.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(12, 4, 3, 2, 'AFLT', 'Flutter Auricular', 'Ritmo auricular compatible con flutter.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(13, 4, 2, 2, 'PSVT', 'Taquicardia supraventricular paroxistica', 'Taquicardia supraventricular de inicio paroxistico.', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO codigo_pacientes (codigo_id, nombre, descripcion, estado, created_at, updated_at) VALUES
(1, 'PACIENTE', 'Codigo generado por el sistema', TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- Ajustar las secuencias en PostgreSQL por haber insertado IDs explicitos
SELECT setval(pg_get_serial_sequence('roles', 'role_id'), COALESCE(MAX(role_id), 1)) FROM roles;
SELECT setval(pg_get_serial_sequence('grupo_cardiacos', 'grupo_id'), COALESCE(MAX(grupo_id), 1)) FROM grupo_cardiacos;
SELECT setval(pg_get_serial_sequence('nivel_gravedades', 'nivel_id'), COALESCE(MAX(nivel_id), 1)) FROM nivel_gravedades;
SELECT setval(pg_get_serial_sequence('clasificacion_arritmias', 'clasificacion_id'), COALESCE(MAX(clasificacion_id), 1)) FROM clasificacion_arritmias;
SELECT setval(pg_get_serial_sequence('ritmo_cardiacos', 'ritmo_id'), COALESCE(MAX(ritmo_id), 1)) FROM ritmo_cardiacos;
SELECT setval(pg_get_serial_sequence('codigo_pacientes', 'codigo_id'), COALESCE(MAX(codigo_id), 1)) FROM codigo_pacientes;

-- =====================================================
-- Triggers de Base de Datos para Sincronizacion
-- =====================================================

-- 1. Trigger AFTER INSERT
CREATE OR REPLACE FUNCTION fn_trg_ecg_analyses_ai()
RETURNS TRIGGER AS $$
DECLARE
    v_ritmo_id INT;
    v_formato VARCHAR(50);
BEGIN
    -- Insertar o actualizar en 'pacientes'
    INSERT INTO pacientes (paciente_id, codigo_id, codigo_generado, edad, sexo, peso, usuario_id, estado, created_at, updated_at)
    VALUES (
        NEW.id,
        1,
        COALESCE(NEW.patient_identifier, 'PACIENTE_' || NEW.id),
        ROUND(NEW.patient_age),
        CASE WHEN NEW.patient_sex = 1 THEN 'M' ELSE 'F' END,
        NEW.patient_weight,
        NEW.user_id,
        TRUE,
        COALESCE(NEW.created_at, CURRENT_TIMESTAMP),
        COALESCE(NEW.updated_at, CURRENT_TIMESTAMP)
    )
    ON CONFLICT (paciente_id) DO UPDATE SET
        codigo_generado = EXCLUDED.codigo_generado,
        edad = EXCLUDED.edad,
        sexo = EXCLUDED.sexo,
        peso = EXCLUDED.peso,
        usuario_id = EXCLUDED.usuario_id,
        updated_at = EXCLUDED.updated_at;

    -- Insertar o actualizar en 'estudios'
    INSERT INTO estudios (estudio_id, paciente_id, usuario_id, legacy_analysis_id, observaciones, estado, created_at, updated_at)
    VALUES (
        NEW.id,
        NEW.id,
        NEW.user_id,
        NEW.id,
        'Migrado desde ecg_analyses. Archivo: ' || NEW.filename,
        TRUE,
        COALESCE(NEW.created_at, CURRENT_TIMESTAMP),
        COALESCE(NEW.updated_at, CURRENT_TIMESTAMP)
    )
    ON CONFLICT (estudio_id) DO UPDATE SET
        usuario_id = EXCLUDED.usuario_id,
        observaciones = EXCLUDED.observaciones,
        updated_at = EXCLUDED.updated_at;

    -- Insertar o actualizar en 'imagenes' (extrae la extension de NEW.filename)
    v_formato := LOWER(COALESCE(substring(NEW.filename from '\.([^.]+)$'), 'pdf'));
    INSERT INTO imagenes (imagen_id, estudio_id, ruta, formato, estado, created_at, updated_at)
    VALUES (
        NEW.id,
        NEW.id,
        NEW.filename,
        v_formato,
        TRUE,
        COALESCE(NEW.created_at, CURRENT_TIMESTAMP),
        COALESCE(NEW.updated_at, CURRENT_TIMESTAMP)
    )
    ON CONFLICT (imagen_id) DO UPDATE SET
        ruta = EXCLUDED.ruta,
        formato = EXCLUDED.formato,
        updated_at = EXCLUDED.updated_at;

    -- Insertar o actualizar en 'predicciones'
    SELECT ritmo_id INTO v_ritmo_id FROM ritmo_cardiacos WHERE label = NEW.label_code LIMIT 1;
    IF v_ritmo_id IS NULL THEN
        v_ritmo_id := 1;
    END IF;

    INSERT INTO predicciones (prediccion_id, imagen_id, ritmo_id, probabilidad, top_predicciones, label_detectado, label_code, tipo, estado, created_at, updated_at)
    VALUES (
        NEW.id,
        NEW.id,
        v_ritmo_id,
        LEAST(GREATEST(NEW.confidence / 100.0, 0.0), 1.0),
        NEW.top_predictions,
        NEW.label,
        NEW.label_code,
        NEW.type,
        TRUE,
        COALESCE(NEW.created_at, CURRENT_TIMESTAMP),
        COALESCE(NEW.updated_at, CURRENT_TIMESTAMP)
    )
    ON CONFLICT (prediccion_id) DO UPDATE SET
        ritmo_id = EXCLUDED.ritmo_id,
        probabilidad = EXCLUDED.probabilidad,
        top_predicciones = EXCLUDED.top_predictions,
        label_detectado = EXCLUDED.label_detectado,
        label_code = EXCLUDED.label_code,
        tipo = EXCLUDED.tipo,
        updated_at = EXCLUDED.updated_at;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_ecg_analyses_ai
AFTER INSERT ON ecg_analyses
FOR EACH ROW
EXECUTE FUNCTION fn_trg_ecg_analyses_ai();


-- 2. Trigger AFTER UPDATE
CREATE OR REPLACE FUNCTION fn_trg_ecg_analyses_au()
RETURNS TRIGGER AS $$
DECLARE
    v_ritmo_id INT;
    v_formato VARCHAR(50);
    v_doc_ritmo_id INT;
BEGIN
    -- Actualizar pacientes
    UPDATE pacientes
    SET codigo_generado = COALESCE(NEW.patient_identifier, 'PACIENTE_' || NEW.id),
        edad = ROUND(NEW.patient_age),
        sexo = CASE WHEN NEW.patient_sex = 1 THEN 'M' ELSE 'F' END,
        peso = NEW.patient_weight,
        usuario_id = NEW.user_id,
        updated_at = COALESCE(NEW.updated_at, CURRENT_TIMESTAMP)
    WHERE paciente_id = NEW.id;

    -- Actualizar estudios
    UPDATE estudios
    SET usuario_id = NEW.user_id,
        observaciones = 'Migrado desde ecg_analyses. Archivo: ' || NEW.filename,
        updated_at = COALESCE(NEW.updated_at, CURRENT_TIMESTAMP)
    WHERE estudio_id = NEW.id;

    -- Actualizar imagenes
    v_formato := LOWER(COALESCE(substring(NEW.filename from '\.([^.]+)$'), 'pdf'));
    UPDATE imagenes
    SET ruta = NEW.filename,
        formato = v_formato,
        updated_at = COALESCE(NEW.updated_at, CURRENT_TIMESTAMP)
    WHERE imagen_id = NEW.id;

    -- Actualizar predicciones
    SELECT ritmo_id INTO v_ritmo_id FROM ritmo_cardiacos WHERE label = NEW.label_code LIMIT 1;
    IF v_ritmo_id IS NULL THEN
        v_ritmo_id := 1;
    END IF;

    UPDATE predicciones
    SET ritmo_id = v_ritmo_id,
        probabilidad = LEAST(GREATEST(NEW.confidence / 100.0, 0.0), 1.0),
        top_predicciones = NEW.top_predictions,
        label_detectado = NEW.label,
        label_code = NEW.label_code,
        tipo = NEW.type,
        updated_at = COALESCE(NEW.updated_at, CURRENT_TIMESTAMP)
    WHERE prediccion_id = NEW.id;

    -- Manejar la valoracion (diagnosticos)
    IF NEW.doctor_result IS NULL THEN
        DELETE FROM diagnosticos WHERE diagnostico_id = NEW.id;
    ELSE
        -- Resolver ID del ritmo del doctor
        SELECT ritmo_id INTO v_doc_ritmo_id
        FROM ritmo_cardiacos
        WHERE nombre = NEW.doctor_label
           OR (NEW.doctor_label LIKE '%Fibrilacion%' AND label = 'AFIB')
           OR (NEW.doctor_label LIKE '%Flutter%' AND label = 'AFLT')
           OR (NEW.doctor_label LIKE '%Taquicardia%' AND label = 'STACH')
           OR (NEW.doctor_label LIKE '%Normal%' AND label = 'NORM')
        LIMIT 1;

        IF v_doc_ritmo_id IS NULL THEN
            v_doc_ritmo_id := 1;
        END IF;

        INSERT INTO diagnosticos (diagnostico_id, estudio_id, ritmo_id, concordancia, resultado, doctor_label, observacion, estado, reviewed_at, created_at, updated_at)
        VALUES (
            NEW.id,
            NEW.id,
            v_doc_ritmo_id,
            NEW.type = NEW.doctor_result,
            NEW.doctor_result,
            NEW.doctor_label,
            NEW.doctor_notes,
            TRUE,
            NEW.reviewed_at,
            COALESCE(NEW.reviewed_at, NEW.updated_at, CURRENT_TIMESTAMP),
            COALESCE(NEW.updated_at, CURRENT_TIMESTAMP)
        )
        ON CONFLICT (diagnostico_id) DO UPDATE SET
            ritmo_id = EXCLUDED.ritmo_id,
            concordancia = EXCLUDED.concordancia,
            resultado = EXCLUDED.resultado,
            doctor_label = EXCLUDED.doctor_label,
            observacion = EXCLUDED.observacion,
            reviewed_at = EXCLUDED.reviewed_at,
            updated_at = EXCLUDED.updated_at;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_ecg_analyses_au
AFTER UPDATE ON ecg_analyses
FOR EACH ROW
EXECUTE FUNCTION fn_trg_ecg_analyses_au();


-- 3. Trigger AFTER DELETE
CREATE OR REPLACE FUNCTION fn_trg_ecg_analyses_ad()
RETURNS TRIGGER AS $$
BEGIN
    DELETE FROM estudios WHERE estudio_id = OLD.id;
    DELETE FROM pacientes WHERE paciente_id = OLD.id;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_ecg_analyses_ad
AFTER DELETE ON ecg_analyses
FOR EACH ROW
EXECUTE FUNCTION fn_trg_ecg_analyses_ad();

-- =====================================================
-- Indices para Optimizacion en PostgreSQL
-- =====================================================

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
