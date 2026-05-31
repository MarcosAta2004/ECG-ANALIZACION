<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Imagen;
use App\Models\Paciente;
use App\Models\Estudio;
use App\Models\Diagnostico;
use App\Models\Prediccion;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

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
                'value' => Imagen::count(),
                'subtitle' => 'Estudios cargados',
                'trend' => 'up',
                'trend_value' => '+15%',
                'icon' => 'file-heart'
            ],
            [
                'title' => 'Diagnósticos',
                'value' => Diagnostico::count(),
                'subtitle' => 'Revisados por médicos',
                'trend' => 'up',
                'trend_value' => 'Estable',
                'icon' => 'check-circle'
            ],
            [
                'title' => 'Arritmias Det.',
                'value' => Prediccion::whereHas('ritmo', function($q){ $q->where('label', '!=', 'NORM'); })->count(),
                'subtitle' => 'Alertas generadas',
                'trend' => 'down',
                'trend_value' => '-5%',
                'icon' => 'alert-triangle'
            ],
            [
                'title' => 'Pacientes',
                'value' => Paciente::count(),
                'subtitle' => 'Registrados',
                'trend' => 'up',
                'trend_value' => '+2',
                'icon' => 'users'
            ],
        ];

        // Actividad reciente basada en Imágenes y sus Predicciones
        $recentActivity = Imagen::with('prediccion.ritmo')
            ->latest()
            ->take(5)
            ->get()
            ->map(function($img) {
                $predik = $img->prediccion;
                $isNorm = ($predik->ritmo->label ?? '') === 'NORM';
                return [
                    'file' => $img->ruta ? basename($img->ruta) : 'Análisis',
                    'time' => $img->created_at->diffForHumans(),
                    'type' => $isNorm ? 'Normal' : 'Arritmia'
                ];
            });

        // Retornar a la vista 'resumen' que es la que tiene el dashboard real
        return view('resumen', compact('stats', 'recentActivity'));
    }

    public function actualizarContrasena(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with([
                'status' => 'error',
                'message' => 'La contraseña actual es incorrecta',
            ]);
        }

        $user->password = $request->input('password');
        $user->save();

        return redirect()->back()->with([
            'status' => 'success',
            'message' => 'Contraseña actualizada correctamente',
        ]);
    }
}
