<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumentoIdentidad extends Model
{
    use HasFactory;

    protected $table = 'tipo_documento_identidades';

    protected $fillable = [
        'siglas',
        'maximo',
        'minimo',
        'descripcion',
        'estado'
    ];

    /**
     * Relación con los usuarios
     */
    public function users()
    {
        return $this->hasMany(User::class, 'tipo_documento_identidad_id');
    }
}
