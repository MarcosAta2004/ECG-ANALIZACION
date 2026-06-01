<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Contraseña por defecto usada en los tests.
     */
    protected static ?string $password;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'login'              => $this->faker->unique()->userName(),
            'email'              => $this->faker->unique()->safeEmail(),
            'nombres'            => $this->faker->firstName(),
            'apellido_paterno'   => $this->faker->lastName(),
            'apellido_materno'   => $this->faker->lastName(),
            'password'           => static::$password ??= Hash::make('password'),
            'remember_token'     => Str::random(10),
            'numero_documento'   => $this->faker->numerify('########'),
            'estado'             => 1,
        ];
    }

    /** Usuario desactivado */
    public function inactivo(): static
    {
        return $this->state(['estado' => 0]);
    }
}
