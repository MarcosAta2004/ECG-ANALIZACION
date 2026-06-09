<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Prediccion extends Model
{
    use Auditable, HasFactory;

    protected $table      = 'predicciones';
    protected $primaryKey = 'prediccion_id';

    /**
     * Campos asignables masivamente (normalizados — sin label_detectado, label_code, tipo)
     * Esa información se obtiene via relación con ritmos_cardiacos.
     */
    protected $fillable = [
        'imagen_id',
        'ritmo_id',
        'probabilidad',
        'tiempo_ms',
        'top_predicciones',
        'estado',
    ];

    protected $casts = [
        'top_predicciones' => 'array',
        'probabilidad'     => 'float',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    /** Imagen ECG asociada */
    public function imagen()
    {
        return $this->belongsTo(Imagen::class, 'imagen_id', 'imagen_id');
    }

    /** Ritmo cardíaco predicho (alias corto) */
    public function ritmo()
    {
        return $this->belongsTo(RitmoCardiaco::class, 'ritmo_id', 'ritmo_id');
    }

    /** Ritmo cardíaco predicho (nombre explícito, con eager-load de sub-relaciones) */
    public function ritmoCardiaco()
    {
        return $this->belongsTo(RitmoCardiaco::class, 'ritmo_id', 'ritmo_id');
    }
}