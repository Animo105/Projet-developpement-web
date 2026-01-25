<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'givenName' => ['required', 'string', 'min:2', 'max:50'],
            'familyName' => ['required', 'string', 'min:2', 'max:50'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'string', 'regex:/^\(\d{3}\) \d{3}-\d{4}$/'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->max(50),
            ],
        ], [
            // Given Name messages
            'givenName.required' => 'Le prénom est requis.',
            'givenName.min' => 'Le prénom doit contenir au moins 2 caractères.',
            'givenName.max' => 'Le prénom ne peut pas dépasser 50 caractères.',

            // Family Name messages
            'familyName.required' => 'Le nom est requis.',
            'familyName.min' => 'Le nom doit contenir au moins 2 caractères.',
            'familyName.max' => 'Le nom ne peut pas dépasser 50 caractères.',

            // Email messages
            'email.required' => 'L\'email est requis.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'email.max' => 'L\'email ne peut pas dépasser 255 caractères.',

            // Phone messages
            'phone.required' => 'Le téléphone est requis.',
            'phone.regex' => 'Format attendu : (819) 201-2013',

            // Password messages
            'password.required' => 'Le mot de passe est requis.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.max' => 'Le mot de passe ne peut pas dépasser 50 caractères.',
            'password.mixed_case' => 'Le mot de passe doit contenir des majuscules et des minuscules.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
        ])->validate();

        return User::create([
            'given_name' => trim($input['givenName']),
            'family_name' => trim($input['familyName']),
            'email' => strtolower(trim($input['email'])),
            'phone' => $input['phone'],
            'password' => $input['password'],
        ]);
    }
}
