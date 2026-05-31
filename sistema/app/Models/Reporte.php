<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $table      = 'reportes';
    protected $primaryKey = 'reporte_id';

    // Pertenece a un estudio
    public function estudio()
    {
        return $this->belongsTo(Estudio::class, 'estudio_id', 'estudio_id');
    }

    // Usuario que solicitó la generación del reporte
    public function generadoPor()
    {
        return $this->belongsTo(User::class, 'generado_por', 'id');
    }
}