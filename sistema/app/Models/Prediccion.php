<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Prediccion extends Model
{
    use Auditable;
    protected $table      = 'predicciones';
    protected $primaryKey = 'prediccion_id';

    // Pertenece a una imagen ECG
    public function imagen()
    {
        return $this->belongsTo(Imagen::class, 'imagen_id', 'imagen_id');
    }

    // Ritmo cardíaco predicho (Alias corto)
    public function ritmo()
    {
        return $this->belongsTo(RitmoCardiaco::class, 'ritmo_id', 'ritmo_id');
    }

    // Ritmo cardíaco predicho por el modelo
    public function ritmoCardiaco()
    {
        return $this->belongsTo(RitmoCardiaco::class, 'ritmo_id', 'ritmo_id');
    }

}