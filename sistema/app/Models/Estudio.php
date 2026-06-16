<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Estudio extends Model
{
    use Auditable, HasFactory;
    protected $table      = 'estudios';
    protected $primaryKey = 'estudio_id';

    // Pertenece a un paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id', 'paciente_id');
    }

    // Fue registrado por un operador
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id');
    }

    // Un estudio tiene exactamente una imagen ECG
    public function imagen()
    {
        return $this->hasOne(Imagen::class, 'estudio_id', 'estudio_id');
    }

    // Un estudio tiene exactamente un diagnóstico del cardiólogo
    public function diagnostico()
    {
        return $this->hasOne(Diagnostico::class, 'estudio_id', 'estudio_id');
    }

    // Un estudio puede tener un reporte generado
    public function reporte()
    {
        return $this->hasOne(Reporte::class, 'estudio_id', 'estudio_id');
    }

    public function tieneDiagnosticoFinal(): bool
    {
        return $this->diagnostico()->exists();
    }
}