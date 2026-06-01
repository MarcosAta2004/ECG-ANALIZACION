<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imagen extends Model
{
    use HasFactory;
    protected $table      = 'imagenes';
    protected $primaryKey = 'imagen_id';

    // Pertenece a un estudio
    public function estudio()
    {
        return $this->belongsTo(Estudio::class, 'estudio_id', 'estudio_id');
    }

    // Una imagen tiene exactamente una predicción del modelo CNN-LSTM
    public function prediccion()
    {
        return $this->hasOne(Prediccion::class, 'imagen_id', 'imagen_id');
    }
}