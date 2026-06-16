<?php
$file = 'c:/Users/marco/ECG-ANALIZACION/sistema/database/seeders/EstudioSeeder.php';
$content = file_get_contents($file);
$content = preg_replace('/\'registrado_por\' => 1/', '\'registrado_por\' => \\App\\Models\\User::where(\'login\', \'licenciado\')->first()?->id ?? 3', $content);
$content = preg_replace('/\'observaciones\' => \'Migrado desde ecg_analyses\. Archivo: [a-zA-Z0-9\-\.]+\'/', '\'observaciones\' => \'falta colocar observaciones clínicas reales del licenciado\'', $content);
file_put_contents($file, $content);

$file = 'c:/Users/marco/ECG-ANALIZACION/sistema/database/seeders/ImagenSeeder.php';
$content = file_get_contents($file);
$content = preg_replace('/\'resolucion\' => null/', '\'resolucion\' => \'1920x1080\'', $content);
$content = preg_replace('/\'tamano_kb\' => null/', '\'tamano_kb\' => rand(200, 1200)', $content);
file_put_contents($file, $content);

$file = 'c:/Users/marco/ECG-ANALIZACION/sistema/database/seeders/DiagnosticoSeeder.php';
$content = file_get_contents($file);
$content = str_replace('\'observacion\' => null', '\'observacion\' => \'ingresar datos de observaciones medicas del cardiologo\'', $content);
file_put_contents($file, $content);

echo "Modified seeders successfully.";
