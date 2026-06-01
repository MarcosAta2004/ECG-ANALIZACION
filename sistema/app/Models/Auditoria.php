<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $primaryKey = 'auditoria_id';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'accion',
        'modulo',
        'entidad',
        'entidad_id',
        'descripcion',
        'valores_anteriores',
        'valores_nuevos',
        'user_agent',
        'created_at',
        // ⭐ Campos nuevos para auditoría mejorada:
        'valoracion_medico',      // Solo cardiólogo
        'cardiologo_id',          // QUÉ cardiólogo valoró
        'nivel_confianza',        // Todos los roles
        'razon_cambio',           // Todos los roles
        'severidad',              // Todos los roles
    ];

    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relación: Usuario que realizó la acción
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación: Cardiólogo que realizó la valoración (opcional)
     * Solo se llena si usuario_id tiene rol CARDIOLOGO
     */
    public function cardiologo()
    {
        return $this->belongsTo(User::class, 'cardiologo_id');
    }
}
