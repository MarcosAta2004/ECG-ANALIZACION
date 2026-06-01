<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Auditable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    use Auditable;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'login',
        'email',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'password',
        'tipo_documento_identidad_id',
        'numero_documento',
        'rol_id',
        'estado',
        'profile_photo_path',
    ];

    /**
     * Relación con el tipo de documento
     */
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumentoIdentidad::class, 'tipo_documento_identidad_id');
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];
    public function rolesa()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }



    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function setPasswordAttribute($password)
    {

        $this->attributes['password'] = Hash::make($password);
    }

    
    public function getJWTCustomClaims()
    {
        return [];
    }
}
