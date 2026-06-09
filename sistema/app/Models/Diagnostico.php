<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Diagnostico extends Model
{
    use Auditable, HasFactory;

    protected $table      = 'diagnosticos';
    protected $primaryKey = 'diagnostico_id';

    /**
     * Campos normalizados:
     * - Sin 'descripcion'   → se obtiene via ritmoCardiaco->nombre
     * - Sin 'medico_id'     → reemplazado por 'registrado_por' (FK a users)
     * - 'concordancia'      → cast boolean (true/false) = TINYINT 1/0
     */
    protected $fillable = [
        'estudio_id',
        'ritmo_id',
        'registrado_por',
        'concordancia',
        'observacion',
        'fecha_revision',
        'estado',
    ];

    protected $casts = [
        'concordancia'   => 'boolean',
        'fecha_revision' => 'datetime',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    /** Estudio clínico al que pertenece el diagnóstico */
    public function estudio()
    {
        return $this->belongsTo(Estudio::class, 'estudio_id', 'estudio_id');
    }

    /** Ritmo cardíaco diagnosticado por el usuario */
    public function ritmoCardiaco()
    {
        return $this->belongsTo(RitmoCardiaco::class, 'ritmo_id', 'ritmo_id');
    }

    /** Usuario (cardiólogo/admin) que registró el diagnóstico */
    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id');
    }
}