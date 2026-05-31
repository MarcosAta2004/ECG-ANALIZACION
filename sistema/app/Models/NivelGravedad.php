<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NivelGravedad extends Model
{
    protected $table      = 'niveles_gravedad';
    protected $primaryKey = 'nivel_id';

    // Un nivel de gravedad tiene muchos ritmos cardíacos
    public function ritmosCardiacos()
    {
        return $this->hasMany(RitmoCardiaco::class, 'nivel_id', 'nivel_id');
    }
}