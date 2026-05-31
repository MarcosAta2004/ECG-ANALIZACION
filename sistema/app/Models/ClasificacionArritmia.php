<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClasificacionArritmia extends Model
{
    protected $table      = 'clasificaciones_arritmia';
    protected $primaryKey = 'clasificacion_id';

    // Una clasificación tiene muchos ritmos cardíacos
    public function ritmosCardiacos()
    {
        return $this->hasMany(RitmoCardiaco::class, 'clasificacion_id', 'clasificacion_id');
    }
}