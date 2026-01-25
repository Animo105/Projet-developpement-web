<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = Auth::guard('web')->user();
        $character = Auth::guard('character')->user();
        $impersonating = $request->session()->get('impersonating');

        $originalUser = null;
        if ($impersonating && isset($impersonating['user_id'])) {
            $originalUser = User::find($impersonating['user_id']);
        }

        $isAdmin = false;
        if ($user) {
            $isAdmin = $user->isAdmin();
        } elseif ($originalUser) {
            $isAdmin = $originalUser->isAdmin();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'family_name' => $user->family_name,
                    'given_name' => $user->given_name,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                ] : ($originalUser ? [
                    'id' => $originalUser->id,
                    'family_name' => $originalUser->family_name,
                    'given_name' => $originalUser->given_name,
                    'email' => $originalUser->email,
                    'full_name' => $originalUser->full_name,
                ] : null),
                'character' => $character ? [
                    'id' => $character->id,
                    'name' => $character->name,
                    'skill_point' => $character->skill_point,
                    'knowledge_point' => $character->knowledge_point,
                ] : null,
                'isAdmin' => $isAdmin,
                'isImpersonating' => !is_null($impersonating),
                'impersonating' => $impersonating,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}
