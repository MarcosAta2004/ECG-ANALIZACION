<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    // Solo lectura — los registros los inserta AuditoriaService automáticamente

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search'  => ['nullable', 'string', 'max:120'],
            'usuario' => ['nullable', 'integer', 'exists:users,id'],
            'modulo'  => ['nullable', 'string', 'max:100'],
            'accion'  => ['nullable', 'string', 'max:100'],
            'desde'   => ['nullable', 'date'],
            'hasta'   => ['nullable', 'date'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));

        $query = Auditoria::query()->with('usuario');

        // Búsqueda general por descripción, entidad o nombre del usuario
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'like', '%' . $search . '%')
                  ->orWhere('entidad', 'like', '%' . $search . '%')
                  ->orWhere('entidad_id', 'like', '%' . $search . '%')
                  ->orWhereHas('usuario', function ($u) use ($search) {
                      $u->whereRaw(
                          "CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) like ?",
                          ['%' . $search . '%']
                      );
                  });
            });
        }

        // Filtros exactos
        if (!empty($validated['usuario'])) {
            $query->where('usuario_id', $validated['usuario']);
        }

        if (!empty($validated['modulo'])) {
            $query->where('modulo', $validated['modulo']);
        }

        if (!empty($validated['accion'])) {
            $query->where('accion', $validated['accion']);
        }

        // Filtro por rango de fechas
        if (!empty($validated['desde'])) {
            $query->whereDate('created_at', '>=', $validated['desde']);
        }

        if (!empty($validated['hasta'])) {
            $query->whereDate('created_at', '<=', $validated['hasta']);
        }

        $auditorias = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        // Listas para los selects del filtro
        $usuarios = User::orderBy('apellido_paterno')->get(['id', 'nombres', 'apellido_paterno', 'apellido_materno']);
        $modulos  = Auditoria::select('modulo')->distinct()->orderBy('modulo')->pluck('modulo');
        $acciones = Auditoria::select('accion')->distinct()->orderBy('accion')->pluck('accion');

        // Estadísticas del panel superior — módulos en español según tu esquema
        $stats = [
            'total'        => Auditoria::count(),
            'hoy'          => Auditoria::whereDate('created_at', today())->count(),
            'usuarios'     => Auditoria::where('modulo', 'usuarios')->count(),
            'clinico'      => Auditoria::whereIn('modulo', ['estudios', 'predicciones', 'diagnosticos', 'reportes'])->count(),
        ];

        // Filtros activos para repintar el formulario
        $filters = [
            'search'  => $search,
            'usuario' => $validated['usuario'] ?? '',
            'modulo'  => $validated['modulo']  ?? '',
            'accion'  => $validated['accion']  ?? '',
            'desde'   => $validated['desde']   ?? '',
            'hasta'   => $validated['hasta']   ?? '',
        ];

        return view('auditoria', compact('auditorias', 'usuarios', 'modulos', 'acciones', 'stats', 'filters'));
    }
}