<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrefijoPaciente extends Model
{
    use HasFactory;
    protected $table      = 'prefijos_paciente';
    protected $primaryKey = 'prefijo_id';

    // Un prefijo puede usarse en muchos pacientes
    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'prefijo_id', 'prefijo_id');
    }
}