-- =====================================================
-- DATOS ACTUALES: Sistema de Clasificacion de Arritmias
-- Exportado desde MySQL bd_arritmias
-- Destino: PostgreSQL (Supabase / ElephantSQL)
-- Generado: 2026-05-21 16:04:04
-- IMPORTANTE: Ejecutar DESPUES de bd_arritmias_postgres.sql
-- =====================================================

-- Desactivar restricciones temporalmente para carga masiva
SET session_replication_role = 'replica';

-- ==> roles
INSERT INTO roles (role_id, nombre, descripcion, estado, created_at, updated_at) VALUES
('1', 'Administrador', 'Acceso completo al sistema', TRUE, '2026-05-04 15:46:27', '2026-05-21 08:47:34'),
('2', 'Medico', 'Gestion clinica de analisis ECG', TRUE, '2026-05-21 08:45:54', '2026-05-21 08:47:34'),
('3', 'Operador', 'Carga y consulta de analisis ECG', TRUE, '2026-05-21 08:45:54', '2026-05-21 08:47:34');
SELECT setval(pg_get_serial_sequence('roles', 'role_id'), COALESCE(MAX(role_id), 1)) FROM roles;


-- ==> usuarios
INSERT INTO usuarios (usuario_id, role_id, nombre, email, password, estado, created_at, updated_at) VALUES
('1', '1', 'Administrador', 'admin@ecg.com', '$2y$12$lUwCBDXMyd6Aivw0OEwwe.9ZCIWAMwDs2.pmS33iJCeNj0B6/B2Nm', TRUE, '2026-04-13 21:44:10', '2026-04-13 21:44:10');
SELECT setval(pg_get_serial_sequence('usuarios', 'usuario_id'), COALESCE(MAX(usuario_id), 1)) FROM usuarios;


-- ==> users (autenticacion Laravel)
INSERT INTO users (id, role_id, name, email, email_verified_at, password, remember_token, estado, created_at, updated_at) VALUES
('1', '1', 'Administrador', 'admin@ecg.com', NULL, '$2y$12$lUwCBDXMyd6Aivw0OEwwe.9ZCIWAMwDs2.pmS33iJCeNj0B6/B2Nm', NULL, TRUE, '2026-04-13 21:44:10', '2026-04-13 21:44:10'),
('2', '2', 'Jose Altamirano', 'jose@ecg.com', NULL, '$2y$12$3RvYW7l8c4xc0GqPeiYgNed2NED5PFWjGgDlE1Hg7mMykHuu6eZn2', NULL, TRUE, '2026-05-21 13:53:49', '2026-05-21 13:53:49'),
('3', '3', 'Fermin', 'fermin@ecg.com', NULL, '$2y$12$C0fOY8pyUr/G.ynN8T6UL.Tl2Kk6YBEqgnV2Dc/yWMc7PycCM2lWG', NULL, TRUE, '2026-05-21 14:21:50', '2026-05-21 14:21:50');
SELECT setval(pg_get_serial_sequence('users', 'id'), COALESCE(MAX(id), 1)) FROM users;


-- ==> grupo_cardiacos
INSERT INTO grupo_cardiacos (grupo_id, nombre, descripcion, estado, created_at, updated_at) VALUES
('1', 'Sinusal', 'Ritmos de origen sinusal', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('2', 'Conduccion', 'Alteraciones de conduccion cardiaca', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('3', 'Ectopias', 'Complejos prematuros o patrones ectopicos', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('4', 'Supraventricular', 'Arritmias de origen supraventricular', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27');
SELECT setval(pg_get_serial_sequence('grupo_cardiacos', 'grupo_id'), COALESCE(MAX(grupo_id), 1)) FROM grupo_cardiacos;


-- ==> nivel_gravedades
INSERT INTO nivel_gravedades (nivel_id, nombre, estado, created_at, updated_at) VALUES
('1', 'Baja', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('2', 'Moderada', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('3', 'Alta', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27');
SELECT setval(pg_get_serial_sequence('nivel_gravedades', 'nivel_id'), COALESCE(MAX(nivel_id), 1)) FROM nivel_gravedades;


-- ==> clasificacion_arritmias
INSERT INTO clasificacion_arritmias (clasificacion_id, nombre, estado, created_at, updated_at) VALUES
('1', 'Normal', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('2', 'Arritmia', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27');
SELECT setval(pg_get_serial_sequence('clasificacion_arritmias', 'clasificacion_id'), COALESCE(MAX(clasificacion_id), 1)) FROM clasificacion_arritmias;


-- ==> ritmo_cardiacos
INSERT INTO ritmo_cardiacos (ritmo_id, grupo_id, nivel_id, clasificacion_id, label, nombre, descripcion, estado, created_at, updated_at) VALUES
('1', '1', '1', '1', 'NORM', 'Ritmo Sinusal Normal', 'ECG dentro de limites normales.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('2', '2', '2', '2', '1AVB', 'Bloqueo AV de primer grado', 'Retraso de conduccion auriculoventricular.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('3', '2', '2', '2', 'WPW', 'Sindrome de Wolff-Parkinson-White', 'Patron de preexcitacion ventricular.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('4', '3', '2', '2', 'PVC', 'Complejo ventricular prematuro', 'Latido ventricular ectopico prematuro.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('5', '3', '2', '2', 'PAC', 'Complejo auricular prematuro', 'Latido auricular ectopico prematuro.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('6', '4', '3', '2', 'AFIB', 'Fibrilacion Auricular', 'Ritmo auricular irregular compatible con fibrilacion.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('7', '1', '2', '2', 'STACH', 'Taquicardia Sinusal', 'Frecuencia sinusal elevada.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('8', '1', '1', '1', 'SARRH', 'Arritmia Sinusal', 'Variabilidad fisiologica del ritmo sinusal.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('9', '1', '1', '1', 'SBRAD', 'Bradicardia Sinusal', 'Frecuencia sinusal disminuida.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('10', '4', '2', '2', 'SVARR', 'Arritmia Supraventricular', 'Alteracion del ritmo de origen supraventricular.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('11', '3', '2', '2', 'BIGU', 'Bigeminismo', 'Patron bigeminal de origen supraventricular o ventricular.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('12', '4', '3', '2', 'AFLT', 'Flutter Auricular', 'Ritmo auricular compatible con flutter.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27'),
('13', '4', '2', '2', 'PSVT', 'Taquicardia supraventricular paroxistica', 'Taquicardia supraventricular de inicio paroxistico.', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27');
SELECT setval(pg_get_serial_sequence('ritmo_cardiacos', 'ritmo_id'), COALESCE(MAX(ritmo_id), 1)) FROM ritmo_cardiacos;


-- ==> codigo_pacientes
INSERT INTO codigo_pacientes (codigo_id, nombre, descripcion, estado, created_at, updated_at) VALUES
('1', 'PACIENTE', 'Codigo generado por el sistema', TRUE, '2026-05-04 15:46:27', '2026-05-04 15:46:27');
SELECT setval(pg_get_serial_sequence('codigo_pacientes', 'codigo_id'), COALESCE(MAX(codigo_id), 1)) FROM codigo_pacientes;


-- ==> pacientes
INSERT INTO pacientes (paciente_id, codigo_id, codigo_generado, fecha_nacimiento, edad, sexo, peso, usuario_id, estado, created_at, updated_at) VALUES
('1', '1', 'PACIENTE_20260406_001', NULL, '58', 'M', '78.00', '1', TRUE, '2026-04-06 08:10:00', '2026-04-06 08:10:00'),
('2', '1', 'PACIENTE_20260406_002', NULL, '55', 'F', '67.00', '1', TRUE, '2026-04-06 09:25:00', '2026-04-06 09:25:00'),
('3', '1', 'PACIENTE_20260406_003', NULL, '42', 'M', '81.00', '1', TRUE, '2026-04-06 10:40:00', '2026-04-06 10:40:00'),
('4', '1', 'PACIENTE_20260407_001', NULL, '61', 'F', '65.00', '1', TRUE, '2026-04-07 08:05:00', '2026-04-07 08:05:00'),
('5', '1', 'PACIENTE_20260407_002', NULL, '47', 'M', '84.00', '1', TRUE, '2026-04-07 09:20:00', '2026-04-07 09:20:00'),
('6', '1', 'PACIENTE_20260407_003', NULL, '52', 'F', '70.00', '1', TRUE, '2026-04-07 10:35:00', '2026-04-07 10:35:00'),
('7', '1', 'PACIENTE_20260407_004', NULL, '64', 'M', '76.00', '1', TRUE, '2026-04-07 11:50:00', '2026-04-07 11:50:00'),
('8', '1', 'PACIENTE_20260408_001', NULL, '59', 'M', '79.00', '1', TRUE, '2026-04-08 08:15:00', '2026-04-08 08:15:00'),
('9', '1', 'PACIENTE_20260408_002', NULL, '37', 'F', '58.00', '1', TRUE, '2026-04-08 09:30:00', '2026-04-08 09:30:00'),
('10', '1', 'PACIENTE_20260409_001', NULL, '66', 'M', '82.00', '1', TRUE, '2026-04-09 08:00:00', '2026-04-09 08:00:00'),
('11', '1', 'PACIENTE_20260409_002', NULL, '54', 'F', '63.00', '1', TRUE, '2026-04-09 09:15:00', '2026-04-09 09:15:00'),
('12', '1', 'PACIENTE_20260409_003', NULL, '45', 'M', '84.00', '1', TRUE, '2026-04-09 10:30:00', '2026-04-09 10:30:00'),
('13', '1', 'PACIENTE_20260410_001', NULL, '62', 'F', '69.00', '1', TRUE, '2026-04-10 08:20:00', '2026-04-10 08:20:00'),
('14', '1', 'PACIENTE_20260410_002', NULL, '34', 'M', '77.00', '1', TRUE, '2026-04-10 09:35:00', '2026-04-10 09:35:00'),
('15', '1', 'PACIENTE_20260410_003', NULL, '47', 'F', '86.00', '1', TRUE, '2026-04-10 10:50:00', '2026-04-10 10:50:00'),
('16', '1', 'PACIENTE_20260413_001', NULL, '69', 'M', '64.00', '1', TRUE, '2026-04-13 08:10:00', '2026-04-13 08:10:00'),
('17', '1', 'PACIENTE_20260413_002', NULL, '52', 'F', '79.00', '1', TRUE, '2026-04-13 09:25:00', '2026-04-13 09:25:00'),
('18', '1', 'PACIENTE_20260414_001', NULL, '31', 'M', '55.00', '1', TRUE, '2026-04-14 08:05:00', '2026-04-14 08:05:00'),
('19', '1', 'PACIENTE_20260414_002', NULL, '44', 'F', '83.00', '1', TRUE, '2026-04-14 09:20:00', '2026-04-14 09:20:00'),
('20', '1', 'PACIENTE_20260414_003', NULL, '58', 'M', '78.00', '1', TRUE, '2026-04-14 10:35:00', '2026-04-14 10:35:00'),
('21', '1', 'PACIENTE_20260414_004', NULL, '42', 'F', '61.00', '1', TRUE, '2026-04-14 11:50:00', '2026-04-14 11:50:00'),
('22', '1', 'PACIENTE_20260415_001', NULL, '49', 'M', '82.00', '1', TRUE, '2026-04-15 08:15:00', '2026-04-15 08:15:00'),
('23', '1', 'PACIENTE_20260415_002', NULL, '64', 'F', '66.00', '1', TRUE, '2026-04-15 09:30:00', '2026-04-15 09:30:00'),
('24', '1', 'PACIENTE_20260415_003', NULL, '55', 'M', '80.00', '1', TRUE, '2026-04-15 10:45:00', '2026-04-15 10:45:00'),
('25', '1', 'PACIENTE_20260416_001', NULL, '68', 'F', '70.00', '1', TRUE, '2026-04-16 08:00:00', '2026-04-16 08:00:00'),
('26', '1', 'PACIENTE_20260416_002', NULL, '37', 'M', '76.00', '1', TRUE, '2026-04-16 09:15:00', '2026-04-16 09:15:00'),
('27', '1', 'PACIENTE_20260416_003', NULL, '61', 'F', '65.00', '1', TRUE, '2026-04-16 10:30:00', '2026-04-16 10:30:00'),
('28', '1', 'PACIENTE_20260417_001', NULL, '44', 'M', '84.00', '1', TRUE, '2026-04-17 08:20:00', '2026-04-17 08:20:00'),
('29', '1', 'PACIENTE_20260417_002', NULL, '52', 'F', '59.00', '1', TRUE, '2026-04-17 09:35:00', '2026-04-17 09:35:00'),
('30', '1', 'PACIENTE_20260417_003', NULL, '57', 'M', '79.00', '1', TRUE, '2026-04-17 10:50:00', '2026-04-17 10:50:00'),
('31', '1', 'PACIENTE_20260420_001', NULL, '63', 'F', '68.00', '1', TRUE, '2026-04-20 08:05:00', '2026-04-20 08:05:00'),
('32', '1', 'PACIENTE_20260420_002', NULL, '39', 'M', '81.00', '1', TRUE, '2026-04-20 09:20:00', '2026-04-20 09:20:00'),
('33', '1', 'PACIENTE_20260420_003', NULL, '60', 'F', '67.00', '1', TRUE, '2026-04-20 10:35:00', '2026-04-20 10:35:00'),
('34', '1', 'PACIENTE_20260420_004', NULL, '47', 'M', '85.00', '1', TRUE, '2026-04-20 11:50:00', '2026-04-20 11:50:00'),
('35', '1', 'PACIENTE_20260421_001', NULL, '35', 'F', '58.00', '1', TRUE, '2026-04-21 08:15:00', '2026-04-21 08:15:00'),
('36', '1', 'PACIENTE_20260421_002', NULL, '53', 'M', '77.00', '1', TRUE, '2026-04-21 09:30:00', '2026-04-21 09:30:00'),
('37', '1', 'PACIENTE_20260422_001', NULL, '58', 'M', '78.00', '1', TRUE, '2026-04-22 08:00:00', '2026-04-22 08:00:00'),
('38', '1', 'PACIENTE_20260422_002', NULL, '55', 'F', '80.00', '1', TRUE, '2026-04-22 09:15:00', '2026-04-22 09:15:00'),
('39', '1', 'PACIENTE_20260422_003', NULL, '68', 'F', '70.00', '1', TRUE, '2026-04-22 10:30:00', '2026-04-22 10:30:00'),
('40', '1', 'PACIENTE_20260423_001', NULL, '37', 'M', '76.00', '1', TRUE, '2026-04-23 08:20:00', '2026-04-23 08:20:00'),
('41', '1', 'PACIENTE_20260423_002', NULL, '61', 'F', '65.00', '1', TRUE, '2026-04-23 09:35:00', '2026-04-23 09:35:00'),
('42', '1', 'PACIENTE_20260423_003', NULL, '44', 'M', '84.00', '1', TRUE, '2026-04-23 10:50:00', '2026-04-23 10:50:00'),
('43', '1', 'PACIENTE_20260424_001', NULL, '52', 'F', '59.00', '1', TRUE, '2026-04-24 08:05:00', '2026-04-24 08:05:00'),
('44', '1', 'PACIENTE_20260424_002', NULL, '57', 'M', '79.00', '1', TRUE, '2026-04-24 09:20:00', '2026-04-24 09:20:00'),
('45', '1', 'PACIENTE_20260424_003', NULL, '63', 'F', '68.00', '1', TRUE, '2026-04-24 10:35:00', '2026-04-24 10:35:00'),
('46', '1', 'PACIENTE_20260427_001', NULL, '39', 'M', '81.00', '1', TRUE, '2026-04-27 08:15:00', '2026-04-27 08:15:00'),
('47', '1', 'PACIENTE_20260427_002', NULL, '60', 'F', '67.00', '1', TRUE, '2026-04-27 09:30:00', '2026-04-27 09:30:00'),
('48', '1', 'PACIENTE_20260427_003', NULL, '47', 'M', '85.00', '1', TRUE, '2026-04-27 10:45:00', '2026-04-27 10:45:00'),
('49', '1', 'PACIENTE_20260427_004', NULL, '35', 'F', '58.00', '1', TRUE, '2026-04-27 12:00:00', '2026-04-27 12:00:00'),
('50', '1', 'PACIENTE_20260428_001', NULL, '53', 'M', '77.00', '1', TRUE, '2026-04-28 08:00:00', '2026-04-28 08:00:00'),
('51', '1', 'PACIENTE_20260428_002', NULL, '58', 'M', '78.00', '1', TRUE, '2026-04-28 09:15:00', '2026-04-28 09:15:00'),
('52', '1', 'PACIENTE_20260428_003', NULL, '55', 'F', '80.00', '1', TRUE, '2026-04-28 10:30:00', '2026-04-28 10:30:00'),
('53', '1', 'PACIENTE_20260429_001', NULL, '68', 'F', '70.00', '1', TRUE, '2026-04-29 08:20:00', '2026-04-29 08:20:00'),
('54', '1', 'PACIENTE_20260429_002', NULL, '37', 'M', '76.00', '1', TRUE, '2026-04-29 09:35:00', '2026-04-29 09:35:00'),
('55', '1', 'PACIENTE_20260429_003', NULL, '61', 'F', '65.00', '1', TRUE, '2026-04-29 10:50:00', '2026-04-29 10:50:00'),
('56', '1', 'PACIENTE_20260430_001', NULL, '44', 'M', '84.00', '1', TRUE, '2026-04-30 08:05:00', '2026-04-30 08:05:00'),
('57', '1', 'PACIENTE_20260430_002', NULL, '52', 'F', '59.00', '1', TRUE, '2026-04-30 09:20:00', '2026-04-30 09:20:00'),
('58', '1', 'PACIENTE_20260504_001', NULL, '57', 'M', '79.00', '1', TRUE, '2026-05-04 08:15:00', '2026-05-04 08:15:00'),
('59', '1', 'PACIENTE_20260504_002', NULL, '63', 'F', '68.00', '1', TRUE, '2026-05-04 09:30:00', '2026-05-04 09:30:00'),
('60', '1', 'PACIENTE_20260504_003', NULL, '39', 'M', '81.00', '1', TRUE, '2026-05-04 10:45:00', '2026-05-04 10:45:00'),
('61', '1', 'PACIENTE_20260504_004', NULL, '60', 'F', '67.00', '1', TRUE, '2026-05-04 12:00:00', '2026-05-04 12:00:00'),
('62', '1', 'PACIENTE_20260505_001', NULL, '47', 'M', '85.00', '1', TRUE, '2026-05-05 08:00:00', '2026-05-05 08:00:00'),
('63', '1', 'PACIENTE_20260505_002', NULL, '35', 'F', '58.00', '1', TRUE, '2026-05-05 09:15:00', '2026-05-05 09:15:00');
SELECT setval(pg_get_serial_sequence('pacientes', 'paciente_id'), COALESCE(MAX(paciente_id), 1)) FROM pacientes;


-- ==> estudios
INSERT INTO estudios (estudio_id, paciente_id, usuario_id, legacy_analysis_id, observaciones, estado, created_at, updated_at) VALUES
('1', '1', '1', '1', 'Migrado desde ecg_analyses. Archivo: 20260406-834219.pdf', TRUE, '2026-04-06 08:10:00', '2026-04-06 08:10:00'),
('2', '2', '1', '2', 'Migrado desde ecg_analyses. Archivo: 20260406-157604.pdf', TRUE, '2026-04-06 09:25:00', '2026-04-06 09:25:00'),
('3', '3', '1', '3', 'Migrado desde ecg_analyses. Archivo: 20260406-692381.pdf', TRUE, '2026-04-06 10:40:00', '2026-04-06 10:40:00'),
('4', '4', '1', '4', 'Migrado desde ecg_analyses. Archivo: 20260407-420918.pdf', TRUE, '2026-04-07 08:05:00', '2026-04-07 08:05:00'),
('5', '5', '1', '5', 'Migrado desde ecg_analyses. Archivo: 20260407-783052.pdf', TRUE, '2026-04-07 09:20:00', '2026-04-07 09:20:00'),
('6', '6', '1', '6', 'Migrado desde ecg_analyses. Archivo: 20260407-269447.pdf', TRUE, '2026-04-07 10:35:00', '2026-04-07 10:35:00'),
('7', '7', '1', '7', 'Migrado desde ecg_analyses. Archivo: 20260407-915630.pdf', TRUE, '2026-04-07 11:50:00', '2026-04-07 11:50:00'),
('8', '8', '1', '8', 'Migrado desde ecg_analyses. Archivo: 20260408-508274.pdf', TRUE, '2026-04-08 08:15:00', '2026-04-08 08:15:00'),
('9', '9', '1', '9', 'Migrado desde ecg_analyses. Archivo: 20260408-731965.pdf', TRUE, '2026-04-08 09:30:00', '2026-04-08 09:30:00'),
('10', '10', '1', '10', 'Migrado desde ecg_analyses. Archivo: 20260409-184603.pdf', TRUE, '2026-04-09 08:00:00', '2026-04-09 08:00:00'),
('11', '11', '1', '11', 'Migrado desde ecg_analyses. Archivo: 20260409-647290.pdf', TRUE, '2026-04-09 09:15:00', '2026-04-09 09:15:00'),
('12', '12', '1', '12', 'Migrado desde ecg_analyses. Archivo: 20260409-392815.pdf', TRUE, '2026-04-09 10:30:00', '2026-04-09 10:30:00'),
('13', '13', '1', '13', 'Migrado desde ecg_analyses. Archivo: 20260410-859104.pdf', TRUE, '2026-04-10 08:20:00', '2026-04-10 08:20:00'),
('14', '14', '1', '14', 'Migrado desde ecg_analyses. Archivo: 20260410-206738.pdf', TRUE, '2026-04-10 09:35:00', '2026-04-10 09:35:00'),
('15', '15', '1', '15', 'Migrado desde ecg_analyses. Archivo: 20260410-574921.pdf', TRUE, '2026-04-10 10:50:00', '2026-04-10 10:50:00'),
('16', '16', '1', '16', 'Migrado desde ecg_analyses. Archivo: 20260413-720381.pdf', TRUE, '2026-04-13 08:10:00', '2026-04-13 08:10:00'),
('17', '17', '1', '17', 'Migrado desde ecg_analyses. Archivo: 20260413-262026.pdf', TRUE, '2026-04-13 09:25:00', '2026-04-13 09:25:00'),
('18', '18', '1', '18', 'Migrado desde ecg_analyses. Archivo: 20260414-296552.pdf', TRUE, '2026-04-14 08:05:00', '2026-04-14 08:05:00'),
('19', '19', '1', '19', 'Migrado desde ecg_analyses. Archivo: 20260414-528797.pdf', TRUE, '2026-04-14 09:20:00', '2026-04-14 09:20:00'),
('20', '20', '1', '20', 'Migrado desde ecg_analyses. Archivo: 20260414-841306.pdf', TRUE, '2026-04-14 10:35:00', '2026-04-14 10:35:00'),
('21', '21', '1', '21', 'Migrado desde ecg_analyses. Archivo: 20260414-973510.pdf', TRUE, '2026-04-14 11:50:00', '2026-04-14 11:50:00'),
('22', '22', '1', '22', 'Migrado desde ecg_analyses. Archivo: 20260415-239487.pdf', TRUE, '2026-04-15 08:15:00', '2026-04-15 08:15:00'),
('23', '23', '1', '23', 'Migrado desde ecg_analyses. Archivo: 20260415-973710.pdf', TRUE, '2026-04-15 09:30:00', '2026-04-15 09:30:00'),
('24', '24', '1', '24', 'Migrado desde ecg_analyses. Archivo: 20260415-675813.pdf', TRUE, '2026-04-15 10:45:00', '2026-04-15 10:45:00'),
('25', '25', '1', '25', 'Migrado desde ecg_analyses. Archivo: 20260416-498501.pdf', TRUE, '2026-04-16 08:00:00', '2026-04-16 08:00:00'),
('26', '26', '1', '26', 'Migrado desde ecg_analyses. Archivo: 20260416-149807.pdf', TRUE, '2026-04-16 09:15:00', '2026-04-16 09:15:00'),
('27', '27', '1', '27', 'Migrado desde ecg_analyses. Archivo: 20260416-595321.pdf', TRUE, '2026-04-16 10:30:00', '2026-04-16 10:30:00'),
('28', '28', '1', '28', 'Migrado desde ecg_analyses. Archivo: 20260417-288592.pdf', TRUE, '2026-04-17 08:20:00', '2026-04-17 08:20:00'),
('29', '29', '1', '29', 'Migrado desde ecg_analyses. Archivo: 20260417-957298.pdf', TRUE, '2026-04-17 09:35:00', '2026-04-17 09:35:00'),
('30', '30', '1', '30', 'Migrado desde ecg_analyses. Archivo: 20260417-180834.pdf', TRUE, '2026-04-17 10:50:00', '2026-04-17 10:50:00'),
('31', '31', '1', '31', 'Migrado desde ecg_analyses. Archivo: 20260420-620001.pdf', TRUE, '2026-04-20 08:05:00', '2026-04-20 08:05:00'),
('32', '32', '1', '32', 'Migrado desde ecg_analyses. Archivo: 20260420-620002.pdf', TRUE, '2026-04-20 09:20:00', '2026-04-20 09:20:00'),
('33', '33', '1', '33', 'Migrado desde ecg_analyses. Archivo: 20260420-620003.pdf', TRUE, '2026-04-20 10:35:00', '2026-04-20 10:35:00'),
('34', '34', '1', '34', 'Migrado desde ecg_analyses. Archivo: 20260420-620004.pdf', TRUE, '2026-04-20 11:50:00', '2026-04-20 11:50:00'),
('35', '35', '1', '35', 'Migrado desde ecg_analyses. Archivo: 20260421-621381.pdf', TRUE, '2026-04-21 08:15:00', '2026-04-21 08:15:00'),
('36', '36', '1', '36', 'Migrado desde ecg_analyses. Archivo: 20260421-846027.pdf', TRUE, '2026-04-21 09:30:00', '2026-04-21 09:30:00'),
('37', '37', '1', '37', 'Migrado desde ecg_analyses. Archivo: 20260422-622731.pdf', TRUE, '2026-04-22 08:00:00', '2026-04-22 08:00:00'),
('38', '38', '1', '38', 'Migrado desde ecg_analyses. Archivo: 20260422-194608.pdf', TRUE, '2026-04-22 09:15:00', '2026-04-22 09:15:00'),
('39', '39', '1', '39', 'Migrado desde ecg_analyses. Archivo: 20260422-753042.pdf', TRUE, '2026-04-22 10:30:00', '2026-04-22 10:30:00'),
('40', '40', '1', '40', 'Migrado desde ecg_analyses. Archivo: 20260423-623915.pdf', TRUE, '2026-04-23 08:20:00', '2026-04-23 08:20:00'),
('41', '41', '1', '41', 'Migrado desde ecg_analyses. Archivo: 20260423-407286.pdf', TRUE, '2026-04-23 09:35:00', '2026-04-23 09:35:00'),
('42', '42', '1', '42', 'Migrado desde ecg_analyses. Archivo: 20260423-865134.pdf', TRUE, '2026-04-23 10:50:00', '2026-04-23 10:50:00'),
('43', '43', '1', '43', 'Migrado desde ecg_analyses. Archivo: 20260424-624508.pdf', TRUE, '2026-04-24 08:05:00', '2026-04-24 08:05:00'),
('44', '44', '1', '44', 'Migrado desde ecg_analyses. Archivo: 20260424-218763.pdf', TRUE, '2026-04-24 09:20:00', '2026-04-24 09:20:00'),
('45', '45', '1', '45', 'Migrado desde ecg_analyses. Archivo: 20260424-790341.pdf', TRUE, '2026-04-24 10:35:00', '2026-04-24 10:35:00'),
('46', '46', '1', '46', 'Migrado desde ecg_analyses. Archivo: 20260427-627194.pdf', TRUE, '2026-04-27 08:15:00', '2026-04-27 08:15:00'),
('47', '47', '1', '47', 'Migrado desde ecg_analyses. Archivo: 20260427-482650.pdf', TRUE, '2026-04-27 09:30:00', '2026-04-27 09:30:00'),
('48', '48', '1', '48', 'Migrado desde ecg_analyses. Archivo: 20260427-936815.pdf', TRUE, '2026-04-27 10:45:00', '2026-04-27 10:45:00'),
('49', '49', '1', '49', 'Migrado desde ecg_analyses. Archivo: 20260427-305742.pdf', TRUE, '2026-04-27 12:00:00', '2026-04-27 12:00:00'),
('50', '50', '1', '50', 'Migrado desde ecg_analyses. Archivo: 20260428-628409.pdf', TRUE, '2026-04-28 08:00:00', '2026-04-28 08:00:00'),
('51', '51', '1', '51', 'Migrado desde ecg_analyses. Archivo: 20260428-174936.pdf', TRUE, '2026-04-28 09:15:00', '2026-04-28 09:15:00'),
('52', '52', '1', '52', 'Migrado desde ecg_analyses. Archivo: 20260428-852617.pdf', TRUE, '2026-04-28 10:30:00', '2026-04-28 10:30:00'),
('53', '53', '1', '53', 'Migrado desde ecg_analyses. Archivo: 20260429-629284.pdf', TRUE, '2026-04-29 08:20:00', '2026-04-29 08:20:00'),
('54', '54', '1', '54', 'Migrado desde ecg_analyses. Archivo: 20260429-416950.pdf', TRUE, '2026-04-29 09:35:00', '2026-04-29 09:35:00'),
('55', '55', '1', '55', 'Migrado desde ecg_analyses. Archivo: 20260429-783621.pdf', TRUE, '2026-04-29 10:50:00', '2026-04-29 10:50:00'),
('56', '56', '1', '56', 'Migrado desde ecg_analyses. Archivo: 20260430-630472.pdf', TRUE, '2026-04-30 08:05:00', '2026-04-30 08:05:00'),
('57', '57', '1', '57', 'Migrado desde ecg_analyses. Archivo: 20260430-205819.pdf', TRUE, '2026-04-30 09:20:00', '2026-04-30 09:20:00'),
('58', '58', '1', '58', 'Migrado desde ecg_analyses. Archivo: 20260504-471638.pdf', TRUE, '2026-05-04 08:15:00', '2026-05-04 08:15:00'),
('59', '59', '1', '59', 'Migrado desde ecg_analyses. Archivo: 20260504-839205.pdf', TRUE, '2026-05-04 09:30:00', '2026-05-04 09:30:00'),
('60', '60', '1', '60', 'Migrado desde ecg_analyses. Archivo: 20260504-126794.pdf', TRUE, '2026-05-04 10:45:00', '2026-05-04 10:45:00'),
('61', '61', '1', '61', 'Migrado desde ecg_analyses. Archivo: 20260504-592481.pdf', TRUE, '2026-05-04 12:00:00', '2026-05-04 12:00:00'),
('62', '62', '1', '62', 'Migrado desde ecg_analyses. Archivo: 20260505-384729.pdf', TRUE, '2026-05-05 08:00:00', '2026-05-05 08:00:00'),
('63', '63', '1', '63', 'Migrado desde ecg_analyses. Archivo: 20260505-917506.pdf', TRUE, '2026-05-05 09:15:00', '2026-05-05 09:15:00');
SELECT setval(pg_get_serial_sequence('estudios', 'estudio_id'), COALESCE(MAX(estudio_id), 1)) FROM estudios;


-- ==> imagenes
INSERT INTO imagenes (imagen_id, estudio_id, ruta, formato, resolucion, tamano_kb, hash, estado, created_at, updated_at) VALUES
('1', '1', '20260406-834219.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-06 08:10:00', '2026-04-06 08:10:00'),
('2', '2', '20260406-157604.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-06 09:25:00', '2026-04-06 09:25:00'),
('3', '3', '20260406-692381.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-06 10:40:00', '2026-04-06 10:40:00'),
('4', '4', '20260407-420918.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-07 08:05:00', '2026-04-07 08:05:00'),
('5', '5', '20260407-783052.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-07 09:20:00', '2026-04-07 09:20:00'),
('6', '6', '20260407-269447.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-07 10:35:00', '2026-04-07 10:35:00'),
('7', '7', '20260407-915630.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-07 11:50:00', '2026-04-07 11:50:00'),
('8', '8', '20260408-508274.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-08 08:15:00', '2026-04-08 08:15:00'),
('9', '9', '20260408-731965.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-08 09:30:00', '2026-04-08 09:30:00'),
('10', '10', '20260409-184603.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-09 08:00:00', '2026-04-09 08:00:00'),
('11', '11', '20260409-647290.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-09 09:15:00', '2026-04-09 09:15:00'),
('12', '12', '20260409-392815.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-09 10:30:00', '2026-04-09 10:30:00'),
('13', '13', '20260410-859104.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-10 08:20:00', '2026-04-10 08:20:00'),
('14', '14', '20260410-206738.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-10 09:35:00', '2026-04-10 09:35:00'),
('15', '15', '20260410-574921.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-10 10:50:00', '2026-04-10 10:50:00'),
('16', '16', '20260413-720381.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-13 08:10:00', '2026-04-13 08:10:00'),
('17', '17', '20260413-262026.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-13 09:25:00', '2026-04-13 09:25:00'),
('18', '18', '20260414-296552.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-14 08:05:00', '2026-04-14 08:05:00'),
('19', '19', '20260414-528797.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-14 09:20:00', '2026-04-14 09:20:00'),
('20', '20', '20260414-841306.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-14 10:35:00', '2026-04-14 10:35:00'),
('21', '21', '20260414-973510.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-14 11:50:00', '2026-04-14 11:50:00'),
('22', '22', '20260415-239487.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-15 08:15:00', '2026-04-15 08:15:00'),
('23', '23', '20260415-973710.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-15 09:30:00', '2026-04-15 09:30:00'),
('24', '24', '20260415-675813.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-15 10:45:00', '2026-04-15 10:45:00'),
('25', '25', '20260416-498501.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-16 08:00:00', '2026-04-16 08:00:00'),
('26', '26', '20260416-149807.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-16 09:15:00', '2026-04-16 09:15:00'),
('27', '27', '20260416-595321.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-16 10:30:00', '2026-04-16 10:30:00'),
('28', '28', '20260417-288592.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-17 08:20:00', '2026-04-17 08:20:00'),
('29', '29', '20260417-957298.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-17 09:35:00', '2026-04-17 09:35:00'),
('30', '30', '20260417-180834.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-17 10:50:00', '2026-04-17 10:50:00'),
('31', '31', '20260420-620001.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-20 08:05:00', '2026-04-20 08:05:00'),
('32', '32', '20260420-620002.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-20 09:20:00', '2026-04-20 09:20:00'),
('33', '33', '20260420-620003.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-20 10:35:00', '2026-04-20 10:35:00'),
('34', '34', '20260420-620004.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-20 11:50:00', '2026-04-20 11:50:00'),
('35', '35', '20260421-621381.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-21 08:15:00', '2026-04-21 08:15:00'),
('36', '36', '20260421-846027.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-21 09:30:00', '2026-04-21 09:30:00'),
('37', '37', '20260422-622731.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-22 08:00:00', '2026-04-22 08:00:00'),
('38', '38', '20260422-194608.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-22 09:15:00', '2026-04-22 09:15:00'),
('39', '39', '20260422-753042.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-22 10:30:00', '2026-04-22 10:30:00'),
('40', '40', '20260423-623915.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-23 08:20:00', '2026-04-23 08:20:00'),
('41', '41', '20260423-407286.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-23 09:35:00', '2026-04-23 09:35:00'),
('42', '42', '20260423-865134.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-23 10:50:00', '2026-04-23 10:50:00'),
('43', '43', '20260424-624508.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-24 08:05:00', '2026-04-24 08:05:00'),
('44', '44', '20260424-218763.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-24 09:20:00', '2026-04-24 09:20:00'),
('45', '45', '20260424-790341.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-24 10:35:00', '2026-04-24 10:35:00'),
('46', '46', '20260427-627194.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-27 08:15:00', '2026-04-27 08:15:00'),
('47', '47', '20260427-482650.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-27 09:30:00', '2026-04-27 09:30:00'),
('48', '48', '20260427-936815.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-27 10:45:00', '2026-04-27 10:45:00'),
('49', '49', '20260427-305742.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-27 12:00:00', '2026-04-27 12:00:00'),
('50', '50', '20260428-628409.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-28 08:00:00', '2026-04-28 08:00:00'),
('51', '51', '20260428-174936.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-28 09:15:00', '2026-04-28 09:15:00'),
('52', '52', '20260428-852617.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-28 10:30:00', '2026-04-28 10:30:00'),
('53', '53', '20260429-629284.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-29 08:20:00', '2026-04-29 08:20:00'),
('54', '54', '20260429-416950.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-29 09:35:00', '2026-04-29 09:35:00'),
('55', '55', '20260429-783621.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-29 10:50:00', '2026-04-29 10:50:00'),
('56', '56', '20260430-630472.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-30 08:05:00', '2026-04-30 08:05:00'),
('57', '57', '20260430-205819.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-04-30 09:20:00', '2026-04-30 09:20:00'),
('58', '58', '20260504-471638.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-05-04 08:15:00', '2026-05-04 08:15:00'),
('59', '59', '20260504-839205.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-05-04 09:30:00', '2026-05-04 09:30:00'),
('60', '60', '20260504-126794.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-05-04 10:45:00', '2026-05-04 10:45:00'),
('61', '61', '20260504-592481.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-05-04 12:00:00', '2026-05-04 12:00:00'),
('62', '62', '20260505-384729.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-05-05 08:00:00', '2026-05-05 08:00:00'),
('63', '63', '20260505-917506.pdf', 'pdf', NULL, NULL, NULL, TRUE, '2026-05-05 09:15:00', '2026-05-05 09:15:00');
SELECT setval(pg_get_serial_sequence('imagenes', 'imagen_id'), COALESCE(MAX(imagen_id), 1)) FROM imagenes;


-- ==> predicciones
INSERT INTO predicciones (prediccion_id, imagen_id, ritmo_id, probabilidad, tiempo_ms, top_predicciones, label_detectado, label_code, tipo, estado, created_at, updated_at) VALUES
('1', '1', '6', '0.9240', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 92.40}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 7.60}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-04-06 08:10:00', '2026-04-06 08:10:00'),
('2', '2', '7', '0.9080', NULL, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 90.80}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.20}]', 'Taquicardia Sinusal', 'STACH', 'arritmia', TRUE, '2026-04-06 09:25:00', '2026-04-06 09:25:00'),
('3', '3', '1', '0.9310', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.10}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.90}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-06 10:40:00', '2026-04-06 10:40:00'),
('4', '4', '6', '0.8870', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 88.70}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 11.30}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-04-07 08:05:00', '2026-04-07 08:05:00'),
('5', '5', '1', '0.9160', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.40}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-07 09:20:00', '2026-04-07 09:20:00'),
('6', '6', '1', '0.9420', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 94.20}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 5.80}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-07 10:35:00', '2026-04-07 10:35:00'),
('7', '7', '1', '0.8090', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 80.90}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 19.10}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-07 11:50:00', '2026-04-07 11:50:00'),
('8', '8', '12', '0.8950', NULL, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 89.50}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.50}]', 'Flutter Auricular', 'AFLT', 'arritmia', TRUE, '2026-04-08 08:15:00', '2026-04-08 08:15:00'),
('9', '9', '1', '0.9270', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.70}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.30}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-08 09:30:00', '2026-04-08 09:30:00'),
('10', '10', '6', '0.9480', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.80}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.20}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-04-09 08:00:00', '2026-04-09 08:00:00'),
('11', '11', '7', '0.9120', NULL, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 91.20}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 8.80}]', 'Taquicardia Sinusal', 'STACH', 'arritmia', TRUE, '2026-04-09 09:15:00', '2026-04-09 09:15:00'),
('12', '12', '1', '0.9040', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.40}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.60}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-09 10:30:00', '2026-04-09 10:30:00'),
('13', '13', '12', '0.8890', NULL, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 88.90}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 11.10}]', 'Flutter Auricular', 'AFLT', 'arritmia', TRUE, '2026-04-10 08:20:00', '2026-04-10 08:20:00'),
('14', '14', '1', '0.9360', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.40}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-10 09:35:00', '2026-04-10 09:35:00'),
('15', '15', '1', '0.9190', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.90}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.10}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-10 10:50:00', '2026-04-10 10:50:00'),
('16', '16', '6', '0.9410', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.10}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.90}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-04-13 08:10:00', '2026-04-13 08:10:00'),
('17', '17', '1', '0.9070', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.70}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.30}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-13 09:25:00', '2026-04-13 09:25:00'),
('18', '18', '7', '0.8980', NULL, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 89.80}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.20}]', 'Taquicardia Sinusal', 'STACH', 'arritmia', TRUE, '2026-04-14 08:05:00', '2026-04-14 08:05:00'),
('19', '19', '1', '0.9250', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.50}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.50}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-14 09:20:00', '2026-04-14 09:20:00'),
('20', '20', '1', '0.9140', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.40}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.60}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-14 10:35:00', '2026-04-14 10:35:00'),
('21', '21', '6', '0.8060', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 80.60}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 19.40}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-04-14 11:50:00', '2026-04-14 11:50:00'),
('22', '22', '12', '0.9090', NULL, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 90.90}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.10}]', 'Flutter Auricular', 'AFLT', 'arritmia', TRUE, '2026-04-15 08:15:00', '2026-04-15 08:15:00'),
('23', '23', '1', '0.9380', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.80}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.20}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-15 09:30:00', '2026-04-15 09:30:00'),
('24', '24', '1', '0.9210', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.10}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.90}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-15 10:45:00', '2026-04-15 10:45:00'),
('25', '25', '6', '0.9450', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.50}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.50}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-04-16 08:00:00', '2026-04-16 08:00:00'),
('26', '26', '1', '0.9100', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.00}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.00}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-16 09:15:00', '2026-04-16 09:15:00'),
('27', '27', '1', '0.8960', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 89.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 10.40}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-16 10:30:00', '2026-04-16 10:30:00'),
('28', '28', '7', '0.9030', NULL, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 90.30}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.70}]', 'Taquicardia Sinusal', 'STACH', 'arritmia', TRUE, '2026-04-17 08:20:00', '2026-04-17 08:20:00'),
('29', '29', '12', '0.9280', NULL, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 92.80}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 7.20}]', 'Flutter Auricular', 'AFLT', 'arritmia', TRUE, '2026-04-17 09:35:00', '2026-04-17 09:35:00'),
('30', '30', '1', '0.9330', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.30}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.70}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-17 10:50:00', '2026-04-17 10:50:00'),
('31', '31', '6', '0.9170', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 91.70}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 8.30}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-04-20 08:05:00', '2026-04-20 08:05:00'),
('32', '32', '1', '0.8840', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 88.40}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 11.60}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-20 09:20:00', '2026-04-20 09:20:00'),
('33', '33', '1', '0.9060', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.40}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-20 10:35:00', '2026-04-20 10:35:00'),
('34', '34', '7', '0.8030', NULL, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 80.30}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 19.70}]', 'Taquicardia Sinusal', 'STACH', 'arritmia', TRUE, '2026-04-20 11:50:00', '2026-04-20 11:50:00'),
('35', '35', '12', '0.8990', NULL, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 89.90}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.10}]', 'Flutter Auricular', 'AFLT', 'arritmia', TRUE, '2026-04-21 08:15:00', '2026-04-21 08:15:00'),
('36', '36', '1', '0.9200', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.00}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.00}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-21 09:30:00', '2026-04-21 09:30:00'),
('37', '37', '6', '0.9460', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.60}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.40}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-04-22 08:00:00', '2026-04-22 08:00:00'),
('38', '38', '1', '0.9150', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.50}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.50}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-22 09:15:00', '2026-04-22 09:15:00'),
('39', '39', '1', '0.9010', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.10}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.90}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-22 10:30:00', '2026-04-22 10:30:00'),
('40', '40', '7', '0.8920', NULL, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 89.20}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.80}]', 'Taquicardia Sinusal', 'STACH', 'arritmia', TRUE, '2026-04-23 08:20:00', '2026-04-23 08:20:00'),
('41', '41', '1', '0.9340', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.40}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.60}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-23 09:35:00', '2026-04-23 09:35:00'),
('42', '42', '1', '0.9220', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.20}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.80}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-23 10:50:00', '2026-04-23 10:50:00'),
('43', '43', '12', '0.8880', NULL, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 88.80}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 11.20}]', 'Flutter Auricular', 'AFLT', 'arritmia', TRUE, '2026-04-24 08:05:00', '2026-04-24 08:05:00'),
('44', '44', '1', '0.9180', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.80}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.20}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-24 09:20:00', '2026-04-24 09:20:00'),
('45', '45', '1', '0.9050', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.50}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.50}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-24 10:35:00', '2026-04-24 10:35:00'),
('46', '46', '6', '0.9400', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.00}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 6.00}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-04-27 08:15:00', '2026-04-27 08:15:00'),
('47', '47', '1', '0.9260', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.40}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-27 09:30:00', '2026-04-27 09:30:00'),
('48', '48', '1', '0.9110', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.10}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.90}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-27 10:45:00', '2026-04-27 10:45:00'),
('49', '49', '1', '0.8970', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 89.70}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 10.30}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-27 12:00:00', '2026-04-27 12:00:00'),
('50', '50', '7', '0.9000', NULL, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 90.00}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.00}]', 'Taquicardia Sinusal', 'STACH', 'arritmia', TRUE, '2026-04-28 08:00:00', '2026-04-28 08:00:00'),
('51', '51', '1', '0.9300', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.00}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.00}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-28 09:15:00', '2026-04-28 09:15:00'),
('52', '52', '1', '0.9230', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.30}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.70}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-28 10:30:00', '2026-04-28 10:30:00'),
('53', '53', '6', '0.9470', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.70}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.30}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-04-29 08:20:00', '2026-04-29 08:20:00'),
('54', '54', '12', '0.9130', NULL, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 91.30}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 8.70}]', 'Flutter Auricular', 'AFLT', 'arritmia', TRUE, '2026-04-29 09:35:00', '2026-04-29 09:35:00'),
('55', '55', '1', '0.8860', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 88.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 11.40}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-29 10:50:00', '2026-04-29 10:50:00'),
('56', '56', '7', '0.9020', NULL, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 90.20}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.80}]', 'Taquicardia Sinusal', 'STACH', 'arritmia', TRUE, '2026-04-30 08:05:00', '2026-04-30 08:05:00'),
('57', '57', '1', '0.9290', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.90}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.10}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-04-30 09:20:00', '2026-04-30 09:20:00'),
('58', '58', '6', '0.9440', NULL, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.40}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.60}]', 'Fibrilacion Auricular', 'AFIB', 'arritmia', TRUE, '2026-05-04 08:15:00', '2026-05-04 08:15:00'),
('59', '59', '7', '0.9160', NULL, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 91.60}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 8.40}]', 'Taquicardia Sinusal', 'STACH', 'arritmia', TRUE, '2026-05-04 09:30:00', '2026-05-04 09:30:00'),
('60', '60', '1', '0.8940', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 89.40}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 10.60}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-05-04 10:45:00', '2026-05-04 10:45:00'),
('61', '61', '1', '0.9080', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.80}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.20}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-05-04 12:00:00', '2026-05-04 12:00:00'),
('62', '62', '12', '0.9350', NULL, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 93.50}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 6.50}]', 'Flutter Auricular', 'AFLT', 'arritmia', TRUE, '2026-05-05 08:00:00', '2026-05-05 08:00:00'),
('63', '63', '1', '0.9190', NULL, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.90}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.10}]', 'Ritmo Sinusal Normal', 'NORM', 'normal', TRUE, '2026-05-05 09:15:00', '2026-05-05 09:15:00');
SELECT setval(pg_get_serial_sequence('predicciones', 'prediccion_id'), COALESCE(MAX(prediccion_id), 1)) FROM predicciones;


-- ==> diagnosticos
INSERT INTO diagnosticos (diagnostico_id, estudio_id, ritmo_id, concordancia, resultado, doctor_label, observacion, estado, reviewed_at, created_at, updated_at) VALUES
('1', '1', '6', TRUE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-04-06 08:10:00', '2026-04-06 08:10:00', '2026-04-06 08:10:00'),
('2', '2', '7', TRUE, 'arritmia', 'Taquicardia Sinusal', '', TRUE, '2026-04-06 09:25:00', '2026-04-06 09:25:00', '2026-04-06 09:25:00'),
('3', '3', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-06 10:40:00', '2026-04-06 10:40:00', '2026-04-06 10:40:00'),
('4', '4', '6', TRUE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-04-07 08:05:00', '2026-04-07 08:05:00', '2026-04-07 08:05:00'),
('5', '5', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-07 09:20:00', '2026-04-07 09:20:00', '2026-04-07 09:20:00'),
('6', '6', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-07 10:35:00', '2026-04-07 10:35:00', '2026-04-07 10:35:00'),
('7', '7', '6', FALSE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-04-07 11:50:00', '2026-04-07 11:50:00', '2026-04-07 11:50:00'),
('8', '8', '12', TRUE, 'arritmia', 'Flutter Auricular', '', TRUE, '2026-04-08 08:15:00', '2026-04-08 08:15:00', '2026-04-08 08:15:00'),
('9', '9', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-08 09:30:00', '2026-04-08 09:30:00', '2026-04-08 09:30:00'),
('10', '10', '6', TRUE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-04-09 08:00:00', '2026-04-09 08:00:00', '2026-04-09 08:00:00'),
('11', '11', '7', TRUE, 'arritmia', 'Taquicardia Sinusal', '', TRUE, '2026-04-09 09:15:00', '2026-04-09 09:15:00', '2026-04-09 09:15:00'),
('12', '12', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-09 10:30:00', '2026-04-09 10:30:00', '2026-04-09 10:30:00'),
('13', '13', '12', TRUE, 'arritmia', 'Flutter Auricular', '', TRUE, '2026-04-10 08:20:00', '2026-04-10 08:20:00', '2026-04-10 08:20:00'),
('14', '14', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-10 09:35:00', '2026-04-10 09:35:00', '2026-04-10 09:35:00'),
('15', '15', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-10 10:50:00', '2026-04-10 10:50:00', '2026-04-10 10:50:00'),
('16', '16', '6', TRUE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-04-13 08:10:00', '2026-04-13 08:10:00', '2026-04-13 08:10:00'),
('17', '17', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-13 09:25:00', '2026-04-13 09:25:00', '2026-04-13 09:25:00'),
('18', '18', '7', TRUE, 'arritmia', 'Taquicardia Sinusal', '', TRUE, '2026-04-14 08:05:00', '2026-04-14 08:05:00', '2026-04-14 08:05:00'),
('19', '19', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-14 09:20:00', '2026-04-14 09:20:00', '2026-04-14 09:20:00'),
('20', '20', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-14 10:35:00', '2026-04-14 10:35:00', '2026-04-14 10:35:00'),
('21', '21', '1', FALSE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-14 11:50:00', '2026-04-14 11:50:00', '2026-04-14 11:50:00'),
('22', '22', '12', TRUE, 'arritmia', 'Flutter Auricular', '', TRUE, '2026-04-15 08:15:00', '2026-04-15 08:15:00', '2026-04-15 08:15:00'),
('23', '23', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-15 09:30:00', '2026-04-15 09:30:00', '2026-04-15 09:30:00'),
('24', '24', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-15 10:45:00', '2026-04-15 10:45:00', '2026-04-15 10:45:00'),
('25', '25', '6', TRUE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-04-16 08:00:00', '2026-04-16 08:00:00', '2026-04-16 08:00:00'),
('26', '26', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-16 09:15:00', '2026-04-16 09:15:00', '2026-04-16 09:15:00'),
('27', '27', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-16 10:30:00', '2026-04-16 10:30:00', '2026-04-16 10:30:00'),
('28', '28', '7', TRUE, 'arritmia', 'Taquicardia Sinusal', '', TRUE, '2026-04-17 08:20:00', '2026-04-17 08:20:00', '2026-04-17 08:20:00'),
('29', '29', '12', TRUE, 'arritmia', 'Flutter Auricular', '', TRUE, '2026-04-17 09:35:00', '2026-04-17 09:35:00', '2026-04-17 09:35:00'),
('30', '30', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-17 10:50:00', '2026-04-17 10:50:00', '2026-04-17 10:50:00'),
('31', '31', '6', TRUE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-04-20 08:05:00', '2026-04-20 08:05:00', '2026-04-20 08:05:00'),
('32', '32', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-20 09:20:00', '2026-04-20 09:20:00', '2026-04-20 09:20:00'),
('33', '33', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-20 10:35:00', '2026-04-20 10:35:00', '2026-04-20 10:35:00'),
('34', '34', '1', FALSE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-20 11:50:00', '2026-04-20 11:50:00', '2026-04-20 11:50:00'),
('35', '35', '12', TRUE, 'arritmia', 'Flutter Auricular', '', TRUE, '2026-04-21 08:15:00', '2026-04-21 08:15:00', '2026-04-21 08:15:00'),
('36', '36', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-21 09:30:00', '2026-04-21 09:30:00', '2026-04-21 09:30:00'),
('37', '37', '6', TRUE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-04-22 08:00:00', '2026-04-22 08:00:00', '2026-04-22 08:00:00'),
('38', '38', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-22 09:15:00', '2026-04-22 09:15:00', '2026-04-22 09:15:00'),
('39', '39', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-22 10:30:00', '2026-04-22 10:30:00', '2026-04-22 10:30:00'),
('40', '40', '7', TRUE, 'arritmia', 'Taquicardia Sinusal', '', TRUE, '2026-04-23 08:20:00', '2026-04-23 08:20:00', '2026-04-23 08:20:00'),
('41', '41', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-23 09:35:00', '2026-04-23 09:35:00', '2026-04-23 09:35:00'),
('42', '42', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-23 10:50:00', '2026-04-23 10:50:00', '2026-04-23 10:50:00'),
('43', '43', '12', TRUE, 'arritmia', 'Flutter Auricular', '', TRUE, '2026-04-24 08:05:00', '2026-04-24 08:05:00', '2026-04-24 08:05:00'),
('44', '44', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-24 09:20:00', '2026-04-24 09:20:00', '2026-04-24 09:20:00'),
('45', '45', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-24 10:35:00', '2026-04-24 10:35:00', '2026-04-24 10:35:00'),
('46', '46', '6', TRUE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-04-27 08:15:00', '2026-04-27 08:15:00', '2026-04-27 08:15:00'),
('47', '47', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-27 09:30:00', '2026-04-27 09:30:00', '2026-04-27 09:30:00'),
('48', '48', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-27 10:45:00', '2026-04-27 10:45:00', '2026-04-27 10:45:00'),
('49', '49', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-27 12:00:00', '2026-04-27 12:00:00', '2026-04-27 12:00:00'),
('50', '50', '7', TRUE, 'arritmia', 'Taquicardia Sinusal', '', TRUE, '2026-04-28 08:00:00', '2026-04-28 08:00:00', '2026-04-28 08:00:00'),
('51', '51', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-28 09:15:00', '2026-04-28 09:15:00', '2026-04-28 09:15:00'),
('52', '52', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-28 10:30:00', '2026-04-28 10:30:00', '2026-04-28 10:30:00'),
('53', '53', '6', TRUE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-04-29 08:20:00', '2026-04-29 08:20:00', '2026-04-29 08:20:00'),
('54', '54', '12', TRUE, 'arritmia', 'Flutter Auricular', '', TRUE, '2026-04-29 09:35:00', '2026-04-29 09:35:00', '2026-04-29 09:35:00'),
('55', '55', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-29 10:50:00', '2026-04-29 10:50:00', '2026-04-29 10:50:00'),
('56', '56', '7', TRUE, 'arritmia', 'Taquicardia Sinusal', '', TRUE, '2026-04-30 08:05:00', '2026-04-30 08:05:00', '2026-04-30 08:05:00'),
('57', '57', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-04-30 09:20:00', '2026-04-30 09:20:00', '2026-04-30 09:20:00'),
('58', '58', '6', TRUE, 'arritmia', 'Fibrilacion Auricular', '', TRUE, '2026-05-04 08:15:00', '2026-05-04 08:15:00', '2026-05-04 08:15:00'),
('59', '59', '7', TRUE, 'arritmia', 'Taquicardia Sinusal', '', TRUE, '2026-05-04 09:30:00', '2026-05-04 09:30:00', '2026-05-04 09:30:00'),
('60', '60', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-05-04 10:45:00', '2026-05-04 10:45:00', '2026-05-04 10:45:00'),
('61', '61', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-05-04 12:00:00', '2026-05-04 12:00:00', '2026-05-04 12:00:00'),
('62', '62', '12', TRUE, 'arritmia', 'Flutter Auricular', '', TRUE, '2026-05-05 08:00:00', '2026-05-05 08:00:00', '2026-05-05 08:00:00'),
('63', '63', '1', TRUE, 'normal', 'Ritmo Sinusal Normal', '', TRUE, '2026-05-05 09:15:00', '2026-05-05 09:15:00', '2026-05-05 09:15:00');
SELECT setval(pg_get_serial_sequence('diagnosticos', 'diagnostico_id'), COALESCE(MAX(diagnostico_id), 1)) FROM diagnosticos;


-- ==> ecg_analyses (tabla principal - 63 registros)
INSERT INTO ecg_analyses (id, user_id, filename, patient_identifier, patient_age, patient_sex, patient_weight, label, label_code, type, confidence, top_predictions, doctor_result, doctor_label, doctor_notes, reviewed_at, created_at, updated_at) VALUES
(1, 1, '20260406-834219.pdf', 'PACIENTE_20260406_001', 58, 1, 78, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 92.4, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 92.40}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 7.60}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-04-06 08:10:00', '2026-04-06 08:10:00', '2026-04-06 08:10:00'),
(2, 1, '20260406-157604.pdf', 'PACIENTE_20260406_002', 55, 0, 67, 'Taquicardia Sinusal', 'STACH', 'arritmia', 90.8, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 90.80}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.20}]', 'arritmia', 'Taquicardia Sinusal', '', '2026-04-06 09:25:00', '2026-04-06 09:25:00', '2026-04-06 09:25:00'),
(3, 1, '20260406-692381.pdf', 'PACIENTE_20260406_003', 42, 1, 81, 'Ritmo Sinusal Normal', 'NORM', 'normal', 93.1, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.10}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.90}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-06 10:40:00', '2026-04-06 10:40:00', '2026-04-06 10:40:00'),
(4, 1, '20260407-420918.pdf', 'PACIENTE_20260407_001', 61, 0, 65, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 88.7, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 88.70}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 11.30}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-04-07 08:05:00', '2026-04-07 08:05:00', '2026-04-07 08:05:00'),
(5, 1, '20260407-783052.pdf', 'PACIENTE_20260407_002', 47, 1, 84, 'Ritmo Sinusal Normal', 'NORM', 'normal', 91.6, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.40}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-07 09:20:00', '2026-04-07 09:20:00', '2026-04-07 09:20:00'),
(6, 1, '20260407-269447.pdf', 'PACIENTE_20260407_003', 52, 0, 70, 'Ritmo Sinusal Normal', 'NORM', 'normal', 94.2, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 94.20}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 5.80}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-07 10:35:00', '2026-04-07 10:35:00', '2026-04-07 10:35:00'),
(7, 1, '20260407-915630.pdf', 'PACIENTE_20260407_004', 64, 1, 76, 'Ritmo Sinusal Normal', 'NORM', 'normal', 80.9, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 80.90}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 19.10}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-04-07 11:50:00', '2026-04-07 11:50:00', '2026-04-07 11:50:00'),
(8, 1, '20260408-508274.pdf', 'PACIENTE_20260408_001', 59, 1, 79, 'Flutter Auricular', 'AFLT', 'arritmia', 89.5, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 89.50}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.50}]', 'arritmia', 'Flutter Auricular', '', '2026-04-08 08:15:00', '2026-04-08 08:15:00', '2026-04-08 08:15:00'),
(9, 1, '20260408-731965.pdf', 'PACIENTE_20260408_002', 37, 0, 58, 'Ritmo Sinusal Normal', 'NORM', 'normal', 92.7, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.70}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.30}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-08 09:30:00', '2026-04-08 09:30:00', '2026-04-08 09:30:00'),
(10, 1, '20260409-184603.pdf', 'PACIENTE_20260409_001', 66, 1, 82, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 94.8, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.80}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.20}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-04-09 08:00:00', '2026-04-09 08:00:00', '2026-04-09 08:00:00'),
(11, 1, '20260409-647290.pdf', 'PACIENTE_20260409_002', 54, 0, 63, 'Taquicardia Sinusal', 'STACH', 'arritmia', 91.2, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 91.20}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 8.80}]', 'arritmia', 'Taquicardia Sinusal', '', '2026-04-09 09:15:00', '2026-04-09 09:15:00', '2026-04-09 09:15:00'),
(12, 1, '20260409-392815.pdf', 'PACIENTE_20260409_003', 45, 1, 84, 'Ritmo Sinusal Normal', 'NORM', 'normal', 90.4, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.40}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.60}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-09 10:30:00', '2026-04-09 10:30:00', '2026-04-09 10:30:00'),
(13, 1, '20260410-859104.pdf', 'PACIENTE_20260410_001', 62, 0, 69, 'Flutter Auricular', 'AFLT', 'arritmia', 88.9, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 88.90}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 11.10}]', 'arritmia', 'Flutter Auricular', '', '2026-04-10 08:20:00', '2026-04-10 08:20:00', '2026-04-10 08:20:00'),
(14, 1, '20260410-206738.pdf', 'PACIENTE_20260410_002', 34, 1, 77, 'Ritmo Sinusal Normal', 'NORM', 'normal', 93.6, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.40}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-10 09:35:00', '2026-04-10 09:35:00', '2026-04-10 09:35:00'),
(15, 1, '20260410-574921.pdf', 'PACIENTE_20260410_003', 47, 0, 86, 'Ritmo Sinusal Normal', 'NORM', 'normal', 91.9, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.90}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.10}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-10 10:50:00', '2026-04-10 10:50:00', '2026-04-10 10:50:00'),
(16, 1, '20260413-720381.pdf', 'PACIENTE_20260413_001', 69, 1, 64, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 94.1, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.10}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.90}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-04-13 08:10:00', '2026-04-13 08:10:00', '2026-04-13 08:10:00'),
(17, 1, '20260413-262026.pdf', 'PACIENTE_20260413_002', 52, 0, 79, 'Ritmo Sinusal Normal', 'NORM', 'normal', 90.7, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.70}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.30}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-13 09:25:00', '2026-04-13 09:25:00', '2026-04-13 09:25:00'),
(18, 1, '20260414-296552.pdf', 'PACIENTE_20260414_001', 31, 1, 55, 'Taquicardia Sinusal', 'STACH', 'arritmia', 89.8, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 89.80}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.20}]', 'arritmia', 'Taquicardia Sinusal', '', '2026-04-14 08:05:00', '2026-04-14 08:05:00', '2026-04-14 08:05:00'),
(19, 1, '20260414-528797.pdf', 'PACIENTE_20260414_002', 44, 0, 83, 'Ritmo Sinusal Normal', 'NORM', 'normal', 92.5, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.50}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.50}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-14 09:20:00', '2026-04-14 09:20:00', '2026-04-14 09:20:00'),
(20, 1, '20260414-841306.pdf', 'PACIENTE_20260414_003', 58, 1, 78, 'Ritmo Sinusal Normal', 'NORM', 'normal', 91.4, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.40}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.60}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-14 10:35:00', '2026-04-14 10:35:00', '2026-04-14 10:35:00'),
(21, 1, '20260414-973510.pdf', 'PACIENTE_20260414_004', 42, 0, 61, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 80.6, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 80.60}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 19.40}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-14 11:50:00', '2026-04-14 11:50:00', '2026-04-14 11:50:00'),
(22, 1, '20260415-239487.pdf', 'PACIENTE_20260415_001', 49, 1, 82, 'Flutter Auricular', 'AFLT', 'arritmia', 90.9, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 90.90}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.10}]', 'arritmia', 'Flutter Auricular', '', '2026-04-15 08:15:00', '2026-04-15 08:15:00', '2026-04-15 08:15:00'),
(23, 1, '20260415-973710.pdf', 'PACIENTE_20260415_002', 64, 0, 66, 'Ritmo Sinusal Normal', 'NORM', 'normal', 93.8, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.80}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.20}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-15 09:30:00', '2026-04-15 09:30:00', '2026-04-15 09:30:00'),
(24, 1, '20260415-675813.pdf', 'PACIENTE_20260415_003', 55, 1, 80, 'Ritmo Sinusal Normal', 'NORM', 'normal', 92.1, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.10}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.90}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-15 10:45:00', '2026-04-15 10:45:00', '2026-04-15 10:45:00'),
(25, 1, '20260416-498501.pdf', 'PACIENTE_20260416_001', 68, 0, 70, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 94.5, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.50}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.50}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-04-16 08:00:00', '2026-04-16 08:00:00', '2026-04-16 08:00:00'),
(26, 1, '20260416-149807.pdf', 'PACIENTE_20260416_002', 37, 1, 76, 'Ritmo Sinusal Normal', 'NORM', 'normal', 91, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.00}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.00}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-16 09:15:00', '2026-04-16 09:15:00', '2026-04-16 09:15:00'),
(27, 1, '20260416-595321.pdf', 'PACIENTE_20260416_003', 61, 0, 65, 'Ritmo Sinusal Normal', 'NORM', 'normal', 89.6, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 89.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 10.40}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-16 10:30:00', '2026-04-16 10:30:00', '2026-04-16 10:30:00'),
(28, 1, '20260417-288592.pdf', 'PACIENTE_20260417_001', 44, 1, 84, 'Taquicardia Sinusal', 'STACH', 'arritmia', 90.3, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 90.30}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.70}]', 'arritmia', 'Taquicardia Sinusal', '', '2026-04-17 08:20:00', '2026-04-17 08:20:00', '2026-04-17 08:20:00'),
(29, 1, '20260417-957298.pdf', 'PACIENTE_20260417_002', 52, 0, 59, 'Flutter Auricular', 'AFLT', 'arritmia', 92.8, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 92.80}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 7.20}]', 'arritmia', 'Flutter Auricular', '', '2026-04-17 09:35:00', '2026-04-17 09:35:00', '2026-04-17 09:35:00'),
(30, 1, '20260417-180834.pdf', 'PACIENTE_20260417_003', 57, 1, 79, 'Ritmo Sinusal Normal', 'NORM', 'normal', 93.3, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.30}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.70}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-17 10:50:00', '2026-04-17 10:50:00', '2026-04-17 10:50:00'),
(31, 1, '20260420-620001.pdf', 'PACIENTE_20260420_001', 63, 0, 68, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 91.7, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 91.70}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 8.30}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-04-20 08:05:00', '2026-04-20 08:05:00', '2026-04-20 08:05:00'),
(32, 1, '20260420-620002.pdf', 'PACIENTE_20260420_002', 39, 1, 81, 'Ritmo Sinusal Normal', 'NORM', 'normal', 88.4, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 88.40}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 11.60}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-20 09:20:00', '2026-04-20 09:20:00', '2026-04-20 09:20:00'),
(33, 1, '20260420-620003.pdf', 'PACIENTE_20260420_003', 60, 0, 67, 'Ritmo Sinusal Normal', 'NORM', 'normal', 90.6, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.40}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-20 10:35:00', '2026-04-20 10:35:00', '2026-04-20 10:35:00'),
(34, 1, '20260420-620004.pdf', 'PACIENTE_20260420_004', 47, 1, 85, 'Taquicardia Sinusal', 'STACH', 'arritmia', 80.3, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 80.30}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 19.70}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-20 11:50:00', '2026-04-20 11:50:00', '2026-04-20 11:50:00'),
(35, 1, '20260421-621381.pdf', 'PACIENTE_20260421_001', 35, 0, 58, 'Flutter Auricular', 'AFLT', 'arritmia', 89.9, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 89.90}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.10}]', 'arritmia', 'Flutter Auricular', '', '2026-04-21 08:15:00', '2026-04-21 08:15:00', '2026-04-21 08:15:00'),
(36, 1, '20260421-846027.pdf', 'PACIENTE_20260421_002', 53, 1, 77, 'Ritmo Sinusal Normal', 'NORM', 'normal', 92, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.00}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.00}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-21 09:30:00', '2026-04-21 09:30:00', '2026-04-21 09:30:00'),
(37, 1, '20260422-622731.pdf', 'PACIENTE_20260422_001', 58, 1, 78, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 94.6, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.60}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.40}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-04-22 08:00:00', '2026-04-22 08:00:00', '2026-04-22 08:00:00'),
(38, 1, '20260422-194608.pdf', 'PACIENTE_20260422_002', 55, 0, 80, 'Ritmo Sinusal Normal', 'NORM', 'normal', 91.5, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.50}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.50}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-22 09:15:00', '2026-04-22 09:15:00', '2026-04-22 09:15:00'),
(39, 1, '20260422-753042.pdf', 'PACIENTE_20260422_003', 68, 0, 70, 'Ritmo Sinusal Normal', 'NORM', 'normal', 90.1, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.10}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.90}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-22 10:30:00', '2026-04-22 10:30:00', '2026-04-22 10:30:00'),
(40, 1, '20260423-623915.pdf', 'PACIENTE_20260423_001', 37, 1, 76, 'Taquicardia Sinusal', 'STACH', 'arritmia', 89.2, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 89.20}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.80}]', 'arritmia', 'Taquicardia Sinusal', '', '2026-04-23 08:20:00', '2026-04-23 08:20:00', '2026-04-23 08:20:00'),
(41, 1, '20260423-407286.pdf', 'PACIENTE_20260423_002', 61, 0, 65, 'Ritmo Sinusal Normal', 'NORM', 'normal', 93.4, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.40}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 6.60}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-23 09:35:00', '2026-04-23 09:35:00', '2026-04-23 09:35:00'),
(42, 1, '20260423-865134.pdf', 'PACIENTE_20260423_003', 44, 1, 84, 'Ritmo Sinusal Normal', 'NORM', 'normal', 92.2, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.20}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.80}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-23 10:50:00', '2026-04-23 10:50:00', '2026-04-23 10:50:00'),
(43, 1, '20260424-624508.pdf', 'PACIENTE_20260424_001', 52, 0, 59, 'Flutter Auricular', 'AFLT', 'arritmia', 88.8, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 88.80}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 11.20}]', 'arritmia', 'Flutter Auricular', '', '2026-04-24 08:05:00', '2026-04-24 08:05:00', '2026-04-24 08:05:00'),
(44, 1, '20260424-218763.pdf', 'PACIENTE_20260424_002', 57, 1, 79, 'Ritmo Sinusal Normal', 'NORM', 'normal', 91.8, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.80}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.20}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-24 09:20:00', '2026-04-24 09:20:00', '2026-04-24 09:20:00'),
(45, 1, '20260424-790341.pdf', 'PACIENTE_20260424_003', 63, 0, 68, 'Ritmo Sinusal Normal', 'NORM', 'normal', 90.5, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.50}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.50}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-24 10:35:00', '2026-04-24 10:35:00', '2026-04-24 10:35:00'),
(46, 1, '20260427-627194.pdf', 'PACIENTE_20260427_001', 39, 1, 81, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 94, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.00}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 6.00}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-04-27 08:15:00', '2026-04-27 08:15:00', '2026-04-27 08:15:00'),
(47, 1, '20260427-482650.pdf', 'PACIENTE_20260427_002', 60, 0, 67, 'Ritmo Sinusal Normal', 'NORM', 'normal', 92.6, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.40}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-27 09:30:00', '2026-04-27 09:30:00', '2026-04-27 09:30:00'),
(48, 1, '20260427-936815.pdf', 'PACIENTE_20260427_003', 47, 1, 85, 'Ritmo Sinusal Normal', 'NORM', 'normal', 91.1, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.10}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.90}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-27 10:45:00', '2026-04-27 10:45:00', '2026-04-27 10:45:00'),
(49, 1, '20260427-305742.pdf', 'PACIENTE_20260427_004', 35, 0, 58, 'Ritmo Sinusal Normal', 'NORM', 'normal', 89.7, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 89.70}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 10.30}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-27 12:00:00', '2026-04-27 12:00:00', '2026-04-27 12:00:00'),
(50, 1, '20260428-628409.pdf', 'PACIENTE_20260428_001', 53, 1, 77, 'Taquicardia Sinusal', 'STACH', 'arritmia', 90, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 90.00}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 10.00}]', 'arritmia', 'Taquicardia Sinusal', '', '2026-04-28 08:00:00', '2026-04-28 08:00:00', '2026-04-28 08:00:00'),
(51, 1, '20260428-174936.pdf', 'PACIENTE_20260428_002', 58, 1, 78, 'Ritmo Sinusal Normal', 'NORM', 'normal', 93, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 93.00}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.00}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-28 09:15:00', '2026-04-28 09:15:00', '2026-04-28 09:15:00'),
(52, 1, '20260428-852617.pdf', 'PACIENTE_20260428_003', 55, 0, 80, 'Ritmo Sinusal Normal', 'NORM', 'normal', 92.3, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.30}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.70}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-28 10:30:00', '2026-04-28 10:30:00', '2026-04-28 10:30:00'),
(53, 1, '20260429-629284.pdf', 'PACIENTE_20260429_001', 68, 0, 70, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 94.7, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.70}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.30}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-04-29 08:20:00', '2026-04-29 08:20:00', '2026-04-29 08:20:00'),
(54, 1, '20260429-416950.pdf', 'PACIENTE_20260429_002', 37, 1, 76, 'Flutter Auricular', 'AFLT', 'arritmia', 91.3, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 91.30}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 8.70}]', 'arritmia', 'Flutter Auricular', '', '2026-04-29 09:35:00', '2026-04-29 09:35:00', '2026-04-29 09:35:00'),
(55, 1, '20260429-783621.pdf', 'PACIENTE_20260429_003', 61, 0, 65, 'Ritmo Sinusal Normal', 'NORM', 'normal', 88.6, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 88.60}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 11.40}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-29 10:50:00', '2026-04-29 10:50:00', '2026-04-29 10:50:00'),
(56, 1, '20260430-630472.pdf', 'PACIENTE_20260430_001', 44, 1, 84, 'Taquicardia Sinusal', 'STACH', 'arritmia', 90.2, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 90.20}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 9.80}]', 'arritmia', 'Taquicardia Sinusal', '', '2026-04-30 08:05:00', '2026-04-30 08:05:00', '2026-04-30 08:05:00'),
(57, 1, '20260430-205819.pdf', 'PACIENTE_20260430_002', 52, 0, 59, 'Ritmo Sinusal Normal', 'NORM', 'normal', 92.9, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 92.90}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 7.10}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-04-30 09:20:00', '2026-04-30 09:20:00', '2026-04-30 09:20:00'),
(58, 1, '20260504-471638.pdf', 'PACIENTE_20260504_001', 57, 1, 79, 'Fibrilacion Auricular', 'AFIB', 'arritmia', 94.4, '[{"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 94.40}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 5.60}]', 'arritmia', 'Fibrilacion Auricular', '', '2026-05-04 08:15:00', '2026-05-04 08:15:00', '2026-05-04 08:15:00'),
(59, 1, '20260504-839205.pdf', 'PACIENTE_20260504_002', 63, 0, 68, 'Taquicardia Sinusal', 'STACH', 'arritmia', 91.6, '[{"code": "STACH", "label": "Taquicardia Sinusal", "probability": 91.60}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 8.40}]', 'arritmia', 'Taquicardia Sinusal', '', '2026-05-04 09:30:00', '2026-05-04 09:30:00', '2026-05-04 09:30:00'),
(60, 1, '20260504-126794.pdf', 'PACIENTE_20260504_003', 39, 1, 81, 'Ritmo Sinusal Normal', 'NORM', 'normal', 89.4, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 89.40}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 10.60}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-05-04 10:45:00', '2026-05-04 10:45:00', '2026-05-04 10:45:00'),
(61, 1, '20260504-592481.pdf', 'PACIENTE_20260504_004', 60, 0, 67, 'Ritmo Sinusal Normal', 'NORM', 'normal', 90.8, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 90.80}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 9.20}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-05-04 12:00:00', '2026-05-04 12:00:00', '2026-05-04 12:00:00'),
(62, 1, '20260505-384729.pdf', 'PACIENTE_20260505_001', 47, 1, 85, 'Flutter Auricular', 'AFLT', 'arritmia', 93.5, '[{"code": "AFLT", "label": "Flutter Auricular", "probability": 93.50}, {"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 6.50}]', 'arritmia', 'Flutter Auricular', '', '2026-05-05 08:00:00', '2026-05-05 08:00:00', '2026-05-05 08:00:00'),
(63, 1, '20260505-917506.pdf', 'PACIENTE_20260505_002', 35, 0, 58, 'Ritmo Sinusal Normal', 'NORM', 'normal', 91.9, '[{"code": "NORM", "label": "Ritmo Sinusal Normal", "probability": 91.90}, {"code": "AFIB", "label": "Fibrilacion Auricular", "probability": 8.10}]', 'normal', 'Ritmo Sinusal Normal', '', '2026-05-05 09:15:00', '2026-05-05 09:15:00', '2026-05-05 09:15:00');
SELECT setval(pg_get_serial_sequence('ecg_analyses', 'id'), COALESCE(MAX(id), 1)) FROM ecg_analyses;

-- ==> auditorias
INSERT INTO auditorias (auditoria_id, usuario_id, accion, modulo, entidad, entidad_id, descripcion, valores_anteriores, valores_nuevos, user_agent, created_at) VALUES
('1', '1', 'logout', 'Autenticacion', 'users', '1', 'Cierre de sesion.', NULL, '{"email": "admin@ecg.com"}', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 14:06:23'),
('2', '2', 'login', 'Autenticacion', 'users', '2', 'Inicio de sesion exitoso.', NULL, '{"rol": "Medico", "email": "jose@ecg.com"}', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 14:06:31'),
('3', '2', 'logout', 'Autenticacion', 'users', '2', 'Cierre de sesion.', NULL, '{"email": "jose@ecg.com"}', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 14:20:55'),
('4', '1', 'login', 'Autenticacion', 'users', '1', 'Inicio de sesion exitoso.', NULL, '{"rol": "Administrador", "email": "admin@ecg.com"}', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 14:21:04'),
('5', '1', 'crear', 'Usuarios', 'users', '3', 'Creacion de usuario.', NULL, '{"id": 3, "name": "Fermin", "email": "fermin@ecg.com", "estado": true, "role_id": "3"}', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 14:21:50'),
('6', '1', 'logout', 'Autenticacion', 'users', '1', 'Cierre de sesion.', NULL, '{"email": "admin@ecg.com"}', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 14:21:55'),
('7', '3', 'login', 'Autenticacion', 'users', '3', 'Inicio de sesion exitoso.', NULL, '{"rol": "Operador", "email": "fermin@ecg.com"}', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 14:22:03'),
('8', '3', 'logout', 'Autenticacion', 'users', '3', 'Cierre de sesion.', NULL, '{"email": "fermin@ecg.com"}', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 14:53:21'),
('9', '1', 'login', 'Autenticacion', 'users', '1', 'Inicio de sesion exitoso.', NULL, '{"rol": "Administrador", "email": "admin@ecg.com"}', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-21 14:53:34');
SELECT setval(pg_get_serial_sequence('auditorias', 'auditoria_id'), COALESCE(MAX(auditoria_id), 1)) FROM auditorias;


-- Reactivar restricciones de integridad referencial
SET session_replication_role = 'origin';

-- =====================================================
-- FIN DEL ARCHIVO DE DATOS
-- =====================================================
