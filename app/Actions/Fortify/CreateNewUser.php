<?php

namespace App\Actions\Fortify;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use ProfileValidationRules;

    private const NAME_REQUIRED = 'El nombre es obligatorio.';

    private const AGE_INVALID = 'La edad debe ser un número entero entre 1 y 120.';

    private const EMAIL_INVALID = 'Introduce un email válido.';

    private const EMAIL_TAKEN = 'Ese email ya está registrado.';

    private const PASSWORD_TOO_SHORT = 'La contraseña debe tener al menos 8 caracteres.';

    private const PASSWORDS_DIFFER = 'Las contraseñas no coinciden.';

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'age' => ['bail', 'required', 'integer', 'between:1,120'],
            // Solo el registro exige mínimo 8 y nada más: restablecer y cambiar la contraseña
            // siguen con PasswordValidationRules hasta la Story "Contraseña segura" del Sprint 2.
            // Se usa min:8 (no la regla Password) porque admite mensajes propios.
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => self::NAME_REQUIRED,
            'age.required' => self::AGE_INVALID,
            'age.integer' => self::AGE_INVALID,
            'age.between' => self::AGE_INVALID,
            'email.required' => self::EMAIL_INVALID,
            'email.email' => self::EMAIL_INVALID,
            'email.unique' => self::EMAIL_TAKEN,
            'password.required' => self::PASSWORD_TOO_SHORT,
            'password.min' => self::PASSWORD_TOO_SHORT,
            'password.confirmed' => self::PASSWORDS_DIFFER,
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'age' => (int) $input['age'],
            'password' => $input['password'],
        ]);
    }
}
