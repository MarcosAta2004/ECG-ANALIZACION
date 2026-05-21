<?php
/**
 * Exportador de datos MySQL -> PostgreSQL
 * Genera el archivo: bd_arritmias_postgres_datos.sql
 * Uso: php database/exportar_datos_postgres.php
 */

$host     = '127.0.0.1';
$port     = '3306';
$dbname   = 'bd_arritmias';
$username = 'root';
$password = '1234';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Error de conexion: " . $e->getMessage() . "\n");
    exit(1);
}

// -------------------------------------------------------
// Funcion: escapa un valor para INSERT en PostgreSQL
// -------------------------------------------------------
function pg_val($value): string
{
    if ($value === null || $value === '') {
        return $value === '' ? "''" : 'NULL';
    }
    // Escapar comillas simples duplicandolas (estandar SQL)
    return "'" . str_replace("'", "''", (string) $value) . "'";
}

// Valores booleanos (MySQL guarda 0/1, Postgres acepta TRUE/FALSE)
function pg_bool($value): string
{
    return ($value == 1 || $value === true || $value === 't') ? 'TRUE' : 'FALSE';
}

// Genera bloque INSERT para una tabla
function generar_insert(PDO $pdo, string $tabla, array $columnas, ?string $conflict_col = null): string
{
    $rows = $pdo->query("SELECT * FROM {$tabla} ORDER BY 1")->fetchAll();
    if (empty($rows)) {
        return "-- (sin datos en {$tabla})\n";
    }

    $cols_str = implode(', ', $columnas);
    $sql = "INSERT INTO {$tabla} ({$cols_str}) VALUES\n";
    $lineas = [];

    foreach ($rows as $row) {
        $vals = [];
        foreach ($columnas as $col) {
            $raw = $row[$col] ?? null;

            // Detectar columnas booleanas por nombre
            $bool_cols = ['estado', 'concordancia', 'email_verified_at'];
            if (in_array($col, ['estado', 'concordancia'])) {
                $vals[] = $raw === null ? 'NULL' : pg_bool($raw);
            } else {
                $vals[] = pg_val($raw);
            }
        }
        $lineas[] = '(' . implode(', ', $vals) . ')';
    }

    $sql .= implode(",\n", $lineas) . ";\n";

    // Ajustar secuencia SERIAL si la tabla tiene ID explicito
    if ($conflict_col) {
        $sql .= "SELECT setval(pg_get_serial_sequence('{$tabla}', '{$conflict_col}'), COALESCE(MAX({$conflict_col}), 1)) FROM {$tabla};\n";
    }

    return $sql . "\n";
}

// -------------------------------------------------------
// Construir el archivo SQL
// -------------------------------------------------------
$output = [];

$output[] = "-- =====================================================";
$output[] = "-- DATOS ACTUALES: Sistema de Clasificacion de Arritmias";
$output[] = "-- Exportado desde MySQL bd_arritmias";
$output[] = "-- Destino: PostgreSQL (Supabase / ElephantSQL)";
$output[] = "-- Generado: " . date('Y-m-d H:i:s');
$output[] = "-- IMPORTANTE: Ejecutar DESPUES de bd_arritmias_postgres.sql";
$output[] = "-- =====================================================";
$output[] = "";
$output[] = "-- Desactivar restricciones temporalmente para carga masiva";
$output[] = "SET session_replication_role = 'replica';";
$output[] = "";

// 1. roles
$output[] = "-- ==> roles";
$output[] = generar_insert($pdo, 'roles',
    ['role_id', 'nombre', 'descripcion', 'estado', 'created_at', 'updated_at'],
    'role_id'
);

// 2. usuarios (tabla espejo del sistema legacy)
$output[] = "-- ==> usuarios";
$output[] = generar_insert($pdo, 'usuarios',
    ['usuario_id', 'role_id', 'nombre', 'email', 'password', 'estado', 'created_at', 'updated_at'],
    'usuario_id'
);

// 3. users (tabla Laravel de autenticacion)
$output[] = "-- ==> users (autenticacion Laravel)";
$output[] = generar_insert($pdo, 'users',
    ['id', 'role_id', 'name', 'email', 'email_verified_at', 'password', 'remember_token', 'estado', 'created_at', 'updated_at'],
    'id'
);

// 4. grupo_cardiacos
$output[] = "-- ==> grupo_cardiacos";
$output[] = generar_insert($pdo, 'grupo_cardiacos',
    ['grupo_id', 'nombre', 'descripcion', 'estado', 'created_at', 'updated_at'],
    'grupo_id'
);

// 5. nivel_gravedades
$output[] = "-- ==> nivel_gravedades";
$output[] = generar_insert($pdo, 'nivel_gravedades',
    ['nivel_id', 'nombre', 'estado', 'created_at', 'updated_at'],
    'nivel_id'
);

// 6. clasificacion_arritmias
$output[] = "-- ==> clasificacion_arritmias";
$output[] = generar_insert($pdo, 'clasificacion_arritmias',
    ['clasificacion_id', 'nombre', 'estado', 'created_at', 'updated_at'],
    'clasificacion_id'
);

// 7. ritmo_cardiacos
$output[] = "-- ==> ritmo_cardiacos";
$output[] = generar_insert($pdo, 'ritmo_cardiacos',
    ['ritmo_id', 'grupo_id', 'nivel_id', 'clasificacion_id', 'label', 'nombre', 'descripcion', 'estado', 'created_at', 'updated_at'],
    'ritmo_id'
);

// 8. codigo_pacientes
$output[] = "-- ==> codigo_pacientes";
$output[] = generar_insert($pdo, 'codigo_pacientes',
    ['codigo_id', 'nombre', 'descripcion', 'estado', 'created_at', 'updated_at'],
    'codigo_id'
);

// 9. pacientes
$output[] = "-- ==> pacientes";
$output[] = generar_insert($pdo, 'pacientes',
    ['paciente_id', 'codigo_id', 'codigo_generado', 'fecha_nacimiento', 'edad', 'sexo', 'peso', 'usuario_id', 'estado', 'created_at', 'updated_at'],
    'paciente_id'
);

// 10. estudios
$output[] = "-- ==> estudios";
$output[] = generar_insert($pdo, 'estudios',
    ['estudio_id', 'paciente_id', 'usuario_id', 'legacy_analysis_id', 'observaciones', 'estado', 'created_at', 'updated_at'],
    'estudio_id'
);

// 11. imagenes
$output[] = "-- ==> imagenes";
$output[] = generar_insert($pdo, 'imagenes',
    ['imagen_id', 'estudio_id', 'ruta', 'formato', 'resolucion', 'tamano_kb', 'hash', 'estado', 'created_at', 'updated_at'],
    'imagen_id'
);

// 12. predicciones
$output[] = "-- ==> predicciones";
$output[] = generar_insert($pdo, 'predicciones',
    ['prediccion_id', 'imagen_id', 'ritmo_id', 'probabilidad', 'tiempo_ms', 'top_predicciones', 'label_detectado', 'label_code', 'tipo', 'estado', 'created_at', 'updated_at'],
    'prediccion_id'
);

// 13. diagnosticos
$output[] = "-- ==> diagnosticos";
$output[] = generar_insert($pdo, 'diagnosticos',
    ['diagnostico_id', 'estudio_id', 'ritmo_id', 'concordancia', 'resultado', 'doctor_label', 'observacion', 'estado', 'reviewed_at', 'created_at', 'updated_at'],
    'diagnostico_id'
);

// 14. ecg_analyses (tabla principal)
$output[] = "-- ==> ecg_analyses (tabla principal - 63 registros)";
// ecg_analyses tiene columna JSON top_predictions; la exportamos como texto literal
$rows_ecg = $pdo->query("SELECT * FROM ecg_analyses ORDER BY id")->fetchAll();
$ecg_cols = ['id', 'user_id', 'filename', 'patient_identifier', 'patient_age', 'patient_sex',
             'patient_weight', 'label', 'label_code', 'type', 'confidence', 'top_predictions',
             'doctor_result', 'doctor_label', 'doctor_notes', 'reviewed_at', 'created_at', 'updated_at'];

$cols_str = implode(', ', $ecg_cols);
$ecg_sql  = "INSERT INTO ecg_analyses ({$cols_str}) VALUES\n";
$lineas   = [];
foreach ($rows_ecg as $row) {
    $vals = [];
    foreach ($ecg_cols as $col) {
        $raw = $row[$col] ?? null;
        // Columnas numericas sin comillas
        if (in_array($col, ['id', 'user_id', 'patient_age', 'patient_sex', 'patient_weight', 'confidence'])) {
            $vals[] = ($raw === null) ? 'NULL' : (string) $raw;
        } else {
            $vals[] = pg_val($raw);
        }
    }
    $lineas[] = '(' . implode(', ', $vals) . ')';
}
$ecg_sql .= implode(",\n", $lineas) . ";\n";
$ecg_sql .= "SELECT setval(pg_get_serial_sequence('ecg_analyses', 'id'), COALESCE(MAX(id), 1)) FROM ecg_analyses;\n";
$output[] = $ecg_sql;

// 15. auditorias (si existen)
$cnt_aud = $pdo->query("SELECT COUNT(*) FROM auditorias")->fetchColumn();
if ($cnt_aud > 0) {
    $output[] = "-- ==> auditorias";
    $output[] = generar_insert($pdo, 'auditorias',
        ['auditoria_id', 'usuario_id', 'accion', 'modulo', 'entidad', 'entidad_id', 'descripcion', 'valores_anteriores', 'valores_nuevos', 'user_agent', 'created_at'],
        'auditoria_id'
    );
}

// Reactivar restricciones
$output[] = "-- Reactivar restricciones de integridad referencial";
$output[] = "SET session_replication_role = 'origin';";
$output[] = "";
$output[] = "-- =====================================================";
$output[] = "-- FIN DEL ARCHIVO DE DATOS";
$output[] = "-- =====================================================";

// Guardar archivo
$destino = __DIR__ . '/bd_arritmias_postgres_datos.sql';
file_put_contents($destino, implode("\n", $output) . "\n");
echo "✓ Datos exportados correctamente en: {$destino}\n";
echo "  Registros exportados:\n";
echo "    ecg_analyses : " . count($rows_ecg) . "\n";
echo "    usuarios     : " . $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn() . "\n";
echo "    users        : " . $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() . "\n";
echo "    diagnosticos : " . $pdo->query("SELECT COUNT(*) FROM diagnosticos")->fetchColumn() . "\n";
echo "    auditorias   : {$cnt_aud}\n";
