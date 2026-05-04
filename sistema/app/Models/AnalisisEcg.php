<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalisisEcg extends Model
{
    protected $table = 'ecg_analyses';

    protected $fillable = [
        'user_id',
        'filename',
        'patient_identifier',
        'patient_age',
        'patient_sex',
        'patient_weight',
        'label',
        'label_code',
        'type',
        'confidence',
        'top_predictions',
        'doctor_result',
        'doctor_label',
        'doctor_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'top_predictions' => 'array',
        'confidence'      => 'float',
        'patient_age'     => 'float',
        'patient_weight'  => 'float',
        'patient_sex'     => 'integer',
        'reviewed_at'     => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    /** Alias legible del sexo */
    public function getSexLabelAttribute(): string
    {
        return $this->patient_sex === 1 ? 'Masculino' : 'Femenino';
    }
}
