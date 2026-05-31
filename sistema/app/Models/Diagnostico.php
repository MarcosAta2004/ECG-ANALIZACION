<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Diagnostico extends Model
{
    use Auditable;
    protected $table      = 'diagnosticos';
    protected $primaryKey = 'diagnostico_id';

    // Pertenece a un estudio
    public function estudio()
    {
        return $this->belongsTo(Estudio::class, 'estudio_id', 'estudio_id');
    }

    // Ritmo cardíaco diagnosticado por el cardiólogo
    public function ritmoCardiaco()
    {
        return $this->belongsTo(RitmoCardiaco::class, 'ritmo_id', 'ritmo_id');
    }

    // Cardiólogo que registró el diagnóstico
    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }
}