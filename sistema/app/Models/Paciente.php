<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Paciente extends Model
{
    use Auditable;
    protected $table      = 'pacientes';
    protected $primaryKey = 'paciente_id';

    // Pertenece a un prefijo de código anónimo
    public function prefijoPaciente()
    {
        return $this->belongsTo(PrefijoPaciente::class, 'prefijo_id', 'prefijo_id');
    }

    // Fue registrado por un operador
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id');
    }

    // Un paciente puede tener muchos estudios
    public function estudios()
    {
        return $this->hasMany(Estudio::class, 'paciente_id', 'paciente_id');
    }
}