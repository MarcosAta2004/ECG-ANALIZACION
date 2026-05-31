<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$updated = 0;
\App\Models\Prediccion::where('probabilidad', '>', 1)->get()->each(function($p) use (&$updated) {
    $p->probabilidad = $p->probabilidad / 100;
    $p->save();
    $updated++;
});
echo "Updated $updated records.\n";
