<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoCardiaco extends Model
{
    protected $table      = 'grupos_cardiacos';
    protected $primaryKey = 'grupo_id';

    // Un grupo tiene muchos ritmos cardíacos
    public function ritmosCardiacos()
    {
        return $this->hasMany(RitmoCardiaco::class, 'grupo_id', 'grupo_id');
    }
}