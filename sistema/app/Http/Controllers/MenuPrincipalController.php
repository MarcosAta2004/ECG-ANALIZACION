<?php

namespace App\Http\Controllers;

use App\Events\MiEventoPrueba;
use App\Models\CentroCosto;
use App\Models\Departamento;
use App\Models\Documento;
use App\Models\DocumentoRuta;
use App\Models\Estado;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

use function PHPUnit\Framework\returnSelf;

class MenuPrincipalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(Request $request)
    {
        // Datos básicos para el Dashboard de Resumen
        $stats = [
            [
                'title' => 'Total Análisis',
                'value' => \App\Models\Imagen::count(),
                'subtitle' => 'Histórico acumulado',
                'trend' => 'up',
                'trend_value' => '+12%',
                'icon' => 'file-heart'
            ],
            [
                'title' => 'Diagnósticos',
                'value' => \App\Models\Diagnostico::count(),
                'subtitle' => 'Revisados por médicos',
                'trend' => 'up',
                'trend_value' => 'Estable',
                'icon' => 'check-circle'
            ],
            [
                'title' => 'Arritmias Det.',
                'value' => \App\Models\Prediccion::whereHas('ritmo', function($q){ $q->where('label', '!=', 'NORM'); })->count(),
                'subtitle' => 'Alertas generadas',
                'trend' => 'down',
                'trend_value' => '-5%',
                'icon' => 'alert-triangle'
            ],
            [
                'title' => 'Pacientes',
                'value' => \App\Models\Paciente::count(),
                'subtitle' => 'Registrados',
                'trend' => 'up',
                'trend_value' => '+2',
                'icon' => 'users'
            ],
        ];

        $recentActivity = \App\Models\Imagen::with('predicciones.ritmo')
            ->latest()
            ->take(5)
            ->get()
            ->map(function($img) {
                $predik = $img->predicciones->first();
                $isNorm = ($predik->ritmo->label ?? '') === 'NORM';
                return [
                    'file' => $img->nombre_original ?? $img->filename,
                    'time' => $img->created_at->diffForHumans(),
                    'type' => $isNorm ? 'Normal' : 'Arritmia'
                ];
            });

        return view('menu-principal.index', compact('stats', 'recentActivity'));
    }



    public function luzVerdeLuzRoja()
    {

        return view('luces.index');
    }


    public function indexActualizarContrasena()
    {

        return view('menu-principal.index'); // Dashboard como pieza central
    }


    public function actualizarContrasena(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed', // El 'confirmed' valida password_confirmation
        ], [
            'current_password.required' => 'La contraseña actual es requerida.',
            'password.required' => 'La nueva contraseña es requerida.',
            'password.min' => 'La nueva contraseña debe contener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $user = User::where('id', auth()->user()->id)->first();

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with([
                'ok' => 'enabled',
                'message' => 'La contraseña actual es incorrecta',
                'alert' => 'danger',
                'data' => ''
            ])->withInput();
        }

        // Actualizar la contraseña (Laravel automáticamente la hasheará si usas un mutator)
        $user->password = $request->input('password');
        $user->save();

        return redirect()->back()->with([
            'ok' => 'enabled',
            'message' => 'Se acaba de actualizar correctamente la contraseña',
            'alert' => 'success',
            'data' => ''
        ]);
    }

    public function send(Request $request)
    {
        $mensaje = $request->query('msg', 'Hola desde Controller');

        // 1. Construyes la instancia del evento
        //$evento = new MiEventoPrueba($mensaje);

        // 2. Lo envías FORZANDO la conexión 'reverb' sobre el PendingBroadcast
        /*broadcast($evento)
            ->onConnection('reverb');*/
        event(new MiEventoPrueba($mensaje));

        return response()->json([
            'status'  => 'ok',
            'message' => "Evento enviado: {$mensaje}"
        ]);
    }
}
