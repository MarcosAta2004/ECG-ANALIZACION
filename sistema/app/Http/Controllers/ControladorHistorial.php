<?php

namespace App\Http\Controllers;

use App\Models\AnalisisEcg;
use App\Services\ServicioAuditoria;
use Illuminate\Http\Request;

class ControladorHistorial extends Controlador
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'filter' => ['nullable', 'in:all,normal,arritmia,reviewed,unreviewed'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $filter = $validated['filter'] ?? 'all';

        $query = AnalisisEcg::query();

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

        $statsBaseQuery = AnalisisEcg::query();
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
     * Guarda o actualiza la valoración médica de un análisis.
     */
    public function review(Request $request, $id)
    {
        $request->validate([
            'doctor_result' => 'required|in:normal,arritmia',
            'doctor_label'  => 'nullable|string|max:200',
            'doctor_notes'  => 'nullable|string|max:1000',
        ]);

        $analysis = AnalisisEcg::where('id', $id)->firstOrFail();
        $anteriores = $analysis->only(['doctor_result', 'doctor_label', 'doctor_notes', 'reviewed_at']);

        $analysis->update([
            'doctor_result' => $request->input('doctor_result'),
            'doctor_label'  => $request->input('doctor_label'),
            'doctor_notes'  => $request->input('doctor_notes'),
            'reviewed_at'   => now(),
        ]);

        ServicioAuditoria::registrar(
            'actualizar',
            'Historial',
            'ecg_analyses',
            $analysis->id,
            'Registro o actualizacion de valoracion medica.',
            $anteriores,
            $analysis->only(['doctor_result', 'doctor_label', 'doctor_notes', 'reviewed_at']),
            $request
        );

        return response()->json([
            'ok'          => true,
            'reviewed_at' => $analysis->reviewed_at->format('Y-m-d H:i'),
        ]);
    }

    /**
     * Elimina la valoración médica de un análisis.
     */
    public function removeReview($id)
    {
        $analysis = AnalisisEcg::where('id', $id)->firstOrFail();
        $anteriores = $analysis->only(['doctor_result', 'doctor_label', 'doctor_notes', 'reviewed_at']);

        $analysis->update([
            'doctor_result' => null,
            'doctor_label'  => null,
            'doctor_notes'  => null,
            'reviewed_at'   => null,
        ]);

        ServicioAuditoria::registrar(
            'eliminar',
            'Historial',
            'ecg_analyses',
            $analysis->id,
            'Eliminacion de valoracion medica.',
            $anteriores,
            $analysis->only(['doctor_result', 'doctor_label', 'doctor_notes', 'reviewed_at']),
            request()
        );

        return response()->json(['ok' => true]);
    }
}
