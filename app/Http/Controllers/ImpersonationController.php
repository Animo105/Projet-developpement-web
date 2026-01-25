<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a character.
     */
    public function start(Request $request, Character $character)
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            abort(403, 'Not authenticated as user.');
        }

        if (!$user->isAdmin()) {
            if (!$character->participant || $character->participant->user_id !== $user->id) {
                abort(403, 'You do not have permission to impersonate this character.');
            }
        }

        $request->session()->put('impersonating', [
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);

        Auth::guard('web')->logout();
        Auth::guard('character')->login($character);

        if ($request->input("asAdmin")) {
            return redirect()->route('cards.index')->with('success', "Carte donnée avec succès à {$character->name}");
        }
        return redirect()->route('character.profile')->with('success', "Vous incarnez maintenant {$character->name}");
    }

    /**
     * Stop impersonating and return to user account.
     */
    public function stop(Request $request)
    {
        $impersonating = $request->session()->get('impersonating');

        if (!$impersonating) {
            return redirect()->route('home')->with('error', 'No active impersonation session.');
        }

        Auth::guard('character')->logout();
        Auth::guard('web')->loginUsingId($impersonating['user_id']);

        $request->session()->forget('impersonating');

        return redirect()->route('participants.index')->with('success', 'Impersonation terminée');
    }
}
