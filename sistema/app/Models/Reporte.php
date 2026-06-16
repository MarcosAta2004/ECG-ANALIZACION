<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    // 1. Configuración de la tabla y llave primaria
    protected $table = 'reportes';
    protected $primaryKey = 'reporte_id';

    // 2. Asignación masiva protegida
    protected $fillable = [
        'estudio_id',
        'generado_por',
        'resumen',
        'ruta_pdf',
        'estado',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    /**
     * Pertenece a un estudio médico.
     */
    public function estudio(): BelongsTo
    {
        return $this->belongsTo(Estudio::class, 'estudio_id', 'estudio_id');
    }

    /**
     * Usuario (médico/sistema) que generó el reporte.
     */
    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por', 'id');
    }

    // ==========================================
    // SCOPES (Filtros rápidos)
    // ==========================================

    /**
     * Scope para obtener solo los reportes activos/oficiales.
     * Uso en tu controlador: Reporte::activos()->where('estudio_id', $id)->first();
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }
    
    /**
     * Scope para obtener el historial de reportes anulados/corregidos.
     */
    public function scopeAnulados($query)
    {
        return $query->where('estado', 0);
    }
}