# Codigo relevante del sistema

Documento generado para estudiar y explicar el sistema. Cada seccion corresponde a un archivo del proyecto.

No se incluyen binarios, caches, logs, dependencias, archivos compilados ni `.env`.

## sistema/composer.json

````json
{
    "$schema": "https://getcomposer.org/schema.json",
    "name": "laravel/laravel",
    "type": "project",
    "description": "The skeleton application for the Laravel framework.",
    "keywords": ["laravel", "framework"],
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/tinker": "^2.10.1"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pail": "^1.2.2",
        "laravel/pint": "^1.24",
        "laravel/sail": "^1.41",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "phpunit/phpunit": "^11.5.50"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "setup": [
            "composer install",
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
            "@php artisan key:generate",
            "@php artisan migrate --force",
            "npm install",
            "npm run build"
        ],
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others"
        ],
        "test": [
            "@php artisan config:clear --ansi",
            "@php artisan test"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi",
            "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
            "@php artisan migrate --graceful --ansi"
        ],
        "pre-package-uninstall": [
            "Illuminate\\Foundation\\ComposerScripts::prePackageUninstall"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}

````

## sistema/package.json

````json
{
    "$schema": "https://www.schemastore.org/package.json",
    "private": true,
    "type": "module",
    "scripts": {
        "build": "vite build",
        "dev": "vite"
    },
    "devDependencies": {
        "@tailwindcss/vite": "^4.2.2",
        "alpinejs": "^3.15.11",
        "axios": "^1.11.0",
        "concurrently": "^9.0.1",
        "laravel-vite-plugin": "^2.0.0",
        "tailwindcss": "^4.2.2",
        "vite": "^7.0.7"
    }
}

````

## sistema/vite.config.js

````javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/aplicacion.css', 'resources/js/aplicacion.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

````

## sistema/bootstrap/app.php

````php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

````

## sistema/bootstrap/providers.php

````php
<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
];

````

## sistema/routes/web.php

````php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControladorAutenticacion;
use App\Http\Controllers\ControladorResumen;
use App\Http\Controllers\ControladorHistorial;
use App\Http\Controllers\ControladorSubida;
use App\Http\Controllers\ControladorMetricas;
use App\Http\Controllers\ControladorReportes;
use App\Http\Middleware\MiddlewareAutenticacion;

// RaÃ­z â†’ redirige al login
Route::get('/', fn() => redirect()->route('login'));

// Rutas pÃºblicas
Route::get('/login',  [ControladorAutenticacion::class, 'showLogin'])->name('login');
Route::post('/login', [ControladorAutenticacion::class, 'login'])->name('login.post');
Route::post('/logout',[ControladorAutenticacion::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware(MiddlewareAutenticacion::class)->group(function () {
    Route::get('/dashboard', [ControladorMetricas::class,   'index'])->name('dashboard');
    Route::get('/dashboard/statistics/pdf', [ControladorMetricas::class, 'downloadStatistics'])->name('dashboard.statistics.pdf');
    Route::get('/resumen',   [ControladorResumen::class, 'index'])->name('resumen');
    Route::get('/upload',   [ControladorSubida::class,  'index'])->name('upload');
    Route::post('/analyze', [ControladorSubida::class,  'analyze'])->name('analyze');
    Route::get('/history',           [ControladorHistorial::class, 'index'])->name('history');
    Route::post('/history/{id}/review',   [ControladorHistorial::class, 'review'])->name('history.review');
    Route::delete('/history/{id}/review', [ControladorHistorial::class, 'removeReview'])->name('history.review.remove');
    Route::get('/reports', [ControladorReportes::class, 'index'])->name('reports');
    Route::post('/reports/download', [ControladorReportes::class, 'download'])->name('reports.download');
});

````

## sistema/routes/console.php

````php
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

````

## sistema/app/Http/Controllers/Controlador.php

````php
<?php

namespace App\Http\Controllers;

abstract class Controlador
{
    //
}

````

## sistema/app/Http/Controllers/ControladorAutenticacion.php

````php
<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ControladorAutenticacion extends Controlador
{
    public function showLogin()
    {
        if (Session::has('user')) {
            return redirect()->route('dashboard');
        }
        return view('autenticacion.inicio-sesion');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:4'],
        ], [
            'email.required'    => 'Por favor, ingresa tu correo electrÃ³nico.',
            'email.email'       => 'Por favor, ingresa un correo electrÃ³nico vÃ¡lido.',
            'password.required' => 'Por favor, ingresa tu contraseÃ±a.',
            'password.min'      => 'La contraseÃ±a debe tener al menos 4 caracteres.',
        ]);

        $user = Usuario::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Credenciales incorrectas.',
            ])->withInput($request->only('email'));
        }

        Session::put('user', [
            'id'    => $user->id,
            'email' => $user->email,
            'name'  => $user->name,
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Session::forget('user');
        return redirect()->route('login');
    }
}

````

## sistema/app/Http/Controllers/ControladorSubida.php

````php
<?php

namespace App\Http\Controllers;

use App\Models\AnalisisEcg;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class ControladorSubida extends Controlador
{
    public function index()
    {
        return view('subir-ecg');
    }

    /**
     * Recibe el archivo + metadata del frontend,
     * lo reenvÃ­a a FastAPI, guarda el resultado en la DB
     * y devuelve el JSON al frontend.
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|mimes:png,jpg,jpeg,pdf,csv,txt|max:20480',
            'age'    => 'required|numeric|min:0|max:120',
            'sex'    => 'required|in:0,1',
            'weight' => 'required|numeric|min:1|max:300',
        ]);

        $apiUrl = env('ECG_API_URL', 'http://localhost:8001');
        $client = new Client(['timeout' => 120]);

        try {
            $file = $request->file('file');
            $generatedFilename = Carbon::now()->format('Ymd') . '-' . random_int(100000, 999999) . '.pdf';
            do {
                $patientIdentifier = 'PACIENTE_' . random_int(100000, 999999);
            } while (AnalisisEcg::where('patient_identifier', $patientIdentifier)->exists());

            $response = $client->post("{$apiUrl}/predict", [
                'multipart' => [
                    ['name' => 'file',   'contents' => fopen($file->getRealPath(), 'r'), 'filename' => $file->getClientOriginalName()],
                    ['name' => 'age',    'contents' => $request->age],
                    ['name' => 'sex',    'contents' => $request->sex],
                    ['name' => 'weight', 'contents' => $request->weight],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            $msg = 'Error al conectar con el servidor de anÃ¡lisis.';
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody()->getContents(), true);
                $msg  = $body['detail'] ?? $msg;
            }
            return response()->json(['error' => $msg], 502);
        }

        // Guardar en la base de datos
        $user = Session::get('user');
        $userId = $user['id'] ?? null;

        if ($userId) {
            $isNormal = str_contains(strtolower($data['label'] ?? ''), 'normal');
            $confidence = (float) ($data['confidence'] ?? 0);

            AnalisisEcg::create([
                'user_id'         => $userId,
                'filename'        => $generatedFilename,
                'patient_identifier' => $patientIdentifier,
                'patient_age'     => $request->age,
                'patient_sex'     => $request->sex,
                'patient_weight'  => $request->weight,
                'label'           => $data['label']       ?? 'Desconocido',
                'label_code'      => $data['top_predictions'][0]['code'] ?? 'NORM',
                'type'            => $isNormal ? 'normal' : 'arritmia',
                'confidence'      => $confidence,
                'top_predictions' => $data['top_predictions'] ?? [],
            ]);
        }

        return response()->json($data);
    }
}

````

## sistema/app/Http/Controllers/ControladorResumen.php

````php
<?php

namespace App\Http\Controllers;

use App\Models\AnalisisEcg;
use Illuminate\Support\Facades\Session;

class ControladorResumen extends Controlador
{
    public function index()
    {
        $userId = Session::get('user.id');
        $summary = AnalisisEcg::query()
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN type = 'normal' THEN 1 ELSE 0 END) as normales")
            ->first();

        $total = (int) ($summary?->total ?? 0);
        $normales = (int) ($summary?->normales ?? 0);
        $arritmias = $total - $normales;
        $pctNormal = $total > 0 ? round($normales / $total * 100, 1) : 0;
        $pctArr = $total > 0 ? round($arritmias / $total * 100, 1) : 0;

        $stats = [
            ['title' => 'ECGs Analizados', 'value' => number_format($total), 'subtitle' => 'Total historico', 'trend' => 'up', 'trend_value' => 'Acumulado', 'icon' => 'file-heart'],
            ['title' => 'Ritmos Normales', 'value' => number_format($normales), 'subtitle' => "{$pctNormal}% del total", 'trend' => 'up', 'trend_value' => 'ECGs sin arritmia', 'icon' => 'check-circle'],
            ['title' => 'Arritmias Detectadas', 'value' => number_format($arritmias), 'subtitle' => "{$pctArr}% del total", 'trend' => 'down', 'trend_value' => 'Requieren seguimiento', 'icon' => 'alert-triangle'],
        ];

        $recentActivity = AnalisisEcg::where('user_id', $userId)
            ->select(['id', 'type', 'filename', 'created_at'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'type' => $r->type === 'normal' ? 'Normal' : 'Arritmia',
                'time' => $r->created_at->diffForHumans(),
                'file' => $r->filename,
            ])->values()->toArray();

        return view('resumen', compact('stats', 'recentActivity'));
    }
}

````

## sistema/app/Http/Controllers/ControladorHistorial.php

````php
<?php

namespace App\Http\Controllers;

use App\Models\AnalisisEcg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ControladorHistorial extends Controlador
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'filter' => ['nullable', 'in:all,normal,arritmia,reviewed,unreviewed'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $userId = Session::get('user.id');
        $search = trim((string) ($validated['search'] ?? ''));
        $filter = $validated['filter'] ?? 'all';

        $query = AnalisisEcg::query()
            ->where('user_id', $userId);

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery
                    ->where('filename', 'like', '%' . $search . '%')
                    ->orWhere('patient_identifier', 'like', '%' . $search . '%')
                    ->orWhere('label', 'like', '%' . $search . '%')
                    ->orWhere('doctor_label', 'like', '%' . $search . '%');
            });
        }

        match ($filter) {
            'normal', 'arritmia' => $query->where('type', $filter),
            'reviewed' => $query->whereNotNull('doctor_result'),
            'unreviewed' => $query->whereNull('doctor_result'),
            default => null,
        };

        $rows = $query
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $history = $rows->through(fn ($r) => [
            'id'            => $r->id,
            'filename'      => $r->filename,
            'patient'       => $r->patient_identifier,
            'date'          => $r->created_at->format('Y-m-d'),
            'time'          => $r->created_at->format('H:i'),
            'result'        => $r->type === 'normal' ? 'Normal' : 'Arritmia',
            'rhythm'        => $r->label,
            'probability'   => round($r->confidence, 1),
            'type'          => $r->type,
            'doctor_result' => $r->doctor_result,
            'doctor_label'  => $r->doctor_label,
            'doctor_notes'  => $r->doctor_notes,
            'reviewed_at'   => $r->reviewed_at?->format('Y-m-d H:i'),
        ]);

        $statsBaseQuery = AnalisisEcg::query()->where('user_id', $userId);
        $stats = [
            'total' => (clone $statsBaseQuery)->count(),
            'normales' => (clone $statsBaseQuery)->where('type', 'normal')->count(),
            'revisados' => (clone $statsBaseQuery)->whereNotNull('doctor_result')->count(),
        ];
        $stats['arritmias'] = $stats['total'] - $stats['normales'];

        $filters = [
            'search' => $search,
            'filter' => $filter,
        ];

        return view('historial', compact('history', 'stats', 'filters'));
    }

    /**
     * Guarda o actualiza la valoraciÃ³n mÃ©dica de un anÃ¡lisis.
     */
    public function review(Request $request, $id)
    {
        $request->validate([
            'doctor_result' => 'required|in:normal,arritmia',
            'doctor_label'  => 'nullable|string|max:200',
            'doctor_notes'  => 'nullable|string|max:1000',
        ]);

        $userId   = Session::get('user.id');
        $analysis = AnalisisEcg::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $analysis->update([
            'doctor_result' => $request->input('doctor_result'),
            'doctor_label'  => $request->input('doctor_label'),
            'doctor_notes'  => $request->input('doctor_notes'),
            'reviewed_at'   => now(),
        ]);

        return response()->json([
            'ok'          => true,
            'reviewed_at' => $analysis->reviewed_at->format('Y-m-d H:i'),
        ]);
    }

    /**
     * Elimina la valoraciÃ³n mÃ©dica de un anÃ¡lisis.
     */
    public function removeReview($id)
    {
        $userId   = Session::get('user.id');
        $analysis = AnalisisEcg::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $analysis->update([
            'doctor_result' => null,
            'doctor_label'  => null,
            'doctor_notes'  => null,
            'reviewed_at'   => null,
        ]);

        return response()->json(['ok' => true]);
    }
}

````

## sistema/app/Http/Controllers/ControladorMetricas.php

````php
<?php

namespace App\Http\Controllers;

use App\Models\AnalisisEcg;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class ControladorMetricas extends Controlador
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $userId = Session::get('user.id');
        $from = !empty($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : null;
        $to = !empty($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : null;

        $baseQuery = AnalisisEcg::query()->where('user_id', $userId);
        $filteredQuery = $this->applyDateRange(clone $baseQuery, $from, $to);

        $total = (clone $filteredQuery)->count();
        $normales = (clone $filteredQuery)->where('type', 'normal')->count();
        $arritmias = $total - $normales;
        $pctNormal = $total > 0 ? round($normales / $total * 100, 1) : 0;
        $pctArr = $total > 0 ? round($arritmias / $total * 100, 1) : 0;
        $avgConfidence = round((float) ((clone $filteredQuery)->avg('confidence') ?? 0), 1);

        $reviewed = $this->applyDateRange(clone $baseQuery, $from, $to)
            ->whereNotNull('doctor_result')
            ->get(['type', 'doctor_result', 'confidence']);

        $reviewedCount = $reviewed->count();
        $reviewedRate = $total > 0 ? round($reviewedCount / $total * 100, 1) : 0;
        $positiveReal = $reviewed->where('doctor_result', 'arritmia')->count();
        $negativeReal = $reviewed->where('doctor_result', 'normal')->count();
        $reviewedAvgConfidence = $reviewedCount > 0
            ? round((float) $reviewed->avg('confidence'), 1)
            : 0;

        $realMetrics = $reviewedCount > 0 ? $this->buildRealMetrics($reviewed) : null;
        $rocData = $reviewedCount > 1 && $positiveReal > 0 && $negativeReal > 0
            ? $this->buildRocData($reviewed)
            : null;

        $metricCards = $realMetrics
            ? [
                [
                    'key' => 'sens',
                    'label' => 'Sensibilidad',
                    'sublabel' => 'Recall / TPR',
                    'value' => $realMetrics['sensitivity'],
                    'badge' => $reviewedCount . ' revisados',
                    'color' => 'hsl(160, 70%, 45%)',
                    'desc' => 'Detecta correctamente ' . $realMetrics['sensitivity'] . '% de las arritmias revisadas en el rango.',
                ],
                [
                    'key' => 'spec',
                    'label' => 'Especificidad',
                    'sublabel' => 'TNR / Selectividad',
                    'value' => $realMetrics['specificity'],
                    'badge' => $negativeReal . ' normales',
                    'color' => 'hsl(198, 90%, 50%)',
                    'desc' => 'Descarta correctamente ' . $realMetrics['specificity'] . '% de los ritmos normales revisados.',
                ],
                [
                    'key' => 'prec',
                    'label' => 'Precision',
                    'sublabel' => 'Valor Predictivo +',
                    'value' => $realMetrics['precision'],
                    'badge' => $positiveReal . ' arritmias',
                    'color' => 'hsl(270, 70%, 65%)',
                    'desc' => 'De cada alerta emitida, ' . $realMetrics['precision'] . '% coincide con la evaluacion medica.',
                ],
                [
                    'key' => 'acc',
                    'label' => 'Exactitud',
                    'sublabel' => 'Overall Accuracy',
                    'value' => $realMetrics['accuracy'],
                    'badge' => $total . ' analisis',
                    'color' => 'hsl(38, 90%, 58%)',
                    'desc' => 'Porcentaje global de coincidencia entre modelo y medico en el periodo seleccionado.',
                ],
            ]
            : [
                [
                    'key' => 'normal_rate',
                    'label' => 'Ritmos Normales',
                    'sublabel' => 'Distribucion del rango',
                    'value' => $pctNormal,
                    'badge' => $normales . ' casos',
                    'color' => 'hsl(160, 70%, 45%)',
                    'desc' => 'Representan ' . $pctNormal . '% de los ECG analizados en el periodo seleccionado.',
                ],
                [
                    'key' => 'arrhythmia_rate',
                    'label' => 'Arritmias',
                    'sublabel' => 'Distribucion del rango',
                    'value' => $pctArr,
                    'badge' => $arritmias . ' casos',
                    'color' => 'hsl(198, 90%, 50%)',
                    'desc' => 'Representan ' . $pctArr . '% de los ECG analizados en el periodo seleccionado.',
                ],
                [
                    'key' => 'avg_confidence',
                    'label' => 'Confianza Media',
                    'sublabel' => 'Promedio del modelo',
                    'value' => $avgConfidence,
                    'badge' => $total . ' lecturas',
                    'color' => 'hsl(270, 70%, 65%)',
                    'desc' => 'Confianza promedio del modelo sobre los analisis del rango filtrado.',
                ],
                [
                    'key' => 'reviewed_rate',
                    'label' => 'Casos Revisados',
                    'sublabel' => 'Cobertura clinica',
                    'value' => $reviewedRate,
                    'badge' => $reviewedCount . ' revisados',
                    'color' => 'hsl(38, 90%, 58%)',
                    'desc' => 'Porcentaje de estudios con validacion medica dentro del rango seleccionado.',
                ],
            ];

        $secondaryCards = [
            [
                'key' => 'f1',
                'label' => 'F1-Score',
                'formula' => '2PR/(P+R)',
                'value' => $realMetrics ? round($realMetrics['f1'] / 100, 3) : 0,
                'display' => $realMetrics ? number_format($realMetrics['f1'] / 100, 3) : '0.000',
                'delta' => $realMetrics ? number_format($realMetrics['f1'], 1) . '%' : 'sin revision',
                'color' => 'hsl(160, 70%, 45%)',
                'format' => 'score',
                'desc' => 'Balance entre precision y sensibilidad usando solo los casos revisados del periodo.',
            ],
            [
                'key' => 'auc',
                'label' => 'AUC-ROC',
                'formula' => 'AREA ROC',
                'value' => $rocData['auc'] ?? 0,
                'display' => $rocData ? number_format($rocData['auc'], 3) : '0.000',
                'delta' => $rocData ? 'umbral optimo ' . number_format($rocData['best_threshold'] * 100, 1) . '%' : 'sin curva',
                'color' => 'hsl(198, 90%, 50%)',
                'format' => 'score',
                'desc' => 'Capacidad del score de separar arritmia y normal dentro del rango filtrado.',
            ],
            [
                'key' => 'coverage',
                'label' => 'Cobertura Revisada',
                'formula' => 'REV/TOT',
                'value' => round($reviewedRate / 100, 3),
                'display' => number_format($reviewedRate, 1) . '%',
                'delta' => $reviewedCount . ' casos',
                'color' => 'hsl(38, 90%, 58%)',
                'format' => 'percent',
                'desc' => 'Porcion del periodo que ya cuenta con validacion medica para medir rendimiento real.',
            ],
        ];

        $dateRangeLabel = match (true) {
            $from && $to => 'Del ' . $from->format('d/m/Y') . ' al ' . $to->format('d/m/Y'),
            $from !== null => 'Desde el ' . $from->format('d/m/Y'),
            $to !== null => 'Hasta el ' . $to->format('d/m/Y'),
            default => 'Todo el historial',
        };

        $filters = [
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
            'label' => $dateRangeLabel,
            'hasRange' => $from !== null || $to !== null,
        ];

        $dashboardData = [
            'metricCards' => $metricCards,
            'secondaryCards' => $secondaryCards,
        ];

        return view('metricas', [
            'filters' => $filters,
            'dashboardData' => $dashboardData,
            'realMetrics' => $realMetrics,
            'rocData' => $rocData,
            'positiveReal' => $positiveReal,
            'negativeReal' => $negativeReal,
            'reviewedAvgConfidence' => $reviewedAvgConfidence,
        ]);
    }

    public function downloadStatistics(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $userId = Session::get('user.id');
        $from = !empty($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : null;
        $to = !empty($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : null;

        $baseQuery = AnalisisEcg::query()->where('user_id', $userId);
        $filteredQuery = $this->applyDateRange(clone $baseQuery, $from, $to);

        $total = (clone $filteredQuery)->count();
        $normales = (clone $filteredQuery)->where('type', 'normal')->count();
        $arritmias = $total - $normales;
        $pctNormal = $total > 0 ? round($normales / $total * 100, 1) : 0;
        $pctArr = $total > 0 ? round($arritmias / $total * 100, 1) : 0;
        $avgConfidence = round((float) ((clone $filteredQuery)->avg('confidence') ?? 0), 1);

        $reviewed = $this->applyDateRange(clone $baseQuery, $from, $to)
            ->whereNotNull('doctor_result')
            ->get(['type', 'doctor_result', 'confidence']);

        $reviewedCount = $reviewed->count();
        $reviewedRate = $total > 0 ? round($reviewedCount / $total * 100, 1) : 0;
        $positiveReal = $reviewed->where('doctor_result', 'arritmia')->count();
        $negativeReal = $reviewed->where('doctor_result', 'normal')->count();
        $reviewedAvgConfidence = $reviewedCount > 0
            ? round((float) $reviewed->avg('confidence'), 1)
            : 0;
        $realMetrics = $reviewedCount > 0 ? $this->buildRealMetrics($reviewed) : null;
        $rocData = $reviewedCount > 1 && $positiveReal > 0 && $negativeReal > 0
            ? $this->buildRocData($reviewed)
            : null;

        $dateRangeLabel = match (true) {
            $from && $to => 'Del ' . $from->format('d/m/Y') . ' al ' . $to->format('d/m/Y'),
            $from !== null => 'Desde el ' . $from->format('d/m/Y'),
            $to !== null => 'Hasta el ' . $to->format('d/m/Y'),
            default => 'Todo el historial',
        };

        $pdf = $this->buildStatisticsPdf([
            'generated_at' => now()->format('d/m/Y H:i'),
            'period' => $dateRangeLabel,
            'total' => $total,
            'normales' => $normales,
            'arritmias' => $arritmias,
            'pct_normal' => $pctNormal,
            'pct_arr' => $pctArr,
            'avg_confidence' => $avgConfidence,
            'reviewed_count' => $reviewedCount,
            'reviewed_rate' => $reviewedRate,
            'unreviewed_count' => max($total - $reviewedCount, 0),
            'reviewed_avg_confidence' => $reviewedAvgConfidence,
            'positive_real' => $positiveReal,
            'negative_real' => $negativeReal,
            'real_metrics' => $realMetrics,
            'roc_data' => $rocData,
        ]);
        $filename = 'estadisticas_ecg_' . now()->format('Ymd_His') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildRealMetrics(Collection $reviewed): array
    {
        $reviewedCount = $reviewed->count();
        $tp = $reviewed->where('type', 'arritmia')->where('doctor_result', 'arritmia')->count();
        $tn = $reviewed->where('type', 'normal')->where('doctor_result', 'normal')->count();
        $fp = $reviewed->where('type', 'arritmia')->where('doctor_result', 'normal')->count();
        $fn = $reviewed->where('type', 'normal')->where('doctor_result', 'arritmia')->count();

        $accuracy = round((($tp + $tn) / max($reviewedCount, 1)) * 100, 1);
        $sensitivity = ($tp + $fn) > 0 ? round(($tp / ($tp + $fn)) * 100, 1) : 0;
        $specificity = ($tn + $fp) > 0 ? round(($tn / ($tn + $fp)) * 100, 1) : 0;
        $precision = ($tp + $fp) > 0 ? round(($tp / ($tp + $fp)) * 100, 1) : 0;
        $f1 = ($precision + $sensitivity) > 0
            ? round((2 * $precision * $sensitivity) / ($precision + $sensitivity), 1)
            : 0;

        $mccDenominator = sqrt(max(($tp + $fp) * ($tp + $fn) * ($tn + $fp) * ($tn + $fn), 0));
        $mcc = $mccDenominator > 0 ? ($tp * $tn - $fp * $fn) / $mccDenominator : 0;

        return [
            'reviewedCount' => $reviewedCount,
            'tp' => $tp,
            'tn' => $tn,
            'fp' => $fp,
            'fn' => $fn,
            'accuracy' => $accuracy,
            'sensitivity' => $sensitivity,
            'specificity' => $specificity,
            'precision' => $precision,
            'f1' => $f1,
            'mcc' => round($mcc, 3),
        ];
    }

    private function buildRocData(Collection $reviewed): ?array
    {
        $scored = $reviewed->map(function ($row) {
            $confidence = max(0, min(100, (float) $row->confidence)) / 100;
            $positiveScore = $row->type === 'arritmia' ? $confidence : 1 - $confidence;

            return [
                'actual_positive' => $row->doctor_result === 'arritmia',
                'score' => round($positiveScore, 4),
            ];
        });

        $positives = $scored->where('actual_positive', true)->count();
        $negatives = $scored->count() - $positives;

        if ($positives === 0 || $negatives === 0) {
            return null;
        }

        $thresholds = $scored
            ->pluck('score')
            ->push(1.0)
            ->push(0.0)
            ->unique()
            ->sortDesc()
            ->values();

        $points = collect();

        foreach ($thresholds as $threshold) {
            $tp = 0;
            $fp = 0;

            foreach ($scored as $item) {
                $predictedPositive = $item['score'] >= $threshold;

                if ($predictedPositive && $item['actual_positive']) {
                    $tp++;
                }

                if ($predictedPositive && ! $item['actual_positive']) {
                    $fp++;
                }
            }

            $points->push([
                'threshold' => (float) $threshold,
                'tpr' => $positives > 0 ? $tp / $positives : 0,
                'fpr' => $negatives > 0 ? $fp / $negatives : 0,
            ]);
        }

        $points = $points
            ->push(['threshold' => 0.0, 'tpr' => 1.0, 'fpr' => 1.0])
            ->push(['threshold' => 1.0, 'tpr' => 0.0, 'fpr' => 0.0])
            ->unique(fn ($point) => number_format($point['fpr'], 5) . '-' . number_format($point['tpr'], 5))
            ->sortBy('fpr')
            ->values();

        $auc = 0.0;
        for ($i = 1; $i < $points->count(); $i++) {
            $previous = $points[$i - 1];
            $current = $points[$i];
            $auc += ($current['fpr'] - $previous['fpr']) * (($current['tpr'] + $previous['tpr']) / 2);
        }

        $bestPoint = $points
            ->sortByDesc(fn ($point) => $point['tpr'] - $point['fpr'])
            ->first();

        $svgWidth = 420;
        $svgHeight = 250;
        $padding = 24;
        $plotWidth = $svgWidth - ($padding * 2);
        $plotHeight = $svgHeight - ($padding * 2);

        $linePoints = $points->map(function ($point) use ($padding, $plotWidth, $plotHeight) {
            $x = $padding + ($point['fpr'] * $plotWidth);
            $y = $padding + ((1 - $point['tpr']) * $plotHeight);

            return round($x, 2) . ',' . round($y, 2);
        })->implode(' ');

        $areaPoints = $linePoints . ' ' . ($padding + $plotWidth) . ',' . ($padding + $plotHeight) . ' ' . $padding . ',' . ($padding + $plotHeight);

        return [
            'auc' => round($auc, 3),
            'points' => $points->map(fn ($point) => [
                'threshold' => round($point['threshold'], 3),
                'tpr' => round($point['tpr'], 3),
                'fpr' => round($point['fpr'], 3),
            ])->all(),
            'line_points' => $linePoints,
            'area_points' => $areaPoints,
            'best_threshold' => round($bestPoint['threshold'] ?? 0.5, 3),
            'best_tpr' => round($bestPoint['tpr'] ?? 0, 3),
            'best_fpr' => round($bestPoint['fpr'] ?? 0, 3),
            'svg' => [
                'width' => $svgWidth,
                'height' => $svgHeight,
                'padding' => $padding,
                'plot_width' => $plotWidth,
                'plot_height' => $plotHeight,
                'best_x' => round($padding + (($bestPoint['fpr'] ?? 0) * $plotWidth), 2),
                'best_y' => round($padding + ((1 - ($bestPoint['tpr'] ?? 0)) * $plotHeight), 2),
            ],
        ];
    }

    private function applyDateRange($query, ?Carbon $from, ?Carbon $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }

    private function buildStatisticsPdf(array $data): string
    {
        $blue = [0.04, 0.55, 0.80];
        $green = [0.08, 0.62, 0.42];
        $orange = [0.93, 0.55, 0.10];
        $red = [0.86, 0.18, 0.22];
        $gray = [0.30, 0.36, 0.45];
        $light = [0.95, 0.98, 1.00];

        $page1 = '';
        $this->drawText($page1, 50, 805, 'REPORTE DE ESTADISTICAS ECG', 18, $blue);
        $this->drawText($page1, 50, 784, 'Generado: ' . $data['generated_at'] . '   |   Periodo: ' . $data['period'], 10, $gray);
        $this->drawLine($page1, 50, 772, 545, 772, [0.82, 0.88, 0.92]);

        $this->drawMetricCard($page1, 50, 690, 112, 58, 'Total', number_format($data['total']), 'analisis', $blue);
        $this->drawMetricCard($page1, 174, 690, 112, 58, 'Normales', number_format($data['normales']), number_format($data['pct_normal'], 1) . '%', $green);
        $this->drawMetricCard($page1, 298, 690, 112, 58, 'Arritmias', number_format($data['arritmias']), number_format($data['pct_arr'], 1) . '%', $orange);
        $this->drawMetricCard($page1, 422, 690, 123, 58, 'Confianza', number_format($data['avg_confidence'], 1) . '%', 'promedio', $blue);

        $this->drawText($page1, 50, 650, 'Comparacion de resultados IA', 13, [0.10, 0.14, 0.20]);
        $this->drawBarChart($page1, 50, 455, 235, 165, [
            ['label' => 'Normal', 'value' => $data['normales'], 'suffix' => ' casos', 'color' => $green],
            ['label' => 'Arritmia', 'value' => $data['arritmias'], 'suffix' => ' casos', 'color' => $orange],
        ], max($data['normales'], $data['arritmias'], 1));

        $this->drawText($page1, 320, 650, 'Cobertura de validacion medica', 13, [0.10, 0.14, 0.20]);
        $this->drawBarChart($page1, 320, 455, 225, 165, [
            ['label' => 'Revisados', 'value' => $data['reviewed_count'], 'suffix' => ' casos', 'color' => $blue],
            ['label' => 'Pendientes', 'value' => $data['unreviewed_count'], 'suffix' => ' casos', 'color' => $gray],
        ], max($data['reviewed_count'], $data['unreviewed_count'], 1));

        $this->drawRect($page1, 50, 335, 495, 82, $light, [0.82, 0.88, 0.92]);
        $this->drawText($page1, 68, 392, 'Lectura detallada del periodo', 12, [0.10, 0.14, 0.20]);
        $this->drawText($page1, 68, 372, 'La IA clasifico ' . number_format($data['normales']) . ' ECG como normales y ' . number_format($data['arritmias']) . ' como arritmias.', 10, $gray);
        $this->drawText($page1, 68, 356, 'La cobertura clinica revisada es ' . number_format($data['reviewed_rate'], 1) . '%, con confianza media revisada de ' . number_format($data['reviewed_avg_confidence'], 1) . '%.', 10, $gray);
        $this->drawText($page1, 68, 340, 'Este reporte compara volumen, validacion medica y rendimiento del clasificador en el rango seleccionado.', 10, $gray);

        $this->drawFooter($page1, 1);

        $page2 = '';
        $this->drawText($page2, 50, 805, 'VALIDACION CLINICA Y RENDIMIENTO', 18, $blue);
        $this->drawText($page2, 50, 784, 'Matriz de confusion y metricas comparativas del modelo.', 10, $gray);
        $this->drawLine($page2, 50, 772, 545, 772, [0.82, 0.88, 0.92]);

        $real = $data['real_metrics'];
        if ($real) {
            $this->drawText($page2, 50, 735, 'Matriz de confusion', 13, [0.10, 0.14, 0.20]);
            $this->drawConfusionCell($page2, 50, 640, 110, 62, 'Verdadero +', $real['tp'], $green);
            $this->drawConfusionCell($page2, 170, 640, 110, 62, 'Falso -', $real['fn'], $red);
            $this->drawConfusionCell($page2, 50, 565, 110, 62, 'Falso +', $real['fp'], $orange);
            $this->drawConfusionCell($page2, 170, 565, 110, 62, 'Verdadero -', $real['tn'], $blue);

            $this->drawText($page2, 320, 735, 'Metricas de rendimiento', 13, [0.10, 0.14, 0.20]);
            $this->drawHorizontalBars($page2, 320, 575, 220, [
                ['label' => 'Sensibilidad', 'value' => $real['sensitivity'], 'color' => $green],
                ['label' => 'Especificidad', 'value' => $real['specificity'], 'color' => $blue],
                ['label' => 'Precision', 'value' => $real['precision'], 'color' => [0.48, 0.28, 0.78]],
                ['label' => 'Exactitud', 'value' => $real['accuracy'], 'color' => $orange],
                ['label' => 'F1-Score', 'value' => $real['f1'], 'color' => [0.18, 0.45, 0.72]],
            ]);
        } else {
            $this->drawText($page2, 50, 720, 'No hay revisiones medicas suficientes para calcular la matriz de confusion.', 11, $gray);
        }

        $this->drawRect($page2, 50, 390, 235, 115, $light, [0.82, 0.88, 0.92]);
        $this->drawText($page2, 68, 478, 'Curva ROC', 13, [0.10, 0.14, 0.20]);
        if ($data['roc_data']) {
            $this->drawText($page2, 68, 452, 'AUC-ROC: ' . number_format($data['roc_data']['auc'], 3), 12, $blue);
            $this->drawText($page2, 68, 430, 'Umbral optimo: ' . number_format($data['roc_data']['best_threshold'] * 100, 1) . '%', 11, $gray);
            $this->drawText($page2, 68, 410, 'Evalua la capacidad del score para separar arritmia y ritmo normal.', 9, $gray);
        } else {
            $this->drawText($page2, 68, 448, 'ROC no disponible en este rango.', 11, $gray);
            $this->drawText($page2, 68, 428, 'Se necesitan casos revisados de ambas clases.', 10, $gray);
        }

        $this->drawRect($page2, 310, 390, 235, 115, [1.00, 0.98, 0.93], [0.90, 0.82, 0.66]);
        $this->drawText($page2, 328, 478, 'Interpretacion', 13, [0.10, 0.14, 0.20]);
        $this->drawText($page2, 328, 452, 'La estadistica resume coincidencia entre IA y medico.', 10, $gray);
        $this->drawText($page2, 328, 432, 'Los falsos positivos indican alarmas innecesarias.', 10, $gray);
        $this->drawText($page2, 328, 412, 'Los falsos negativos requieren especial seguimiento clinico.', 10, $gray);

        $this->drawText($page2, 50, 340, 'Nota clinica', 12, [0.10, 0.14, 0.20]);
        $this->drawText($page2, 50, 318, 'Este reporte es una herramienta de apoyo para lectura del ECG y no sustituye la evaluacion del medico especialista.', 10, $gray);

        $this->drawFooter($page2, 2);

        return $this->buildPdfDocument([$page1, $page2]);
    }

    private function drawMetricCard(string &$content, float $x, float $y, float $w, float $h, string $title, string $value, string $subtitle, array $color): void
    {
        $this->drawRect($content, $x, $y, $w, $h, [0.97, 0.99, 1.00], [0.82, 0.88, 0.92]);
        $this->drawText($content, $x + 10, $y + 38, $title, 9, [0.30, 0.36, 0.45]);
        $this->drawText($content, $x + 10, $y + 19, $value, 16, $color);
        $this->drawText($content, $x + 10, $y + 8, $subtitle, 8, [0.38, 0.45, 0.54]);
    }

    private function drawBarChart(string &$content, float $x, float $y, float $w, float $h, array $items, float $max): void
    {
        $this->drawLine($content, $x, $y, $x + $w, $y, [0.70, 0.76, 0.82]);
        $barWidth = 54;
        $gap = ($w - ($barWidth * count($items))) / max(count($items) + 1, 1);

        foreach ($items as $index => $item) {
            $barHeight = $max > 0 ? (($item['value'] / $max) * ($h - 35)) : 0;
            $barX = $x + $gap + (($barWidth + $gap) * $index);
            $barY = $y + 18;
            $this->drawRect($content, $barX, $barY, $barWidth, $barHeight, $item['color']);
            $this->drawText($content, $barX + 4, $barY + $barHeight + 10, number_format($item['value']) . $item['suffix'], 9, [0.10, 0.14, 0.20]);
            $this->drawText($content, $barX + 1, $y + 3, $item['label'], 9, [0.30, 0.36, 0.45]);
        }
    }

    private function drawConfusionCell(string &$content, float $x, float $y, float $w, float $h, string $label, int $value, array $color): void
    {
        $this->drawRect($content, $x, $y, $w, $h, [0.98, 0.99, 1.00], $color);
        $this->drawText($content, $x + 10, $y + 39, $label, 10, [0.30, 0.36, 0.45]);
        $this->drawText($content, $x + 10, $y + 15, number_format($value), 20, $color);
    }

    private function drawHorizontalBars(string &$content, float $x, float $y, float $w, array $items): void
    {
        foreach ($items as $index => $item) {
            $rowY = $y + ((count($items) - $index - 1) * 30);
            $this->drawText($content, $x, $rowY + 10, $item['label'], 9, [0.30, 0.36, 0.45]);
            $this->drawRect($content, $x + 78, $rowY + 8, $w - 118, 9, [0.88, 0.92, 0.95]);
            $this->drawRect($content, $x + 78, $rowY + 8, (($w - 118) * min(max($item['value'], 0), 100)) / 100, 9, $item['color']);
            $this->drawText($content, $x + $w - 32, $rowY + 7, number_format($item['value'], 1) . '%', 9, [0.10, 0.14, 0.20]);
        }
    }

    private function drawFooter(string &$content, int $page): void
    {
        $this->drawLine($content, 50, 42, 545, 42, [0.82, 0.88, 0.92]);
        $this->drawText($content, 50, 25, 'ECG Analyzer - Reporte generado automaticamente', 8, [0.45, 0.50, 0.58]);
        $this->drawText($content, 510, 25, 'Pagina ' . $page, 8, [0.45, 0.50, 0.58]);
    }

    private function drawText(string &$content, float $x, float $y, string $text, int $size, array $color): void
    {
        $content .= "BT\n";
        $content .= sprintf('%.3F %.3F %.3F rg', $color[0], $color[1], $color[2]) . "\n";
        $content .= "/F1 {$size} Tf\n";
        $content .= sprintf('%.2F %.2F Td', $x, $y) . "\n";
        $content .= '(' . $this->pdfText($text) . ") Tj\n";
        $content .= "ET\n";
    }

    private function drawRect(string &$content, float $x, float $y, float $w, float $h, array $fill, ?array $stroke = null): void
    {
        $content .= "q\n";
        $content .= sprintf('%.3F %.3F %.3F rg', $fill[0], $fill[1], $fill[2]) . "\n";
        if ($stroke) {
            $content .= sprintf('%.3F %.3F %.3F RG', $stroke[0], $stroke[1], $stroke[2]) . "\n";
            $content .= sprintf('%.2F %.2F %.2F %.2F re B', $x, $y, max($w, 0), max($h, 0)) . "\n";
        } else {
            $content .= sprintf('%.2F %.2F %.2F %.2F re f', $x, $y, max($w, 0), max($h, 0)) . "\n";
        }
        $content .= "Q\n";
    }

    private function drawLine(string &$content, float $x1, float $y1, float $x2, float $y2, array $color): void
    {
        $content .= "q\n";
        $content .= sprintf('%.3F %.3F %.3F RG', $color[0], $color[1], $color[2]) . "\n";
        $content .= sprintf('%.2F %.2F m %.2F %.2F l S', $x1, $y1, $x2, $y2) . "\n";
        $content .= "Q\n";
    }

    private function buildPdfDocument(array $pages): string
    {
        $objects = [
            1 => "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            3 => "3 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n",
        ];
        $kids = [];

        foreach (array_values($pages) as $index => $content) {
            $pageObj = 4 + ($index * 2);
            $contentObj = $pageObj + 1;
            $kids[] = $pageObj . ' 0 R';
            $objects[$pageObj] = "{$pageObj} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentObj} 0 R >>\nendobj\n";
            $objects[$contentObj] = "{$contentObj} 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream\nendobj\n";
        }

        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $kids) . '] /Count ' . count($pages) . " >>\nendobj\n";
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        $maxObject = max(array_keys($objects));

        for ($i = 1; $i <= $maxObject; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $objects[$i];
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . ($maxObject + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $maxObject; $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . ($maxObject + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function pdfText(string $text): string
    {
        $encoded = mb_convert_encoding($text, 'Windows-1252', 'UTF-8');

        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $encoded
        );
    }
}

````

## sistema/app/Http/Controllers/ControladorReportes.php

````php
<?php

namespace App\Http\Controllers;

use App\Models\AnalisisEcg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class ControladorReportes extends Controlador
{
    public function index()
    {
        $userId = Session::get('user.id');

        $patients = AnalisisEcg::query()
            ->where('user_id', $userId)
            ->whereNotNull('patient_identifier')
            ->select('patient_identifier')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('MAX(created_at) as last_analysis')
            ->groupBy('patient_identifier')
            ->orderBy('patient_identifier')
            ->get();

        $stats = [
            'patients' => $patients->count(),
            'analyses' => AnalisisEcg::query()->where('user_id', $userId)->count(),
        ];

        return view('reportes', compact('patients', 'stats'));
    }

    public function download(Request $request)
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:all,selected'],
            'patients' => ['nullable', 'array'],
            'patients.*' => ['string'],
        ]);

        $selectedPatients = $validated['patients'] ?? [];
        if ($validated['mode'] === 'selected' && empty($selectedPatients)) {
            return back()->with('error', 'Selecciona al menos un paciente para generar el reporte.');
        }

        $userId = Session::get('user.id');

        $query = AnalisisEcg::query()
            ->where('user_id', $userId)
            ->orderBy('patient_identifier')
            ->orderBy('created_at');

        if ($validated['mode'] === 'selected') {
            $query->whereIn('patient_identifier', $selectedPatients);
        }

        $rows = $query->get();
        $filename = 'reporte_pacientes_' . now()->format('Ymd_His') . '.xlsx';
        $path = $this->buildExcelReport($rows);

        return Response::download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function buildExcelReport($rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'ecg_report_');
        if ($path === false) {
            throw new RuntimeException('No se pudo crear el archivo temporal del reporte.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo generar el archivo Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($rows));
        $zip->close();

        return $path;
    }

    private function worksheetXml($rows): string
    {
        $headers = [
            'Paciente',
            'Archivo',
            'Fecha',
            'Hora',
            'Ritmo detectado',
            'Probabilidad',
            'Estado IA',
            'Valoracion medica',
            'Diagnostico medico',
            'Notas medicas',
        ];

        $sheetRows = [$headers];
        foreach ($rows as $row) {
            $sheetRows[] = [
                $row->patient_identifier,
                $row->filename,
                $row->created_at?->format('Y-m-d'),
                $row->created_at?->format('H:i'),
                $row->label,
                number_format((float) $row->confidence, 1, '.', '') . '%',
                $row->type === 'normal' ? 'Normal' : 'Arritmia',
                $row->doctor_result
                    ? ($row->doctor_result === 'normal' ? 'Normal' : 'Arritmia')
                    : 'Sin valorar',
                $row->doctor_label ?? '',
                $row->doctor_notes ?? '',
            ];
        }

        $xmlRows = '';
        foreach ($sheetRows as $rowIndex => $values) {
            $rowNumber = $rowIndex + 1;
            $xmlRows .= '<row r="' . $rowNumber . '">';
            foreach ($values as $columnIndex => $value) {
                $cell = $this->columnName($columnIndex + 1) . $rowNumber;
                $style = $rowNumber === 1 ? ' s="1"' : '';
                $xmlRows .= '<c r="' . $cell . '" t="inlineStr"' . $style . '><is><t>' . $this->xml((string) $value) . '</t></is></c>';
            }
            $xmlRows .= '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<cols>'
            . '<col min="1" max="1" width="18" customWidth="1"/>'
            . '<col min="2" max="2" width="22" customWidth="1"/>'
            . '<col min="3" max="4" width="12" customWidth="1"/>'
            . '<col min="5" max="10" width="24" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . $xmlRows . '</sheetData>'
            . '</worksheet>';
    }

    private function columnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Reporte ECG" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}

````

## sistema/app/Http/Middleware/MiddlewareAutenticacion.php

````php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class MiddlewareAutenticacion
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Session::has('user')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

````

## sistema/app/Models/AnalisisEcg.php

````php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalisisEcg extends Model
{
    protected $table = 'ecg_analyses';

    protected $fillable = [
        'user_id',
        'filename',
        'patient_identifier',
        'patient_age',
        'patient_sex',
        'patient_weight',
        'label',
        'label_code',
        'type',
        'confidence',
        'top_predictions',
        'doctor_result',
        'doctor_label',
        'doctor_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'top_predictions' => 'array',
        'confidence'      => 'float',
        'patient_age'     => 'float',
        'patient_weight'  => 'float',
        'patient_sex'     => 'integer',
        'reviewed_at'     => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    /** Alias legible del sexo */
    public function getSexLabelAttribute(): string
    {
        return $this->patient_sex === 1 ? 'Masculino' : 'Femenino';
    }
}

````

## sistema/app/Models/Usuario.php

````php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    protected $table = 'users';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function analisisEcg()
    {
        return $this->hasMany(AnalisisEcg::class, 'user_id');
    }
}

````

## sistema/app/Providers/AppServiceProvider.php

````php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

````

## sistema/config/app.php

````php
<?php

return [

    'name' => env('APP_NAME', 'Laravel'),


    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => 'UTC',

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],
    
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];

````

## sistema/config/auth.php

````php
<?php

use App\Models\Usuario;

return [


    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],


    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', Usuario::class),
        ],
    ],


    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];

````

## sistema/config/database.php

````php
<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

return [


    'default' => env('DB_CONNECTION', 'sqlite'),


    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],



    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],



    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];

````

## sistema/config/filesystems.php

````php
<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),


    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

````

## sistema/config/session.php

````php
<?php

use Illuminate\Support\Str;

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    'encrypt' => env('SESSION_ENCRYPT', false),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    'secure' => env('SESSION_SECURE_COOKIE'),

    'http_only' => env('SESSION_HTTP_ONLY', true),

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];

````

## sistema/database/bd_arritmias_mysql.sql

````sql
-- =====================================================
-- BASE DE DATOS: Sistema de Clasificacion de Arritmias
-- Ubicacion actual del sistema Laravel: sistema/
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS bd_arritmias CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bd_arritmias;

DROP TABLE IF EXISTS reportes, diagnosticos, predicciones, imagenes, estudios, pacientes, codigo_pacientes, ritmo_cardiacos, clasificacion_arritmias, nivel_gravedades, grupo_cardiacos, usuarios, roles;
DROP TABLE IF EXISTS ecg_analyses, users, password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs, migrations;

SET FOREIGN_KEY_CHECKS = 1;

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
    usuario_id INT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pacientes_codigo FOREIGN KEY (codigo_id) REFERENCES codigo_pacientes(codigo_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pacientes_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_pacientes_sexo CHECK (sexo IN ('M', 'F'))
) ENGINE=InnoDB;

CREATE TABLE estudios (
    estudio_id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    usuario_id INT NULL,
    legacy_analysis_id BIGINT UNSIGNED NULL,
    observaciones TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudios_pacientes FOREIGN KEY (paciente_id) REFERENCES pacientes(paciente_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_estudios_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON UPDATE CASCADE ON DELETE SET NULL
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

-- Tablas de compatibilidad con Laravel actual.
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
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

````

## sistema/database/generar_mysql_bd_arritmias.php

````php
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
$sql[] = 'DROP TABLE IF EXISTS reportes, diagnosticos, predicciones, imagenes, estudios, pacientes, codigo_pacientes, ritmo_cardiacos, clasificacion_arritmias, nivel_gravedades, grupo_cardiacos, usuarios, roles;';
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
    usuario_id INT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pacientes_codigo FOREIGN KEY (codigo_id) REFERENCES codigo_pacientes(codigo_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pacientes_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_pacientes_sexo CHECK (sexo IN ('M', 'F'))
) ENGINE=InnoDB;

CREATE TABLE estudios (
    estudio_id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    usuario_id INT NULL,
    legacy_analysis_id BIGINT UNSIGNED NULL,
    observaciones TEXT,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudios_pacientes FOREIGN KEY (paciente_id) REFERENCES pacientes(paciente_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_estudios_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON UPDATE CASCADE ON DELETE SET NULL
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

-- Tablas de compatibilidad con Laravel actual.
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
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
$sql[] = "INSERT INTO roles (role_id, nombre, descripcion, estado, created_at, updated_at) VALUES (1, 'Administrador', 'Rol administrativo migrado desde SQLite', 1, NOW(), NOW());";

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
            sql_value($user['name']),
            sql_value($user['email']),
            sql_value($user['email_verified_at']),
            sql_value($user['password']),
            sql_value($user['remember_token']),
            sql_value($user['created_at']),
            sql_value($user['updated_at']),
        ]);
    }
    $sql[] = 'INSERT INTO users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) VALUES';
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

````

## sistema/resources/views/plantillas/aplicacion.blade.php

````blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>ECG Analyzer - @yield('title', 'Panel')</title>
    @vite(['resources/css/aplicacion.css', 'resources/js/aplicacion.js'])
</head>
<body class="bg-background min-h-screen pb-16" x-data="{ showClinicalDisclaimer: false }">

    @include('parciales.barra-lateral')

    <main class="lg:ml-64 min-h-screen">
        <header class="sticky top-0 z-30 border-b border-border"
                style="background:hsl(var(--background)/0.8);backdrop-filter:blur(12px);">
            <div class="px-4 lg:px-8 py-4 lg:py-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="pt-12 lg:pt-0">
                        @yield('page-header')
                    </div>
                    <div class="flex items-center gap-2">
                        @yield('header-actions')
                    </div>
                </div>
            </div>
        </header>

        <div class="px-4 lg:px-8 py-6 lg:py-8">
            @yield('content')
        </div>
    </main>

    @include('parciales.pie-clinico', ['withSidebar' => true])

</body>
</html>

````

## sistema/resources/views/plantillas/invitado.blade.php

````blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>ECG Analyzer - @yield('title', 'Acceso')</title>
    @vite(['resources/css/aplicacion.css', 'resources/js/aplicacion.js'])
</head>
<body class="bg-background min-h-screen pb-16" x-data="{ showClinicalDisclaimer: false }">
    @yield('content')

    @include('parciales.pie-clinico', ['withSidebar' => false])
</body>
</html>

````

## sistema/resources/views/parciales/barra-lateral.blade.php

````blade
@php $user = session('user'); @endphp

<div x-data="{ open: false }">

    {{-- BotÃ³n hamburguesa (mÃ³vil) --}}
    <button @click="open = !open"
            class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg bg-card border border-border hover:bg-secondary transition-colors">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Overlay mÃ³vil --}}
    <div x-show="open" @click="open = false"
         class="lg:hidden fixed inset-0 z-40"
         style="background:hsl(var(--background)/0.8);backdrop-filter:blur(4px);"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    {{-- Sidebar --}}
    <aside class="sidebar"
           :class="open ? '' : 'sidebar-hidden'">

        {{-- Logo --}}
        <div class="p-6 border-b border-sidebar-border">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary animate-heartbeat"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                    </svg>
                    <div class="absolute inset-0 rounded-full"
                         style="background:hsl(var(--primary)/0.2);filter:blur(12px);"></div>
                </div>
                <div>
                    <h1 class="text-xl font-bold gradient-text">ECG Analyzer</h1>
                    <p class="text-xs text-muted-foreground">AnÃ¡lisis Inteligente</p>
                </div>
            </div>
        </div>

        {{-- Usuario --}}
        <div class="px-6 py-4 border-b border-sidebar-border">
            <p class="text-sm text-muted-foreground">SesiÃ³n activa</p>
            <p class="text-sm font-medium truncate">{{ $user['email'] ?? '' }}</p>
        </div>

        {{-- NavegaciÃ³n --}}
        <nav class="flex-1 p-4 space-y-2">
            @php
                $navItems = [
                    ['title' => 'Dashboard',           'route' => 'dashboard', 'icon' => 'chart'],
                    ['title' => 'Resumen',             'route' => 'resumen',   'icon' => 'home'],
                    ['title' => 'Subir ECG',           'route' => 'upload',    'icon' => 'upload'],
                    ['title' => 'Historial','route' => 'history',   'icon' => 'file-text'],
                    ['title' => 'Reportes',            'route' => 'reports',   'icon' => 'report'],
                ];
            @endphp

            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   @click="open = false"
                   class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">

                    @if($item['icon'] === 'home')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9a2 2 0 00-2-2h-2" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 3h6v4H9zM9 12h6M9 16h4" />
                        </svg>
                    @elseif($item['icon'] === 'upload')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    @elseif($item['icon'] === 'chart')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2
                                     a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14
                                     a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    @elseif($item['icon'] === 'report')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17h6M9 13h6m-6-4h3m5 12H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M14 3v5h5" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                     a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    @endif

                    <span>{{ $item['title'] }}</span>
                </a>
            @endforeach
        </nav>

        {{-- Logout --}}
        <div class="p-4 border-t border-sidebar-border">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-3 w-full px-4 py-3 rounded-lg text-destructive hover:bg-destructive/10 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6
                                 a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="font-medium">Cerrar sesiÃ³n</span>
                </button>
            </form>
        </div>

    </aside>
</div>

````

## sistema/resources/views/parciales/pie-clinico.blade.php

````blade
@php($withSidebar = $withSidebar ?? false)

<footer class="fixed bottom-0 left-0 right-0 z-40 border-t border-border px-3 lg:px-5 py-2"
        style="background:hsl(var(--card)/0.96);backdrop-filter:blur(10px);">
    <div class="{{ $withSidebar ? 'lg:ml-64' : '' }} flex items-center justify-center gap-2 text-center flex-wrap">
        <p class="text-[11px] leading-4 text-muted-foreground">
            Herramienta de apoyo. No reemplaza la evaluacion ni el criterio del medico cardiologo.
        </p>

        <a href="#"
           @click.prevent="showClinicalDisclaimer = true"
           class="text-[11px] font-medium text-primary underline underline-offset-2 transition-opacity hover:opacity-70">
            Mas informacion
        </a>
    </div>
</footer>

<div x-show="showClinicalDisclaimer"
     x-transition.opacity
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;">
    <div class="absolute inset-0 bg-black/40" @click="showClinicalDisclaimer = false"></div>

    <div class="relative w-full max-w-2xl rounded-2xl border border-border px-8 py-7 shadow-elevated"
         style="background:hsl(var(--card));">
        <div class="relative mb-6 border-b border-border pb-5 text-center">
            <div class="mx-auto max-w-xl">
                <h2 class="text-xl font-bold tracking-wide text-foreground">IMPORTANTE</h2>
                <p class="text-sm leading-6 text-muted-foreground mt-3">
                    El sistema ayuda a apoyar la lectura del ECG, pero no sustituye la decisiÃ³n clÃ­nica final.
                </p>
            </div>

            <button type="button"
                    @click="showClinicalDisclaimer = false"
                    class="absolute right-0 top-0 inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border hover:bg-secondary transition-colors">
                <span class="sr-only">Cerrar</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <ol class="mx-auto max-w-xl space-y-4 text-sm leading-7 text-muted-foreground text-justify">
            <li>
                <span class="font-semibold text-foreground">1.</span>
                Este sistema utiliza tÃ©cnicas de inteligencia artificial, especÃ­ficamente redes neuronales, para analizar electrocardiogramas (ECG) y apoyar en la clasificaciÃ³n de posibles arritmias cardÃ­acas.
            </li>
            <li>
                <span class="font-semibold text-foreground">2.</span>
                El modelo ha sido entrenado con un conjunto de datos clÃ­nicos previamente etiquetados (PTB-XL), lo que le permite reconocer patrones en seÃ±ales ECG y sugerir una posible clasificaciÃ³n.
            </li>
            <li>
                <span class="font-semibold text-foreground">3.</span>
                Este sistema es una herramienta de apoyo y puede presentar errores; los resultados deben ser siempre interpretados por un mÃ©dico especialista.
            </li>
            <li>
                <span class="font-semibold text-foreground">4.</span>
                EstÃ¡ orientado a apoyar al personal de salud en la evaluaciÃ³n preliminar de electrocardiogramas y agilizar el proceso de anÃ¡lisis.
            </li>
            <li>
                <span class="font-semibold text-foreground">5.</span>
                Este sistema forma parte de un proyecto de investigaciÃ³n acadÃ©mica sobre el uso de redes neuronales en el diagnÃ³stico de arritmias cardÃ­acas.
            </li>
        </ol>

        <div class="mt-6 flex justify-center">
            <button type="button"
                    @click="showClinicalDisclaimer = false"
                    class="btn-primary">
                Cerrar
            </button>
        </div>
    </div>
</div>

````

## sistema/resources/views/autenticacion/inicio-sesion.blade.php

````blade
@extends('plantillas.invitado')

@section('title', 'Iniciar sesiÃ³n')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    {{-- Fondo animado --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" style="opacity:0.2;">
        <div class="absolute inset-0 medical-grid"></div>
        <svg class="absolute w-full h-32" style="bottom:25%;"
             viewBox="0 0 1200 100" preserveAspectRatio="none">
            <path d="M0,50 L100,50 L120,50 L140,20 L160,80 L180,50 L200,50 L220,50 L240,50
                     L260,10 L280,90 L300,50 L320,50 L400,50 L420,50 L440,20 L460,80 L480,50
                     L500,50 L520,50 L540,50 L560,10 L580,90 L600,50 L620,50 L700,50 L720,50
                     L740,20 L760,80 L780,50 L800,50 L820,50 L840,50 L860,10 L880,90 L900,50
                     L920,50 L1000,50 L1020,50 L1040,20 L1060,80 L1080,50 L1100,50 L1120,50
                     L1140,50 L1160,10 L1180,90 L1200,50"
                  fill="none" stroke="hsl(var(--primary))" stroke-width="2"
                  class="ecg-line" />
        </svg>
        <div class="absolute top-1/2 left-1/2 w-[800px] h-[800px] rounded-full"
             style="transform:translate(-50%,-50%);
                    background:radial-gradient(ellipse at center,hsl(var(--primary)/0.08) 0%,transparent 70%);"></div>
    </div>

    {{-- Tarjeta de login --}}
    <div class="relative w-full max-w-md animate-fade-in-up">
        <div class="absolute -inset-1 rounded-2xl"
             style="background:linear-gradient(135deg,hsl(var(--primary)/0.2),hsl(var(--primary)/0.1),hsl(var(--primary)/0.2));
                    filter:blur(16px);opacity:0.6;"></div>

        <div class="relative glass rounded-2xl p-8 shadow-elevated">

            {{-- Header --}}
            <div class="text-center mb-8 animate-fade-in-delay-1">
                <div class="inline-flex items-center justify-center mb-4">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-16 w-16 text-primary animate-heartbeat"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                        </svg>
                        <div class="absolute inset-0 rounded-full"
                             style="background:hsl(var(--primary)/0.3);filter:blur(20px);"></div>
                    </div>
                </div>
                <h1 class="text-3xl font-bold gradient-text mb-2">ECG Analyzer</h1>
                <p class="text-muted-foreground">Sistema de AnÃ¡lisis de Electrocardiogramas</p>
            </div>

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login.post') }}"
                  x-data="loginForm()" @submit.prevent="handleSubmit">
                @csrf

                {{-- Email --}}
                <div class="mb-5 animate-fade-in-delay-2">
                    <label for="email" class="block text-sm font-medium text-foreground mb-2">
                        Correo electrÃ³nico
                    </label>
                    <div class="input-with-icon">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="input-icon"
                             width="20" height="20"
                             aria-hidden="true"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7
                                     a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <input id="email" name="email" type="email"
                               value="{{ old('email') }}"
                               placeholder="doctor@hospital.com"
                               class="input-field"
                               :disabled="loading"
                               autocomplete="email" />
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ContraseÃ±a --}}
                <div class="mb-5 animate-fade-in-delay-3">
                    <label for="password" class="block text-sm font-medium text-foreground mb-2">
                        ContraseÃ±a
                    </label>
                    <div class="input-with-icon">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="input-icon"
                             width="20" height="20"
                             aria-hidden="true"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6
                                     a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <input id="password" name="password" type="password"
                               placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢"
                               class="input-field"
                               :disabled="loading"
                               autocomplete="current-password" />
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Error general --}}
                @if(session('error'))
                    <div class="alert-error mb-5 animate-fade-in">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{-- BotÃ³n --}}
                <button type="submit"
                        :disabled="loading"
                        class="btn-primary w-full glow-cyan animate-fade-in-delay-4">
                    <template x-if="loading">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                 fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Ingresando...
                        </span>
                    </template>
                    <template x-if="!loading">
                        <span>Ingresar</span>
                    </template>
                </button>

            </form>

            <p class="mt-6 text-center text-xs text-muted-foreground animate-fade-in-delay-4">
                Sistema de diagnÃ³stico asistido por IA para profesionales de la salud
            </p>
        </div>
    </div>
</div>

<script>
function loginForm() {
    return {
        loading: false,
        handleSubmit(e) {
            this.loading = true;
            e.target.submit();
        }
    }
}
</script>
@endsection

````

## sistema/resources/views/bienvenida.blade.php

````blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/aplicacion.css', 'resources/js/aplicacion.js'])
        @else
            <style>
                /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */@layer theme{:root,:host{--font-sans:'Instrument Sans',ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";--font-serif:ui-serif,Georgia,Cambria,"Times New Roman",Times,serif;--font-mono:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;--color-red-50:oklch(.971 .013 17.38);--color-red-100:oklch(.936 .032 17.717);--color-red-200:oklch(.885 .062 18.334);--color-red-300:oklch(.808 .114 19.571);--color-red-400:oklch(.704 .191 22.216);--color-red-500:oklch(.637 .237 25.331);--color-red-600:oklch(.577 .245 27.325);--color-red-700:oklch(.505 .213 27.518);--color-red-800:oklch(.444 .177 26.899);--color-red-900:oklch(.396 .141 25.723);--color-red-950:oklch(.258 .092 26.042);--color-orange-50:oklch(.98 .016 73.684);--color-orange-100:oklch(.954 .038 75.164);--color-orange-200:oklch(.901 .076 70.697);--color-orange-300:oklch(.837 .128 66.29);--color-orange-400:oklch(.75 .183 55.934);--color-orange-500:oklch(.705 .213 47.604);--color-orange-600:oklch(.646 .222 41.116);--color-orange-700:oklch(.553 .195 38.402);--color-orange-800:oklch(.47 .157 37.304);--color-orange-900:oklch(.408 .123 38.172);--color-orange-950:oklch(.266 .079 36.259);--color-amber-50:oklch(.987 .022 95.277);--color-amber-100:oklch(.962 .059 95.617);--color-amber-200:oklch(.924 .12 95.746);--color-amber-300:oklch(.879 .169 91.605);--color-amber-400:oklch(.828 .189 84.429);--color-amber-500:oklch(.769 .188 70.08);--color-amber-600:oklch(.666 .179 58.318);--color-amber-700:oklch(.555 .163 48.998);--color-amber-800:oklch(.473 .137 46.201);--color-amber-900:oklch(.414 .112 45.904);--color-amber-950:oklch(.279 .077 45.635);--color-yellow-50:oklch(.987 .026 102.212);--color-yellow-100:oklch(.973 .071 103.193);--color-yellow-200:oklch(.945 .129 101.54);--color-yellow-300:oklch(.905 .182 98.111);--color-yellow-400:oklch(.852 .199 91.936);--color-yellow-500:oklch(.795 .184 86.047);--color-yellow-600:oklch(.681 .162 75.834);--color-yellow-700:oklch(.554 .135 66.442);--color-yellow-800:oklch(.476 .114 61.907);--color-yellow-900:oklch(.421 .095 57.708);--color-yellow-950:oklch(.286 .066 53.813);--color-lime-50:oklch(.986 .031 120.757);--color-lime-100:oklch(.967 .067 122.328);--color-lime-200:oklch(.938 .127 124.321);--color-lime-300:oklch(.897 .196 126.665);--color-lime-400:oklch(.841 .238 128.85);--color-lime-500:oklch(.768 .233 130.85);--color-lime-600:oklch(.648 .2 131.684);--color-lime-700:oklch(.532 .157 131.589);--color-lime-800:oklch(.453 .124 130.933);--color-lime-900:oklch(.405 .101 131.063);--color-lime-950:oklch(.274 .072 132.109);--color-green-50:oklch(.982 .018 155.826);--color-green-100:oklch(.962 .044 156.743);--color-green-200:oklch(.925 .084 155.995);--color-green-300:oklch(.871 .15 154.449);--color-green-400:oklch(.792 .209 151.711);--color-green-500:oklch(.723 .219 149.579);--color-green-600:oklch(.627 .194 149.214);--color-green-700:oklch(.527 .154 150.069);--color-green-800:oklch(.448 .119 151.328);--color-green-900:oklch(.393 .095 152.535);--color-green-950:oklch(.266 .065 152.934);--color-emerald-50:oklch(.979 .021 166.113);--color-emerald-100:oklch(.95 .052 163.051);--color-emerald-200:oklch(.905 .093 164.15);--color-emerald-300:oklch(.845 .143 164.978);--color-emerald-400:oklch(.765 .177 163.223);--color-emerald-500:oklch(.696 .17 162.48);--color-emerald-600:oklch(.596 .145 163.225);--color-emerald-700:oklch(.508 .118 165.612);--color-emerald-800:oklch(.432 .095 166.913);--color-emerald-900:oklch(.378 .077 168.94);--color-emerald-950:oklch(.262 .051 172.552);--color-teal-50:oklch(.984 .014 180.72);--color-teal-100:oklch(.953 .051 180.801);--color-teal-200:oklch(.91 .096 180.426);--color-teal-300:oklch(.855 .138 181.071);--color-teal-400:oklch(.777 .152 181.912);--color-teal-500:oklch(.704 .14 182.503);--color-teal-600:oklch(.6 .118 184.704);--color-teal-700:oklch(.511 .096 186.391);--color-teal-800:oklch(.437 .078 188.216);--color-teal-900:oklch(.386 .063 188.416);--color-teal-950:oklch(.277 .046 192.524);--color-cyan-50:oklch(.984 .019 200.873);--color-cyan-100:oklch(.956 .045 203.388);--color-cyan-200:oklch(.917 .08 205.041);--color-cyan-300:oklch(.865 .127 207.078);--color-cyan-400:oklch(.789 .154 211.53);--color-cyan-500:oklch(.715 .143 215.221);--color-cyan-600:oklch(.609 .126 221.723);--color-cyan-700:oklch(.52 .105 223.128);--color-cyan-800:oklch(.45 .085 224.283);--color-cyan-900:oklch(.398 .07 227.392);--color-cyan-950:oklch(.302 .056 229.695);--color-sky-50:oklch(.977 .013 236.62);--color-sky-100:oklch(.951 .026 236.824);--color-sky-200:oklch(.901 .058 230.902);--color-sky-300:oklch(.828 .111 230.318);--color-sky-400:oklch(.746 .16 232.661);--color-sky-500:oklch(.685 .169 237.323);--color-sky-600:oklch(.588 .158 241.966);--color-sky-700:oklch(.5 .134 242.749);--color-sky-800:oklch(.443 .11 240.79);--color-sky-900:oklch(.391 .09 240.876);--color-sky-950:oklch(.293 .066 243.157);--color-blue-50:oklch(.97 .014 254.604);--color-blue-100:oklch(.932 .032 255.585);--color-blue-200:oklch(.882 .059 254.128);--color-blue-300:oklch(.809 .105 251.813);--color-blue-400:oklch(.707 .165 254.624);--color-blue-500:oklch(.623 .214 259.815);--color-blue-600:oklch(.546 .245 262.881);--color-blue-700:oklch(.488 .243 264.376);--color-blue-800:oklch(.424 .199 265.638);--color-blue-900:oklch(.379 .146 265.522);--color-blue-950:oklch(.282 .091 267.935);--color-indigo-50:oklch(.962 .018 272.314);--color-indigo-100:oklch(.93 .034 272.788);--color-indigo-200:oklch(.87 .065 274.039);--color-indigo-300:oklch(.785 .115 274.713);--color-indigo-400:oklch(.673 .182 276.935);--color-indigo-500:oklch(.585 .233 277.117);--color-indigo-600:oklch(.511 .262 276.966);--color-indigo-700:oklch(.457 .24 277.023);--color-indigo-800:oklch(.398 .195 277.366);--color-indigo-900:oklch(.359 .144 278.697);--color-indigo-950:oklch(.257 .09 281.288);--color-violet-50:oklch(.969 .016 293.756);--color-violet-100:oklch(.943 .029 294.588);--color-violet-200:oklch(.894 .057 293.283);--color-violet-300:oklch(.811 .111 293.571);--color-violet-400:oklch(.702 .183 293.541);--color-violet-500:oklch(.606 .25 292.717);--color-violet-600:oklch(.541 .281 293.009);--color-violet-700:oklch(.491 .27 292.581);--color-violet-800:oklch(.432 .232 292.759);--color-violet-900:oklch(.38 .189 293.745);--color-violet-950:oklch(.283 .141 291.089);--color-purple-50:oklch(.977 .014 308.299);--color-purple-100:oklch(.946 .033 307.174);--color-purple-200:oklch(.902 .063 306.703);--color-purple-300:oklch(.827 .119 306.383);--color-purple-400:oklch(.714 .203 305.504);--color-purple-500:oklch(.627 .265 303.9);--color-purple-600:oklch(.558 .288 302.321);--color-purple-700:oklch(.496 .265 301.924);--color-purple-800:oklch(.438 .218 303.724);--color-purple-900:oklch(.381 .176 304.987);--color-purple-950:oklch(.291 .149 302.717);--color-fuchsia-50:oklch(.977 .017 320.058);--color-fuchsia-100:oklch(.952 .037 318.852);--color-fuchsia-200:oklch(.903 .076 319.62);--color-fuchsia-300:oklch(.833 .145 321.434);--color-fuchsia-400:oklch(.74 .238 322.16);--color-fuchsia-500:oklch(.667 .295 322.15);--color-fuchsia-600:oklch(.591 .293 322.896);--color-fuchsia-700:oklch(.518 .253 323.949);--color-fuchsia-800:oklch(.452 .211 324.591);--color-fuchsia-900:oklch(.401 .17 325.612);--color-fuchsia-950:oklch(.293 .136 325.661);--color-pink-50:oklch(.971 .014 343.198);--color-pink-100:oklch(.948 .028 342.258);--color-pink-200:oklch(.899 .061 343.231);--color-pink-300:oklch(.823 .12 346.018);--color-pink-400:oklch(.718 .202 349.761);--color-pink-500:oklch(.656 .241 354.308);--color-pink-600:oklch(.592 .249 .584);--color-pink-700:oklch(.525 .223 3.958);--color-pink-800:oklch(.459 .187 3.815);--color-pink-900:oklch(.408 .153 2.432);--color-pink-950:oklch(.284 .109 3.907);--color-rose-50:oklch(.969 .015 12.422);--color-rose-100:oklch(.941 .03 12.58);--color-rose-200:oklch(.892 .058 10.001);--color-rose-300:oklch(.81 .117 11.638);--color-rose-400:oklch(.712 .194 13.428);--color-rose-500:oklch(.645 .246 16.439);--color-rose-600:oklch(.586 .253 17.585);--color-rose-700:oklch(.514 .222 16.935);--color-rose-800:oklch(.455 .188 13.697);--color-rose-900:oklch(.41 .159 10.272);--color-rose-950:oklch(.271 .105 12.094);--color-slate-50:oklch(.984 .003 247.858);--color-slate-100:oklch(.968 .007 247.896);--color-slate-200:oklch(.929 .013 255.508);--color-slate-300:oklch(.869 .022 252.894);--color-slate-400:oklch(.704 .04 256.788);--color-slate-500:oklch(.554 .046 257.417);--color-slate-600:oklch(.446 .043 257.281);--color-slate-700:oklch(.372 .044 257.287);--color-slate-800:oklch(.279 .041 260.031);--color-slate-900:oklch(.208 .042 265.755);--color-slate-950:oklch(.129 .042 264.695);--color-gray-50:oklch(.985 .002 247.839);--color-gray-100:oklch(.967 .003 264.542);--color-gray-200:oklch(.928 .006 264.531);--color-gray-300:oklch(.872 .01 258.338);--color-gray-400:oklch(.707 .022 261.325);--color-gray-500:oklch(.551 .027 264.364);--color-gray-600:oklch(.446 .03 256.802);--color-gray-700:oklch(.373 .034 259.733);--color-gray-800:oklch(.278 .033 256.848);--color-gray-900:oklch(.21 .034 264.665);--color-gray-950:oklch(.13 .028 261.692);--color-zinc-50:oklch(.985 0 0);--color-zinc-100:oklch(.967 .001 286.375);--color-zinc-200:oklch(.92 .004 286.32);--color-zinc-300:oklch(.871 .006 286.286);--color-zinc-400:oklch(.705 .015 286.067);--color-zinc-500:oklch(.552 .016 285.938);--color-zinc-600:oklch(.442 .017 285.786);--color-zinc-700:oklch(.37 .013 285.805);--color-zinc-800:oklch(.274 .006 286.033);--color-zinc-900:oklch(.21 .006 285.885);--color-zinc-950:oklch(.141 .005 285.823);--color-neutral-50:oklch(.985 0 0);--color-neutral-100:oklch(.97 0 0);--color-neutral-200:oklch(.922 0 0);--color-neutral-300:oklch(.87 0 0);--color-neutral-400:oklch(.708 0 0);--color-neutral-500:oklch(.556 0 0);--color-neutral-600:oklch(.439 0 0);--color-neutral-700:oklch(.371 0 0);--color-neutral-800:oklch(.269 0 0);--color-neutral-900:oklch(.205 0 0);--color-neutral-950:oklch(.145 0 0);--color-stone-50:oklch(.985 .001 106.423);--color-stone-100:oklch(.97 .001 106.424);--color-stone-200:oklch(.923 .003 48.717);--color-stone-300:oklch(.869 .005 56.366);--color-stone-400:oklch(.709 .01 56.259);--color-stone-500:oklch(.553 .013 58.071);--color-stone-600:oklch(.444 .011 73.639);--color-stone-700:oklch(.374 .01 67.558);--color-stone-800:oklch(.268 .007 34.298);--color-stone-900:oklch(.216 .006 56.043);--color-stone-950:oklch(.147 .004 49.25);--color-black:#000;--color-white:#fff;--spacing:.25rem;--breakpoint-sm:40rem;--breakpoint-md:48rem;--breakpoint-lg:64rem;--breakpoint-xl:80rem;--breakpoint-2xl:96rem;--container-3xs:16rem;--container-2xs:18rem;--container-xs:20rem;--container-sm:24rem;--container-md:28rem;--container-lg:32rem;--container-xl:36rem;--container-2xl:42rem;--container-3xl:48rem;--container-4xl:56rem;--container-5xl:64rem;--container-6xl:72rem;--container-7xl:80rem;--text-xs:.75rem;--text-xs--line-height:calc(1/.75);--text-sm:.875rem;--text-sm--line-height:calc(1.25/.875);--text-base:1rem;--text-base--line-height: 1.5 ;--text-lg:1.125rem;--text-lg--line-height:calc(1.75/1.125);--text-xl:1.25rem;--text-xl--line-height:calc(1.75/1.25);--text-2xl:1.5rem;--text-2xl--line-height:calc(2/1.5);--text-3xl:1.875rem;--text-3xl--line-height: 1.2 ;--text-4xl:2.25rem;--text-4xl--line-height:calc(2.5/2.25);--text-5xl:3rem;--text-5xl--line-height:1;--text-6xl:3.75rem;--text-6xl--line-height:1;--text-7xl:4.5rem;--text-7xl--line-height:1;--text-8xl:6rem;--text-8xl--line-height:1;--text-9xl:8rem;--text-9xl--line-height:1;--font-weight-thin:100;--font-weight-extralight:200;--font-weight-light:300;--font-weight-normal:400;--font-weight-medium:500;--font-weight-semibold:600;--font-weight-bold:700;--font-weight-extrabold:800;--font-weight-black:900;--tracking-tighter:-.05em;--tracking-tight:-.025em;--tracking-normal:0em;--tracking-wide:.025em;--tracking-wider:.05em;--tracking-widest:.1em;--leading-tight:1.25;--leading-snug:1.375;--leading-normal:1.5;--leading-relaxed:1.625;--leading-loose:2;--radius-xs:.125rem;--radius-sm:.25rem;--radius-md:.375rem;--radius-lg:.5rem;--radius-xl:.75rem;--radius-2xl:1rem;--radius-3xl:1.5rem;--radius-4xl:2rem;--shadow-2xs:0 1px #0000000d;--shadow-xs:0 1px 2px 0 #0000000d;--shadow-sm:0 1px 3px 0 #0000001a,0 1px 2px -1px #0000001a;--shadow-md:0 4px 6px -1px #0000001a,0 2px 4px -2px #0000001a;--shadow-lg:0 10px 15px -3px #0000001a,0 4px 6px -4px #0000001a;--shadow-xl:0 20px 25px -5px #0000001a,0 8px 10px -6px #0000001a;--shadow-2xl:0 25px 50px -12px #00000040;--inset-shadow-2xs:inset 0 1px #0000000d;--inset-shadow-xs:inset 0 1px 1px #0000000d;--inset-shadow-sm:inset 0 2px 4px #0000000d;--drop-shadow-xs:0 1px 1px #0000000d;--drop-shadow-sm:0 1px 2px #00000026;--drop-shadow-md:0 3px 3px #0000001f;--drop-shadow-lg:0 4px 4px #00000026;--drop-shadow-xl:0 9px 7px #0000001a;--drop-shadow-2xl:0 25px 25px #00000026;--ease-in:cubic-bezier(.4,0,1,1);--ease-out:cubic-bezier(0,0,.2,1);--ease-in-out:cubic-bezier(.4,0,.2,1);--animate-spin:spin 1s linear infinite;--animate-ping:ping 1s cubic-bezier(0,0,.2,1)infinite;--animate-pulse:pulse 2s cubic-bezier(.4,0,.6,1)infinite;--animate-bounce:bounce 1s infinite;--blur-xs:4px;--blur-sm:8px;--blur-md:12px;--blur-lg:16px;--blur-xl:24px;--blur-2xl:40px;--blur-3xl:64px;--perspective-dramatic:100px;--perspective-near:300px;--perspective-normal:500px;--perspective-midrange:800px;--perspective-distant:1200px;--aspect-video:16/9;--default-transition-duration:.15s;--default-transition-timing-function:cubic-bezier(.4,0,.2,1);--default-font-family:var(--font-sans);--default-font-feature-settings:var(--font-sans--font-feature-settings);--default-font-variation-settings:var(--font-sans--font-variation-settings);--default-mono-font-family:var(--font-mono);--default-mono-font-feature-settings:var(--font-mono--font-feature-settings);--default-mono-font-variation-settings:var(--font-mono--font-variation-settings)}}@layer base{*,:after,:before,::backdrop{box-sizing:border-box;border:0 solid;margin:0;padding:0}::file-selector-button{box-sizing:border-box;border:0 solid;margin:0;padding:0}html,:host{-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;line-height:1.5;font-family:var(--default-font-family,ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji");font-feature-settings:var(--default-font-feature-settings,normal);font-variation-settings:var(--default-font-variation-settings,normal);-webkit-tap-highlight-color:transparent}body{line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;-webkit-text-decoration:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,samp,pre{font-family:var(--default-mono-font-family,ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace);font-feature-settings:var(--default-mono-font-feature-settings,normal);font-variation-settings:var(--default-mono-font-variation-settings,normal);font-size:1em}small{font-size:80%}sub,sup{vertical-align:baseline;font-size:75%;line-height:0;position:relative}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}:-moz-focusring{outline:auto}progress{vertical-align:baseline}summary{display:list-item}ol,ul,menu{list-style:none}img,svg,video,canvas,audio,iframe,embed,object{vertical-align:middle;display:block}img,video{max-width:100%;height:auto}button,input,select,optgroup,textarea{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}::file-selector-button{font:inherit;font-feature-settings:inherit;font-variation-settings:inherit;letter-spacing:inherit;color:inherit;opacity:1;background-color:#0000;border-radius:0}:where(select:is([multiple],[size])) optgroup{font-weight:bolder}:where(select:is([multiple],[size])) optgroup option{padding-inline-start:20px}::file-selector-button{margin-inline-end:4px}::placeholder{opacity:1;color:color-mix(in oklab,currentColor 50%,transparent)}textarea{resize:vertical}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-date-and-time-value{min-height:1lh;text-align:inherit}::-webkit-datetime-edit{display:inline-flex}::-webkit-datetime-edit-fields-wrapper{padding:0}::-webkit-datetime-edit{padding-block:0}::-webkit-datetime-edit-year-field{padding-block:0}::-webkit-datetime-edit-month-field{padding-block:0}::-webkit-datetime-edit-day-field{padding-block:0}::-webkit-datetime-edit-hour-field{padding-block:0}::-webkit-datetime-edit-minute-field{padding-block:0}::-webkit-datetime-edit-second-field{padding-block:0}::-webkit-datetime-edit-millisecond-field{padding-block:0}::-webkit-datetime-edit-meridiem-field{padding-block:0}:-moz-ui-invalid{box-shadow:none}button,input:where([type=button],[type=reset],[type=submit]){-webkit-appearance:button;-moz-appearance:button;appearance:button}::file-selector-button{-webkit-appearance:button;-moz-appearance:button;appearance:button}::-webkit-inner-spin-button{height:auto}::-webkit-outer-spin-button{height:auto}[hidden]:where(:not([hidden=until-found])){display:none!important}}@layer components;@layer utilities{.absolute{position:absolute}.relative{position:relative}.static{position:static}.inset-0{inset:calc(var(--spacing)*0)}.-mt-\[4\.9rem\]{margin-top:-4.9rem}.-mb-px{margin-bottom:-1px}.mb-1{margin-bottom:calc(var(--spacing)*1)}.mb-2{margin-bottom:calc(var(--spacing)*2)}.mb-4{margin-bottom:calc(var(--spacing)*4)}.mb-6{margin-bottom:calc(var(--spacing)*6)}.-ml-8{margin-left:calc(var(--spacing)*-8)}.flex{display:flex}.hidden{display:none}.inline-block{display:inline-block}.inline-flex{display:inline-flex}.table{display:table}.aspect-\[335\/376\]{aspect-ratio:335/376}.h-1{height:calc(var(--spacing)*1)}.h-1\.5{height:calc(var(--spacing)*1.5)}.h-2{height:calc(var(--spacing)*2)}.h-2\.5{height:calc(var(--spacing)*2.5)}.h-3{height:calc(var(--spacing)*3)}.h-3\.5{height:calc(var(--spacing)*3.5)}.h-14{height:calc(var(--spacing)*14)}.h-14\.5{height:calc(var(--spacing)*14.5)}.min-h-screen{min-height:100vh}.w-1{width:calc(var(--spacing)*1)}.w-1\.5{width:calc(var(--spacing)*1.5)}.w-2{width:calc(var(--spacing)*2)}.w-2\.5{width:calc(var(--spacing)*2.5)}.w-3{width:calc(var(--spacing)*3)}.w-3\.5{width:calc(var(--spacing)*3.5)}.w-\[448px\]{width:448px}.w-full{width:100%}.max-w-\[335px\]{max-width:335px}.max-w-none{max-width:none}.flex-1{flex:1}.shrink-0{flex-shrink:0}.translate-y-0{--tw-translate-y:calc(var(--spacing)*0);translate:var(--tw-translate-x)var(--tw-translate-y)}.transform{transform:var(--tw-rotate-x)var(--tw-rotate-y)var(--tw-rotate-z)var(--tw-skew-x)var(--tw-skew-y)}.flex-col{flex-direction:column}.flex-col-reverse{flex-direction:column-reverse}.items-center{align-items:center}.justify-center{justify-content:center}.justify-end{justify-content:flex-end}.gap-3{gap:calc(var(--spacing)*3)}.gap-4{gap:calc(var(--spacing)*4)}:where(.space-x-1>:not(:last-child)){--tw-space-x-reverse:0;margin-inline-start:calc(calc(var(--spacing)*1)*var(--tw-space-x-reverse));margin-inline-end:calc(calc(var(--spacing)*1)*calc(1 - var(--tw-space-x-reverse)))}.overflow-hidden{overflow:hidden}.rounded-full{border-radius:3.40282e38px}.rounded-sm{border-radius:var(--radius-sm)}.rounded-t-lg{border-top-left-radius:var(--radius-lg);border-top-right-radius:var(--radius-lg)}.rounded-br-lg{border-bottom-right-radius:var(--radius-lg)}.rounded-bl-lg{border-bottom-left-radius:var(--radius-lg)}.border{border-style:var(--tw-border-style);border-width:1px}.border-\[\#19140035\]{border-color:#19140035}.border-\[\#e3e3e0\]{border-color:#e3e3e0}.border-black{border-color:var(--color-black)}.border-transparent{border-color:#0000}.bg-\[\#1b1b18\]{background-color:#1b1b18}.bg-\[\#FDFDFC\]{background-color:#fdfdfc}.bg-\[\#dbdbd7\]{background-color:#dbdbd7}.bg-\[\#fff2f2\]{background-color:#fff2f2}.bg-white{background-color:var(--color-white)}.p-6{padding:calc(var(--spacing)*6)}.px-5{padding-inline:calc(var(--spacing)*5)}.py-1{padding-block:calc(var(--spacing)*1)}.py-1\.5{padding-block:calc(var(--spacing)*1.5)}.py-2{padding-block:calc(var(--spacing)*2)}.pb-12{padding-bottom:calc(var(--spacing)*12)}.text-sm{font-size:var(--text-sm);line-height:var(--tw-leading,var(--text-sm--line-height))}.text-\[13px\]{font-size:13px}.leading-\[20px\]{--tw-leading:20px;line-height:20px}.leading-normal{--tw-leading:var(--leading-normal);line-height:var(--leading-normal)}.font-medium{--tw-font-weight:var(--font-weight-medium);font-weight:var(--font-weight-medium)}.text-\[\#1b1b18\]{color:#1b1b18}.text-\[\#706f6c\]{color:#706f6c}.text-\[\#F53003\],.text-\[\#f53003\]{color:#f53003}.text-white{color:var(--color-white)}.underline{text-decoration-line:underline}.underline-offset-4{text-underline-offset:4px}.opacity-100{opacity:1}.shadow-\[0px_0px_1px_0px_rgba\(0\,0\,0\,0\.03\)\,0px_1px_2px_0px_rgba\(0\,0\,0\,0\.06\)\]{--tw-shadow:0px 0px 1px 0px var(--tw-shadow-color,#00000008),0px 1px 2px 0px var(--tw-shadow-color,#0000000f);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.shadow-\[inset_0px_0px_0px_1px_rgba\(26\,26\,0\,0\.16\)\]{--tw-shadow:inset 0px 0px 0px 1px var(--tw-shadow-color,#1a1a0029);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.\!filter{filter:var(--tw-blur,)var(--tw-brightness,)var(--tw-contrast,)var(--tw-grayscale,)var(--tw-hue-rotate,)var(--tw-invert,)var(--tw-saturate,)var(--tw-sepia,)var(--tw-drop-shadow,)!important}.filter{filter:var(--tw-blur,)var(--tw-brightness,)var(--tw-contrast,)var(--tw-grayscale,)var(--tw-hue-rotate,)var(--tw-invert,)var(--tw-saturate,)var(--tw-sepia,)var(--tw-drop-shadow,)}.transition-all{transition-property:all;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.transition-opacity{transition-property:opacity;transition-timing-function:var(--tw-ease,var(--default-transition-timing-function));transition-duration:var(--tw-duration,var(--default-transition-duration))}.delay-300{transition-delay:.3s}.duration-750{--tw-duration:.75s;transition-duration:.75s}.not-has-\[nav\]\:hidden:not(:has(:is(nav))){display:none}.before\:absolute:before{content:var(--tw-content);position:absolute}.before\:top-0:before{content:var(--tw-content);top:calc(var(--spacing)*0)}.before\:top-1\/2:before{content:var(--tw-content);top:50%}.before\:bottom-0:before{content:var(--tw-content);bottom:calc(var(--spacing)*0)}.before\:bottom-1\/2:before{content:var(--tw-content);bottom:50%}.before\:left-\[0\.4rem\]:before{content:var(--tw-content);left:.4rem}.before\:border-l:before{content:var(--tw-content);border-left-style:var(--tw-border-style);border-left-width:1px}.before\:border-\[\#e3e3e0\]:before{content:var(--tw-content);border-color:#e3e3e0}@media (hover:hover){.hover\:border-\[\#1915014a\]:hover{border-color:#1915014a}.hover\:border-\[\#19140035\]:hover{border-color:#19140035}.hover\:border-black:hover{border-color:var(--color-black)}.hover\:bg-black:hover{background-color:var(--color-black)}}@media (width>=64rem){.lg\:-mt-\[6\.6rem\]{margin-top:-6.6rem}.lg\:mb-0{margin-bottom:calc(var(--spacing)*0)}.lg\:mb-6{margin-bottom:calc(var(--spacing)*6)}.lg\:-ml-px{margin-left:-1px}.lg\:ml-0{margin-left:calc(var(--spacing)*0)}.lg\:block{display:block}.lg\:aspect-auto{aspect-ratio:auto}.lg\:w-\[438px\]{width:438px}.lg\:max-w-4xl{max-width:var(--container-4xl)}.lg\:grow{flex-grow:1}.lg\:flex-row{flex-direction:row}.lg\:justify-center{justify-content:center}.lg\:rounded-t-none{border-top-left-radius:0;border-top-right-radius:0}.lg\:rounded-tl-lg{border-top-left-radius:var(--radius-lg)}.lg\:rounded-r-lg{border-top-right-radius:var(--radius-lg);border-bottom-right-radius:var(--radius-lg)}.lg\:rounded-br-none{border-bottom-right-radius:0}.lg\:p-8{padding:calc(var(--spacing)*8)}.lg\:p-20{padding:calc(var(--spacing)*20)}}@media (prefers-color-scheme:dark){.dark\:block{display:block}.dark\:hidden{display:none}.dark\:border-\[\#3E3E3A\]{border-color:#3e3e3a}.dark\:border-\[\#eeeeec\]{border-color:#eeeeec}.dark\:bg-\[\#0a0a0a\]{background-color:#0a0a0a}.dark\:bg-\[\#1D0002\]{background-color:#1d0002}.dark\:bg-\[\#3E3E3A\]{background-color:#3e3e3a}.dark\:bg-\[\#161615\]{background-color:#161615}.dark\:bg-\[\#eeeeec\]{background-color:#eeeeec}.dark\:text-\[\#1C1C1A\]{color:#1c1c1a}.dark\:text-\[\#A1A09A\]{color:#a1a09a}.dark\:text-\[\#EDEDEC\]{color:#ededec}.dark\:text-\[\#F61500\]{color:#f61500}.dark\:text-\[\#FF4433\]{color:#f43}.dark\:shadow-\[inset_0px_0px_0px_1px_\#fffaed2d\]{--tw-shadow:inset 0px 0px 0px 1px var(--tw-shadow-color,#fffaed2d);box-shadow:var(--tw-inset-shadow),var(--tw-inset-ring-shadow),var(--tw-ring-offset-shadow),var(--tw-ring-shadow),var(--tw-shadow)}.dark\:before\:border-\[\#3E3E3A\]:before{content:var(--tw-content);border-color:#3e3e3a}@media (hover:hover){.dark\:hover\:border-\[\#3E3E3A\]:hover{border-color:#3e3e3a}.dark\:hover\:border-\[\#62605b\]:hover{border-color:#62605b}.dark\:hover\:border-white:hover{border-color:var(--color-white)}.dark\:hover\:bg-white:hover{background-color:var(--color-white)}}}@starting-style{.starting\:translate-y-4{--tw-translate-y:calc(var(--spacing)*4);translate:var(--tw-translate-x)var(--tw-translate-y)}}@starting-style{.starting\:translate-y-6{--tw-translate-y:calc(var(--spacing)*6);translate:var(--tw-translate-x)var(--tw-translate-y)}}@starting-style{.starting\:opacity-0{opacity:0}}}@keyframes spin{to{transform:rotate(360deg)}}@keyframes ping{75%,to{opacity:0;transform:scale(2)}}@keyframes pulse{50%{opacity:.5}}@keyframes bounce{0%,to{animation-timing-function:cubic-bezier(.8,0,1,1);transform:translateY(-25%)}50%{animation-timing-function:cubic-bezier(0,0,.2,1);transform:none}}@property --tw-translate-x{syntax:"*";inherits:false;initial-value:0}@property --tw-translate-y{syntax:"*";inherits:false;initial-value:0}@property --tw-translate-z{syntax:"*";inherits:false;initial-value:0}@property --tw-rotate-x{syntax:"*";inherits:false;initial-value:rotateX(0)}@property --tw-rotate-y{syntax:"*";inherits:false;initial-value:rotateY(0)}@property --tw-rotate-z{syntax:"*";inherits:false;initial-value:rotateZ(0)}@property --tw-skew-x{syntax:"*";inherits:false;initial-value:skewX(0)}@property --tw-skew-y{syntax:"*";inherits:false;initial-value:skewY(0)}@property --tw-space-x-reverse{syntax:"*";inherits:false;initial-value:0}@property --tw-border-style{syntax:"*";inherits:false;initial-value:solid}@property --tw-leading{syntax:"*";inherits:false}@property --tw-font-weight{syntax:"*";inherits:false}@property --tw-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-shadow-color{syntax:"*";inherits:false}@property --tw-inset-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-inset-shadow-color{syntax:"*";inherits:false}@property --tw-ring-color{syntax:"*";inherits:false}@property --tw-ring-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-inset-ring-color{syntax:"*";inherits:false}@property --tw-inset-ring-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-ring-inset{syntax:"*";inherits:false}@property --tw-ring-offset-width{syntax:"<length>";inherits:false;initial-value:0}@property --tw-ring-offset-color{syntax:"*";inherits:false;initial-value:#fff}@property --tw-ring-offset-shadow{syntax:"*";inherits:false;initial-value:0 0 #0000}@property --tw-blur{syntax:"*";inherits:false}@property --tw-brightness{syntax:"*";inherits:false}@property --tw-contrast{syntax:"*";inherits:false}@property --tw-grayscale{syntax:"*";inherits:false}@property --tw-hue-rotate{syntax:"*";inherits:false}@property --tw-invert{syntax:"*";inherits:false}@property --tw-opacity{syntax:"*";inherits:false}@property --tw-saturate{syntax:"*";inherits:false}@property --tw-sepia{syntax:"*";inherits:false}@property --tw-drop-shadow{syntax:"*";inherits:false}@property --tw-duration{syntax:"*";inherits:false}@property --tw-content{syntax:"*";inherits:false;initial-value:""}
            </style>
        @endif
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
                <div class="text-[13px] leading-[20px] flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
                    <h1 class="mb-1 font-medium">Let's get started</h1>
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">Laravel has an incredibly rich ecosystem. <br>We suggest starting with the following.</p>
                    <ul class="flex flex-col mb-4 lg:mb-6">
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:top-1/2 before:bottom-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span>
                                Read the
                                <a href="https://laravel.com/docs" target="_blank" class="inline-flex items-center space-x-1 font-medium underline underline-offset-4 text-[#f53003] dark:text-[#FF4433] ml-1">
                                    <span>Documentation</span>
                                    <svg
                                        width="10"
                                        height="11"
                                        viewBox="0 0 10 11"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-2.5 h-2.5"
                                    >
                                        <path
                                            d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                                            stroke="currentColor"
                                            stroke-linecap="square"
                                        />
                                    </svg>
                                </a>
                            </span>
                        </li>
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:bottom-1/2 before:top-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span>
                                Watch video tutorials at
                                <a href="https://laracasts.com" target="_blank" class="inline-flex items-center space-x-1 font-medium underline underline-offset-4 text-[#f53003] dark:text-[#FF4433] ml-1">
                                    <span>Laracasts</span>
                                    <svg
                                        width="10"
                                        height="11"
                                        viewBox="0 0 10 11"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-2.5 h-2.5"
                                    >
                                        <path
                                            d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                                            stroke="currentColor"
                                            stroke-linecap="square"
                                        />
                                    </svg>
                                </a>
                            </span>
                        </li>
                    </ul>
                    <ul class="flex gap-3 text-sm leading-normal">
                        <li>
                            <a href="https://cloud.laravel.com" target="_blank" class="inline-block dark:bg-[#eeeeec] dark:border-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white dark:hover:border-white hover:bg-black hover:border-black px-5 py-1.5 bg-[#1b1b18] rounded-sm border border-black text-white text-sm leading-normal">
                                Deploy now
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="bg-[#fff2f2] dark:bg-[#1D0002] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
                    {{-- Laravel Logo --}}
                    <svg class="w-full text-[#F53003] dark:text-[#F61500] transition-all translate-y-0 opacity-100 max-w-none duration-750 starting:opacity-0 starting:translate-y-6" viewBox="0 0 438 104" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.2036 -3H0V102.197H49.5189V86.7187H17.2036V-3Z" fill="currentColor" />
                        <path d="M110.256 41.6337C108.061 38.1275 104.945 35.3731 100.905 33.3681C96.8667 31.3647 92.8016 30.3618 88.7131 30.3618C83.4247 30.3618 78.5885 31.3389 74.201 33.2923C69.8111 35.2456 66.0474 37.928 62.9059 41.3333C59.7643 44.7401 57.3198 48.6726 55.5754 53.1293C53.8287 57.589 52.9572 62.274 52.9572 67.1813C52.9572 72.1925 53.8287 76.8995 55.5754 81.3069C57.3191 85.7173 59.7636 89.6241 62.9059 93.0293C66.0474 96.4361 69.8119 99.1155 74.201 101.069C78.5885 103.022 83.4247 103.999 88.7131 103.999C92.8016 103.999 96.8667 102.997 100.905 100.994C104.945 98.9911 108.061 96.2359 110.256 92.7282V102.195H126.563V32.1642H110.256V41.6337ZM108.76 75.7472C107.762 78.4531 106.366 80.8078 104.572 82.8112C102.776 84.8161 100.606 86.4183 98.0637 87.6206C95.5202 88.823 92.7004 89.4238 89.6103 89.4238C86.5178 89.4238 83.7252 88.823 81.2324 87.6206C78.7388 86.4183 76.5949 84.8161 74.7998 82.8112C73.004 80.8078 71.6319 78.4531 70.6856 75.7472C69.7356 73.0421 69.2644 70.1868 69.2644 67.1821C69.2644 64.1758 69.7356 61.3205 70.6856 58.6154C71.6319 55.9102 73.004 53.5571 74.7998 51.5522C76.5949 49.5495 78.738 47.9451 81.2324 46.7427C83.7252 45.5404 86.5178 44.9396 89.6103 44.9396C92.7012 44.9396 95.5202 45.5404 98.0637 46.7427C100.606 47.9451 102.776 49.5487 104.572 51.5522C106.367 53.5571 107.762 55.9102 108.76 58.6154C109.756 61.3205 110.256 64.1758 110.256 67.1821C110.256 70.1868 109.756 73.0421 108.76 75.7472Z" fill="currentColor" />
                        <path d="M242.805 41.6337C240.611 38.1275 237.494 35.3731 233.455 33.3681C229.416 31.3647 225.351 30.3618 221.262 30.3618C215.974 30.3618 211.138 31.3389 206.75 33.2923C202.36 35.2456 198.597 37.928 195.455 41.3333C192.314 44.7401 189.869 48.6726 188.125 53.1293C186.378 57.589 185.507 62.274 185.507 67.1813C185.507 72.1925 186.378 76.8995 188.125 81.3069C189.868 85.7173 192.313 89.6241 195.455 93.0293C198.597 96.4361 202.361 99.1155 206.75 101.069C211.138 103.022 215.974 103.999 221.262 103.999C225.351 103.999 229.416 102.997 233.455 100.994C237.494 98.9911 240.611 96.2359 242.805 92.7282V102.195H259.112V32.1642H242.805V41.6337ZM241.31 75.7472C240.312 78.4531 238.916 80.8078 237.122 82.8112C235.326 84.8161 233.156 86.4183 230.614 87.6206C228.07 88.823 225.251 89.4238 222.16 89.4238C219.068 89.4238 216.275 88.823 213.782 87.6206C211.289 86.4183 209.145 84.8161 207.35 82.8112C205.554 80.8078 204.182 78.4531 203.236 75.7472C202.286 73.0421 201.814 70.1868 201.814 67.1821C201.814 64.1758 202.286 61.3205 203.236 58.6154C204.182 55.9102 205.554 53.5571 207.35 51.5522C209.145 49.5495 211.288 47.9451 213.782 46.7427C216.275 45.5404 219.068 44.9396 222.16 44.9396C225.251 44.9396 228.07 45.5404 230.614 46.7427C233.156 47.9451 235.326 49.5487 237.122 51.5522C238.917 53.5571 240.312 55.9102 241.31 58.6154C242.306 61.3205 242.806 64.1758 242.806 67.1821C242.805 70.1868 242.305 73.0421 241.31 75.7472Z" fill="currentColor" />
                        <path d="M438 -3H421.694V102.197H438V-3Z" fill="currentColor" />
                        <path d="M139.43 102.197H155.735V48.2834H183.712V32.1665H139.43V102.197Z" fill="currentColor" />
                        <path d="M324.49 32.1665L303.995 85.794L283.498 32.1665H266.983L293.748 102.197H314.242L341.006 32.1665H324.49Z" fill="currentColor" />
                        <path d="M376.571 30.3656C356.603 30.3656 340.797 46.8497 340.797 67.1828C340.797 89.6597 356.094 104 378.661 104C391.29 104 399.354 99.1488 409.206 88.5848L398.189 80.0226C398.183 80.031 389.874 90.9895 377.468 90.9895C363.048 90.9895 356.977 79.3111 356.977 73.269H411.075C413.917 50.1328 398.775 30.3656 376.571 30.3656ZM357.02 61.0967C357.145 59.7487 359.023 43.3761 376.442 43.3761C393.861 43.3761 395.978 59.7464 396.099 61.0967H357.02Z" fill="currentColor" />
                    </svg>

                    {{-- Light Mode 12 SVG --}}
                    <svg class="w-[448px] max-w-none relative -mt-[4.9rem] -ml-8 lg:ml-0 lg:-mt-[6.6rem] dark:hidden" viewBox="0 0 440 376" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4">
                            <path d="M188.263 355.73L188.595 355.73C195.441 348.845 205.766 339.761 219.569 328.477C232.93 317.193 242.978 308.205 249.714 301.511C256.34 294.626 260.867 287.358 263.296 279.708C265.725 272.058 264.565 264.121 259.816 255.896C254.516 246.716 247.062 239.352 237.454 233.805C227.957 228.067 217.908 225.198 207.307 225.198C196.927 225.197 190.136 227.97 186.934 233.516C183.621 238.872 184.726 246.331 190.247 255.894L125.647 255.891C116.371 239.825 112.395 225.481 113.72 212.858C115.265 200.235 121.559 190.481 132.602 183.596C143.754 176.52 158.607 172.982 177.159 172.983C196.594 172.984 215.863 176.523 234.968 183.6C253.961 190.486 271.299 200.241 286.98 212.864C302.661 225.488 315.14 239.833 324.416 255.899C333.03 270.817 336.841 283.918 335.847 295.203C335.075 306.487 331.376 316.336 324.75 324.751C318.346 333.167 308.408 343.494 294.936 355.734L377.094 355.737L405.917 405.656L217.087 405.649L188.263 355.73Z" fill="black" />
                            <path d="M9.11884 226.339L-13.7396 226.338L-42.7286 176.132L43.0733 176.135L175.595 405.649L112.651 405.647L9.11884 226.339Z" fill="black" />
                            <path d="M188.263 355.73L188.595 355.73C195.441 348.845 205.766 339.761 219.569 328.477C232.93 317.193 242.978 308.205 249.714 301.511C256.34 294.626 260.867 287.358 263.296 279.708C265.725 272.058 264.565 264.121 259.816 255.896C254.516 246.716 247.062 239.352 237.454 233.805C227.957 228.067 217.908 225.198 207.307 225.198C196.927 225.197 190.136 227.97 186.934 233.516C183.621 238.872 184.726 246.331 190.247 255.894L125.647 255.891C116.371 239.825 112.395 225.481 113.72 212.858C115.265 200.235 121.559 190.481 132.602 183.596C143.754 176.52 158.607 172.982 177.159 172.983C196.594 172.984 215.863 176.523 234.968 183.6C253.961 190.486 271.299 200.241 286.98 212.864C302.661 225.488 315.14 239.833 324.416 255.899C333.03 270.817 336.841 283.918 335.847 295.203C335.075 306.487 331.376 316.336 324.75 324.751C318.346 333.167 308.408 343.494 294.936 355.734L377.094 355.737L405.917 405.656L217.087 405.649L188.263 355.73Z" stroke="#1B1B18" stroke-width="1" />
                            <path d="M9.11884 226.339L-13.7396 226.338L-42.7286 176.132L43.0733 176.135L175.595 405.649L112.651 405.647L9.11884 226.339Z" stroke="#1B1B18" stroke-width="1" />
                            <path d="M204.592 327.449L204.923 327.449C211.769 320.564 222.094 311.479 235.897 300.196C249.258 288.912 259.306 279.923 266.042 273.23C272.668 266.345 277.195 259.077 279.624 251.427C282.053 243.777 280.893 235.839 276.145 227.615C270.844 218.435 263.39 211.071 253.782 205.524C244.285 199.786 234.236 196.917 223.635 196.916C213.255 196.916 206.464 199.689 203.262 205.235C199.949 210.59 201.054 218.049 206.575 227.612L141.975 227.61C132.699 211.544 128.723 197.2 130.048 184.577C131.593 171.954 137.887 162.2 148.93 155.315C160.083 148.239 174.935 144.701 193.487 144.702C212.922 144.703 232.192 148.242 251.296 155.319C270.289 162.205 287.627 171.96 303.308 184.583C318.989 197.207 331.468 211.552 340.745 227.618C349.358 242.536 353.169 255.637 352.175 266.921C351.403 278.205 347.704 288.055 341.078 296.47C334.674 304.885 324.736 315.213 311.264 327.453L393.422 327.456L422.246 377.375L233.415 377.368L204.592 327.449Z" fill="#F8B803" />
                            <path d="M25.447 198.058L2.58852 198.057L-26.4005 147.851L59.4015 147.854L191.923 377.368L128.979 377.365L25.447 198.058Z" fill="#F8B803" />
                            <path d="M204.592 327.449L204.923 327.449C211.769 320.564 222.094 311.479 235.897 300.196C249.258 288.912 259.306 279.923 266.042 273.23C272.668 266.345 277.195 259.077 279.624 251.427C282.053 243.777 280.893 235.839 276.145 227.615C270.844 218.435 263.39 211.071 253.782 205.524C244.285 199.786 234.236 196.917 223.635 196.916C213.255 196.916 206.464 199.689 203.262 205.235C199.949 210.59 201.054 218.049 206.575 227.612L141.975 227.61C132.699 211.544 128.723 197.2 130.048 184.577C131.593 171.954 137.887 162.2 148.93 155.315C160.083 148.239 174.935 144.701 193.487 144.702C212.922 144.703 232.192 148.242 251.296 155.319C270.289 162.205 287.627 171.96 303.308 184.583C318.989 197.207 331.468 211.552 340.745 227.618C349.358 242.536 353.169 255.637 352.175 266.921C351.403 278.205 347.704 288.055 341.078 296.47C334.674 304.885 324.736 315.213 311.264 327.453L393.422 327.456L422.246 377.375L233.415 377.368L204.592 327.449Z" stroke="#1B1B18" stroke-width="1" />
                            <path d="M25.447 198.058L2.58852 198.057L-26.4005 147.851L59.4015 147.854L191.923 377.368L128.979 377.365L25.447 198.058Z" stroke="#1B1B18" stroke-width="1" />
                        </g>
                        <g style="mix-blend-mode: hard-light" class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4">
                            <path d="M217.342 305.363L217.673 305.363C224.519 298.478 234.844 289.393 248.647 278.11C262.008 266.826 272.056 257.837 278.792 251.144C285.418 244.259 289.945 236.991 292.374 229.341C294.803 221.691 293.643 213.753 288.895 205.529C283.594 196.349 276.14 188.985 266.532 183.438C257.035 177.7 246.986 174.831 236.385 174.83C226.005 174.83 219.214 177.603 216.012 183.149C212.699 188.504 213.804 195.963 219.325 205.527L154.725 205.524C145.449 189.458 141.473 175.114 142.798 162.491C144.343 149.868 150.637 140.114 161.68 133.229C172.833 126.153 187.685 122.615 206.237 122.616C225.672 122.617 244.942 126.156 264.046 133.233C283.039 140.119 300.377 149.874 316.058 162.497C331.739 175.121 344.218 189.466 353.495 205.532C362.108 220.45 365.919 233.551 364.925 244.835C364.153 256.12 360.454 265.969 353.828 274.384C347.424 282.799 337.486 293.127 324.014 305.367L406.172 305.37L434.996 355.289L246.165 355.282L217.342 305.363Z" fill="#F0ACB8" />
                            <path d="M38.197 175.972L15.3385 175.971L-13.6505 125.765L72.1515 125.768L204.673 355.282L141.729 355.279L38.197 175.972Z" fill="#F0ACB8" />
                            <path d="M217.342 305.363L217.673 305.363C224.519 298.478 234.844 289.393 248.647 278.11C262.008 266.826 272.056 257.837 278.792 251.144C285.418 244.259 289.945 236.991 292.374 229.341C294.803 221.691 293.643 213.753 288.895 205.529C283.594 196.349 276.14 188.985 266.532 183.438C257.035 177.7 246.986 174.831 236.385 174.83C226.005 174.83 219.214 177.603 216.012 183.149C212.699 188.504 213.804 195.963 219.325 205.527L154.725 205.524C145.449 189.458 141.473 175.114 142.798 162.491C144.343 149.868 150.637 140.114 161.68 133.229C172.833 126.153 187.685 122.615 206.237 122.616C225.672 122.617 244.942 126.156 264.046 133.233C283.039 140.119 300.377 149.874 316.058 162.497C331.739 175.121 344.218 189.466 353.495 205.532C362.108 220.45 365.919 233.551 364.925 244.835C364.153 256.12 360.454 265.969 353.828 274.384C347.424 282.799 337.486 293.127 324.014 305.367L406.172 305.37L434.996 355.289L246.165 355.282L217.342 305.363Z" stroke="#1B1B18" stroke-width="1" />
                            <path d="M38.197 175.972L15.3385 175.971L-13.6505 125.765L72.1515 125.768L204.673 355.282L141.729 355.279L38.197 175.972Z" stroke="#1B1B18" stroke-width="1" />
                        </g>
                        <g style="mix-blend-mode: plus-darker" class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4">
                            <path d="M230.951 281.792L231.282 281.793C238.128 274.907 248.453 265.823 262.256 254.539C275.617 243.256 285.666 234.267 292.402 227.573C299.027 220.688 303.554 213.421 305.983 205.771C308.412 198.12 307.253 190.183 302.504 181.959C297.203 172.778 289.749 165.415 280.142 159.868C270.645 154.13 260.596 151.26 249.995 151.26C239.615 151.26 232.823 154.033 229.621 159.579C226.309 164.934 227.413 172.393 232.935 181.956L168.335 181.954C159.058 165.888 155.082 151.543 156.407 138.92C157.953 126.298 164.247 116.544 175.289 109.659C186.442 102.583 201.294 99.045 219.846 99.0457C239.281 99.0464 258.551 102.585 277.655 109.663C296.649 116.549 313.986 126.303 329.667 138.927C345.349 151.551 357.827 165.895 367.104 181.961C375.718 196.88 379.528 209.981 378.535 221.265C377.762 232.549 374.063 242.399 367.438 250.814C361.033 259.229 351.095 269.557 337.624 281.796L419.782 281.8L448.605 331.719L259.774 331.712L230.951 281.792Z" fill="#F3BEC7" />
                            <path d="M51.8063 152.402L28.9479 152.401L-0.0411453 102.195L85.7608 102.198L218.282 331.711L155.339 331.709L51.8063 152.402Z" fill="#F3BEC7" />
                            <path d="M230.951 281.792L231.282 281.793C238.128 274.907 248.453 265.823 262.256 254.539C275.617 243.256 285.666 234.267 292.402 227.573C299.027 220.688 303.554 213.421 305.983 205.771C308.412 198.12 307.253 190.183 302.504 181.959C297.203 172.778 289.749 165.415 280.142 159.868C270.645 154.13 260.596 151.26 249.995 151.26C239.615 151.26 232.823 154.033 229.621 159.579C226.309 164.934 227.413 172.393 232.935 181.956L168.335 181.954C159.058 165.888 155.082 151.543 156.407 138.92C157.953 126.298 164.247 116.544 175.289 109.659C186.442 102.583 201.294 99.045 219.846 99.0457C239.281 99.0464 258.551 102.585 277.655 109.663C296.649 116.549 313.986 126.303 329.667 138.927C345.349 151.551 357.827 165.895 367.104 181.961C375.718 196.88 379.528 209.981 378.535 221.265C377.762 232.549 374.063 242.399 367.438 250.814C361.033 259.229 351.095 269.557 337.624 281.796L419.782 281.8L448.605 331.719L259.774 331.712L230.951 281.792Z" stroke="#1B1B18" stroke-width="1" />
                            <path d="M51.8063 152.402L28.9479 152.401L-0.0411453 102.195L85.7608 102.198L218.282 331.711L155.339 331.709L51.8063 152.402Z" stroke="#1B1B18" stroke-width="1" />
                        </g>
                        <g class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4">
                            <path d="M188.467 355.363L188.798 355.363C195.644 348.478 205.969 339.393 219.772 328.11C233.133 316.826 243.181 307.837 249.917 301.144C253.696 297.217 256.792 293.166 259.205 288.991C261.024 285.845 262.455 282.628 263.499 279.341C265.928 271.691 264.768 263.753 260.02 255.529C254.719 246.349 247.265 238.985 237.657 233.438C228.16 227.7 218.111 224.831 207.51 224.83C197.13 224.83 190.339 227.603 187.137 233.149C183.824 238.504 184.929 245.963 190.45 255.527L125.851 255.524C116.574 239.458 112.598 225.114 113.923 212.491C114.615 206.836 116.261 201.756 118.859 197.253C122.061 191.704 126.709 187.03 132.805 183.229C143.958 176.153 158.81 172.615 177.362 172.616C196.797 172.617 216.067 176.156 235.171 183.233C254.164 190.119 271.502 199.874 287.183 212.497C302.864 225.121 315.343 239.466 324.62 255.532C333.233 270.45 337.044 283.551 336.05 294.835C335.46 303.459 333.16 311.245 329.151 318.194C327.915 320.337 326.515 322.4 324.953 324.384C318.549 332.799 308.611 343.127 295.139 355.367L377.297 355.37L406.121 405.289L217.29 405.282L188.467 355.363Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M9.32197 225.972L-13.5365 225.971L-42.5255 175.765L43.2765 175.768L175.798 405.282L112.854 405.279L9.32197 225.972Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M345.247 111.915C329.566 99.2919 312.229 89.5371 293.235 82.6512L235.167 183.228C254.161 190.114 271.498 199.869 287.179 212.492L345.247 111.915Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M382.686 154.964C373.41 138.898 360.931 124.553 345.25 111.93L287.182 212.506C302.863 225.13 315.342 239.475 324.618 255.541L382.686 154.964Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M293.243 82.6472C274.139 75.57 254.869 72.031 235.434 72.0303L177.366 172.607C196.801 172.608 216.071 176.147 235.175 183.224L293.243 82.6472Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M394.118 194.257C395.112 182.973 391.301 169.872 382.688 154.953L324.619 255.53C333.233 270.448 337.044 283.55 336.05 294.834L394.118 194.257Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M235.432 72.0311C216.88 72.0304 202.027 75.5681 190.875 82.6442L132.806 183.221C143.959 176.145 158.812 172.607 177.363 172.608L235.432 72.0311Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M265.59 124.25C276.191 124.251 286.24 127.12 295.737 132.858L237.669 233.435C228.172 227.697 218.123 224.828 207.522 224.827L265.59 124.25Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M295.719 132.859C305.326 138.406 312.78 145.77 318.081 154.95L260.013 255.527C254.712 246.347 247.258 238.983 237.651 233.436L295.719 132.859Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M387.218 217.608C391.227 210.66 393.527 202.874 394.117 194.25L336.049 294.827C335.459 303.451 333.159 311.237 329.15 318.185L387.218 217.608Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M245.211 132.577C248.413 127.03 255.204 124.257 265.584 124.258L207.516 224.835C197.136 224.834 190.345 227.607 187.143 233.154L245.211 132.577Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M318.094 154.945C322.842 163.17 324.002 171.107 321.573 178.757L263.505 279.334C265.934 271.684 264.774 263.746 260.026 255.522L318.094 154.945Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M176.925 96.6737C180.127 91.1249 184.776 86.4503 190.871 82.6499L132.803 183.227C126.708 187.027 122.059 191.702 118.857 197.25L176.925 96.6737Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M387.226 217.606C385.989 219.749 384.59 221.813 383.028 223.797L324.96 324.373C326.522 322.39 327.921 320.326 329.157 318.183L387.226 217.606Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M317.269 188.408C319.087 185.262 320.519 182.045 321.562 178.758L263.494 279.335C262.451 282.622 261.019 285.839 259.201 288.985L317.269 188.408Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M245.208 132.573C241.895 137.928 243 145.387 248.522 154.95L190.454 255.527C184.932 245.964 183.827 238.505 187.14 233.15L245.208 132.573Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M176.93 96.6719C174.331 101.175 172.686 106.255 171.993 111.91L113.925 212.487C114.618 206.831 116.263 201.752 118.862 197.249L176.93 96.6719Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M317.266 188.413C314.853 192.589 311.757 196.64 307.978 200.566L249.91 301.143C253.689 297.216 256.785 293.166 259.198 288.99L317.266 188.413Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M464.198 304.708L435.375 254.789L377.307 355.366L406.13 405.285L464.198 304.708Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M353.209 254.787C366.68 242.548 376.618 232.22 383.023 223.805L324.955 324.382C318.55 332.797 308.612 343.124 295.141 355.364L353.209 254.787Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M435.37 254.787L353.212 254.784L295.144 355.361L377.302 355.364L435.37 254.787Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M183.921 154.947L248.521 154.95L190.453 255.527L125.853 255.524L183.921 154.947Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M171.992 111.914C170.668 124.537 174.643 138.881 183.92 154.947L125.852 255.524C116.575 239.458 112.599 225.114 113.924 212.491L171.992 111.914Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M307.987 200.562C301.251 207.256 291.203 216.244 277.842 227.528L219.774 328.105C233.135 316.821 243.183 307.832 249.919 301.139L307.987 200.562Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M15.5469 75.1797L44.5359 125.386L-13.5321 225.963L-42.5212 175.756L15.5469 75.1797Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M277.836 227.536C264.033 238.82 253.708 247.904 246.862 254.789L188.794 355.366C195.64 348.481 205.965 339.397 219.768 328.113L277.836 227.536Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M275.358 304.706L464.189 304.713L406.12 405.29L217.29 405.283L275.358 304.706Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M44.5279 125.39L67.3864 125.39L9.31834 225.967L-13.5401 225.966L44.5279 125.39Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M101.341 75.1911L233.863 304.705L175.795 405.282L43.2733 175.768L101.341 75.1911ZM15.5431 75.19L-42.525 175.767L43.277 175.77L101.345 75.1932L15.5431 75.19Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M246.866 254.784L246.534 254.784L188.466 355.361L188.798 355.361L246.866 254.784Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M246.539 254.781L275.362 304.701L217.294 405.277L188.471 355.358L246.539 254.781Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M67.3906 125.391L170.923 304.698L112.855 405.275L9.32257 225.967L67.3906 125.391Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                            <path d="M170.921 304.699L233.865 304.701L175.797 405.278L112.853 405.276L170.921 304.699Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="bevel" />
                        </g>
                        <g style="mix-blend-mode: hard-light" class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4">
                            <path d="M246.544 254.79L246.875 254.79C253.722 247.905 264.046 238.82 277.849 227.537C291.21 216.253 301.259 207.264 307.995 200.57C314.62 193.685 319.147 186.418 321.577 178.768C324.006 171.117 322.846 163.18 318.097 154.956C312.796 145.775 305.342 138.412 295.735 132.865C286.238 127.127 276.189 124.258 265.588 124.257C255.208 124.257 248.416 127.03 245.214 132.576C241.902 137.931 243.006 145.39 248.528 154.953L183.928 154.951C174.652 138.885 170.676 124.541 172 111.918C173.546 99.2946 179.84 89.5408 190.882 82.6559C202.035 75.5798 216.887 72.0421 235.439 72.0428C254.874 72.0435 274.144 75.5825 293.248 82.6598C312.242 89.5457 329.579 99.3005 345.261 111.924C360.942 124.548 373.421 138.892 382.697 154.958C391.311 169.877 395.121 182.978 394.128 194.262C393.355 205.546 389.656 215.396 383.031 223.811C376.627 232.226 366.688 242.554 353.217 254.794L435.375 254.797L464.198 304.716L275.367 304.709L246.544 254.79Z" fill="#F0ACB8" />
                            <path d="M246.544 254.79L246.875 254.79C253.722 247.905 264.046 238.82 277.849 227.537C291.21 216.253 301.259 207.264 307.995 200.57C314.62 193.685 319.147 186.418 321.577 178.768C324.006 171.117 322.846 163.18 318.097 154.956C312.796 145.775 305.342 138.412 295.735 132.865C286.238 127.127 276.189 124.258 265.588 124.257C255.208 124.257 248.416 127.03 245.214 132.576C241.902 137.931 243.006 145.39 248.528 154.953L183.928 154.951C174.652 138.885 170.676 124.541 172 111.918C173.546 99.2946 179.84 89.5408 190.882 82.6559C202.035 75.5798 216.887 72.0421 235.439 72.0428C254.874 72.0435 274.144 75.5825 293.248 82.6598C312.242 89.5457 329.579 99.3005 345.261 111.924C360.942 124.548 373.421 138.892 382.697 154.958C391.311 169.877 395.121 182.978 394.128 194.262C393.355 205.546 389.656 215.396 383.031 223.811C376.627 232.226 366.688 242.554 353.217 254.794L435.375 254.797L464.198 304.716L275.367 304.709L246.544 254.79Z" stroke="#1B1B18" stroke-width="1" stroke-linejoin="round" />
                        </g>
                        <g style="mix-blend-mode: hard-light" class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4">
                            <path d="M67.41 125.402L44.5515 125.401L15.5625 75.1953L101.364 75.1985L233.886 304.712L170.942 304.71L67.41 125.402Z" fill="#F0ACB8" />
                            <path d="M67.41 125.402L44.5515 125.401L15.5625 75.1953L101.364 75.1985L233.886 304.712L170.942 304.71L67.41 125.402Z" stroke="#1B1B18" stroke-width="1" />
                        </g>
                    </svg>

                    {{-- Dark Mode 12 SVG --}}
                    <svg class="w-[448px] max-w-none relative -mt-[4.9rem] -ml-8 lg:ml-0 lg:-mt-[6.6rem] hidden dark:block" viewBox="0 0 440 376" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4">
                            <path d="M188.263 355.73L188.595 355.73C195.441 348.845 205.766 339.761 219.569 328.477C232.93 317.193 242.978 308.205 249.714 301.511C256.34 294.626 260.867 287.358 263.296 279.708C265.725 272.058 264.565 264.121 259.816 255.896C254.516 246.716 247.062 239.352 237.454 233.805C227.957 228.067 217.908 225.198 207.307 225.198C196.927 225.197 190.136 227.97 186.934 233.516C183.621 238.872 184.726 246.331 190.247 255.894L125.647 255.891C116.371 239.825 112.395 225.481 113.72 212.858C115.265 200.235 121.559 190.481 132.602 183.596C143.754 176.52 158.607 172.982 177.159 172.983C196.594 172.984 215.863 176.523 234.968 183.6C253.961 190.486 271.299 200.241 286.98 212.864C302.661 225.488 315.14 239.833 324.416 255.899C333.03 270.817 336.841 283.918 335.847 295.203C335.075 306.487 331.376 316.336 324.75 324.751C318.346 333.167 308.408 343.494 294.936 355.734L377.094 355.737L405.917 405.656L217.087 405.649L188.263 355.73Z" fill="black"/>
                            <path d="M9.11884 226.339L-13.7396 226.338L-42.7286 176.132L43.0733 176.135L175.595 405.649L112.651 405.647L9.11884 226.339Z" fill="black"/>
                            <path d="M188.263 355.73L188.595 355.73C195.441 348.845 205.766 339.761 219.569 328.477C232.93 317.193 242.978 308.205 249.714 301.511C256.34 294.626 260.867 287.358 263.296 279.708C265.725 272.058 264.565 264.121 259.816 255.896C254.516 246.716 247.062 239.352 237.454 233.805C227.957 228.067 217.908 225.198 207.307 225.198C196.927 225.197 190.136 227.97 186.934 233.516C183.621 238.872 184.726 246.331 190.247 255.894L125.647 255.891C116.371 239.825 112.395 225.481 113.72 212.858C115.265 200.235 121.559 190.481 132.602 183.596C143.754 176.52 158.607 172.982 177.159 172.983C196.594 172.984 215.863 176.523 234.968 183.6C253.961 190.486 271.299 200.241 286.98 212.864C302.661 225.488 315.14 239.833 324.416 255.899C333.03 270.817 336.841 283.918 335.847 295.203C335.075 306.487 331.376 316.336 324.75 324.751C318.346 333.167 308.408 343.494 294.936 355.734L377.094 355.737L405.917 405.656L217.087 405.649L188.263 355.73Z" stroke="#FF750F" stroke-width="1"/>
                            <path d="M9.11884 226.339L-13.7396 226.338L-42.7286 176.132L43.0733 176.135L175.595 405.649L112.651 405.647L9.11884 226.339Z" stroke="#FF750F" stroke-width="1"/>
                            <path d="M204.592 327.449L204.923 327.449C211.769 320.564 222.094 311.479 235.897 300.196C249.258 288.912 259.306 279.923 266.042 273.23C272.668 266.345 277.195 259.077 279.624 251.427C282.053 243.777 280.893 235.839 276.145 227.615C270.844 218.435 263.39 211.071 253.782 205.524C244.285 199.786 234.236 196.917 223.635 196.916C213.255 196.916 206.464 199.689 203.262 205.235C199.949 210.59 201.054 218.049 206.575 227.612L141.975 227.61C132.699 211.544 128.723 197.2 130.048 184.577C131.593 171.954 137.887 162.2 148.93 155.315C160.083 148.239 174.935 144.701 193.487 144.702C212.922 144.703 232.192 148.242 251.296 155.319C270.289 162.205 287.627 171.96 303.308 184.583C318.989 197.207 331.468 211.552 340.745 227.618C349.358 242.536 353.169 255.637 352.175 266.921C351.403 278.205 347.704 288.055 341.078 296.47C334.674 304.885 324.736 315.213 311.264 327.453L393.422 327.456L422.246 377.375L233.415 377.368L204.592 327.449Z" fill="#391800"/>
                            <path d="M25.447 198.058L2.58852 198.057L-26.4005 147.851L59.4015 147.854L191.923 377.368L128.979 377.365L25.447 198.058Z" fill="#391800"/>
                            <path d="M204.592 327.449L204.923 327.449C211.769 320.564 222.094 311.479 235.897 300.196C249.258 288.912 259.306 279.923 266.042 273.23C272.668 266.345 277.195 259.077 279.624 251.427C282.053 243.777 280.893 235.839 276.145 227.615C270.844 218.435 263.39 211.071 253.782 205.524C244.285 199.786 234.236 196.917 223.635 196.916C213.255 196.916 206.464 199.689 203.262 205.235C199.949 210.59 201.054 218.049 206.575 227.612L141.975 227.61C132.699 211.544 128.723 197.2 130.048 184.577C131.593 171.954 137.887 162.2 148.93 155.315C160.083 148.239 174.935 144.701 193.487 144.702C212.922 144.703 232.192 148.242 251.296 155.319C270.289 162.205 287.627 171.96 303.308 184.583C318.989 197.207 331.468 211.552 340.745 227.618C349.358 242.536 353.169 255.637 352.175 266.921C351.403 278.205 347.704 288.055 341.078 296.47C334.674 304.885 324.736 315.213 311.264 327.453L393.422 327.456L422.246 377.375L233.415 377.368L204.592 327.449Z" stroke="#FF750F" stroke-width="1"/>
                            <path d="M25.447 198.058L2.58852 198.057L-26.4005 147.851L59.4015 147.854L191.923 377.368L128.979 377.365L25.447 198.058Z" stroke="#FF750F" stroke-width="1"/>
                        </g>
                        <g class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4" style="mix-blend-mode:hard-light">
                            <path d="M217.342 305.363L217.673 305.363C224.519 298.478 234.844 289.393 248.647 278.11C262.008 266.826 272.056 257.837 278.792 251.144C285.418 244.259 289.945 236.991 292.374 229.341C294.803 221.691 293.643 213.753 288.895 205.529C283.594 196.349 276.14 188.985 266.532 183.438C257.035 177.7 246.986 174.831 236.385 174.83C226.005 174.83 219.214 177.603 216.012 183.149C212.699 188.504 213.804 195.963 219.325 205.527L154.725 205.524C145.449 189.458 141.473 175.114 142.798 162.491C144.343 149.868 150.637 140.114 161.68 133.229C172.833 126.153 187.685 122.615 206.237 122.616C225.672 122.617 244.942 126.156 264.046 133.233C283.039 140.119 300.377 149.874 316.058 162.497C331.739 175.121 344.218 189.466 353.495 205.532C362.108 220.45 365.919 233.551 364.925 244.835C364.153 256.12 360.454 265.969 353.828 274.384C347.424 282.799 337.486 293.127 324.014 305.367L406.172 305.37L434.996 355.289L246.165 355.282L217.342 305.363Z" fill="#733000"/>
                            <path d="M38.197 175.972L15.3385 175.971L-13.6505 125.765L72.1515 125.768L204.673 355.282L141.729 355.279L38.197 175.972Z" fill="#733000"/>
                            <path d="M217.342 305.363L217.673 305.363C224.519 298.478 234.844 289.393 248.647 278.11C262.008 266.826 272.056 257.837 278.792 251.144C285.418 244.259 289.945 236.991 292.374 229.341C294.803 221.691 293.643 213.753 288.895 205.529C283.594 196.349 276.14 188.985 266.532 183.438C257.035 177.7 246.986 174.831 236.385 174.83C226.005 174.83 219.214 177.603 216.012 183.149C212.699 188.504 213.804 195.963 219.325 205.527L154.725 205.524C145.449 189.458 141.473 175.114 142.798 162.491C144.343 149.868 150.637 140.114 161.68 133.229C172.833 126.153 187.685 122.615 206.237 122.616C225.672 122.617 244.942 126.156 264.046 133.233C283.039 140.119 300.377 149.874 316.058 162.497C331.739 175.121 344.218 189.466 353.495 205.532C362.108 220.45 365.919 233.551 364.925 244.835C364.153 256.12 360.454 265.969 353.828 274.384C347.424 282.799 337.486 293.127 324.014 305.367L406.172 305.37L434.996 355.289L246.165 355.282L217.342 305.363Z" stroke="#FF750F" stroke-width="1"/>
                            <path d="M38.197 175.972L15.3385 175.971L-13.6505 125.765L72.1515 125.768L204.673 355.282L141.729 355.279L38.197 175.972Z" stroke="#FF750F" stroke-width="1"/>
                        </g>
                        <g class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4">
                            <path d="M217.342 305.363L217.673 305.363C224.519 298.478 234.844 289.393 248.647 278.11C262.008 266.826 272.056 257.837 278.792 251.144C285.418 244.259 289.945 236.991 292.374 229.341C294.803 221.691 293.643 213.753 288.895 205.529C283.594 196.349 276.14 188.985 266.532 183.438C257.035 177.7 246.986 174.831 236.385 174.83C226.005 174.83 219.214 177.603 216.012 183.149C212.699 188.504 213.804 195.963 219.325 205.527L154.726 205.524C145.449 189.458 141.473 175.114 142.798 162.491C144.343 149.868 150.637 140.114 161.68 133.229C172.833 126.153 187.685 122.615 206.237 122.616C225.672 122.617 244.942 126.156 264.046 133.233C283.039 140.119 300.377 149.874 316.058 162.497C331.739 175.121 344.218 189.466 353.495 205.532C362.108 220.45 365.919 233.551 364.925 244.835C364.153 256.12 360.454 265.969 353.828 274.384C347.424 282.799 337.486 293.127 324.014 305.367L406.172 305.37L434.996 355.289L246.165 355.282L217.342 305.363Z" stroke="#FF750F" stroke-width="1"/>
                            <path d="M38.197 175.972L15.3385 175.971L-13.6505 125.765L72.1515 125.768L204.673 355.282L141.729 355.279L38.197 175.972Z" stroke="#FF750F" stroke-width="1"/>
                        </g>
                        <g class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4">
                            <path d="M188.467 355.363L188.798 355.363C195.644 348.478 205.969 339.393 219.772 328.11C233.133 316.826 243.181 307.837 249.917 301.144C253.696 297.217 256.792 293.166 259.205 288.991C261.024 285.845 262.455 282.628 263.499 279.341C265.928 271.691 264.768 263.753 260.02 255.529C254.719 246.349 247.265 238.985 237.657 233.438C228.16 227.7 218.111 224.831 207.51 224.83C197.13 224.83 190.339 227.603 187.137 233.149C183.824 238.504 184.929 245.963 190.45 255.527L125.851 255.524C116.574 239.458 112.598 225.114 113.923 212.491C114.615 206.836 116.261 201.756 118.859 197.253C122.061 191.704 126.709 187.03 132.805 183.229C143.958 176.153 158.81 172.615 177.362 172.616C196.797 172.617 216.067 176.156 235.171 183.233C254.164 190.119 271.502 199.874 287.183 212.497C302.864 225.121 315.343 239.466 324.62 255.532C333.233 270.45 337.044 283.551 336.05 294.835C335.46 303.459 333.16 311.245 329.151 318.194C327.915 320.337 326.515 322.4 324.953 324.384C318.549 332.799 308.611 343.127 295.139 355.367L377.297 355.37L406.121 405.289L217.29 405.282L188.467 355.363Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M9.32197 225.972L-13.5365 225.971L-42.5255 175.765L43.2765 175.768L175.798 405.282L112.854 405.279L9.32197 225.972Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M345.247 111.915C329.566 99.2919 312.229 89.5371 293.235 82.6512L235.167 183.228C254.161 190.114 271.498 199.869 287.179 212.492L345.247 111.915Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M382.686 154.964C373.41 138.898 360.931 124.553 345.25 111.93L287.182 212.506C302.863 225.13 315.342 239.475 324.618 255.541L382.686 154.964Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M293.243 82.6472C274.139 75.57 254.869 72.031 235.434 72.0303L177.366 172.607C196.801 172.608 216.071 176.147 235.175 183.224L293.243 82.6472Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M394.118 194.257C395.112 182.973 391.301 169.872 382.688 154.953L324.619 255.53C333.233 270.448 337.044 283.55 336.05 294.834L394.118 194.257Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M235.432 72.0311C216.88 72.0304 202.027 75.5681 190.875 82.6442L132.806 183.221C143.959 176.145 158.812 172.607 177.363 172.608L235.432 72.0311Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M265.59 124.25C276.191 124.251 286.24 127.12 295.737 132.858L237.669 233.435C228.172 227.697 218.123 224.828 207.522 224.827L265.59 124.25Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M295.719 132.859C305.326 138.406 312.78 145.77 318.081 154.95L260.013 255.527C254.712 246.347 247.258 238.983 237.651 233.436L295.719 132.859Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M387.218 217.608C391.227 210.66 393.527 202.874 394.117 194.25L336.049 294.827C335.459 303.451 333.159 311.237 329.15 318.185L387.218 217.608Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M245.211 132.577C248.413 127.03 255.204 124.257 265.584 124.258L207.516 224.835C197.136 224.834 190.345 227.607 187.143 233.154L245.211 132.577Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M318.094 154.945C322.842 163.17 324.002 171.107 321.573 178.757L263.505 279.334C265.934 271.684 264.774 263.746 260.026 255.522L318.094 154.945Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M176.925 96.6737C180.127 91.1249 184.776 86.4503 190.871 82.6499L132.803 183.227C126.708 187.027 122.059 191.702 118.857 197.25L176.925 96.6737Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M387.226 217.606C385.989 219.749 384.59 221.813 383.028 223.797L324.96 324.373C326.522 322.39 327.921 320.326 329.157 318.183L387.226 217.606Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M317.269 188.408C319.087 185.262 320.519 182.045 321.562 178.758L263.494 279.335C262.451 282.622 261.019 285.839 259.201 288.985L317.269 188.408Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M245.208 132.573C241.895 137.928 243 145.387 248.522 154.95L190.454 255.527C184.932 245.964 183.827 238.505 187.14 233.15L245.208 132.573Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M176.93 96.6719C174.331 101.175 172.686 106.255 171.993 111.91L113.925 212.487C114.618 206.831 116.263 201.752 118.862 197.249L176.93 96.6719Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M317.266 188.413C314.853 192.589 311.757 196.64 307.978 200.566L249.91 301.143C253.689 297.216 256.785 293.166 259.198 288.99L317.266 188.413Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M464.198 304.708L435.375 254.789L377.307 355.366L406.13 405.285L464.198 304.708Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M353.209 254.787C366.68 242.548 376.618 232.22 383.023 223.805L324.955 324.382C318.55 332.797 308.612 343.124 295.141 355.364L353.209 254.787Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M435.37 254.787L353.212 254.784L295.144 355.361L377.302 355.364L435.37 254.787Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M183.921 154.947L248.521 154.95L190.453 255.527L125.853 255.524L183.921 154.947Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M171.992 111.914C170.668 124.537 174.643 138.881 183.92 154.947L125.852 255.524C116.575 239.458 112.599 225.114 113.924 212.491L171.992 111.914Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M307.987 200.562C301.251 207.256 291.203 216.244 277.842 227.528L219.774 328.105C233.135 316.821 243.183 307.832 249.919 301.139L307.987 200.562Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M15.5469 75.1797L44.5359 125.386L-13.5321 225.963L-42.5212 175.756L15.5469 75.1797Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M277.836 227.536C264.033 238.82 253.708 247.904 246.862 254.789L188.794 355.366C195.64 348.481 205.965 339.397 219.768 328.113L277.836 227.536Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M275.358 304.706L464.189 304.713L406.12 405.29L217.29 405.283L275.358 304.706Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M44.5279 125.39L67.3864 125.39L9.31834 225.967L-13.5401 225.966L44.5279 125.39Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M101.341 75.1911L233.863 304.705L175.795 405.282L43.2733 175.768L101.341 75.1911ZM15.5431 75.19L-42.525 175.767L43.277 175.77L101.345 75.1932L15.5431 75.19Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M246.866 254.784L246.534 254.784L188.466 355.361L188.798 355.361L246.866 254.784Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M246.539 254.781L275.362 304.701L217.294 405.277L188.471 355.358L246.539 254.781Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M67.3906 125.391L170.923 304.698L112.855 405.275L9.32257 225.967L67.3906 125.391Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                            <path d="M170.921 304.699L233.865 304.701L175.797 405.278L112.853 405.276L170.921 304.699Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="bevel"/>
                        </g>
                        <g class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4" style="mix-blend-mode:hard-light">
                            <path d="M246.544 254.79L246.875 254.79C253.722 247.905 264.046 238.82 277.849 227.537C291.21 216.253 301.259 207.264 307.995 200.57C314.62 193.685 319.147 186.418 321.577 178.768C324.006 171.117 322.846 163.18 318.097 154.956C312.796 145.775 305.342 138.412 295.735 132.865C286.238 127.127 276.189 124.258 265.588 124.257C255.208 124.257 248.416 127.03 245.214 132.576C241.902 137.931 243.006 145.39 248.528 154.953L183.928 154.951C174.652 138.885 170.676 124.541 172 111.918C173.546 99.2946 179.84 89.5408 190.882 82.6559C202.035 75.5798 216.887 72.0421 235.439 72.0428C254.874 72.0435 274.144 75.5825 293.248 82.6598C312.242 89.5457 329.579 99.3005 345.261 111.924C360.942 124.548 373.421 138.892 382.697 154.958C391.311 169.877 395.121 182.978 394.128 194.262C393.355 205.546 389.656 215.396 383.031 223.811C376.627 232.226 366.688 242.554 353.217 254.794L435.375 254.797L464.198 304.716L275.367 304.709L246.544 254.79Z" fill="#4B0600"/>
                            <path d="M246.544 254.79L246.875 254.79C253.722 247.905 264.046 238.82 277.849 227.537C291.21 216.253 301.259 207.264 307.995 200.57C314.62 193.685 319.147 186.418 321.577 178.768C324.006 171.117 322.846 163.18 318.097 154.956C312.796 145.775 305.342 138.412 295.735 132.865C286.238 127.127 276.189 124.258 265.588 124.257C255.208 124.257 248.416 127.03 245.214 132.576C241.902 137.931 243.006 145.39 248.528 154.953L183.928 154.951C174.652 138.885 170.676 124.541 172 111.918C173.546 99.2946 179.84 89.5408 190.882 82.6559C202.035 75.5798 216.887 72.0421 235.439 72.0428C254.874 72.0435 274.144 75.5825 293.248 82.6598C312.242 89.5457 329.579 99.3005 345.261 111.924C360.942 124.548 373.421 138.892 382.697 154.958C391.311 169.877 395.121 182.978 394.128 194.262C393.355 205.546 389.656 215.396 383.031 223.811C376.627 232.226 366.688 242.554 353.217 254.794L435.375 254.797L464.198 304.716L275.367 304.709L246.544 254.79Z" stroke="#FF750F" stroke-width="1" stroke-linejoin="round"/>
                        </g>
                        <g class="transition-all delay-300 translate-y-0 opacity-100 duration-750 starting:opacity-0 starting:translate-y-4" style="mix-blend-mode:hard-light">
                            <path d="M67.41 125.402L44.5515 125.401L15.5625 75.1953L101.364 75.1985L233.886 304.712L170.942 304.71L67.41 125.402Z" fill="#4B0600"/>
                            <path d="M67.41 125.402L44.5515 125.401L15.5625 75.1953L101.364 75.1985L233.886 304.712L170.942 304.71L67.41 125.402Z" stroke="#FF750F" stroke-width="1"/>
                        </g>
                    </svg>
                    <div class="absolute inset-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"></div>
                </div>
            </main>
        </div>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>

````

## sistema/resources/views/subir-ecg.blade.php

````blade
@extends('plantillas.aplicacion')

@section('title', 'Subir ECG')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Analisis de ECG</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Sube un archivo ECG para analizarlo con la red neuronal.
    </p>
@endsection

@section('content')
<div x-data="ecgUpload()" class="space-y-6">
    <div x-show="!file" class="animate-fade-in-up">
        <div
            class="dropzone"
            :class="dragOver ? 'drag-over' : ''"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleDrop($event)"
            @click="$refs.fileInput.click()"
        >
            <input type="file" x-ref="fileInput" class="hidden" accept=".png,.jpg,.jpeg,.pdf,.csv,.txt" @change="handleFileChange($event)" />

            <div class="flex flex-col items-center gap-4">
                <div class="p-5 rounded-full" style="background:hsl(var(--primary)/0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </div>
                <div class="text-center">
                    <p class="text-lg font-semibold">Arrastra tu archivo ECG aqui</p>
                    <p class="text-sm text-muted-foreground mt-1">
                        o <span class="text-primary cursor-pointer hover:underline">haz clic para seleccionar</span>
                    </p>
                    <p class="text-xs text-muted-foreground mt-3">Formatos soportados: PNG, JPG, JPEG, PDF, CSV, TXT</p>
                </div>
            </div>
        </div>
    </div>

    <div x-show="file && !result" class="animate-fade-in-up">
        <div class="card">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded-xl shrink-0" style="background:hsl(var(--primary)/0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="font-semibold truncate" x-text="file?.name"></p>
                    <p class="text-sm text-muted-foreground" x-text="file ? (file.size / 1024).toFixed(1) + ' KB' : ''"></p>

                    <div x-show="preview" class="mt-4 relative">
                        <img :src="preview" alt="Vista previa" class="rounded-lg max-h-64 object-contain border border-border" />
                    </div>

                    <div x-show="analysisError" class="alert-error mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2" />
                            <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                        </svg>
                        <span x-text="analysisError"></span>
                    </div>
                </div>

                <button @click="resetFile()" class="p-2 rounded-lg text-muted-foreground hover:bg-secondary transition-colors shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Edad (aÃ±os)</label>
                    <input type="number" x-model.number="patientAge" min="0" max="120" step="1" class="input-field" placeholder="Ej: 45" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Sexo</label>
                    <select x-model.number="patientSex" class="input-field">
                        <option value="">Seleccionar</option>
                        <option value="0">Femenino</option>
                        <option value="1">Masculino</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-muted-foreground mb-1">Peso (kg)</label>
                    <input type="number" x-model.number="patientWeight" min="1" max="300" step="0.1" class="input-field" placeholder="Ej: 70" />
                </div>
            </div>

            <div x-show="metaError" class="alert-error mt-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                </svg>
                <span x-text="metaError"></span>
            </div>

            <div class="mt-4 flex gap-3">
                <button @click="analyzeECG()" :disabled="isAnalyzing" class="btn-primary glow-cyan flex-1">
                    <template x-if="isAnalyzing">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                            </svg>
                            Analizando ECG...
                        </span>
                    </template>
                    <template x-if="!isAnalyzing">
                        <span class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                            </svg>
                            Analizar ECG
                        </span>
                    </template>
                </button>

                <button @click="resetFile()" class="px-4 py-3 rounded-lg border border-border text-muted-foreground hover:bg-secondary transition-colors">
                    Cancelar
                </button>
            </div>

            <div x-show="isAnalyzing" class="mt-4">
                <div class="progress-bar">
                    <div class="progress-bar-fill animate-pulse" :style="`width:${progressPct}%;`"></div>
                </div>
                <p class="text-xs text-muted-foreground mt-2 text-center" x-text="progressMsg"></p>
            </div>
        </div>
    </div>

    <div x-show="result" class="animate-fade-in-up space-y-6">
        <div
            class="p-6 rounded-xl border"
            :style="result?.type === 'normal' ? 'background:hsl(var(--success)/0.05);border-color:hsl(var(--success)/0.3);' : 'background:hsl(var(--warning)/0.05);border-color:hsl(var(--warning)/0.3);'"
        >
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-4 rounded-xl" :style="result?.type === 'normal' ? 'background:hsl(var(--success)/0.15);' : 'background:hsl(var(--warning)/0.15);'">
                        <template x-if="result?.type === 'normal'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="result?.type !== 'normal'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold" :class="result?.type === 'normal' ? 'text-success' : 'text-warning'" x-text="result?.type === 'normal' ? 'Ritmo Normal' : 'Arritmia Detectada'"></h2>
                        <p class="text-muted-foreground" x-text="result?.rhythm"></p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="text-center px-4 py-2 rounded-lg bg-card border border-border">
                        <p class="font-mono font-bold text-primary" x-text="result?.probability + '%'"></p>
                        <p class="text-xs text-muted-foreground">Probabilidad</p>
                    </div>
                    <div class="text-center px-4 py-2 rounded-lg bg-card border border-border">
                        <p class="font-mono font-bold text-primary" x-text="result?.confidence"></p>
                        <p class="text-xs text-muted-foreground">Confianza</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" x-show="result?.top_predictions?.length > 0">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Top 5 Predicciones del Modelo
            </h3>
            <div class="space-y-2">
                <template x-for="(pred, i) in result?.top_predictions ?? []" :key="i">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono w-8 text-muted-foreground" x-text="pred.code"></span>
                        <div class="flex-1 relative h-7 rounded-lg overflow-hidden bg-muted/40">
                            <div class="h-full rounded-lg transition-all duration-500" :style="`width:${pred.probability}%; background:hsl(var(--primary)/0.6)`"></div>
                            <span class="absolute inset-0 flex items-center px-2 text-xs font-medium" x-text="pred.label"></span>
                        </div>
                        <span class="text-xs font-mono w-14 text-right text-primary font-bold" x-text="pred.probability + '%'"></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="card">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Recomendaciones Clinicas
            </h3>
            <ul class="space-y-2">
                <template x-for="(rec, i) in result?.recommendations ?? []" :key="i">
                    <li class="flex items-center gap-3 p-3 rounded-lg bg-muted/40">
                        <div class="w-2 h-2 rounded-full bg-primary shrink-0"></div>
                        <span class="text-sm" x-text="rec"></span>
                    </li>
                </template>
            </ul>
        </div>

        <div x-show="chartData.length > 0" class="card">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                </svg>
                Senal ECG - Derivaciones
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="ecgCharts"></div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button @click="downloadReport()" class="btn-primary flex-1 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Descargar Reporte
            </button>
            <button @click="resetAll()" class="flex-1 px-4 py-3 rounded-lg border border-border font-semibold text-foreground hover:bg-secondary transition-colors flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Nuevo Analisis
            </button>
        </div>
    </div>
</div>

<script>
const API_BASE_URL = '{{ env("ECG_API_URL", "http://localhost:8001") }}';
const ANALYZE_URL = '{{ route("analyze") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';
const LEADS_ORDER = ['I', 'II', 'III', 'aVR', 'aVL', 'aVF', 'V1', 'V2', 'V3', 'V4', 'V5', 'V6'];
const NORMAL_RECS = [
    'Continuar con monitoreo regular',
    'Proxima revision en 6 meses',
    'Mantener habitos de vida saludables',
];
const ARRHYTHMIA_RECS = [
    'Se recomienda evaluacion cardiologica',
    'Considerar monitoreo Holter 24h',
    'Evaluar factores de riesgo cardiovascular',
];

function ecgUpload() {
    return {
        file: null,
        preview: null,
        previewUrl: null,
        dragOver: false,
        patientAge: '',
        patientSex: '',
        patientWeight: '',
        metaError: null,
        isAnalyzing: false,
        progressPct: 0,
        progressMsg: 'Iniciando analisis...',
        analysisError: null,
        result: null,
        chartData: [],

        handleFileChange(event) {
            const file = event.target.files?.[0];
            if (file) this.loadFile(file);
        },

        handleDrop(event) {
            this.dragOver = false;
            const file = event.dataTransfer.files?.[0];
            if (file) this.loadFile(file);
        },

        loadFile(file) {
            this.releasePreview();
            this.file = file;
            this.result = null;
            this.analysisError = null;
            this.metaError = null;
            this.chartData = [];

            const imageTypes = ['image/png', 'image/jpg', 'image/jpeg'];
            if (imageTypes.includes(file.type)) {
                this.previewUrl = URL.createObjectURL(file);
                this.preview = this.previewUrl;
                return;
            }

            if (file.type === 'application/pdf') {
                this.fetchPdfPreview(file);
                return;
            }

            this.preview = null;
        },

        async fetchPdfPreview(file) {
            try {
                const form = new FormData();
                form.append('file', file);

                const response = await fetch(`${API_BASE_URL}/preview`, { method: 'POST', body: form });
                if (!response.ok) return;

                const data = await response.json();
                this.preview = data.image ?? null;
            } catch {}
        },

        releasePreview() {
            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
            }
        },

        resetFile() {
            this.releasePreview();
            this.file = null;
            this.preview = null;
            this.result = null;
            this.analysisError = null;
            this.metaError = null;
            this.chartData = [];
        },

        resetAll() {
            this.resetFile();
        },

        validateMeta() {
            if (this.patientAge === '' || this.patientAge === null) {
                this.metaError = 'Ingresa la edad del paciente.';
                return false;
            }
            if (this.patientSex === '' || this.patientSex === null) {
                this.metaError = 'Selecciona el sexo del paciente.';
                return false;
            }
            if (this.patientWeight === '' || this.patientWeight === null) {
                this.metaError = 'Ingresa el peso del paciente.';
                return false;
            }

            this.metaError = null;
            return true;
        },

        async analyzeECG() {
            if (!this.file || !this.validateMeta()) return;

            this.isAnalyzing = true;
            this.analysisError = null;
            this.progressPct = 10;
            this.progressMsg = 'Enviando archivo al servidor...';

            try {
                const extension = this.file.name.split('.').pop()?.toLowerCase();
                if (['csv', 'txt'].includes(extension)) {
                    await this.analyzeCSV();
                } else {
                    await this.analyzeImageOrPdf();
                }
            } catch (error) {
                this.analysisError = error?.message ?? 'Error al analizar el archivo.';
            } finally {
                this.isAnalyzing = false;
                this.progressPct = 0;
            }
        },

        async analyzeImageOrPdf() {
            const form = new FormData();
            form.append('file', this.file);
            form.append('age', String(this.patientAge));
            form.append('sex', String(this.patientSex));
            form.append('weight', String(this.patientWeight));
            form.append('_token', CSRF_TOKEN);

            this.progressPct = 30;
            this.progressMsg = 'Digitalizando derivaciones ECG...';

            const response = await fetch(ANALYZE_URL, {
                method: 'POST',
                body: form,
            });

            this.progressPct = 85;
            this.progressMsg = 'Ejecutando modelo de prediccion...';

            if (!response.ok) {
                throw new Error(await this.extractError(response));
            }

            const data = await response.json();
            this.progressPct = 100;
            this.setResult(data);
        },

        async analyzeCSV() {
            const signal = await this.parseCSVFile(this.file);
            const csv = this.signalToCsv(signal);
            const blob = new Blob([csv], { type: 'text/csv' });
            const form = new FormData();
            form.append('file', blob, this.file.name);
            form.append('age', String(this.patientAge));
            form.append('sex', String(this.patientSex));
            form.append('weight', String(this.patientWeight));
            form.append('_token', CSRF_TOKEN);

            this.progressPct = 50;
            this.progressMsg = 'Ejecutando modelo de prediccion...';

            const response = await fetch(ANALYZE_URL, {
                method: 'POST',
                body: form,
            });

            if (!response.ok) {
                throw new Error(await this.extractError(response));
            }

            const data = await response.json();
            this.progressPct = 100;
            const signals = data.signals?.length ? data.signals : signal;
            this.setResult(data, signals);
        },

        async extractError(response) {
            let detail = 'Error en el servidor de analisis.';

            try {
                const payload = await response.json();
                detail = payload?.error ?? payload?.detail ?? payload?.message ?? detail;
            } catch {}

            return detail;
        },

        setResult(data, signalOverride = null) {
            const isNormal = data.label?.toLowerCase().includes('normal');
            const rawConfidence = Number(
                data.confidence ?? Math.round(Math.max(...(data.scores ?? [0.82])) * 100)
            );
            const confidence = Number.isFinite(rawConfidence) ? rawConfidence : 0;
            const topPredictions = (data.top_predictions ?? []).map((prediction) => ({
                ...prediction,
                probability: Number(prediction.probability ?? 0),
            }));

            this.result = {
                rhythm: isNormal ? 'Ritmo Sinusal Normal' : (data.label ?? 'Arritmia Detectada'),
                probability: Math.round(confidence),
                type: isNormal ? 'normal' : 'arritmia',
                confidence: confidence > 90 ? 'Alta' : confidence > 75 ? 'Media' : 'Baja',
                recommendations: isNormal ? NORMAL_RECS : ARRHYTHMIA_RECS,
                metrics: data.metrics ?? null,
                top_predictions: topPredictions,
            };

            const signals = signalOverride ?? data.signals ?? null;
            if (signals?.length) {
                this.chartData = signals;
                this.$nextTick(() => this.renderCharts(signals));
            }
        },

        parseCSVFile(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onerror = () => reject(new Error('No se pudo leer el archivo.'));
                reader.onload = () => {
                    const rows = reader.result
                        .split(/\r?\n/)
                        .map((line) => line.trim())
                        .filter(Boolean)
                        .map((line) => line.split(/[;,\t ]+/).filter(Boolean).map(Number));

                    if (!rows.length) {
                        reject(new Error('Archivo vacio.'));
                        return;
                    }

                    if (rows[0].length === 12 && rows.length >= 1000) {
                        resolve(Array.from({ length: 12 }, (_, columnIndex) => rows.slice(0, 1000).map((row) => row[columnIndex])));
                        return;
                    }

                    if (rows.length === 12) {
                        resolve(rows.map((row) => row.slice(0, 1000)));
                        return;
                    }

                    reject(new Error('El CSV debe tener 12 columnas y al menos 1000 filas, o 12 filas de senales.'));
                };
                reader.readAsText(file);
            });
        },

        signalToCsv(signal) {
            const length = Math.max(...signal.map((channel) => channel.length));
            const rows = [];

            for (let index = 0; index < length; index++) {
                rows.push(signal.map((channel) => channel[index] ?? '').join(','));
            }

            return rows.join('\n');
        },

        renderCharts(signal) {
            const container = document.getElementById('ecgCharts');
            if (!container) return;

            container.innerHTML = '';

            const canvases = [];
            signal.forEach((channel, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'p-3 rounded-lg border border-border';
                wrapper.style.background = 'hsl(var(--muted)/0.3)';

                const label = document.createElement('p');
                label.className = 'text-xs font-mono font-bold text-primary mb-2';
                label.textContent = LEADS_ORDER[index] ?? `CH${index + 1}`;

                const canvas = document.createElement('canvas');
                canvas.style.width = '100%';
                canvas.style.display = 'block';
                canvas.height = 100;

                wrapper.appendChild(label);
                wrapper.appendChild(canvas);
                container.appendChild(wrapper);
                canvases.push({ canvas, channel });
            });

            requestAnimationFrame(() => {
                canvases.forEach(({ canvas, channel }) => {
                    const width = canvas.offsetWidth || canvas.parentElement?.offsetWidth || 400;
                    canvas.width = width;

                    const ctx = canvas.getContext('2d');
                    const points = this.downsampleSignal(channel, Math.max(width * 2, 600));
                    const bounds = this.getSignalBounds(points);
                    const range = bounds.max - bounds.min || 1;
                    const height = 100;
                    const padding = 6;

                    ctx.clearRect(0, 0, width, height);
                    ctx.beginPath();
                    ctx.strokeStyle = 'hsl(var(--primary))';
                    ctx.lineWidth = 1.2;

                    points.forEach((value, pointIndex) => {
                        const px = points.length > 1 ? (pointIndex / (points.length - 1)) * width : 0;
                        const py = height - padding - ((value - bounds.min) / range) * (height - padding * 2);
                        if (pointIndex === 0) {
                            ctx.moveTo(px, py);
                        } else {
                            ctx.lineTo(px, py);
                        }
                    });

                    ctx.stroke();
                });
            });
        },

        downsampleSignal(channel, maxPoints) {
            if (!Array.isArray(channel) || channel.length <= maxPoints) {
                return channel;
            }

            const step = channel.length / maxPoints;
            const result = [];

            for (let index = 0; index < maxPoints; index++) {
                result.push(channel[Math.floor(index * step)]);
            }

            result[result.length - 1] = channel[channel.length - 1];
            return result;
        },

        getSignalBounds(points) {
            let min = Infinity;
            let max = -Infinity;

            for (const value of points) {
                if (value < min) min = value;
                if (value > max) max = value;
            }

            if (!Number.isFinite(min) || !Number.isFinite(max)) {
                return { min: 0, max: 1 };
            }

            return { min, max };
        },

        downloadReport() {
            if (!this.result) return;

            const lines = [
                'REPORTE ECG - ECG Analyzer',
                '===========================',
                `Archivo:  ${this.file?.name ?? 'N/A'}`,
                `Fecha:    ${new Date().toLocaleString('es-PE')}`,
                `Paciente: Edad ${this.patientAge} anos | Sexo ${this.patientSex == 1 ? 'Masculino' : 'Femenino'} | Peso ${this.patientWeight} kg`,
                '',
                `Resultado:    ${this.result.type === 'normal' ? 'NORMAL' : 'ARRITMIA'}`,
                `Diagnostico:  ${this.result.rhythm}`,
                `Confianza:    ${this.result.probability}% (${this.result.confidence})`,
                '',
                ...(this.result.top_predictions?.length ? [
                    'Top predicciones:',
                    ...this.result.top_predictions.map((prediction) => `  [${prediction.code}] ${prediction.label}: ${prediction.probability}%`),
                    '',
                ] : []),
                'Recomendaciones:',
                ...this.result.recommendations.map((recommendation) => `  - ${recommendation}`),
                '',
                'ADVERTENCIA: Este analisis es orientativo. Requiere validacion medica.',
                'Sistema de diagnostico asistido por IA - ECG-Net v2.1',
            ];

            const blob = new Blob([lines.join('\n')], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const anchor = document.createElement('a');
            anchor.href = url;
            anchor.download = `reporte_ecg_${Date.now()}.txt`;
            anchor.click();
            URL.revokeObjectURL(url);
        },
    };
}
</script>
@endsection

````

## sistema/resources/views/resumen.blade.php

````blade
@extends('plantillas.aplicacion')

@section('title', 'Resumen')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Resumen general</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Vista consolidada de los analisis ECG y la actividad reciente del sistema.
    </p>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2 px-4 py-2 rounded-full animate-fade-in-delay-2"
         style="background:hsl(var(--success)/0.1);border:1px solid hsl(var(--success)/0.2);">
        <div class="w-2 h-2 rounded-full animate-pulse" style="background:hsl(var(--success));"></div>
        <span class="text-sm font-medium text-success">Sistema activo</span>
    </div>
@endsection

@section('content')
<div class="space-y-8">
    @php
        $statsCount = count($stats);
        $statsGridClass = match (true) {
            $statsCount >= 4 => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6',
            $statsCount === 3 => 'grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6',
            $statsCount === 2 => 'grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6',
            default => 'grid grid-cols-1 gap-4 lg:gap-6',
        };
    @endphp

    <section>
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Resumen Estadistico
        </h2>
        <div class="{{ $statsGridClass }}">
            @foreach($stats as $i => $stat)
                <div class="card animate-fade-in-up group h-full min-h-[188px]" style="animation-delay:{{ $i * 100 }}ms;">
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none rounded-xl"
                         style="background:linear-gradient(to bottom right,hsl(var(--primary)/0.05),transparent);"></div>
                    <div class="relative flex items-start justify-between gap-4 h-full">
                        <div class="space-y-2 flex-1">
                            <p class="text-sm text-muted-foreground font-medium">{{ $stat['title'] }}</p>
                            <p class="text-3xl font-bold tracking-tight">{{ $stat['value'] }}</p>
                            <p class="text-sm text-muted-foreground">{{ $stat['subtitle'] }}</p>
                            <div class="inline-flex items-center gap-1 text-sm font-medium {{ $stat['trend'] === 'up' ? 'text-success' : ($stat['trend'] === 'down' ? 'text-destructive' : 'text-muted-foreground') }}">
                                <span>{!! $stat['trend'] === 'up' ? '&uarr;' : ($stat['trend'] === 'down' ? '&darr;' : '&rarr;') !!}</span>
                                <span>{{ $stat['trend_value'] }}</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl shrink-0 self-start" style="background:hsl(var(--primary)/0.1);">
                            @if($stat['icon'] === 'file-heart')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                             a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            @elseif($stat['icon'] === 'check-circle')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif($stat['icon'] === 'alert-triangle')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856
                                             c1.54 0 2.502-1.667 1.732-3L13.732 4
                                             c-.77-1.333-2.694-1.333-3.464 0
                                             L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 animate-fade-in-up" style="animation-delay:400ms;">
            <div class="card">
                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                    </svg>
                    Actividad Reciente
                </h2>
                <div class="space-y-3">
                    @foreach($recentActivity as $item)
                        <div class="flex items-center justify-between p-3 rounded-lg transition-colors"
                             style="background:hsl(var(--muted)/0.5);">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full"
                                     style="background:hsl(var(--{{ $item['type'] === 'Normal' ? 'success' : 'warning' }}));"></div>
                                <div>
                                    <p class="text-sm font-medium">{{ $item['file'] }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $item['time'] }}</p>
                                </div>
                            </div>
                            <span class="badge {{ $item['type'] === 'Normal' ? 'badge-success' : 'badge-warning' }}">
                                {{ $item['type'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="animate-fade-in-up" style="animation-delay:500ms;">
            <div class="card h-full">
                <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                    </svg>
                    Acciones Rapidas
                </h2>
                <div class="space-y-3">
                    <a href="{{ route('upload') }}"
                       class="block p-4 rounded-lg transition-all group"
                       style="background:hsl(var(--primary)/0.1);border:1px solid hsl(var(--primary)/0.2);"
                       onmouseover="this.style.background='hsl(var(--primary)/0.2)'"
                       onmouseout="this.style.background='hsl(var(--primary)/0.1)'">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg text-primary transition-transform group-hover:scale-110"
                                 style="background:hsl(var(--primary)/0.2);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Nuevo Analisis</p>
                                <p class="text-xs text-muted-foreground">Subir archivo ECG</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('history') }}"
                       class="block p-4 rounded-lg transition-all group bg-secondary hover:bg-secondary/80">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-muted text-muted-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                    <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Ver Historial</p>
                                <p class="text-xs text-muted-foreground">Reportes anteriores</p>
                            </div>
                        </div>
                    </a>

                    <div class="p-4 rounded-lg border border-border" style="background:hsl(var(--muted)/0.3);">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-muted text-muted-foreground">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                                             M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                                             m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-muted-foreground">Multi-usuario</p>
                                <p class="text-xs text-muted-foreground">Proximamente</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

````

## sistema/resources/views/historial.blade.php

````blade
@extends('plantillas.aplicacion')

@section('title', 'Historial')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Historial de Analisis</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Consulta analisis anteriores y anade valoraciones medicas.
    </p>
@endsection

@section('content')
<div
    class="space-y-6"
    x-data="historyPage({
        history: {{ Js::from($history->items()) }},
        csrf: '{{ csrf_token() }}',
    })"
>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 animate-fade-in-up">
        <div class="card">
            <p class="text-sm text-muted-foreground">Total Analisis</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--success)/0.05);border-color:hsl(var(--success)/0.2);">
            <p class="text-sm text-muted-foreground">Normales</p>
            <p class="text-2xl font-bold text-success mt-1">{{ number_format($stats['normales']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--warning)/0.05);border-color:hsl(var(--warning)/0.2);">
            <p class="text-sm text-muted-foreground">Arritmias</p>
            <p class="text-2xl font-bold text-warning mt-1">{{ number_format($stats['arritmias']) }}</p>
        </div>
        <div class="card" style="background:hsl(var(--primary)/0.05);border-color:hsl(var(--primary)/0.2);">
            <p class="text-sm text-muted-foreground">Revisados</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ number_format($stats['revisados']) }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('history') }}" class="card animate-fade-in-up" style="animation-delay:100ms;">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_220px_auto] gap-4 items-end">
            <label class="filter-field">
                <span class="filter-label">Buscar</span>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Archivo, ritmo o diagnostico medico"
                    class="input-field"
                />
            </label>
            <label class="filter-field">
                <span class="filter-label">Filtro</span>
                <select name="filter" class="input-field">
                    <option value="all" @selected($filters['filter'] === 'all')>Todos</option>
                    <option value="normal" @selected($filters['filter'] === 'normal')>Solo normales</option>
                    <option value="arritmia" @selected($filters['filter'] === 'arritmia')>Solo arritmias</option>
                    <option value="reviewed" @selected($filters['filter'] === 'reviewed')>Con valoracion</option>
                    <option value="unreviewed" @selected($filters['filter'] === 'unreviewed')>Sin valoracion</option>
                </select>
            </label>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1">Aplicar</button>
                <a href="{{ route('history') }}" class="filter-clear-btn flex-1">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="rounded-xl bg-card border border-border shadow-card overflow-hidden animate-fade-in-up" style="animation-delay:200ms;">
        <div class="hidden md:block overflow-x-auto">
            <table class="table-ecg">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Paciente</th>
                        <th>Fecha</th>
                        <th>Ritmo Detectado</th>
                        <th>Probabilidad</th>
                        <th>Estado IA</th>
                        <th>Valoracion Medica</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in history" :key="item.id">
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg" style="background:hsl(var(--primary)/0.1);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium" x-text="item.filename"></span>
                                </div>
                            </td>
                            <td>
                                <span class="font-mono text-xs font-semibold text-primary" x-text="item.patient"></span>
                            </td>
                            <td>
                                <div class="flex items-center gap-1 text-muted-foreground text-xs">
                                    <span x-text="item.date"></span>
                                    <span>&bull;</span>
                                    <span x-text="item.time"></span>
                                </div>
                            </td>
                            <td x-text="item.rhythm"></td>
                            <td><span class="font-mono text-sm" x-text="item.probability + '%'"></span></td>
                            <td>
                                <span class="badge" :class="item.type === 'normal' ? 'badge-success' : 'badge-warning'" x-text="item.result"></span>
                            </td>
                            <td>
                                <template x-if="item.doctor_result">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="badge" :class="item.doctor_result === 'normal' ? 'badge-success' : 'badge-warning'" x-text="item.doctor_result === 'normal' ? 'Normal' : 'Arritmia'"></span>
                                        <span class="text-xs text-muted-foreground" x-show="item.doctor_label" x-text="item.doctor_label || ''"></span>
                                    </div>
                                </template>
                                <template x-if="!item.doctor_result">
                                    <span class="text-xs text-muted-foreground italic">Sin valorar</span>
                                </template>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        @click="openReview(item)"
                                        class="p-2 rounded-lg transition-colors"
                                        :class="item.doctor_result ? 'hover:bg-primary/10 text-primary' : 'hover:bg-secondary text-muted-foreground'"
                                        :title="item.doctor_result ? 'Editar valoracion' : 'Anadir valoracion medica'"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y border-border">
            <template x-for="item in history" :key="item.id">
                <div class="p-4 hover:bg-muted/30 transition-colors">
                    <div class="flex items-start justify-between mb-3 gap-3">
                        <div>
                            <p class="font-medium text-sm" x-text="item.filename"></p>
                            <p class="text-xs font-mono text-primary" x-text="item.patient"></p>
                            <p class="text-xs text-muted-foreground" x-text="item.date + ' â€¢ ' + item.time"></p>
                        </div>
                        <span class="badge" :class="item.type === 'normal' ? 'badge-success' : 'badge-warning'" x-text="item.result"></span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs text-muted-foreground" x-text="'Ritmo: ' + item.rhythm"></p>
                        <p class="text-xs font-mono text-muted-foreground" x-text="'Prob.: ' + item.probability + '%'"></p>
                        <template x-if="item.doctor_result">
                            <p class="text-xs font-medium" :class="item.doctor_result === 'normal' ? 'text-success' : 'text-warning'" x-text="'Med.: ' + (item.doctor_result === 'normal' ? 'Normal' : 'Arritmia') + (item.doctor_label ? ' â€¢ ' + item.doctor_label : '')"></p>
                        </template>
                        <template x-if="!item.doctor_result">
                            <p class="text-xs text-muted-foreground italic">Sin valoracion medica</p>
                        </template>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button
                            type="button"
                            @click="openReview(item)"
                            class="p-2 rounded-lg hover:bg-secondary transition-colors"
                            :class="item.doctor_result ? 'text-primary' : 'text-muted-foreground'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        @if ($history->isEmpty())
            <div class="p-12 text-center">
                <p class="text-muted-foreground">No se encontraron resultados con los filtros actuales.</p>
            </div>
        @endif
    </div>

    @if ($history->hasPages())
        <div class="card">
            {{ $history->links() }}
        </div>
    @endif

    <div
        x-show="reviewModal.open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none; background:rgba(0,0,0,0.4); backdrop-filter:blur(4px);"
        @click.self="reviewModal.open = false"
    >
        <div
            x-show="reviewModal.open"
            x-transition
            class="w-full max-w-md rounded-2xl shadow-2xl p-6"
            style="background:hsl(var(--card)); border:1px solid hsl(var(--border));"
        >
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="font-semibold">Valoracion Medica</h3>
                    <p class="text-xs text-muted-foreground">Resultado del especialista</p>
                </div>
                <button type="button" @click="reviewModal.open = false" class="p-1.5 rounded-lg hover:bg-secondary transition-colors text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Diagnostico medico <span class="text-destructive">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer">
                        <input type="radio" x-model="reviewModal.result" value="normal" class="sr-only peer">
                        <div class="p-3 rounded-xl border-2 text-center transition-all peer-checked:border-success peer-checked:bg-success/10 peer-checked:text-success border-border hover:border-success/50">
                            <p class="text-sm font-semibold">Normal</p>
                        </div>
                    </label>
                    <label class="relative cursor-pointer">
                        <input type="radio" x-model="reviewModal.result" value="arritmia" class="sr-only peer">
                        <div class="p-3 rounded-xl border-2 text-center transition-all peer-checked:border-warning peer-checked:bg-warning/10 peer-checked:text-warning border-border hover:border-warning/50">
                            <p class="text-sm font-semibold">Arritmia</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1.5">Diagnostico especifico</label>
                <input type="text" x-model="reviewModal.label" placeholder="Ej: Fibrilacion auricular" class="input-field w-full" />
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium mb-1.5">Notas clinicas</label>
                <textarea x-model="reviewModal.notes" rows="3" placeholder="Observaciones adicionales del medico" class="input-field w-full resize-none"></textarea>
            </div>

            <p x-show="reviewModal.error" x-text="reviewModal.error" class="text-sm text-destructive mb-4 px-3 py-2 rounded-lg" style="background:hsl(var(--destructive)/0.08);"></p>

            <div class="flex gap-3">
                <button type="button" @click="submitReview()" :disabled="reviewModal.saving" class="flex-1 py-2.5 rounded-xl font-semibold text-sm text-white transition-opacity hover:opacity-90 disabled:opacity-60" style="background:hsl(var(--primary));">
                    <span x-show="!reviewModal.saving">Guardar Valoracion</span>
                    <span x-show="reviewModal.saving">Guardando...</span>
                </button>
                <template x-if="findHistoryItem(reviewModal.id)?.doctor_result">
                    <button type="button" @click="removeReview()" :disabled="reviewModal.saving" class="px-4 py-2.5 rounded-xl font-medium text-sm text-destructive hover:bg-destructive/10 transition-colors disabled:opacity-60" style="border:1px solid hsl(var(--destructive)/0.3);">
                        Quitar
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function historyPage(config) {
    return {
        history: config.history ?? [],
        csrf: config.csrf,
        reviewModal: {
            open: false,
            id: null,
            result: '',
            label: '',
            notes: '',
            saving: false,
            error: '',
        },

        findHistoryItem(id) {
            return this.history.find((item) => item.id === id) ?? null;
        },

        openReview(item) {
            this.reviewModal = {
                open: true,
                id: item.id,
                result: item.doctor_result || '',
                label: item.doctor_label || '',
                notes: item.doctor_notes || '',
                saving: false,
                error: '',
            };
        },

        async submitReview() {
            if (!this.reviewModal.result) {
                this.reviewModal.error = 'Selecciona un resultado medico.';
                return;
            }

            this.reviewModal.saving = true;
            this.reviewModal.error = '';

            try {
                const resp = await fetch(`/history/${this.reviewModal.id}/review`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        doctor_result: this.reviewModal.result,
                        doctor_label: this.reviewModal.label,
                        doctor_notes: this.reviewModal.notes,
                    }),
                });

                const payload = await resp.json().catch(() => ({}));
                if (!resp.ok) {
                    throw new Error(payload.message || payload.error || 'No se pudo guardar la valoracion.');
                }

                const item = this.findHistoryItem(this.reviewModal.id);
                if (item) {
                    item.doctor_result = this.reviewModal.result;
                    item.doctor_label = this.reviewModal.label;
                    item.doctor_notes = this.reviewModal.notes;
                    item.reviewed_at = payload.reviewed_at ?? null;
                }

                this.reviewModal.open = false;
            } catch (error) {
                this.reviewModal.error = error.message || 'No se pudo guardar. Intenta de nuevo.';
            } finally {
                this.reviewModal.saving = false;
            }
        },

        async removeReview() {
            this.reviewModal.saving = true;
            this.reviewModal.error = '';

            try {
                const resp = await fetch(`/history/${this.reviewModal.id}/review`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                });

                const payload = await resp.json().catch(() => ({}));
                if (!resp.ok) {
                    throw new Error(payload.message || payload.error || 'No se pudo eliminar la valoracion.');
                }

                const item = this.findHistoryItem(this.reviewModal.id);
                if (item) {
                    item.doctor_result = null;
                    item.doctor_label = null;
                    item.doctor_notes = null;
                    item.reviewed_at = null;
                }

                this.reviewModal.open = false;
            } catch (error) {
                this.reviewModal.error = error.message || 'No se pudo eliminar la valoracion.';
            } finally {
                this.reviewModal.saving = false;
            }
        },
    };
}
</script>
@endsection

````

## sistema/resources/views/metricas.blade.php

````blade
@extends('plantillas.aplicacion')

@section('title', 'Dashboard')

@section('page-header')
    <div class="animate-fade-in">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-1 h-8 rounded-full" style="background: var(--gradient-primary);"></div>
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">
                <span class="gradient-text">Dashboard</span>
            </h1>
        </div>
        <p class="text-muted-foreground ml-4 pl-3 animate-fade-in-delay-1" style="border-left: 1px solid hsl(var(--border));">
            Estadisticas del sistema ECG filtradas por periodo Â· {{ $filters['label'] }}
        </p>
    </div>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2 animate-fade-in-delay-2">
        <a href="{{ route('dashboard.statistics.pdf', array_filter(['from' => $filters['from'], 'to' => $filters['to']])) }}"
           class="btn-primary text-sm py-2 px-3">
            Descargar estadisticas
        </a>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-mono"
             style="background: hsl(var(--primary)/0.1); border: 1px solid hsl(var(--primary)/0.3); color: hsl(var(--primary));">
            <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: hsl(var(--primary));"></span>
            {{ $filters['hasRange'] ? 'Rango activo' : 'Historico completo' }}
        </div>
    </div>
@endsection

@section('content')
<style>
    .dash-panel {
        background: hsl(var(--card));
        border: 1px solid hsl(var(--border));
        border-radius: 16px;
        box-shadow: var(--shadow-card);
        position: relative;
        overflow: hidden;
    }

    .dash-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, hsl(var(--primary) / 0.04), transparent 55%);
        pointer-events: none;
    }

    .metric-card {
        background: hsl(var(--card));
        border: 1px solid hsl(var(--border));
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-card);
        position: relative;
        overflow: hidden;
    }

    .metric-card::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 2px;
        background: var(--ring-color, hsl(var(--primary)));
        opacity: 0.45;
    }

    .ring-track { stroke: hsl(var(--secondary)); }
    .ring-fill { stroke-linecap: round; }

    .mini-metric {
        background: hsl(var(--background) / 0.55);
        border: 1px solid hsl(var(--border));
        border-radius: 14px;
        padding: 1rem 1.1rem;
    }

    .cm-box {
        border-radius: 14px;
        padding: 1rem;
        text-align: center;
        border: 1px solid transparent;
    }

    .cm-tp { background: hsl(160 84% 95%); border-color: hsl(160 70% 80%); }
    .cm-fp { background: hsl(38 92% 95%); border-color: hsl(38 70% 80%); }
    .cm-fn { background: hsl(0 84% 96%); border-color: hsl(0 65% 82%); }
    .cm-tn { background: hsl(198 100% 95%); border-color: hsl(198 80% 78%); }

    .roc-grid-line {
        stroke: hsl(var(--border));
        stroke-dasharray: 4 4;
        opacity: 0.9;
    }

    .roc-axis {
        stroke: hsl(var(--muted-foreground) / 0.45);
        stroke-width: 1.25;
    }

    .filter-panel {
        display: grid;
        gap: 0.85rem;
        align-items: center;
    }

    .filter-intro {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .filter-grid {
        display: grid;
        gap: 0.7rem;
        align-items: end;
    }

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .filter-label {
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: hsl(var(--muted-foreground));
    }

    .filter-actions {
        display: grid;
        gap: 0.6rem;
    }

    .filter-clear-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 42px;
        padding: 0.65rem 0.9rem;
        border-radius: 12px;
        border: 1px solid hsl(var(--border));
        background: hsl(var(--background) / 0.8);
        color: hsl(var(--foreground));
        font-size: 0.88rem;
        font-weight: 600;
        transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .filter-clear-btn:hover {
        background: hsl(var(--secondary));
        border-color: hsl(var(--primary) / 0.18);
        transform: translateY(-1px);
    }

    .filter-range-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        width: fit-content;
        padding: 0.45rem 0.72rem;
        border-radius: 999px;
        border: 1px solid hsl(var(--border));
        background: hsl(var(--background) / 0.78);
        color: hsl(var(--foreground));
        font-size: 0.72rem;
        font-family: "JetBrains Mono", monospace;
    }

    .confidence-spotlight {
        grid-column: span 2;
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        padding: 1.05rem 1.15rem;
        border: 1px solid hsl(var(--primary) / 0.18);
        background:
            radial-gradient(circle at top right, hsl(var(--accent) / 0.24), transparent 34%),
            linear-gradient(135deg, hsl(var(--primary) / 0.12), hsl(198 100% 97%));
        box-shadow: 0 10px 28px hsl(var(--primary) / 0.08);
    }

    .confidence-spotlight::after {
        content: '';
        position: absolute;
        right: -26px;
        top: -18px;
        width: 110px;
        height: 110px;
        border-radius: 999px;
        background: hsl(var(--primary) / 0.08);
        pointer-events: none;
    }

    .confidence-meta {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.32rem 0.68rem;
        border-radius: 999px;
        background: hsl(var(--card) / 0.72);
        border: 1px solid hsl(var(--primary) / 0.16);
        color: hsl(var(--primary));
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        position: relative;
        z-index: 1;
    }

    .confidence-value {
        position: relative;
        z-index: 1;
        margin-top: 0.7rem;
        display: flex;
        align-items: baseline;
        gap: 0.55rem;
        flex-wrap: wrap;
    }

    .confidence-value strong {
        font-size: clamp(1.75rem, 2.4vw, 2.35rem);
        line-height: 1;
        font-weight: 800;
        color: hsl(var(--primary));
        letter-spacing: -0.03em;
    }

    .confidence-value span {
        font-size: 0.82rem;
        color: hsl(var(--foreground) / 0.82);
        font-family: "JetBrains Mono", monospace;
    }

    .confidence-caption {
        position: relative;
        z-index: 1;
        margin-top: 0.5rem;
        font-size: 0.85rem;
        line-height: 1.45;
        color: hsl(var(--foreground) / 0.82);
        max-width: 34rem;
    }

    .confidence-caption b {
        color: hsl(var(--foreground));
    }

    @media (min-width: 1024px) {
        .filter-panel {
            grid-template-columns: auto minmax(0, 1fr);
            gap: 1rem;
        }

        .filter-grid {
            grid-template-columns: minmax(140px, 170px) minmax(140px, 170px) auto;
        }

        .filter-actions {
            grid-column: auto;
            grid-template-columns: auto auto;
        }
    }
</style>

<div class="space-y-6" x-data="metricsPage()" x-init="init()">
    <section class="dash-panel p-4 sm:p-5 animate-fade-in-up">
        <form method="GET" action="{{ route('dashboard') }}" class="filter-panel">
            <div class="filter-intro">
                <p class="text-base font-semibold">Filtrar por fechas</p>
                <div class="filter-range-chip">
                    <span class="w-2 h-2 rounded-full" style="background:hsl(var(--primary));"></span>
                    {{ $filters['label'] }}
                </div>
            </div>
            <div class="filter-grid">
                <label class="filter-field">
                    <span class="filter-label">Desde</span>
                    <input type="date" name="from" value="{{ $filters['from'] }}" class="input-field" />
                </label>
                <label class="filter-field">
                    <span class="filter-label">Hasta</span>
                    <input type="date" name="to" value="{{ $filters['to'] }}" class="input-field" />
                </label>
                <div class="filter-actions">
                    <button type="submit" class="btn-primary w-full">Aplicar filtro</button>
                    <a href="{{ route('dashboard') }}" class="filter-clear-btn">
                        Limpiar rango
                    </a>
                </div>
            </div>
        </form>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <template x-for="(metric, index) in metrics" :key="metric.key">
            <article class="metric-card animate-fade-in-up" :style="`animation-delay:${index * 90}ms; --ring-color:${metric.color}`">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground" x-text="metric.label"></p>
                        <p class="text-xs text-muted-foreground mt-1" x-text="metric.sublabel"></p>
                    </div>
                    <span class="text-xs font-mono px-2 py-0.5 rounded-full"
                          :style="`background:${metric.color}18; color:${metric.color}; border:1px solid ${metric.color}30`"
                          x-text="metric.badge"></span>
                </div>

                <div class="flex items-center justify-center">
                    <div class="relative">
                        <svg width="120" height="120" viewBox="0 0 120 120" style="transform: rotate(-90deg);">
                            <circle class="ring-track" cx="60" cy="60" r="50" fill="none" stroke-width="8"></circle>
                            <circle class="ring-fill"
                                    cx="60" cy="60" r="50" fill="none" stroke-width="8"
                                    :stroke="metric.color"
                                    :stroke-dasharray="314"
                                    :stroke-dashoffset="animated ? (314 * (1 - metric.value / 100)) : 314"
                                    style="transition: stroke-dashoffset 1.2s cubic-bezier(0.4,0,0.2,1);"></circle>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-mono font-bold text-2xl leading-none"
                                  :style="`color:${metric.color}`"
                                  x-text="metricDisplay[metric.key]"></span>
                            <span class="text-xs text-muted-foreground mt-1">score</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 h-1 rounded-full" style="background:hsl(var(--secondary));">
                    <div class="h-1 rounded-full"
                         :style="`width:${animated ? metric.value : 0}%; background:${metric.color}; box-shadow:0 0 8px ${metric.color}60; transition: width 1.2s cubic-bezier(0.4,0,0.2,1);`"></div>
                </div>

                <p class="text-xs text-muted-foreground mt-3 leading-relaxed" x-text="metric.desc"></p>
            </article>
        </template>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-5 gap-4">
        <div class="dash-panel p-5 xl:col-span-3 animate-fade-in-up" style="animation-delay:220ms;">
            <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                <div>
                    <h2 class="font-semibold text-base">Curva ROC</h2>
                    <p class="text-xs text-muted-foreground mt-1">
                        @if($rocData)
                            Construida con la confianza del modelo sobre casos revisados en el periodo.
                        @else
                            Se necesitan casos revisados de ambas clases para calcular la curva ROC.
                        @endif
                    </p>
                </div>

                @if($rocData)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-mono"
                         style="background:hsl(var(--primary)/0.08); border:1px solid hsl(var(--primary)/0.24); color:hsl(var(--primary));">
                        AUC = {{ number_format($rocData['auc'], 3) }}
                    </div>
                @endif
            </div>

            @if($rocData)
                @php
                    $svg = $rocData['svg'];
                    $plotRight = $svg['padding'] + $svg['plot_width'];
                    $plotBottom = $svg['padding'] + $svg['plot_height'];
                @endphp
                <div class="rounded-2xl border border-border p-3" style="background:hsl(var(--background)/0.55);">
                    <svg viewBox="0 0 {{ $svg['width'] }} {{ $svg['height'] }}" class="w-full h-auto">
                        @for($i = 0; $i <= 5; $i++)
                            @php
                                $x = $svg['padding'] + (($svg['plot_width'] / 5) * $i);
                                $y = $svg['padding'] + (($svg['plot_height'] / 5) * $i);
                            @endphp
                            <line x1="{{ $x }}" y1="{{ $svg['padding'] }}" x2="{{ $x }}" y2="{{ $plotBottom }}" class="roc-grid-line" />
                            <line x1="{{ $svg['padding'] }}" y1="{{ $y }}" x2="{{ $plotRight }}" y2="{{ $y }}" class="roc-grid-line" />
                        @endfor

                        <line x1="{{ $svg['padding'] }}" y1="{{ $plotBottom }}" x2="{{ $plotRight }}" y2="{{ $svg['padding'] }}"
                              stroke="hsl(var(--muted-foreground) / 0.35)" stroke-dasharray="6 5" />

                        <polyline points="{{ $rocData['area_points'] }}"
                                  fill="hsl(var(--primary) / 0.10)" stroke="none" />
                        <polyline points="{{ $rocData['line_points'] }}"
                                  fill="none" stroke="hsl(var(--primary))" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" />

                        <circle cx="{{ $rocData['svg']['best_x'] }}" cy="{{ $rocData['svg']['best_y'] }}" r="5"
                                fill="hsl(var(--primary))" stroke="hsl(var(--card))" stroke-width="2" />

                        <line x1="{{ $svg['padding'] }}" y1="{{ $plotBottom }}" x2="{{ $plotRight }}" y2="{{ $plotBottom }}" class="roc-axis" />
                        <line x1="{{ $svg['padding'] }}" y1="{{ $plotBottom }}" x2="{{ $svg['padding'] }}" y2="{{ $svg['padding'] }}" class="roc-axis" />

                        @for($i = 0; $i <= 5; $i++)
                            @php
                                $tick = number_format($i / 5, 1);
                                $x = $svg['padding'] + (($svg['plot_width'] / 5) * $i);
                                $y = $plotBottom - (($svg['plot_height'] / 5) * $i);
                            @endphp
                            <text x="{{ $x }}" y="{{ $plotBottom + 18 }}" text-anchor="middle" font-size="10" fill="hsl(var(--muted-foreground))">{{ $tick }}</text>
                            <text x="{{ $svg['padding'] - 10 }}" y="{{ $y + 3 }}" text-anchor="end" font-size="10" fill="hsl(var(--muted-foreground))">{{ $tick }}</text>
                        @endfor
                    </svg>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                    <div class="mini-metric">
                        <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground">AUC</p>
                        <p class="text-2xl font-bold text-primary mt-2">{{ number_format($rocData['auc'], 3) }}</p>
                        <p class="text-xs text-muted-foreground mt-1">Area bajo la curva ROC del rango.</p>
                    </div>
                    <div class="mini-metric">
                        <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground">Umbral optimo</p>
                        <p class="text-2xl font-bold mt-2" style="color:hsl(var(--success));">{{ number_format($rocData['best_threshold'] * 100, 1) }}%</p>
                        <p class="text-xs text-muted-foreground mt-1">Maximiza TPR - FPR sobre los revisados.</p>
                    </div>
                </div>
            @else
                <div class="mini-metric">
                    <p class="font-semibold mb-2">ROC no disponible en este rango</p>
                    <p class="text-sm text-muted-foreground">
                        Para calcular la curva ROC y el AUC se necesitan casos revisados por medico tanto normales como con arritmia dentro del periodo seleccionado.
                    </p>
                </div>
            @endif
        </div>

        <div class="dash-panel p-5 xl:col-span-2 animate-fade-in-up" style="animation-delay:280ms;">
            <div class="flex items-center justify-between gap-3 mb-5 flex-wrap">
                <div>
                    <h2 class="font-semibold text-base">Validacion Clinica del Rango</h2>
                    <p class="text-xs text-muted-foreground mt-1">
                        @if($realMetrics)
                            Basado en {{ number_format($realMetrics['reviewedCount']) }} analisis revisados por el medico.
                        @else
                            Aun no hay revisiones medicas suficientes dentro del periodo seleccionado.
                        @endif
                    </p>
                </div>
                <a href="{{ route('history') }}" class="text-sm font-medium text-primary hover:underline">Ir al historial</a>
            </div>

            @if($realMetrics)
                <div class="grid grid-cols-2 gap-3">
                    <div class="cm-box cm-tp">
                        <p class="text-xs font-mono text-success mb-1">Verdadero +</p>
                        <p class="text-3xl font-bold text-success">{{ number_format($realMetrics['tp']) }}</p>
                    </div>
                    <div class="cm-box cm-fn">
                        <p class="text-xs font-mono text-destructive mb-1">Falso -</p>
                        <p class="text-3xl font-bold text-destructive">{{ number_format($realMetrics['fn']) }}</p>
                    </div>
                    <div class="cm-box cm-fp">
                        <p class="text-xs font-mono text-warning mb-1">Falso +</p>
                        <p class="text-3xl font-bold text-warning">{{ number_format($realMetrics['fp']) }}</p>
                    </div>
                    <div class="cm-box cm-tn">
                        <p class="text-xs font-mono text-primary mb-1">Verdadero -</p>
                        <p class="text-3xl font-bold text-primary">{{ number_format($realMetrics['tn']) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4 text-xs font-mono">
                    <div class="mini-metric">
                        <p class="text-muted-foreground">Positivos reales</p>
                        <p class="text-lg font-bold text-success mt-1">{{ number_format($positiveReal) }}</p>
                    </div>
                    <div class="mini-metric">
                        <p class="text-muted-foreground">Negativos reales</p>
                        <p class="text-lg font-bold text-primary mt-1">{{ number_format($negativeReal) }}</p>
                    </div>
                    <div class="confidence-spotlight">
                        <div class="confidence-meta">
                            <span class="w-2 h-2 rounded-full" style="background:hsl(var(--primary));"></span>
                            Casos revisados por medico
                        </div>
                        <div class="confidence-value">
                            <strong>{{ number_format($reviewedAvgConfidence, 1) }}%</strong>
                            <span>confianza media del modelo</span>
                        </div>
                        <p class="confidence-caption">
                            Este promedio resume cuan segura fue la prediccion del modelo en los
                            <b>{{ number_format($realMetrics['reviewedCount']) }} casos validados clinicamente</b>
                            dentro del rango seleccionado.
                        </p>
                    </div>
                </div>
            @else
                <div class="mini-metric">
                    <p class="font-semibold mb-2">Sin metrica clinica en este rango</p>
                    <p class="text-sm text-muted-foreground">
                        Cuando existan revisiones medicas entre las fechas seleccionadas, aqui se mostraran sensibilidad,
                        especificidad, precision, exactitud y la matriz de confusion del periodo.
                    </p>
                </div>
            @endif
        </div>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 animate-fade-in-up" style="animation-delay:340ms;">
        <template x-for="card in secondary" :key="card.key">
            <div class="mini-metric">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-mono uppercase tracking-widest text-muted-foreground" x-text="card.label"></p>
                        <p class="text-xs text-muted-foreground mt-1" x-text="card.desc"></p>
                    </div>
                    <span class="text-[11px] font-mono px-1.5 py-0.5 rounded"
                          style="background:hsl(var(--secondary)); color:hsl(var(--muted-foreground));"
                          x-text="card.formula"></span>
                </div>
                <div class="flex items-end justify-between mt-4">
                    <span class="font-mono font-bold text-2xl" :style="`color:${card.color}`" x-text="secondaryDisplay[card.key]"></span>
                    <span class="text-xs text-right max-w-[9rem]" :style="`color:${card.color}`" x-text="card.delta"></span>
                </div>
            </div>
        </template>
    </section>
</div>

<script>
function metricsPage() {
    const dashboardData = @json($dashboardData);

    return {
        animated: false,
        metrics: dashboardData.metricCards ?? [],
        secondary: dashboardData.secondaryCards ?? [],
        metricDisplay: {},
        secondaryDisplay: {},

        init() {
            this.metrics.forEach((metric) => {
                this.metricDisplay[metric.key] = '0.0%';
            });

            this.secondary.forEach((card) => {
                this.secondaryDisplay[card.key] = card.format === 'score' ? '0.000' : '0.0%';
            });

            setTimeout(() => {
                this.animated = true;
                this.animateMetrics();
                this.animateSecondary();
            }, 250);
        },

        animateMetrics() {
            const duration = 1200;
            const start = performance.now();
            const targets = Object.fromEntries(this.metrics.map((metric) => [metric.key, Number(metric.value) || 0]));

            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - t, 3);

                Object.keys(targets).forEach((key) => {
                    this.metricDisplay[key] = `${(targets[key] * ease).toFixed(1)}%`;
                });

                if (t < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        },

        animateSecondary() {
            const duration = 1200;
            const start = performance.now();

            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - t, 3);

                this.secondary.forEach((card) => {
                    const target = Number(card.value) || 0;

                    if (card.format === 'score') {
                        this.secondaryDisplay[card.key] = (target * ease).toFixed(3);
                    } else {
                        this.secondaryDisplay[card.key] = `${(target * 100 * ease).toFixed(1)}%`;
                    }
                });

                if (t < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        },
    };
}
</script>
@endsection

````

## sistema/resources/views/reportes.blade.php

````blade
@extends('plantillas.aplicacion')

@section('title', 'Reportes')

@section('page-header')
    <h1 class="text-2xl lg:text-3xl font-bold animate-fade-in">
        <span class="gradient-text">Reportes</span>
    </h1>
    <p class="text-muted-foreground mt-1 animate-fade-in-delay-1">
        Descarga reportes de todos los pacientes o solo de pacientes seleccionados.
    </p>
@endsection

@section('content')
<div class="space-y-6" x-data="{ mode: 'all' }">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 animate-fade-in-up">
        <div class="card">
            <p class="text-sm text-muted-foreground">Pacientes registrados</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['patients']) }}</p>
        </div>
        <div class="card">
            <p class="text-sm text-muted-foreground">Analisis disponibles</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['analyses']) }}</p>
        </div>
    </div>

    @if (session('error'))
        <div class="alert-error animate-fade-in">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('reports.download') }}" class="card animate-fade-in-up" style="animation-delay:120ms;">
        @csrf

        <div class="flex flex-col gap-5">
            <div>
                <h2 class="text-lg font-semibold">Tipo de reporte</h2>
                <p class="text-sm text-muted-foreground mt-1">
                    El archivo se descargara en formato Excel (.xlsx).
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="cursor-pointer rounded-xl border border-border p-4 transition-colors hover:bg-muted/30"
                       :class="mode === 'all' ? 'border-primary shadow-glow' : ''">
                    <input type="radio" name="mode" value="all" x-model="mode" class="sr-only">
                    <span class="block font-semibold">Todos los pacientes</span>
                    <span class="block text-sm text-muted-foreground mt-1">
                        Incluye todos los registros disponibles del historial.
                    </span>
                </label>

                <label class="cursor-pointer rounded-xl border border-border p-4 transition-colors hover:bg-muted/30"
                       :class="mode === 'selected' ? 'border-primary shadow-glow' : ''">
                    <input type="radio" name="mode" value="selected" x-model="mode" class="sr-only">
                    <span class="block font-semibold">Algunos pacientes</span>
                    <span class="block text-sm text-muted-foreground mt-1">
                        Permite elegir uno o varios pacientes especificos.
                    </span>
                </label>
            </div>

            <div x-show="mode === 'selected'" x-transition class="rounded-xl border border-border p-4" style="background:hsl(var(--background)/0.55);">
                <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                    <div>
                        <h3 class="font-semibold">Seleccionar pacientes</h3>
                        <p class="text-sm text-muted-foreground">Marca los pacientes que deseas incluir.</p>
                    </div>
                </div>

                @if ($patients->isEmpty())
                    <p class="text-sm text-muted-foreground">No hay pacientes registrados para generar reportes.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        @foreach ($patients as $patient)
                            <label class="flex items-start gap-3 rounded-lg border border-border p-3 cursor-pointer hover:bg-card transition-colors">
                                <input type="checkbox"
                                       name="patients[]"
                                       value="{{ $patient->patient_identifier }}"
                                       class="mt-1"
                                       :disabled="mode !== 'selected'">
                                <span>
                                    <span class="block font-mono text-sm font-semibold text-primary">
                                        {{ $patient->patient_identifier }}
                                    </span>
                                    <span class="block text-xs text-muted-foreground mt-0.5">
                                        {{ number_format($patient->total) }} analisis
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <p class="text-xs text-muted-foreground">
                    El reporte incluye paciente, archivo, fecha, ritmo, probabilidad, estado IA y valoracion medica.
                </p>
                <button type="submit" class="btn-primary">
                    Descargar Excel
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

````

## sistema/resources/views/errors/404.blade.php

````blade
@extends('plantillas.invitado')

@section('title', 'PÃ¡gina no encontrada')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <div class="fixed inset-0 pointer-events-none" style="opacity:0.15;">
        <div class="absolute inset-0 medical-grid"></div>
    </div>

    <div class="relative text-center animate-fade-in-up max-w-lg">
        <div class="mb-8">
            <div class="relative inline-flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-24 w-24 text-primary animate-heartbeat"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                </svg>
                <div class="absolute inset-0 rounded-full"
                     style="background:hsl(var(--primary)/0.15);filter:blur(24px);"></div>
            </div>
        </div>

        <h1 class="text-8xl font-bold gradient-text mb-4">404</h1>
        <h2 class="text-2xl font-semibold mb-3">PÃ¡gina no encontrada</h2>
        <p class="text-muted-foreground mb-8">
            La pÃ¡gina que buscas no existe o fue movida.
        </p>

        <a href="{{ route('dashboard') }}"
           class="btn-primary inline-flex items-center gap-2 glow-cyan">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            Volver al Dashboard
        </a>
    </div>
</div>
@endsection

````

## sistema/resources/css/aplicacion.css

````css
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

@import 'tailwindcss';

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';

@theme {
    --font-sans: 'Outfit', ui-sans-serif, system-ui, sans-serif;
}

/* â”€â”€ Variables de diseÃ±o mÃ©dico â”€â”€ */
:root {
    --background: 210 40% 98%;
    --foreground: 213 20% 15%;
    --card: 210 32% 100%;
    --primary: 198 100% 42%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 16% 90%;
    --muted: 210 16% 90%;
    --muted-foreground: 213 20% 45%;
    --accent: 198 93% 48%;
    --destructive: 0 84% 60%;
    --border: 210 25% 88%;
    --input: 210 25% 88%;
    --ring: 198 100% 42%;
    --radius: 0.75rem;
    --cyan-glow: 198 100% 42%;
    --success: 160 84% 39%;
    --warning: 38 92% 50%;
    --sidebar-background: 210 32% 100%;
    --sidebar-foreground: 213 20% 15%;
    --sidebar-border: 210 25% 88%;
    --gradient-primary: linear-gradient(135deg, hsl(198 93% 48%) 0%, hsl(205 90% 58%) 100%);
    --shadow-glow: 0 0 30px hsl(198 93% 48% / 0.2);
    --shadow-card: 0 4px 24px hsl(213 20% 15% / 0.1);
    --shadow-elevated: 0 8px 40px hsl(213 20% 15% / 0.1);
}

/* â”€â”€ Base â”€â”€ */
*, *::before, *::after { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body {
    background-color: hsl(var(--background));
    color: hsl(var(--foreground));
    font-family: 'Outfit', sans-serif;
    -webkit-font-smoothing: antialiased;
    margin: 0;
}
h1,h2,h3,h4,h5,h6 { font-weight: 600; letter-spacing: -0.025em; }

/* â”€â”€ Colores â”€â”€ */
.bg-background  { background-color: hsl(var(--background)); }
.bg-card        { background-color: hsl(var(--card)); }
.bg-primary     { background-color: hsl(var(--primary)); }
.bg-secondary   { background-color: hsl(var(--secondary)); }
.bg-muted       { background-color: hsl(var(--muted)); }
.bg-sidebar     { background-color: hsl(var(--sidebar-background)); }
.bg-destructive { background-color: hsl(var(--destructive)); }

.text-foreground         { color: hsl(var(--foreground)); }
.text-muted-foreground   { color: hsl(var(--muted-foreground)); }
.text-primary            { color: hsl(var(--primary)); }
.text-primary-foreground { color: hsl(var(--primary-foreground)); }
.text-destructive        { color: hsl(var(--destructive)); }
.text-success            { color: hsl(var(--success)); }
.text-warning            { color: hsl(var(--warning)); }
.text-sidebar-foreground { color: hsl(var(--sidebar-foreground)); }

.border-border         { border-color: hsl(var(--border)); }
.border-sidebar-border { border-color: hsl(var(--sidebar-border)); }
.border-input          { border-color: hsl(var(--input)); }
.border-primary        { border-color: hsl(var(--primary)); }

/* â”€â”€ Grid mÃ©dico â”€â”€ */
.medical-grid {
    background-image:
        linear-gradient(hsl(var(--border) / 0.3) 1px, transparent 1px),
        linear-gradient(90deg, hsl(var(--border) / 0.3) 1px, transparent 1px);
    background-size: 40px 40px;
}

/* â”€â”€ Glass â”€â”€ */
.glass {
    background: hsl(var(--card) / 0.8);
    backdrop-filter: blur(16px);
    border: 1px solid hsl(var(--border) / 0.5);
}

/* â”€â”€ Sombras â”€â”€ */
.shadow-card     { box-shadow: var(--shadow-card); }
.shadow-elevated { box-shadow: var(--shadow-elevated); }
.shadow-glow     { box-shadow: var(--shadow-glow); }

/* â”€â”€ Glow â”€â”€ */
.glow-cyan {
    box-shadow:
        0 0 20px hsl(var(--cyan-glow) / 0.4),
        0 0 40px hsl(var(--cyan-glow) / 0.2),
        0 0 60px hsl(var(--cyan-glow) / 0.1);
}

/* â”€â”€ Gradiente texto â”€â”€ */
.gradient-text {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* â”€â”€ BotÃ³n primario â”€â”€ */
.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 1rem;
    background-color: hsl(var(--primary));
    color: hsl(var(--primary-foreground));
    border: none;
    cursor: pointer;
    transition: background-color 0.2s, opacity 0.2s;
    text-decoration: none;
}
.btn-primary:hover:not(:disabled) { background-color: hsl(var(--primary) / 0.9); }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

/* â”€â”€ Inputs â”€â”€ */
.input-field {
    width: 100%;
    min-height: 3rem;
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    background: hsl(var(--background) / 0.5);
    border: 1px solid hsl(var(--input));
    color: hsl(var(--foreground));
    font-size: 0.875rem;
    line-height: 1.25rem;
    outline: none;
    transition: box-shadow 0.2s, border-color 0.2s;
    font-family: inherit;
}
.input-field:focus { box-shadow: 0 0 0 2px hsl(var(--ring)); border-color: transparent; }
.input-field::placeholder { color: hsl(var(--muted-foreground)); }
.input-field:disabled { opacity: 0.5; cursor: not-allowed; }

.input-with-icon {
    position: relative;
    display: block;
}

.input-with-icon .input-field {
    display: block;
    padding-left: 3rem;
}

.input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1.25rem !important;
    height: 1.25rem !important;
    max-width: 1.25rem !important;
    max-height: 1.25rem !important;
    color: hsl(var(--muted-foreground));
    stroke: currentColor;
    pointer-events: none;
    flex: none;
    display: block;
    overflow: visible;
    z-index: 10;
}

.input-with-icon > .input-icon {
    width: 1.25rem !important;
    height: 1.25rem !important;
}

/* â”€â”€ Sidebar â”€â”€ */
.sidebar {
    position: fixed; left: 0; top: 0; z-index: 40;
    height: 100vh; width: 16rem;
    background: hsl(var(--sidebar-background));
    border-right: 1px solid hsl(var(--sidebar-border));
    transition: transform 0.3s ease-in-out;
    display: flex; flex-direction: column;
}
.sidebar-hidden { transform: translateX(-100%); }
@media (min-width: 1024px) { .sidebar { transform: translateX(0) !important; } }

.nav-link {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem 1rem; border-radius: var(--radius);
    color: hsl(var(--sidebar-foreground)); text-decoration: none;
    font-weight: 500; transition: background-color 0.2s, color 0.2s;
}
.nav-link:hover { background-color: hsl(var(--secondary)); color: hsl(var(--foreground)); }
.nav-link.active {
    background-color: hsl(var(--primary) / 0.1);
    color: hsl(var(--primary));
    border: 1px solid hsl(var(--primary) / 0.2);
    box-shadow: var(--shadow-glow);
}

/* â”€â”€ Tarjeta â”€â”€ */
.card {
    background: hsl(var(--card));
    border: 1px solid hsl(var(--border));
    border-radius: calc(var(--radius) + 2px);
    padding: 1.5rem;
    box-shadow: var(--shadow-card);
    transition: box-shadow 0.3s, border-color 0.3s;
    position: relative; overflow: hidden;
}
.card:hover { box-shadow: var(--shadow-elevated); border-color: hsl(var(--primary) / 0.3); }

/* â”€â”€ Badge â”€â”€ */
.badge {
    display: inline-flex; align-items: center; gap: 0.25rem;
    padding: 0.25rem 0.75rem; border-radius: 9999px;
    font-size: 0.75rem; font-weight: 500;
}
.badge-success  { background: hsl(var(--success)  / 0.1); color: hsl(var(--success)); }
.badge-warning  { background: hsl(var(--warning)  / 0.1); color: hsl(var(--warning)); }
.badge-destructive { background: hsl(var(--destructive) / 0.1); color: hsl(var(--destructive)); }

/* â”€â”€ Tabla â”€â”€ */
.table-ecg { width: 100%; border-collapse: collapse; }
.table-ecg thead tr {
    border-bottom: 1px solid hsl(var(--border));
    background: hsl(var(--muted) / 0.5);
}
.table-ecg th {
    padding: 1rem 1.5rem; text-align: left;
    font-size: 0.875rem; font-weight: 500;
    color: hsl(var(--muted-foreground));
}
.table-ecg tbody tr {
    border-bottom: 1px solid hsl(var(--border) / 0.5);
    transition: background-color 0.15s;
}
.table-ecg tbody tr:hover { background: hsl(var(--muted) / 0.3); }
.table-ecg td { padding: 1rem 1.5rem; font-size: 0.875rem; }

/* â”€â”€ ECG Line animada â”€â”€ */
.ecg-line {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
    animation: ecg-draw 2s ease-in-out infinite;
}
@keyframes ecg-draw {
    0%   { stroke-dashoffset: 1000; }
    50%  { stroke-dashoffset: 0; }
    100% { stroke-dashoffset: -1000; }
}

/* â”€â”€ Animaciones â”€â”€ */
@keyframes fade-in    { from { opacity:0; } to { opacity:1; } }
@keyframes fade-in-up { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

.animate-fade-in         { animation: fade-in 0.6s ease-out forwards; }
.animate-fade-in-up      { animation: fade-in-up 0.6s ease-out forwards; }
.animate-fade-in-delay-1 { animation: fade-in-up 0.6s ease-out 0.1s forwards; opacity:0; }
.animate-fade-in-delay-2 { animation: fade-in-up 0.6s ease-out 0.2s forwards; opacity:0; }
.animate-fade-in-delay-3 { animation: fade-in-up 0.6s ease-out 0.3s forwards; opacity:0; }
.animate-fade-in-delay-4 { animation: fade-in-up 0.6s ease-out 0.4s forwards; opacity:0; }

@keyframes heartbeat {
    0%,100% { transform:scale(1); } 14% { transform:scale(1.15); }
    28%      { transform:scale(1); } 42% { transform:scale(1.08); }
    70%      { transform:scale(1); }
}
.animate-heartbeat { animation: heartbeat 1.5s ease-in-out infinite; }

@keyframes spin    { to { transform: rotate(360deg); } }
@keyframes pulse   { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
.animate-spin  { animation: spin 1s linear infinite; }
.animate-pulse { animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite; }

/* â”€â”€ Upload dropzone â”€â”€ */
.dropzone {
    border: 2px dashed hsl(var(--border));
    border-radius: calc(var(--radius) + 4px);
    padding: 3rem; text-align: center; cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
}
.dropzone:hover, .dropzone.drag-over {
    border-color: hsl(var(--primary));
    background: hsl(var(--primary) / 0.04);
}

/* â”€â”€ Alert error â”€â”€ */
.alert-error {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.75rem 1rem; border-radius: var(--radius);
    background: hsl(var(--destructive) / 0.1);
    border: 1px solid hsl(var(--destructive) / 0.2);
    color: hsl(var(--destructive)); font-size: 0.875rem;
}

/* â”€â”€ Barra progreso â”€â”€ */
.progress-bar { height: 0.5rem; border-radius:9999px; background:hsl(var(--secondary)); overflow:hidden; }
.progress-bar-fill { height:100%; border-radius:9999px; background:var(--gradient-primary); transition:width 0.3s ease; }

/* â”€â”€ Fuente mono â”€â”€ */
.font-mono { font-family: 'JetBrains Mono', monospace; }

/* â”€â”€ Scrollbar â”€â”€ */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: hsl(var(--background)); }
::-webkit-scrollbar-thumb { background: hsl(var(--border)); border-radius:9999px; }
::-webkit-scrollbar-thumb:hover { background: hsl(var(--primary) / 0.5); }

````

## sistema/resources/js/aplicacion.js

````javascript
import './inicio';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

````

## sistema/resources/js/inicio.js

````javascript
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

````

## modelo/api.py

````python
"""
API REST para el pipeline ECG de detecciÃ³n de arritmias.
Expone los endpoints /predict y /preview para el frontend Laravel.

Uso:
    uvicorn api:app --host 0.0.0.0 --port 8001 --reload
"""

import os
import sys
import tempfile
import base64
import traceback

import cv2
import numpy as np
from fastapi import FastAPI, File, UploadFile, Form, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse

# Fijar el directorio de trabajo al directorio del script para que todas las
# rutas relativas del pipeline (modelo, norm_stats, rois_derivaciones) funcionen
# sin importar desde dÃ³nde se lance uvicorn.
_BACKEND_DIR = os.path.dirname(os.path.abspath(__file__))
os.chdir(_BACKEND_DIR)
sys.path.insert(0, _BACKEND_DIR)

from pipeline_unificado import (
    extraer_ecg_de_pdf,
    detectar_region_ecg,
    eliminar_cuadricula,
    preprocesar_para_digitalizacion,
    seleccionar_rois,
    digitalizar_todas_derivaciones,
    preparar_para_modelo,
    predecir_con_modelo,
    detect_metrics,
    LEADS_ORDER,
)
import config
from arr_constantes import LABEL_NAMES

app = FastAPI(title="ECG Arritmia API", version="1.0.0")

# Permitir peticiones desde el frontend Laravel (cualquier origen en desarrollo)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# â”€â”€â”€ Funciones auxiliares â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

def _cargar_imagen_desde_bytes(data: bytes, filename: str) -> np.ndarray:
    """Carga una imagen PNG/JPG desde bytes y la devuelve como array BGR."""
    arr = np.frombuffer(data, dtype=np.uint8)
    img = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    if img is None:
        raise ValueError(f"No se pudo decodificar la imagen '{filename}'")
    return img


def _ejecutar_pipeline_imagen(img: np.ndarray, age: float, sex: int, weight: float) -> dict:
    # Preprocesar
    img_sin_grid = eliminar_cuadricula(img)
    img_procesada = preprocesar_para_digitalizacion(img_sin_grid)

    # ROIs (guardados â†’ auto-detecciÃ³n)
    rois = seleccionar_rois(img_procesada, interactivo=False)

    if len(rois) < 12:
        raise RuntimeError(
            f"Solo se detectaron {len(rois)}/12 derivaciones. "
            "Comprueba que el ECG sea de 12 derivaciones y sea legible."
        )

    # Digitalizar
    derivaciones_mv = digitalizar_todas_derivaciones(img_procesada, rois)
    # Preparar tensor para el modelo
    tensor, signals = preparar_para_modelo(derivaciones_mv)
    # Predecir
    label, confidence, probs, signals = predecir_con_modelo(
        tensor, signals, age, sex, weight
    )
    # MÃ©tricas clÃ­nicas bÃ¡sicas (Lead II si estÃ¡ disponible)
    lead_ii = signals[1] if len(signals) > 1 else signals[0]
    beats, hr, variability, amplitude = detect_metrics(lead_ii)

    # Construir seÃ±ales para el grÃ¡fico (12 canales, 1000 muestras completas)
    chart_signals = [
        [float(v) for v in s[:1000].tolist()] for s in signals
    ]

    # Top-5 predicciones
    top_idx = np.argsort(probs)[::-1][:5]
    top_predictions = [
        {
            "label": LABEL_NAMES.get(config.LABEL_CODES[i], f"Clase {i}"),
            "code": config.LABEL_CODES[i],
            "probability": round(float(probs[i]) * 100, 2),
        }
        for i in top_idx
    ]

    return {
        "label": label,
        "confidence": round(float(confidence), 2),
        "scores": [round(float(p), 6) for p in probs.tolist()],
        "leads": LEADS_ORDER,
        "signals": chart_signals,
        "metrics": {
            "heart_rate": round(float(hr), 1),
            "beats": int(beats),
            "variability": round(float(variability), 4),
            "amplitude": round(float(amplitude), 4),
        },
        "top_predictions": top_predictions,
    }


# â”€â”€â”€ Endpoints â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

@app.get("/health")
def health():
    return {"status": "ok", "model": config.MODEL_NAME}


@app.post("/preview")
async def preview(file: UploadFile = File(...)):
    data = await file.read()
    filename = file.filename or "upload"
    ext = os.path.splitext(filename)[1].lower()

    try:
        if ext == ".pdf":
            with tempfile.NamedTemporaryFile(suffix=".pdf", delete=False) as tmp:
                tmp.write(data)
                tmp_path = tmp.name
            try:
                img = extraer_ecg_de_pdf(tmp_path, dpi=150)
            finally:
                os.unlink(tmp_path)
        elif ext in (".png", ".jpg", ".jpeg"):
            img = _cargar_imagen_desde_bytes(data, filename)
        else:
            raise HTTPException(status_code=400, detail="Formato no soportado para preview.")

        # Redimensionar para preview (mÃ¡x 1200px ancho)
        h, w = img.shape[:2]
        if w > 1200:
            scale = 1200 / w
            img = cv2.resize(img, (1200, int(h * scale)))

        _, buf = cv2.imencode(".jpg", img, [cv2.IMWRITE_JPEG_QUALITY, 80])
        b64 = base64.b64encode(buf).decode("utf-8")
        return {"image": f"data:image/jpeg;base64,{b64}"}

    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/predict")
async def predict(
    file: UploadFile = File(...),
    age: float = Form(...),
    sex: int = Form(...),
    weight: float = Form(...),
):
    data = await file.read()
    filename = file.filename or "upload"
    ext = os.path.splitext(filename)[1].lower()

    try:
        # â”€â”€ Cargar imagen â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        if ext == ".pdf":
            with tempfile.NamedTemporaryFile(suffix=".pdf", delete=False) as tmp:
                tmp.write(data)
                tmp_path = tmp.name
            try:
                img = extraer_ecg_de_pdf(tmp_path, dpi=300)
                img = detectar_region_ecg(img)
            finally:
                os.unlink(tmp_path)

        elif ext in (".png", ".jpg", ".jpeg"):
            img = _cargar_imagen_desde_bytes(data, filename)
            img = detectar_region_ecg(img)

        elif ext in (".csv", ".txt"):
            # CSV: columnas = derivaciones (12), filas = muestras (>=1000)
            # El frontend ya envÃ­a el CSV en el formato correcto
            import io
            content = data.decode("utf-8", errors="replace")
            rows = [
                [float(v) for v in line.strip().split(",") if v.strip()]
                for line in content.splitlines()
                if line.strip()
            ]
            if not rows:
                raise ValueError("El archivo CSV estÃ¡ vacÃ­o.")

            arr = np.array(rows, dtype=np.float32)
            # Aceptar (N, 12) o (12, N)
            if arr.shape[1] == 12:
                signals_raw = [arr[:, i] for i in range(12)]
            elif arr.shape[0] == 12:
                signals_raw = [arr[i, :] for i in range(12)]
            else:
                raise ValueError(
                    f"El CSV debe tener 12 columnas (derivaciones). "
                    f"Shape detectado: {arr.shape}"
                )

            derivaciones_mv = {LEADS_ORDER[i]: signals_raw[i] for i in range(12)}
            tensor, signals = preparar_para_modelo(derivaciones_mv)
            label, confidence, probs, signals = predecir_con_modelo(
                tensor, signals, age, sex, weight
            )

            lead_ii = signals[1] if len(signals) > 1 else signals[0]
            beats, hr, variability, amplitude = detect_metrics(lead_ii)
            chart_signals = [[float(v) for v in s[:500].tolist()] for s in signals]
            top_idx = np.argsort(probs)[::-1][:5]
            top_predictions = [
                {
                    "label": LABEL_NAMES.get(config.LABEL_CODES[i], f"Clase {i}"),
                    "code": config.LABEL_CODES[i],
                    "probability": round(float(probs[i]) * 100, 2),
                }
                for i in top_idx
            ]

            return {
                "label": label,
                "confidence": round(float(confidence), 2),
                "scores": [round(float(p), 6) for p in probs.tolist()],
                "leads": LEADS_ORDER,
                "signals": chart_signals,
                "metrics": {
                    "heart_rate": round(float(hr), 1),
                    "beats": int(beats),
                    "variability": round(float(variability), 4),
                    "amplitude": round(float(amplitude), 4),
                },
                "top_predictions": top_predictions,
            }

        else:
            raise HTTPException(
                status_code=400,
                detail=f"Formato '{ext}' no soportado. Usa PNG, JPG, JPEG, PDF, CSV o TXT.",
            )

        # â”€â”€ Pipeline para imagen/PDF â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        result = _ejecutar_pipeline_imagen(img, age, sex, weight)
        return result

    except HTTPException:
        raise
    except RuntimeError as e:
        raise HTTPException(status_code=422, detail=str(e))
    except Exception as e:
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=f"Error interno: {str(e)}")


if __name__ == "__main__":
    import uvicorn
    uvicorn.run("api:app", host="0.0.0.0", port=8001, reload=True)

````

## modelo/config.py

````python
# 02_config.py - parametros de inferencia para modelo ECG-only.
from arr_constantes import LABEL_CODES as ARR_LABEL_CODES, LABEL_NAMES as ARR_LABEL_NAMES

# Parametros de la red. Deben coincidir exactamente con el entrenamiento.
SAMPLING_RATE = 100
DURATION = 10
INPUT_SHAPE = (1000, 12)
NUM_CLASSES = len(ARR_LABEL_CODES)
MODEL_NAME = "modelo_arritmias_Fina_v4.keras"

LABEL_CODE_TO_INDEX = {code: idx for idx, code in enumerate(ARR_LABEL_CODES)}
LABEL_NAMES = ARR_LABEL_NAMES
LABEL_CODES = ARR_LABEL_CODES
LABEL_CODE_TO_NAME = ARR_LABEL_NAMES

````

## modelo/arr_constantes.py

````python
# 01_arr_constantes.py - clases activas del modelo de inferencia ECG-only.
# El orden debe coincidir exactamente con las salidas de modelo_arritmias_Fina_v4.keras.

TRAINABLE_RHYTHM_CODES = [
    "AFIB",
    "PVC",
    "STACH",
    "SBRAD",
    "1AVB",
]

LABEL_CODES = ["NORM", *TRAINABLE_RHYTHM_CODES]

LABEL_NAMES = {
    "NORM": "normal ECG",
    "AFIB": "atrial fibrillation",
    "PVC": "ventricular premature complex",
    "STACH": "sinus tachycardia",
    "SBRAD": "sinus bradycardia",
    "1AVB": "first degree AV block",
}

````

## modelo/modelo.py

````python
import tensorflow as tf
from tensorflow.keras.layers import (
    BatchNormalization,
    Bidirectional,
    Conv1D,
    Dense,
    Dropout,
    Input,
    LSTM,
    MaxPooling1D,
)
from tensorflow.keras.models import Model
from tensorflow.keras.optimizers import Adam

import config


def focal_loss(gamma=2.0, alpha=0.25):
    """Focal loss usada durante el entrenamiento del modelo."""
    def loss_fn(y_true, y_pred):
        y_pred = tf.clip_by_value(y_pred, 1e-7, 1.0)
        ce = -y_true * tf.math.log(y_pred)
        pt = tf.reduce_sum(y_true * y_pred, axis=-1, keepdims=True)
        focal_weight = alpha * tf.pow(1.0 - pt, gamma)
        return tf.reduce_mean(focal_weight * ce)

    return loss_fn


def construir_modelo():
    print(">>> Construyendo arquitectura CNN-LSTM ECG-only...")

    ecg_input = Input(shape=config.INPUT_SHAPE, name="ecg_input")

    x = Conv1D(64, 5, activation="relu")(ecg_input)
    x = BatchNormalization()(x)
    x = MaxPooling1D(2)(x)
    x = Dropout(0.3)(x)

    x = Conv1D(128, 3, activation="relu")(x)
    x = BatchNormalization()(x)
    x = MaxPooling1D(2)(x)
    x = Dropout(0.3)(x)

    x = Conv1D(256, 3, activation="relu")(x)
    x = BatchNormalization()(x)
    x = MaxPooling1D(2)(x)
    x = Dropout(0.3)(x)

    x = Bidirectional(LSTM(128, return_sequences=False))(x)
    x = Dropout(0.3)(x)

    x = Dense(64, activation="relu")(x)
    x = Dropout(0.3)(x)
    output = Dense(config.NUM_CLASSES, activation="softmax")(x)

    model = Model(inputs=ecg_input, outputs=output)

    model.compile(
        optimizer=Adam(learning_rate=0.0005),
        loss=focal_loss(gamma=2.0, alpha=0.25),
        metrics=[
            "accuracy",
            tf.keras.metrics.AUC(name="auc", multi_label=False),
        ],
    )
    return model

````

## modelo/pipeline_unificado.py

````python
"""
Pipeline Unificado para ExtracciÃ³n, DigitalizaciÃ³n y PredicciÃ³n de ECG
Integra los 3 scripts en un flujo coherente y optimizado
"""

import os
import json
import cv2
import numpy as np
import matplotlib.pyplot as plt
from pdf2image import convert_from_path
from scipy.ndimage import gaussian_filter1d, median_filter
from scipy.signal import find_peaks
import tensorflow as tf
from tensorflow.keras.models import load_model
from modelo import focal_loss

# ==================== CONFIGURACIÃ“N ====================
import config
from arr_constantes import LABEL_NAMES

# ConfiguraciÃ³n de archivos
PDF_PATH = '20250522-030159-2205250301.pdf'
OUTPUT_IMAGE = 'ecg_procesado.png'
OUTPUT_CSV = 'ecg_digitalizado.csv'
ROI_CONFIG_FILE = 'rois_derivaciones.json'
MODELO_PATH = config.MODEL_NAME

# ParÃ¡metros de procesamiento
DPI = 300
ECG_MM_PER_S = 25
ECG_MM_PER_MV = 10
NORMALIZE_SIGNALS = True  # Importante: normalizar para el modelo

# Orden estÃ¡ndar de derivaciones
LEADS_ORDER = ['I', 'II', 'III', 'aVR', 'aVL', 'aVF', 'V1', 'V2', 'V3', 'V4', 'V5', 'V6']

# ==================== PASO 1: EXTRACCIÃ“N DEL PDF ====================

def extraer_ecg_de_pdf(pdf_path, dpi=300):
    """Extrae imagen del PDF con alta resoluciÃ³n"""
    print(f"[1/7] Extrayendo ECG del PDF (DPI={dpi})...")
    imagenes = convert_from_path(pdf_path, dpi=dpi)
    if len(imagenes) == 0:
        raise Exception("No se pudo extraer ninguna imagen del PDF")
    img_pil = imagenes[0]
    img = cv2.cvtColor(np.array(img_pil), cv2.COLOR_RGB2BGR)
    print(f"   âœ“ Imagen extraÃ­da: {img.shape[1]}x{img.shape[0]} pÃ­xeles")
    return img


def detectar_region_ecg(img):
    """Detecta y recorta la regiÃ³n que contiene el ECG"""
    print("[2/7] Detectando regiÃ³n del ECG...")
    gris = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    _, binaria = cv2.threshold(gris, 200, 255, cv2.THRESH_BINARY_INV)
    
    proyeccion_h = np.sum(binaria, axis=1)
    proyeccion_v = np.sum(binaria, axis=0)
    
    umbral_h = np.max(proyeccion_h) * 0.05
    umbral_v = np.max(proyeccion_v) * 0.05
    
    filas = np.where(proyeccion_h > umbral_h)[0]
    cols = np.where(proyeccion_v > umbral_v)[0]
    
    if len(filas) == 0 or len(cols) == 0:
        return img
    
    y_ini, y_fin = filas[0], filas[-1]
    x_ini, x_fin = cols[0], cols[-1]
    
    margen = 20
    y_ini = max(0, y_ini - margen)
    y_fin = min(img.shape[0], y_fin + margen)
    x_ini = max(0, x_ini - margen)
    x_fin = min(img.shape[1], x_fin + margen)
    
    ecg_recortado = img[y_ini:y_fin, x_ini:x_fin]
    print(f"   âœ“ RegiÃ³n ECG: {ecg_recortado.shape[1]}x{ecg_recortado.shape[0]} pÃ­xeles")
    return ecg_recortado


# ==================== PASO 2: PREPROCESAMIENTO ====================

def eliminar_cuadricula(img):
    """Elimina la cuadrÃ­cula preservando el trazo"""
    print("[3/7] Eliminando cuadrÃ­cula...")
    gris = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY) if len(img.shape) == 3 else img.copy()
    
    # Detectar lÃ­neas de cuadrÃ­cula
    kernel_h = cv2.getStructuringElement(cv2.MORPH_RECT, (15, 1))
    lineas_h = cv2.morphologyEx(gris, cv2.MORPH_OPEN, kernel_h, iterations=1)
    
    kernel_v = cv2.getStructuringElement(cv2.MORPH_RECT, (1, 15))
    lineas_v = cv2.morphologyEx(gris, cv2.MORPH_OPEN, kernel_v, iterations=1)
    
    cuadricula = cv2.add(lineas_h, lineas_v)
    _, mask_cuadricula = cv2.threshold(cuadricula, 180, 255, cv2.THRESH_BINARY)
    
    # Restar cuadrÃ­cula
    gris_float = gris.astype(np.float32)
    cuadricula_float = (mask_cuadricula.astype(np.float32) / 255.0) * 30
    gris_sin_grid = np.clip(gris_float - cuadricula_float, 0, 255).astype(np.uint8)
    
    print(f"   âœ“ CuadrÃ­cula eliminada")
    return gris_sin_grid


def preprocesar_para_digitalizacion(img):
    """Preprocesa la imagen para digitalizaciÃ³n Ã³ptima"""
    clahe = cv2.createCLAHE(clipLimit=1.5, tileGridSize=(16, 16))
    img_contraste = clahe.apply(img)
    
    img_suave = cv2.GaussianBlur(img_contraste, (3, 3), 0.5)
    
    _, img_binaria = cv2.threshold(img_suave, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    
    if np.mean(img_binaria) < 127:
        img_binaria = cv2.bitwise_not(img_binaria)
    
    return img_binaria


# ==================== PASO 3: DETECCIÃ“N / SELECCIÃ“N DE ROIs ====================

# Layouts estÃ¡ndar de 12 derivaciones.
# Clave: n_cols detectadas
# 'leads_por_columna': lista de listas, una por columna, de arriba a abajo
_LAYOUTS_CONOCIDOS = {
    2: {
        'n_filas': 6,
        'leads_por_columna': [
            ['I', 'II', 'III', 'aVR', 'aVL', 'aVF'],   # columna izquierda
            ['V1', 'V2', 'V3', 'V4', 'V5', 'V6'],       # columna derecha
        ],
    },
    3: {
        'n_filas': 4,
        'leads_por_columna': [
            ['I',   'II',  'III', 'aVR'],
            ['aVL', 'aVF', 'V1', 'V2'],
            ['V3',  'V4',  'V5', 'V6'],
        ],
    },
    4: {
        'n_filas': 3,
        'leads_por_columna': [
            ['I',   'II',  'III'],
            ['aVR', 'aVL', 'aVF'],
            ['V1',  'V2',  'V3'],
            ['V4',  'V5',  'V6'],
        ],
    },
}


def cargar_rois_guardados():
    """Carga ROIs previamente guardados"""
    if not os.path.exists(ROI_CONFIG_FILE):
        return {}
    try:
        with open(ROI_CONFIG_FILE, 'r', encoding='utf-8') as f:
            data = json.load(f)
        return data.get('rois', {})
    except Exception:
        return {}


def guardar_rois(rois):
    """Guarda ROIs para uso futuro"""
    try:
        with open(ROI_CONFIG_FILE, 'w', encoding='utf-8') as f:
            json.dump({'rois': rois}, f, indent=2)
        print(f"   âœ“ ROIs guardados en {ROI_CONFIG_FILE}")
    except Exception:
        print("    No se pudieron guardar los ROIs")


def _detectar_columnas(binaria, ancho, min_fraccion=0.10):
    """
    Detecta columnas de seÃ±al. Filtra columnas estrechas (calibraciÃ³n, etiquetas).

    Analiza solo el 70 % superior de la imagen para evitar que un rhythm-strip
    de ancho completo en la parte inferior fusione las dos columnas de leads.
    """
    alto = binaria.shape[0]
    analisis = binaria[:int(alto * 0.70), :]   # excluir franja inferior (rhythm strip)
    proj_v = gaussian_filter1d(np.sum(analisis, axis=0).astype(float), sigma=25)
    umbral = np.max(proj_v) * 0.15
    activo = proj_v > umbral
    cambios = np.diff(activo.astype(int))
    inicios = list(np.where(cambios == 1)[0])
    fines   = list(np.where(cambios == -1)[0])
    if activo[0]:  inicios.insert(0, 0)
    if activo[-1]: fines.append(ancho - 1)
    min_px = ancho * min_fraccion
    return [(ini, fin) for ini, fin in zip(inicios, fines) if (fin - ini) > min_px]


def _encontrar_limites_ecg(binaria, bandas_v):
    """
    Detecta filas de inicio y fin del Ã¡rea ECG (excluye encabezado y pie).

    Para el INICIO busca la lÃ­nea separadora horizontal gruesa (>35% de pÃ­xeles
    de trazo en toda la fila). Para el FIN busca la primera caÃ­da de seÃ±al en
    el tercio inferior de la imagen.
    """
    alto, ancho = binaria.shape

    # â”€â”€ INICIO: lÃ­nea separadora del encabezado â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    zona_header = int(alto * 0.45)
    proj_full   = np.sum(binaria[:zona_header, :], axis=1) / (ancho * 255.0)

    seps = np.where(proj_full > 0.35)[0]           # lÃ­nea gruesa continua
    if len(seps) > 0:
        ecg_start = int(seps[-1]) + 5
    else:
        texto = np.where(proj_full > 0.06)[0]      # fin del texto del header
        ecg_start = int(texto[-1]) + 20 if len(texto) > 0 else int(alto * 0.22)

    # â”€â”€ FIN: pie de pÃ¡gina â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    proj_col = np.zeros(alto, dtype=float)
    for x0, x1 in bandas_v:
        proj_col += np.sum(binaria[:, x0:x1], axis=1)
    proj_col /= len(bandas_v)
    suave = gaussian_filter1d(proj_col, sigma=15)
    umbral = np.max(suave) * 0.08
    activo = suave > umbral

    zona_inf = int(alto * 0.65)
    caidas   = np.where(np.diff(activo[zona_inf:].astype(int)) == -1)[0]
    ecg_end  = min(zona_inf + int(caidas[0]) + 5, alto - 1) if len(caidas) > 0 else int(alto * 0.95)

    return ecg_start, ecg_end


def _gaps_entre_derivaciones(col_binaria, n_leads, min_gap_px=3):
    """
    Encuentra los n_leads-1 separadores entre derivaciones buscando filas
    con CERO pÃ­xeles de trazo (gaps reales en la imagen binarizada).

    No usa suavizado â€” trabaja directamente con la imagen binaria, lo que
    permite detectar gaps de tan sÃ³lo 3-5 pÃ­xeles sin riesgo de borrarlos.

    Retorna lista de posiciones (centro de cada gap), ordenada.
    """
    alto = col_binaria.shape[0]

    # Fila con al menos 1 pÃ­xel de trazo = tiene seÃ±al
    tiene_seÃ±al = np.any(col_binaria > 0, axis=1)

    # Encontrar regiones consecutivas sin seÃ±al
    gaps = []
    i = 0
    while i < alto:
        if not tiene_seÃ±al[i]:
            j = i
            while j < alto and not tiene_seÃ±al[j]:
                j += 1
            largo = j - i
            if largo >= min_gap_px:
                gaps.append({'pos': (i + j) // 2, 'largo': largo, 'ini': i, 'fin': j})
            i = j
        else:
            i += 1

    if len(gaps) == 0:
        # Sin gaps visibles: divisiÃ³n equitativa
        return [int(k * alto / n_leads) for k in range(1, n_leads)]

    if len(gaps) >= n_leads - 1:
        # Elegir los n_leads-1 gaps mÃ¡s largos (mÃ¡s significativos)
        gaps_ord = sorted(gaps, key=lambda g: -g['largo'])[:n_leads - 1]
        return sorted(g['pos'] for g in gaps_ord)

    # Menos gaps que separadores necesarios: usar los que hay + divisiÃ³n local
    seps_hallados = sorted(g['pos'] for g in gaps)
    # Completar con divisiÃ³n equitativa de los segmentos sin gap
    puntos = [0] + seps_hallados + [alto]
    seps_extra = []
    for k in range(len(puntos) - 1):
        segmento = puntos[k + 1] - puntos[k]
        faltantes = (n_leads // (len(puntos) - 1)) - 1
        for f in range(1, faltantes + 1):
            seps_extra.append(puntos[k] + int(f * segmento / (faltantes + 1)))
    todos = sorted(set(seps_hallados + seps_extra))
    return todos[:n_leads - 1]


def _extent_senal(region, trazo_oscuro=True):
    """
    Devuelve (sig_top, baseline, sig_bot) en coordenadas relativas a `region`.

    sig_top / sig_bot: primera y Ãºltima fila que contiene al menos un pÃ­xel de
    trazo. Esto captura el rango COMPLETO de la seÃ±al, incluyendo los picos QRS
    mÃ¡s altos (que son breves pero deben estar dentro del ROI para digitalizarse
    correctamente).

    baseline: mediana de la posiciÃ³n Y del trazo columna a columna â€” representa
    la lÃ­nea isoelÃ©ctrica estable, independiente de los picos.

    Por quÃ© NO usar percentiles aquÃ­:
    - Un pico QRS dura ~10 de 200 columnas (~5 % del tiempo).
    - Con P5/P95 ese pico queda fuera del rango â†’ el ROI lo recorta.
    - Necesitamos el rango absoluto (min/max de filas con seÃ±al), no estadÃ­stico.
    """
    alto, ancho = region.shape[:2]

    if trazo_oscuro:
        tiene_trazo = np.any(region < 128, axis=1)
    else:
        tiene_trazo = np.any(region > 0, axis=1)

    filas = np.where(tiene_trazo)[0]
    if len(filas) == 0:
        return 0, alto // 2, alto

    sig_top = int(filas[0])
    sig_bot = int(filas[-1])

    # Baseline: mediana de la posiciÃ³n del trazo por columna (robusto a picos)
    pos_y = []
    for c in range(ancho):
        col = region[:, c]
        px = np.where(col < 128)[0] if trazo_oscuro else np.where(col > 0)[0]
        if len(px) > 0:
            pos_y.append(float(np.median(px)))
    baseline = int(np.median(pos_y)) if pos_y else (sig_top + sig_bot) // 2

    return sig_top, baseline, sig_bot


def _bandas_senal_aware(col_img, separadores, alto, margen_pct=0.05, margen_min_px=5):
    """
    Calcula las bandas ROI ajustadas al rango REAL (completo) de la seÃ±al.

    Por cada derivaciÃ³n:
      1. Extrae la zona nominal (entre separadores vecinos).
      2. Detecta el rango absoluto de seÃ±al: primera y Ãºltima fila con pÃ­xel de
         trazo â†’ captura todos los picos QRS sin excluirlos.
      3. AÃ±ade margen = max(margen_min_px, 5 % Ã— altura_seÃ±al).
      4. Limita estrictamente a [separador_arriba, separador_abajo]:
         como los separadores son centros de filas vacÃ­as, esta frontera
         garantiza que no hay solapamiento por construcciÃ³n.
    """
    n = len(separadores) + 1
    puntos = [0] + list(separadores) + [alto]

    bandas = []
    for i in range(n):
        zona_ini = puntos[i]
        zona_fin = puntos[i + 1]

        if zona_fin <= zona_ini:
            bandas.append((max(0, zona_ini), min(alto, zona_fin)))
            continue

        zona_img = col_img[zona_ini:zona_fin, :]

        # Rango completo (trazo=255 en col_img invertida)
        sig_top_rel, _, sig_bot_rel = _extent_senal(zona_img, trazo_oscuro=False)
        senal_top = zona_ini + sig_top_rel
        senal_bot = zona_ini + sig_bot_rel
        altura    = max(1, senal_bot - senal_top + 1)

        margen = max(margen_min_px, int(altura * margen_pct))

        # LÃ­mites duros: centros de gaps vacÃ­os â†’ sin solapamiento
        y0 = max(puntos[i],     senal_top - margen)
        y1 = min(puntos[i + 1], senal_bot + margen)

        # GarantÃ­a: el ROI siempre cubre la seÃ±al real completa
        y0 = min(y0, senal_top)
        y1 = max(y1, senal_bot)

        bandas.append((max(0, y0), min(alto, y1)))

    return bandas


def _detectar_inicio_footer(img_gray):
    """
    Busca el inicio del pie de pÃ¡gina (footer) del ECG desde abajo hacia arriba.

    Sube desde la Ãºltima fila con pÃ­xeles hasta encontrar la primera fila vacÃ­a
    (el espacio entre el footer y la Ãºltima derivaciÃ³n). Devuelve esa fila como
    lÃ­mite inferior seguro para aVF / V6.

    GarantÃ­a: nunca devuelve un valor por encima del 70 % de la imagen.
    """
    alto, ancho = img_gray.shape[:2]
    # Umbral relativo al ancho: 0.3 % de los pÃ­xeles de la fila â†’ vacÃ­o
    # Adapta a cualquier resoluciÃ³n (300 DPI ~7 px, 150 DPI ~4 px, 72 DPI ~2 px)
    umbral_vacio = max(3, ancho * 0.003)

    # Invertir para que el trazo sea brillante
    inv = cv2.bitwise_not(img_gray)
    densidad = np.sum(inv > 30, axis=1).astype(float)
    suave = gaussian_filter1d(densidad, sigma=3)

    # 1. Bajar hasta encontrar la Ãºltima fila con contenido (saltar margen en blanco)
    limite_inf = alto - 1
    while limite_inf > 0 and suave[limite_inf] < umbral_vacio:
        limite_inf -= 1

    if limite_inf < int(alto * 0.70):
        return int(alto * 0.92)   # fallback conservador

    # 2. Subir desde ahÃ­ hasta encontrar la primera fila vacÃ­a â†’ tope del footer
    footer_top = limite_inf
    while footer_top > 0 and suave[footer_top] > umbral_vacio:
        footer_top -= 1

    # AÃ±adir un pequeÃ±o buffer y garantizar que no corta demasiado arriba
    return max(int(alto * 0.70), footer_top - 5)


def _encontrar_mejor_frontera(img_gray, x, w, y_candidato, margen_busqueda=80):
    """
    Dado un y candidato a frontera entre dos derivaciones adyacentes, busca
    la fila con MÃNIMA densidad de trazo en la ventana
    [y_candidato - margen_busqueda, y_candidato + margen_busqueda].

    Esto encuentra el gap real entre las dos seÃ±ales aunque los picos de la
    derivaciÃ³n inferior se hayan "fugado" por encima del lÃ­mite nominal:
    el mÃ­nimo estarÃ¡ en el espacio vacÃ­o que siempre existe entre la seÃ±al
    de arriba (I) y los picos de abajo (II), incluso si ese espacio vacÃ­o
    estÃ¡ por encima de la frontera originalmente calculada.

    Devuelve la coordenada y absoluta del mejor lÃ­mite.
    """
    alto_img = img_gray.shape[0]
    y_ini = max(0, y_candidato - margen_busqueda)
    y_fin = min(alto_img, y_candidato + margen_busqueda)

    if y_fin <= y_ini:
        return y_candidato

    region = img_gray[y_ini:y_fin, x:x + w]
    densidad = np.sum(region < 128, axis=1).astype(float)

    # Suavizado para evitar elegir un mÃ­nimo ruidoso de 1 px
    suave = gaussian_filter1d(densidad, sigma=5)
    min_row = int(np.argmin(suave))

    return y_ini + min_row


def auto_detectar_rois(img):
    """
    Detecta automÃ¡ticamente las ROIs de las 12 derivaciones.

    Algoritmo:
      1. Detectar columnas de seÃ±al (proyecciÃ³n vertical suavizada).
      2. Detectar lÃ­mites del Ã¡rea ECG (encabezado + pie de pÃ¡gina).
      3. Para cada columna buscar gaps REALES (filas con cero pÃ­xeles de trazo).
      4. Ajustar cada banda a la extensiÃ³n real de la seÃ±al + margen dinÃ¡mico.

    Layouts soportados: 2Ã—6 | 3Ã—4 | 4Ã—3
    """
    gris = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY) if len(img.shape) == 3 else img.copy()
    alto, ancho = gris.shape

    # Trazo = 255, fondo = 0
    inv = cv2.bitwise_not(gris)
    _, binaria = cv2.threshold(inv, 30, 255, cv2.THRESH_BINARY)

    # 1. Columnas
    bandas_v = _detectar_columnas(binaria, ancho, min_fraccion=0.10)
    n_cols = len(bandas_v)
    if n_cols not in _LAYOUTS_CONOCIDOS:
        print(f"   {n_cols} columnas â€” layout no soportado {list(_LAYOUTS_CONOCIDOS.keys())}")
        return None

    layout = _LAYOUTS_CONOCIDOS[n_cols]
    leads_por_columna = layout['leads_por_columna']
    print(f"   Layout detectado: {layout['n_filas']}Ã—{n_cols}")

    # 2. LÃ­mites del Ã¡rea ECG
    ecg_start, ecg_end = _encontrar_limites_ecg(binaria, bandas_v)
    print(f"   Ãrea ECG: y=[{ecg_start}, {ecg_end}]  ({ecg_end - ecg_start}px Ãºtiles)")

    # 3. Analizar cada columna independientemente
    rois = {}
    for col_idx, (x_ini, x_fin) in enumerate(bandas_v):
        col_leads = leads_por_columna[col_idx]
        n = len(col_leads)

        col_img   = binaria[ecg_start:ecg_end, x_ini:x_fin]
        alto_col  = col_img.shape[0]

        # Gaps reales entre derivaciones
        seps = _gaps_entre_derivaciones(col_img, n)

        # Bandas ajustadas a la extensiÃ³n real de seÃ±al + margen dinÃ¡mico
        bandas_h = _bandas_senal_aware(col_img, seps, alto_col)

        for i, (y0r, y1r) in enumerate(bandas_h):
            y_ini = ecg_start + y0r
            y_fin = ecg_start + y1r
            rois[col_leads[i]] = [x_ini, y_ini, x_fin - x_ini, y_fin - y_ini]
            print(f"   {col_leads[i]:>4}: y=[{y_ini},{y_fin}]  h={y_fin - y_ini}")

    return rois


def visualizar_rois_detectados(img, rois, path='rois_detectados.png'):
    """Guarda una imagen con las ROIs dibujadas para verificaciÃ³n visual."""
    vis = cv2.cvtColor(img, cv2.COLOR_GRAY2BGR) if len(img.shape) == 2 else img.copy()
    colores = plt.cm.tab20.colors  # 20 colores distintos

    for i, (nombre, roi) in enumerate(rois.items()):
        x, y, w, h = roi
        color = tuple(int(c * 255) for c in colores[i % 20][:3])[::-1]  # BGR
        cv2.rectangle(vis, (x, y), (x + w, y + h), color, 3)
        cv2.putText(vis, nombre, (x + 5, y + 30),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.9, color, 2)

    # Escalar para visualizaciÃ³n (mÃ¡x 1200px de ancho)
    escala = min(1.0, 1200 / vis.shape[1])
    if escala < 1.0:
        vis = cv2.resize(vis, None, fx=escala, fy=escala)

    cv2.imwrite(path, vis)
    print(f"   âœ“ Vista de ROIs guardada en: {path}")


def _ajustar_rois_sin_solapamiento(img_bin, rois_base, leads_order,
                                    margen_pct=0.05, margen_min_px=5):
    """
    Ajusta todos los ROIs de forma conjunta para que:

      1. Cubran la seÃ±al real completa de cada derivaciÃ³n, incluyendo picos
         que fÃ­sicamente se "fugan" por encima del lÃ­mite nominal del ROI base.
      2. NO se superpongan entre derivaciones adyacentes.
      3. Usen padding conservador: max(margen_min_px, margen_pct Ã— rango_seÃ±al).

    Algoritmo (por columna de leads):
      a) Calcula la baseline de cada lead dentro de su ROI base (estable aunque
         los picos desborden, porque la isoelectrica sÃ­ estÃ¡ dentro del ROI).
      b) Entre cada par adyacente busca la frontera Ã³ptima con
         _encontrar_mejor_frontera: fila con MÃNIMA densidad de trazo cerca del
         midpoint entre las dos baselines. Esto coloca la frontera en el espacio
         vacÃ­o real entre las seÃ±ales, aunque los picos de la inferior hayan
         cruzado el lÃ­mite nominal.
      c) Usa esas fronteras "reales" para recortar regiones por lead y calcular
         la extensiÃ³n final de la seÃ±al con margen.

    ParÃ¡metros:
        img_bin      : imagen del ECG (se normaliza internamente)
        rois_base    : dict nombre â†’ [x, y, w, h]  (ROIs de referencia)
        leads_order  : lista ordenada de nombres de derivaciones
        margen_pct   : fracciÃ³n de la altura de seÃ±al para el margen (5 % por defecto)
        margen_min_px: margen mÃ­nimo en pÃ­xeles cuando la seÃ±al es casi plana
    """
    # Normalizar imagen: fondo claro, trazo oscuro (<128)
    if len(img_bin.shape) == 3:
        img_gray = cv2.cvtColor(img_bin, cv2.COLOR_BGR2GRAY)
    else:
        img_gray = img_bin.copy()
    if np.mean(img_gray) < 127:
        img_gray = cv2.bitwise_not(img_gray)

    img_alto = img_gray.shape[0]

    # â”€â”€ Detectar inicio del footer (lÃ­mite inferior para aVF y V6) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    #   Sube desde el fondo de la imagen hasta encontrar el espacio vacÃ­o
    #   entre la Ãºltima derivaciÃ³n y la lÃ­nea de parÃ¡metros tÃ©cnicos del ECG.
    _ecg_end = _detectar_inicio_footer(img_gray)

    # â”€â”€ Paso 1: extent y baseline por lead dentro del ROI base â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    #   La isoelÃ©ctrica SIEMPRE estÃ¡ en el ROI base aunque los picos se fuguen,
    #   por eso baseline y extent se calculan Ãºnicamente sobre el ROI base.
    extent_base = {}   # nombre â†’ (abs_top, abs_bot) dentro del ROI base
    baseline    = {}   # nombre â†’ y absoluto de lÃ­nea isoelÃ©ctrica
    for nombre in leads_order:
        if nombre not in rois_base:
            continue
        x, y, w, h = [int(v) for v in rois_base[nombre]]
        y_clip = min(img_alto, y + h)
        if y_clip <= y:
            mid = y + h // 2
            extent_base[nombre] = (mid, mid)
            baseline[nombre]    = mid
            continue
        region = img_gray[y:y_clip, x:x + w]
        sig_top_rel, base_rel, sig_bot_rel = _extent_senal(region, trazo_oscuro=True)
        extent_base[nombre] = (y + sig_top_rel, y + sig_bot_rel)
        baseline[nombre]    = y + base_rel

    # â”€â”€ Paso 2: agrupar leads por columna (misma franja horizontal X) â”€â”€â”€â”€â”€â”€â”€â”€
    def solapa_x(n1, n2):
        x1, _, w1, _ = [int(v) for v in rois_base[n1]]
        x2, _, w2, _ = [int(v) for v in rois_base[n2]]
        return not (x1 + w1 <= x2 or x2 + w2 <= x1)

    presentes = [n for n in leads_order if n in baseline]
    columnas, asignado = [], set()
    for n1 in presentes:
        if n1 in asignado:
            continue
        col = [n1]
        asignado.add(n1)
        for n2 in presentes:
            if n2 not in asignado and solapa_x(n1, n2):
                col.append(n2)
                asignado.add(n2)
        col.sort(key=lambda n: baseline[n])   # orden top â†’ bottom por baseline
        columnas.append(col)

    # â”€â”€ Paso 3: fronteras reales entre pares INTERNOS de la columna â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    #
    #   Para cada par adyacente (Narr, Nabj) â†’ arriba y abajo respectivamente:
    #     1. Midpoint entre sus baselines â†’ candidato inicial de frontera.
    #     2. _encontrar_mejor_frontera busca la fila de MÃNIMA densidad
    #        cerca de ese midpoint â†’ localiza el gap fÃ­sico real.
    #     3. Sanity-clamp estricto: la frontera DEBE estar entre las dos
    #        baselines (baseline_Narr â‰¤ frontera â‰¤ baseline_Nabj). Esto
    #        garantiza que la frontera nunca invade el cuerpo de ninguna seÃ±al.
    #
    #   Los extremos de la columna (primer lead arriba / Ãºltimo lead abajo)
    #   no usan este mecanismo: se limitan a la extensiÃ³n del ROI base + margen
    #   para no incluir encabezados ni pies de pÃ¡gina del ECG.
    rois_final = {}
    for col in columnas:
        n = len(col)

        # Fronteras internas
        fronteras = []
        for i in range(n - 1):
            n_arr = col[i]        # lead superior
            n_abj = col[i + 1]   # lead inferior
            mid   = (baseline[n_arr] + baseline[n_abj]) // 2
            x_col = int(rois_base[n_arr][0])
            w_col = int(rois_base[n_arr][2])

            frontera = _encontrar_mejor_frontera(img_gray, x_col, w_col, mid,
                                                 margen_busqueda=100)

            # Sanity-clamp: la frontera debe estar entre las dos baselines
            clamp_lo = baseline[n_arr]   # nunca por encima de la baseline superior
            clamp_hi = baseline[n_abj]   # nunca por debajo de la baseline inferior
            frontera = max(clamp_lo, min(clamp_hi, frontera))

            fronteras.append(int(frontera))

        # ROIs finales
        for i, nombre in enumerate(col):
            x, y_b, w, h_b = [int(v) for v in rois_base[nombre]]
            sig_top, sig_bot = extent_base[nombre]
            altura_senal = max(1, sig_bot - sig_top + 1)
            margen = max(margen_min_px, int(altura_senal * margen_pct))

            # â”€â”€ Borde superior â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            if i == 0:
                # Primer lead: usa extensiÃ³n del ROI base con margen pequeÃ±o
                y0 = max(0, sig_top - margen)
                y0 = min(y0, sig_top)   # garantÃ­a: cubre la seÃ±al real
            else:
                # Lead interno: su borde superior ES la frontera con el lead de arriba
                # NO se aplica garantÃ­a de sig_top para evitar incluir overflow de vecino
                y0 = fronteras[i - 1]

            # â”€â”€ Borde inferior â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
            if i == n - 1:
                # Ãšltimo lead: usa extensiÃ³n del ROI base con margen pequeÃ±o
                y1 = min(img_alto, sig_bot + margen)
                y1 = max(y1, sig_bot)   # garantÃ­a: cubre la seÃ±al real
                # Doble capa de protecciÃ³n contra footer / tira de ritmo:
                #  1. No superar el fondo del ROI base calibrado manualmente.
                #  2. No superar el fin del Ã¡rea ECG detectado desde la imagen.
                y1 = min(y1, y_b + h_b)
                y1 = min(y1, _ecg_end)
            else:
                # Lead interno: su borde inferior ES la frontera con el lead de abajo
                # NO se aplica garantÃ­a de sig_bot para evitar incluir overflow de vecino
                y1 = fronteras[i]

            rois_final[nombre] = [x, max(0, y0), w, max(1, y1 - y0)]

    return rois_final


def _validar_sin_solapamiento(rois, leads_order):
    """
    Comprueba que ningÃºn par de ROIs de la misma columna se solapa.
    Imprime advertencias (no lanza excepciÃ³n) para no detener el pipeline.
    """
    presentes = [n for n in leads_order if n in rois]
    sin_solapamiento = True
    for i in range(len(presentes)):
        for j in range(i + 1, len(presentes)):
            n1, n2 = presentes[i], presentes[j]
            x1, y1, w1, h1 = rois[n1]
            x2, y2, w2, h2 = rois[n2]
            # Solo verificar pares con solapamiento horizontal (misma columna)
            if x1 + w1 <= x2 or x2 + w2 <= x1:
                continue
            # Â¿Se solapan verticalmente?
            y1_bot = y1 + h1
            y2_bot = y2 + h2
            if y1_bot > y2 and y2_bot > y1:
                overlap = min(y1_bot, y2_bot) - max(y1, y2)
                print(f"  âš   Solapamiento {n1}/{n2}: {overlap} px")
                sin_solapamiento = False
    if sin_solapamiento:
        print("  âœ“ ValidaciÃ³n: ningÃºn ROI se solapa")


def seleccionar_rois(img, interactivo=False):
    """
    Obtiene las ROIs de las 12 derivaciones en este orden de preferencia:
      1. ROIs guardados en rois_derivaciones.json  (calibrados manualmente)
         â†’ Se ajustan dinÃ¡micamente a la seÃ±al real del ECG actual.
      2. DetecciÃ³n automÃ¡tica (gap-based + seÃ±al-aware)
      3. SelecciÃ³n manual interactiva (sÃ³lo si interactivo=True)

    En modo NO interactivo (pipeline automÃ¡tico) nunca pide input al usuario.
    """
    print("\n" + "="*70)
    print("[4/7] DETECCIÃ“N DE DERIVACIONES")
    print("="*70)

    # â”€â”€ 1. ROIs guardados ajustados a la seÃ±al real (sin solapamiento) â”€â”€â”€â”€â”€â”€
    rois_guardados = cargar_rois_guardados()
    if all(n in rois_guardados for n in LEADS_ORDER):
        print("  âœ“ ROIs base cargados. Ajustando a seÃ±al real (sin solapamiento)...")
        rois_ajustados = _ajustar_rois_sin_solapamiento(img, rois_guardados, LEADS_ORDER)

        for nombre in LEADS_ORDER:
            if nombre not in rois_ajustados:
                continue
            x0, y0, w0, h0 = [int(v) for v in rois_guardados[nombre]]
            xa, ya, wa, ha  = rois_ajustados[nombre]
            if ha != h0 or ya != y0:
                print(f"    {nombre:>4}: y {y0}â†’{ya}  h {h0}â†’{ha}")

        _validar_sin_solapamiento(rois_ajustados, LEADS_ORDER)
        visualizar_rois_detectados(img, rois_ajustados)
        return rois_ajustados

    # â”€â”€ 2. DetecciÃ³n automÃ¡tica â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    print("  Sin ROIs guardados. Intentando detecciÃ³n automÃ¡tica...")
    rois_auto = auto_detectar_rois(img)

    if rois_auto and all(n in rois_auto for n in LEADS_ORDER):
        print("  âœ“ DetecciÃ³n automÃ¡tica exitosa.")
        visualizar_rois_detectados(img, rois_auto)

        if interactivo:
            print("  Revisa 'rois_detectados.png' para verificar que sean correctas.")
            respuesta = input("  Â¿Son correctas? (s=guardar y continuar / n=selecciÃ³n manual): ").strip().lower()
            if respuesta == 'n':
                # caer a selecciÃ³n manual abajo
                rois_auto = None
            else:
                guardar_rois(rois_auto)
                return rois_auto
        else:
            # Pipeline automÃ¡tico: guardar y continuar
            guardar_rois(rois_auto)
            return rois_auto

    # â”€â”€ 3. SelecciÃ³n manual (sÃ³lo en modo interactivo) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    if not interactivo:
        raise RuntimeError(
            "No hay ROIs guardados y la detecciÃ³n automÃ¡tica fallÃ³.\n"
            "Ejecuta en modo interactivo o selecciona los ROIs manualmente:\n"
            "  python pipeline_unificado.py --manual"
        )

    print("\nSelecciÃ³n manual:")
    print("  - Arrastra para marcar cada derivaciÃ³n, luego ENTER/ESPACIO")
    print("  - Sin dibujar + ENTER â†’ usa el ROI guardado (si existe)")
    print("="*70 + "\n")

    rois = {}
    for nombre in LEADS_ORDER:
        print(f"â†’ Selecciona derivaciÃ³n: {nombre}")
        try:
            cv2.namedWindow(f"Selecciona {nombre}", cv2.WINDOW_NORMAL)
            roi = cv2.selectROI(f"Selecciona {nombre}", img,
                                showCrosshair=True, fromCenter=False)
            cv2.destroyWindow(f"Selecciona {nombre}")

            if roi[2] == 0 or roi[3] == 0:
                if nombre in rois_guardados:
                    rois[nombre] = rois_guardados[nombre]
                    print(f"  âœ“ Usando ROI guardado para {nombre}")
                else:
                    print(f"  âš  Sin ROI para {nombre}, se omitirÃ¡.")
            else:
                rois[nombre] = [int(roi[0]), int(roi[1]), int(roi[2]), int(roi[3])]
                print(f"  âœ“ {nombre} seleccionado")
        except Exception:
            if nombre in rois_guardados:
                rois[nombre] = rois_guardados[nombre]

    guardar_rois(rois)
    return rois


# Alias para compatibilidad con test_digitalizacion.py
def seleccionar_rois_interactivo(img):
    return seleccionar_rois(img, interactivo=True)


# ==================== PASO 4: DIGITALIZACIÃ“N ====================

def _segmentar_columna_pip(px_oscuros, gap_min=3):
    """Divide indices de pixeles en segmentos conectados (gap >= gap_min)."""
    if len(px_oscuros) == 0:
        return []
    segs, ini = [], 0
    for k in range(1, len(px_oscuros)):
        if px_oscuros[k] - px_oscuros[k - 1] >= gap_min:
            segs.append(px_oscuros[ini:k])
            ini = k
    segs.append(px_oscuros[ini:])
    return segs


def _digitalizar_region_robusta_pip(region, umbral=128, margen_borde=12):
    """
    Extrae la posicion vertical del trazo ECG columna a columna,
    robusta ante pixeles invasores de derivaciones adyacentes.

    SOLUCION - PROXIMIDAD A BASELINE + EXCLUSION DE ZONA FRONTERA:
      1a pasada: para cada columna elige el grupo mas cercano al centro
                 geometrico del ROI => estimacion inicial del trazo propio.
      Baseline:  mediana robusta (percentil 10-90) de la 1a pasada.
      2a pasada: para cada columna:
         a) Descarta segmentos dentro de 'margen_borde' px del borde del ROI
            cuyo centro este lejos de la baseline (>= alto/4). Esos pixeles
            son casi siempre de la derivacion vecina que se "fuga".
         b) De los segmentos validos elige el mas cercano a la baseline.
         c) Si NO hay segmentos validos (solo hay invasores en el borde),
            usa la ultima posicion valida en lugar de adoptar el invasor.
    """
    alto, ancho = region.shape
    centro_roi = alto / 2.0
    umbral_lejos = alto / 4.0   # distancia maxima a baseline para aceptar borde

    # Primera pasada â€” estimacion inicial con centro geometrico
    pos_primera = []
    for col in range(ancho):
        px = np.where(region[:, col] < umbral)[0]
        segs = _segmentar_columna_pip(px)
        if not segs:
            pos_primera.append(None)
            continue
        centros = [float(np.median(s)) for s in segs]
        pos_primera.append(min(centros, key=lambda c: abs(c - centro_roi)))

    vals = [v for v in pos_primera if v is not None]
    if not vals:
        return np.full(ancho, centro_roi)

    # Baseline robusta (percentil 10-90)
    arr = np.array(vals)
    p10, p90 = np.percentile(arr, [10, 90])
    mascara = (arr >= p10) & (arr <= p90)
    baseline = float(np.median(arr[mascara])) if mascara.any() else float(np.median(arr))

    # Segunda pasada â€” con exclusion de zona frontera
    senal  = np.zeros(ancho)
    ultimo = baseline
    for col in range(ancho):
        px = np.where(region[:, col] < umbral)[0]
        segs = _segmentar_columna_pip(px)
        if not segs:
            senal[col] = ultimo
            continue

        # Filtrar segmentos sospechosos: en zona de borde Y lejos de la baseline
        segs_validos = []
        for s in segs:
            c = float(np.median(s))
            en_borde = (c < margen_borde) or (c > alto - margen_borde)
            dist_bl  = abs(c - baseline)
            # Aceptar si NO estÃ¡ en borde, o si estÃ¡ cerca de la baseline
            if not en_borde or dist_bl < umbral_lejos:
                segs_validos.append(s)

        if not segs_validos:
            # Todos los segmentos son invasores de borde â†’ mantener posicion anterior
            senal[col] = ultimo
            continue

        centros = [float(np.median(s)) for s in segs_validos]
        mejor   = min(centros, key=lambda c: abs(c - baseline))
        senal[col] = mejor
        ultimo = mejor

    return senal


def digitalizar_roi(img, roi):
    """
    Digitaliza una region ROI extrayendo la posicion vertical del trazo.
    Usa _digitalizar_region_robusta_pip para descartar pixeles invasores
    de derivaciones adyacentes mediante seleccion por proximidad a baseline.
    """
    x, y, w, h = roi
    region = img[y:y+h, x:x+w]

    if np.mean(region) < 127:
        region = cv2.bitwise_not(region)

    seÃ±al = _digitalizar_region_robusta_pip(region)
    seÃ±al = h - seÃ±al                          # invertir Y
    seÃ±al = median_filter(seÃ±al, size=3)       # suavizar ruido
    return seÃ±al


def convertir_a_milivoltios(seÃ±al, dpi, mm_per_mv=10):
    """Convierte pÃ­xeles a mV"""
    mm_por_pixel = 25.4 / dpi
    pixeles_por_mv = mm_per_mv / mm_por_pixel
    
    linea_base = np.median(seÃ±al)
    seÃ±al_centrada = seÃ±al - linea_base
    seÃ±al_mv = seÃ±al_centrada / pixeles_por_mv
    
    return seÃ±al_mv


def digitalizar_todas_derivaciones(img, rois):
    """Digitaliza todas las derivaciones"""
    print("\n[5/7] Digitalizando derivaciones...")
    
    derivaciones = {}
    derivaciones_mv = {}
    
    for nombre in LEADS_ORDER:
        if nombre not in rois:
            print(f"   {nombre}: No disponible")
            continue
        
        print(f"   â†’ {nombre}...", end=" ")
        seÃ±al = digitalizar_roi(img, rois[nombre])
        derivaciones[nombre] = seÃ±al
        
        # Convertir a mV
        seÃ±al_mv = convertir_a_milivoltios(seÃ±al, DPI, ECG_MM_PER_MV)
        derivaciones_mv[nombre] = seÃ±al_mv
        
        print(f"âœ“ {len(seÃ±al)} muestras, std={np.std(seÃ±al_mv):.3f} mV")
    
    return derivaciones_mv


# ==================== PASO 5: PREPARAR PARA EL MODELO ====================

def preparar_para_modelo(derivaciones_mv):
    """Prepara las seÃ±ales para el modelo (1000 muestras, 12 leads)"""
    print("\n[6/7] Preparando datos para el modelo...")
    
    signals = []
    
    for lead in LEADS_ORDER:
        if lead not in derivaciones_mv:
            raise ValueError(f"Falta la derivaciÃ³n {lead}")
        
        values = derivaciones_mv[lead]
        
        if len(values) < 10:
            raise ValueError(f"DerivaciÃ³n {lead} tiene muy pocos datos")
        
        # Resamplear a 1000 puntos
        x_old = np.linspace(0, 1, len(values))
        x_new = np.linspace(0, 1, 1000)
        resampled = np.interp(x_new, x_old, values)
        
        # Normalizar si estÃ¡ activado
        if NORMALIZE_SIGNALS:
            normed = (resampled - np.mean(resampled)) / (np.std(resampled) + 1e-8)
        else:
            normed = resampled
        
        signals.append(normed)
        print(f"   âœ“ {lead}: {len(values)} â†’ 1000 muestras")
    
    # Construir tensor (1, 1000, 12)
    arr = np.stack(signals, axis=1)
    tensor = arr.reshape(1, 1000, arr.shape[1])
    
    print(f"   âœ“ Tensor creado: {tensor.shape}")
    return tensor, signals


# ==================== PASO 6: PREDICCIÃ“N ====================

def detect_metrics(signal_curve):
    """Detecta mÃ©tricas clÃ­nicas bÃ¡sicas"""
    diff = np.diff(signal_curve)
    peaks = np.where((diff[:-1] > 0) & (diff[1:] < 0))[0]
    beats = len(peaks)
    hr = beats * 6  # aprox para ventana de 10 segundos
    variability = np.std(np.diff(peaks)) if beats > 1 else 0.0
    amplitude = signal_curve.max() - signal_curve.min()
    return beats, hr, variability, amplitude


def pedir_metadata_paciente():
    """Solicita los datos clÃ­nicos del paciente por consola."""
    print("\n" + "="*70)
    print("DATOS DEL PACIENTE (necesarios para el modelo)")
    print("="*70)
    while True:
        try:
            age = float(input("  Edad (aÃ±os): "))
            break
        except ValueError:
            print("  Ingresa un nÃºmero vÃ¡lido.")
    while True:
        try:
            sex_str = input("  Sexo (0=Femenino, 1=Masculino): ").strip()
            sex = int(sex_str)
            if sex not in (0, 1):
                raise ValueError
            break
        except ValueError:
            print("  Ingresa 0 o 1.")
    while True:
        try:
            weight = float(input("  Peso (kg): "))
            break
        except ValueError:
            print("  Ingresa un nÃºmero vÃ¡lido.")
    return age, sex, weight


def normalizar_metadata_paciente(age, sex, weight):
    """Normaliza metadata con las stats del entrenamiento."""
    with open('norm_stats.json') as f:
        stats = json.load(f)
    age_norm    = (age    - stats['age_mean']) / stats['age_std']
    weight_norm = (weight - stats['w_mean'])   / stats['w_std']
    return np.array([[age_norm, sex, weight_norm]], dtype='float32')


def _leer_pesos_keras3(h5_path):
    """
    Lee los pesos guardados en formato Keras 3 (layers/<name>/vars/N)
    y devuelve un dict: layer_name â†’ lista de arrays numpy.
    Maneja el caso especial del Bidirectional LSTM.
    """
    import h5py
    pesos = {}
    with h5py.File(h5_path, 'r') as f:
        if 'layers' not in f:
            return pesos
        for layer_name in f['layers']:
            grp = f['layers'][layer_name]
            # Caso estÃ¡ndar: layers/<name>/vars/0, 1, ...
            if 'vars' in grp:
                vars_grp = grp['vars']
                idx = 0
                arr_list = []
                while str(idx) in vars_grp:
                    arr_list.append(vars_grp[str(idx)][:])
                    idx += 1
                if arr_list:
                    pesos[layer_name] = arr_list
            # Caso Bidirectional: sublayers forward_layer / backward_layer
            sub_keys = [k for k in grp.keys() if k in ('forward_layer', 'backward_layer')]
            if sub_keys:
                combined = []
                for sub in ('forward_layer', 'backward_layer'):
                    if sub not in grp:
                        continue
                    cell_grp = grp[sub].get('cell', grp[sub])
                    if 'vars' in cell_grp:
                        vars_grp = cell_grp['vars']
                        idx = 0
                        while str(idx) in vars_grp:
                            combined.append(vars_grp[str(idx)][:])
                            idx += 1
                if combined:
                    pesos[layer_name] = combined
    return pesos


def _cargar_modelo():
    """
    Carga el modelo compatible con Keras 2 (TF 2.15) aunque los pesos
    hayan sido guardados con Keras 3.

    Reconstruye la arquitectura desde modelo.py y asigna los pesos
    leyÃ©ndolos directamente del H5 interno del .keras zip.

    El mapeo usa primero el nombre de la capa; si hay discrepancia de forma
    (Keras 2 y Keras 3 a veces numeran las Dense de forma distinta),
    busca en el pool restante de pesos una entrada con formas compatibles.
    """
    import zipfile, tempfile, os as _os
    from modelo import construir_modelo

    if not _os.path.exists(MODELO_PATH):
        raise FileNotFoundError(
            f"No existe el modelo '{MODELO_PATH}'. "
            "Copia modelo_arritmias_Fina_v4.keras dentro de la carpeta modelo."
        )

    model = construir_modelo()

    with zipfile.ZipFile(MODELO_PATH, 'r') as zf:
        with tempfile.NamedTemporaryFile(suffix='.h5', delete=False) as tmp:
            tmp.write(zf.read('model.weights.h5'))
            tmp_path = tmp.name

    try:
        pesos_k3 = _leer_pesos_keras3(tmp_path)
    finally:
        _os.unlink(tmp_path)

    capas_con_pesos = [l for l in model.layers if l.weights]

    # Pool de pesos aÃºn no asignados (para fallback por forma)
    pool = dict(pesos_k3)
    asignadas, omitidas = 0, 0

    for layer in capas_con_pesos:
        nombre = layer.name
        esperados = len(layer.weights)
        formas_modelo = [tuple(w.shape) for w in layer.weights]

        # 1. Intento por nombre exacto con forma correcta
        if nombre in pool:
            arr_list = pool[nombre]
            formas_k3 = [tuple(a.shape) for a in arr_list]
            if formas_k3 == formas_modelo:
                layer.set_weights(arr_list)
                del pool[nombre]
                asignadas += 1
                continue

        # 2. Fallback: buscar en el pool una entrada con formas idÃ©nticas
        candidato = None
        for clave, arr_list in pool.items():
            if [tuple(a.shape) for a in arr_list] == formas_modelo:
                candidato = clave
                break

        if candidato is not None:
            print(f"   >> {nombre} <- '{candidato}' (reasignado por forma)")
            layer.set_weights(pool[candidato])
            del pool[candidato]
            asignadas += 1
        else:
            print(f"   âš   Sin pesos compatibles para: {nombre} {formas_modelo}")
            omitidas += 1

    print(f"   OK Pesos cargados (Keras 3->2): {asignadas} capas OK, {omitidas} omitidas")
    if omitidas > 0:
        raise RuntimeError(
            f"{omitidas} capas sin pesos. "
            "Verifica que modelo.py coincide con la arquitectura guardada."
        )
    return model


def predecir_con_modelo(tensor, signals, age=None, sex=None, weight=None):
    """Realiza la prediccion con el modelo ECG-only."""
    print("\n[7/7] Ejecutando predicciÃ³n con el modelo...")

    model = _cargar_modelo()

    probs = model.predict(tensor, verbose=0)[0]

    idx = int(np.argmax(probs))
    label = LABEL_NAMES.get(config.LABEL_CODES[idx], "Desconocido")
    confidence = probs[idx] * 100

    print(f"\n{'='*70}")
    print(f"RESULTADO DEL DIAGNÃ“STICO")
    print(f"{'='*70}")
    print(f"DiagnÃ³stico: {label}")
    print(f"Confianza:   {confidence:.2f}%")
    print(f"{'='*70}\n")

    return label, confidence, probs, signals


# ==================== PASO 7: VISUALIZACIÃ“N ====================

def guardar_reporte_completo(signals, label, confidence, probs, derivaciones_mv):
    """Genera y guarda el reporte visual completo"""
    print("Generando reporte visual...")
    
    fig = plt.figure(figsize=(18, 12))
    gs = fig.add_gridspec(4, 4, width_ratios=[1, 1, 1, 1.2], height_ratios=[1, 1, 1, 1])
    
    # Panel de derivaciones (3x4 grid)
    for idx, nombre in enumerate(LEADS_ORDER):
        fila = idx // 4
        col = idx % 4
        
        if col == 3 and fila < 3:  # Ãšltima columna reservada para otros paneles
            continue
        
        ax = fig.add_subplot(gs[fila, col])
        
        if nombre in derivaciones_mv:
            seÃ±al = signals[idx]
            ax.plot(seÃ±al, color='black', linewidth=0.8)
            ax.set_title(f'{nombre}', fontsize=10, fontweight='bold')
            ax.set_xticks([])
            ax.set_yticks([])
            ax.grid(True, alpha=0.3)
        else:
            ax.text(0.5, 0.5, 'N/A', ha='center', va='center')
            ax.set_title(f'{nombre}', fontsize=10)
    
    # Panel de probabilidades (top 5)
    ax_bar = fig.add_subplot(gs[0:2, 3])
    top_idx = np.argsort(probs)[::-1][:5]
    labels = [LABEL_NAMES.get(config.LABEL_CODES[i], f"Clase {i}") for i in top_idx]
    values = probs[top_idx] * 100
    colors = ['green' if 'normal' in lbl.lower() else 'red' for lbl in labels]
    ax_bar.barh(labels[::-1], values[::-1], color=colors[::-1])
    ax_bar.set_xlabel("Probabilidad (%)", fontsize=10)
    ax_bar.set_title("Top 5 Predicciones", fontsize=12, fontweight='bold')
    ax_bar.set_xlim(0, 100)
    ax_bar.grid(axis='x', linestyle='--', alpha=0.3)
    
    # Panel de mÃ©tricas
    ax_metrics = fig.add_subplot(gs[2, 3])
    ax_metrics.axis('off')
    beats, hr, variability, amplitude = detect_metrics(signals[1])  # Usar lead II
    texto_metricas = (
        "MÃ‰TRICAS CLÃNICAS (Lead II)\n"
        f"â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”\n"
        f"Frecuencia Cardiaca: {hr:.0f} lpm\n"
        f"Variabilidad: {variability:.2f}\n"
        f"Latidos detectados: {beats}\n"
        f"Amplitud QRS: {amplitude:.2f}"
    )
    ax_metrics.text(0.1, 0.5, texto_metricas, fontsize=9, family='monospace',
                    bbox=dict(facecolor='lightcyan', alpha=0.8, boxstyle='round'))
    
    # Panel de diagnÃ³stico
    ax_diag = fig.add_subplot(gs[3, 3])
    ax_diag.axis('off')
    texto_diag = (
        f"DIAGNÃ“STICO AUTOMATIZADO\n"
        f"â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”â”\n"
        f"Resultado: {label}\n"
        f"Confianza: {confidence:.1f}%\n"
        f"Modelo: {os.path.basename(MODELO_PATH)}\n\n"
        " IMPORTANTE:\n"
        "Este anÃ¡lisis es orientativo.\n"
        "Requiere validaciÃ³n mÃ©dica."
    )
    color_fondo = 'lightgreen' if 'normal' in label.lower() else 'lightyellow'
    ax_diag.text(0.1, 0.5, texto_diag, fontsize=9, family='monospace',
                 bbox=dict(facecolor=color_fondo, alpha=0.8, boxstyle='round'))
    
    fig.suptitle(f'REPORTE ECG - {label}', fontsize=16, fontweight='bold')
    plt.tight_layout()
    plt.savefig('reporte_ecg_completo.png', dpi=150, bbox_inches='tight')
    plt.close()
    
    print("âœ“ Reporte guardado: reporte_ecg_completo.png")


def exportar_csv(derivaciones_mv):
    """Exporta las seÃ±ales a CSV"""
    print("Exportando a CSV...")
    
    max_len = max(len(derivaciones_mv[n]) for n in LEADS_ORDER if n in derivaciones_mv)
    
    with open(OUTPUT_CSV, 'w', encoding='utf-8') as f:
        f.write('Muestra,' + ','.join(LEADS_ORDER) + '\n')
        
        for i in range(max_len):
            fila = f"{i}"
            for nombre in LEADS_ORDER:
                if nombre in derivaciones_mv and i < len(derivaciones_mv[nombre]):
                    fila += f",{derivaciones_mv[nombre][i]:.6f}"
                else:
                    fila += ","
            f.write(fila + '\n')
    
    print(f"âœ“ CSV guardado: {OUTPUT_CSV}")


# ==================== PIPELINE PRINCIPAL ====================

def ejecutar_pipeline_completo(args=None):
    """Ejecuta el pipeline completo de extracciÃ³n a predicciÃ³n"""
    import argparse
    if args is None:
        parser = argparse.ArgumentParser(description='Pipeline ECG: PDF â†’ Arritmia')
        parser.add_argument('--pdf',    type=str,  default=PDF_PATH,
                            help='Ruta al archivo PDF del ECG')
        parser.add_argument('--manual', action='store_true',
                            help='Fuerza selecciÃ³n manual de ROIs (Ãºtil para nuevo formato de ECG)')
        args = parser.parse_args()

    pdf_path = args.pdf

    print("\n" + "="*70)
    print("PIPELINE UNIFICADO: EXTRACCIÃ“N â†’ DIGITALIZACIÃ“N â†’ PREDICCIÃ“N")
    print("="*70)
    print(f"  PDF: {pdf_path}")
    print(f"  Modo ROI: {'manual' if args.manual else 'automÃ¡tico (usa guardados si existen)'}\n")

    try:
        # 1. Extraer imagen del PDF
        img_original = extraer_ecg_de_pdf(pdf_path, dpi=DPI)
        
        # 2. Detectar y recortar regiÃ³n del ECG
        ecg_recortado = detectar_region_ecg(img_original)
        
        # 3. Preprocesar
        img_sin_grid = eliminar_cuadricula(ecg_recortado)
        img_procesada = preprocesar_para_digitalizacion(img_sin_grid)
        
        # Guardar imagen procesada
        cv2.imwrite(OUTPUT_IMAGE, img_procesada)
        print(f"âœ“ Imagen procesada guardada: {OUTPUT_IMAGE}\n")
        
        # 4. Seleccionar ROIs (guardados â†’ auto-detecciÃ³n â†’ error)
        rois = seleccionar_rois(img_procesada, interactivo=args.manual)

        if len(rois) < 12:
            print(f"\n  ADVERTENCIA: Solo {len(rois)}/12 derivaciones seleccionadas")
            if not args.manual:
                raise RuntimeError("Faltan derivaciones. Ejecuta con --manual para seleccionarlas.")
            respuesta = input("Â¿Continuar de todos modos? (s/n): ")
            if respuesta.lower() != 's':
                return
        
        # 5. Digitalizar
        derivaciones_mv = digitalizar_todas_derivaciones(img_procesada, rois)
        
        # 6. Exportar CSV
        exportar_csv(derivaciones_mv)
        
        # 7. Preparar para modelo
        tensor, signals = preparar_para_modelo(derivaciones_mv)

        # 7.5 Pedir datos del paciente
        age, sex, weight = pedir_metadata_paciente()

        # 8. Predecir
        label, confidence, probs, signals = predecir_con_modelo(tensor, signals, age, sex, weight)
        
        # 9. Generar reporte
        guardar_reporte_completo(signals, label, confidence, probs, derivaciones_mv)
        
        # 10. Resumen final
        print("\n" + "="*70)
        print("PIPELINE COMPLETADO EXITOSAMENTE")
        print("="*70)
        print(f"âœ“ Derivaciones procesadas: {len(derivaciones_mv)}/12")
        print(f"âœ“ DiagnÃ³stico: {label} ({confidence:.1f}%)")
        print(f"âœ“ Archivos generados:")
        print(f"  - {OUTPUT_IMAGE}")
        print(f"  - {OUTPUT_CSV}")
        print(f"  - reporte_ecg_completo.png")
        print("="*70 + "\n")
        
    except Exception as e:
        print(f"\nâŒ Error en el pipeline: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    ejecutar_pipeline_completo()

````

## modelo/norm_stats.json

````json
{
  "age_mean": 62.03249610723715,
  "age_std": 31.058443303374126,
  "w_mean": 70.32462257125448,
  "w_std": 10.304296187320496
}
````

## modelo/rois_derivaciones.json

````json
{
  "rois": {
    "I":   [270, 592, 1353, 172],
    "II":  [275, 773, 1331, 289],
    "III": [292, 1139, 1312, 315],
    "aVR": [307, 1570, 1282, 217],
    "aVL": [311, 1909, 1278, 134],
    "aVF": [307, 2055, 1288, 195],
    "V1":  [1746, 601, 1330, 193],
    "V2":  [1775, 830, 1309, 261],
    "V3":  [1759, 1088, 1306, 304],
    "V4":  [1764, 1395, 1314, 342],
    "V5":  [1777, 1748, 1292, 274],
    "V6":  [1772, 2028, 1308, 230]
  }
}

````

