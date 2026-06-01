<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Prediccion extends Model
{
    use Auditable, HasFactory;
    protected $table      = 'predicciones';
    protected $primaryKey = 'prediccion_id';

    protected $casts = [
        'top_predicciones' => 'array',
    ];

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