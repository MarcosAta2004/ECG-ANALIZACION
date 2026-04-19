<?php

namespace App\Http\Controllers;

use App\Models\EcgAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HistoryController extends Controller
{
    public function index()
    {
        $userId = Session::get('user.id');

        $rows = EcgAnalysis::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        $history = $rows->map(fn($r) => [
            'id'            => $r->id,
            'filename'      => $r->filename,
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
        ])->values()->toArray();

        return view('history', compact('history'));
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

        $userId   = Session::get('user.id');
        $analysis = EcgAnalysis::where('id', $id)
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
     * Elimina la valoración médica de un análisis.
     */
    public function removeReview($id)
    {
        $userId   = Session::get('user.id');
        $analysis = EcgAnalysis::where('id', $id)
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
