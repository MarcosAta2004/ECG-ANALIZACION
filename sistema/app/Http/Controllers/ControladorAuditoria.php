<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Usuario;
use Illuminate\Http\Request;

class ControladorAuditoria extends Controlador
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'user' => ['nullable', 'integer', 'exists:users,id'],
            'module' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));

        $query = Auditoria::query()->with('usuario');

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery
                    ->where('descripcion', 'like', '%' . $search . '%')
                    ->orWhere('entidad', 'like', '%' . $search . '%')
                    ->orWhere('entidad_id', 'like', '%' . $search . '%')
                    ->orWhereHas('usuario', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        if (! empty($validated['user'])) {
            $query->where('usuario_id', $validated['user']);
        }

        if (! empty($validated['module'])) {
            $query->where('modulo', $validated['module']);
        }

        if (! empty($validated['action'])) {
            $query->where('accion', $validated['action']);
        }

        if (! empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if (! empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        $auditorias = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $usuarios = Usuario::query()->orderBy('name')->get(['id', 'name', 'email']);
        $modulos = Auditoria::query()->select('modulo')->distinct()->orderBy('modulo')->pluck('modulo');
        $acciones = Auditoria::query()->select('accion')->distinct()->orderBy('accion')->pluck('accion');

        $stats = [
            'total' => Auditoria::count(),
            'hoy' => Auditoria::whereDate('created_at', today())->count(),
            'usuarios' => Auditoria::where('modulo', 'Usuarios')->count(),
            'clinico' => Auditoria::whereIn('modulo', ['ECG', 'Historial', 'Reportes'])->count(),
        ];

        $filters = [
            'search' => $search,
            'user' => $validated['user'] ?? '',
            'module' => $validated['module'] ?? '',
            'action' => $validated['action'] ?? '',
            'from' => $validated['from'] ?? '',
            'to' => $validated['to'] ?? '',
        ];

        return view('auditoria', compact('auditorias', 'usuarios', 'modulos', 'acciones', 'stats', 'filters'));
    }
}
