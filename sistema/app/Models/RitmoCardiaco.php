<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RitmoCardiaco extends Model
{
    protected $table      = 'ritmos_cardiacos';
    protected $primaryKey = 'ritmo_id';

    // Pertenece a un grupo cardíaco
    public function grupoCardiaco()
    {
        return $this->belongsTo(GrupoCardiaco::class, 'grupo_id', 'grupo_id');
    }

    // Pertenece a un nivel de gravedad
    public function nivelGravedad()
    {
        return $this->belongsTo(NivelGravedad::class, 'nivel_id', 'nivel_id');
    }

    // Pertenece a una clasificación de arritmia
    public function clasificacionArritmia()
    {
        return $this->belongsTo(ClasificacionArritmia::class, 'clasificacion_id', 'clasificacion_id');
    }

    // Un ritmo puede aparecer en muchas predicciones
    public function predicciones()
    {
        return $this->hasMany(Prediccion::class, 'ritmo_id', 'ritmo_id');
    }

    // Un ritmo puede aparecer en muchos diagnósticos
    public function diagnosticos()
    {
        return $this->hasMany(Diagnostico::class, 'ritmo_id', 'ritmo_id');
    }
}