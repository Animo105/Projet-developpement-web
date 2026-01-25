<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CharacterLoginController extends Controller
{
    /**
     * Handle character login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $character = Character::where('name', $request->name)
            ->with('participant')
            ->first();

        if (!$character || !$character->participant) {
            throw ValidationException::withMessages([
                'name' => ['Ces identifiants ne correspondent pas à nos enregistrements.'],
            ]);
        }

        $participant = $character->participant;

        if (!$participant->password) {
            throw ValidationException::withMessages([
                'name' => ['Ce personnage ne peut pas se connecter car le participant n\'a pas de mot de passe défini.'],
            ]);
        }

        if (!Hash::check($request->password, $participant->password)) {
            throw ValidationException::withMessages([
                'name' => ['Ces identifiants ne correspondent pas à nos enregistrements.'],
            ]);
        }

        $remember = $request->filled('remember') && $request->boolean('remember');

        Auth::guard('character')->login($character, $remember);
        $request->session()->regenerate();

        return back()->with('success', "Bienvenue {$character->name}!");
    }


    /**
     * Handle character logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('character')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()->with('success', "Au revoir!");
    }
}
