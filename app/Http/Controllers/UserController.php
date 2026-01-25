<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class UserController extends Controller
{
    private function passwordValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|string|min:8|max:255',
            'new_password_confirmation' => 'nullable|string|same:new_password|required_with:new_password',
        ], [
            'old_password.required' => __("Le mot de passe actuel est obligatoire."),
            'new_password.required' => __("Le nouveau mot de passe est obligatoire."),
            'new_password.min' => __("Le nouveau mot de passe doit contenir au moins 8 caractères."),
            'new_password.confirmed' => __("La confirmation du mot de passe doit correspondre."),
            'new_password_confirmation.required' => __('La confirmation du mot de passe est obligatoire.'),
            'new_password_confirmation.required_with' => __('La confirmation du mot de passe est obligatoire.'),
            'new_password_confirmation.same' => __('Les mots de passe doivent correspondre.'),
        ]);
    }
    private function infoValidator(Request $request) {
        $account = $request->user();
        return Validator::make($request->all(), [
            'given_name' => 'required|filled|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ]+(?:-[A-Za-zÀ-ÖØ-öø-ÿ]+)*$/|max:64|min:2',
            'family_name' => 'required|filled|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ]+(?:-[A-Za-zÀ-ÖØ-öø-ÿ]+)*$/|max:64|min:2',
            'email' => 'required|email|max:128|regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/|unique:users,email,' . $account->id,
            'phone' => ['required', 'regex:/^(\(\d{3}\)|\d{3})( |-)?\d{3}-?\d{4}$/'],
        ], [
            'given_name.required' => __('Un prénom est requis.'),
            'given_name.filled' => __('Le prénom ne peut pas être vide'),
            'given_name.regex' => __('Le prénom contient des caractères non autorisés.'),
            'given_name.max' => __('Le prénom ne peut pas dépasser 64 caractères'),
            'given_name.min' => __('Le prénom ne peut pas avoir moins de 2 caractères'),
            'family_name.required' => __('Un nom de famille est requis.'),
            'family_name.filled' => __('Le nom de famille ne peut pas être vide'),
            'family_name.regex' => __('Le nom de famille contient des caractères non autorisés.'),
            'family_name.max' => __('Le nom de famille ne peut pas dépasser 64 caractères'),
            'family_name.min' => __('Le nom de famille ne peut pas avoir moins de 2 caractères'),
            'birthdate.required' => __('Une date de naissance est requise.'),
            'email.required' => __("L'adresse email est obligatoire."),
            'email.email' => __("L'adresse email n'est pas valide."),
            'email.max' => __("L'adresse email ne doit pas dépasser 128 caractères."),
            'email.unique' => __("Un autre compte a déjà cette adresse email."),
            'email.regex' => __("L'adresse email n'a pas un bon format."),
            'phone.required' => __("Le numéro de téléphone est obligatoire."),
            'phone.regex' => __("Numéro de téléphone invalide."),
        ]);
    }

    public function index(Request $request)
    {
        $accounts = User::orderBy('family_name')
            ->with('contacts')
            ->orderBy('given_name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'given_name' => $user->given_name,
                    'family_name' => $user->family_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'contacts' => $user->contacts->map(fn ($c) => [
                        'id' => $c->id,
                        'family_name' => $c->family_name,
                        'given_name' => $c->given_name,
                        'email' => $c->email,
                        'phone' => $c->phone,
                    ]),
                    'participants' => $user->participants->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->family_name . ' ' . $p->given_name,
                        'is_adult' => $p->is_adult(),
                        'character' => $p->character? [
                            'id' => $p->character->id,
                            'name' => $p->character->name,
                ] : null
                    ]),
                ];
            })->values()->toArray();
        return Inertia::render('Users/index', ['users'=>$accounts]);
    }

    public function profile(Request $request) {
        $user = $request->user();
        return Inertia::render('Users/profile', [
            'user' => [
                'id' => $user->id,
                'given_name' => $user->given_name,
                'family_name' => $user->family_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'contacts' => $user->contacts->map(fn($c) => [
                        'id' => $c->id,
                        'given_name' => $c->given_name,
                        'family_name' => $c->family_name,
                        'email' => $c->email,
                        'phone' => $c->phone,
                    ]),
            ]
        ]);
    }

    public function filter(Request $request): JsonResponse
    {
        if ($request->routeIs('api.accounts.filter')) {
            $query = User::query();

            // Recherche par nom complet
            if (!empty($request['nom'])) {
                $query->where(function ($q) use ($request) {
                    $q->where('accounts.given_name', 'LIKE', '%' . $request['nom'] . '%')
                        ->orWhere('accounts.family_name', 'LIKE', '%' . $request['nom'] . '%');
                });
            }
            // Recherche par courriel
            if (!empty($request['courriel'])) {
                $query->where('email', 'LIKE', '%' . $request['courriel'] . '%');
            }
            // Recherche par téléphone
            if (!empty($request['telephone'])) {
                $query->where('phone', 'LIKE', '%' . $request['telephone'] . '%');
            }

            // Recherche par ordre alphabétique
            if (!empty($request['order']) && $request['order'] == 2)
                $query->orderBy('accounts.given_name', 'desc');
            else
                $query->orderBy('accounts.given_name');

            $billStatus = $request->input('bill_status', 0); // 0 = Tous
            if ($billStatus != 0) {
                $query->join('participants', 'participants.account_id', '=', 'accounts.id')
                    ->join('inscription_locations', 'inscription_locations.participant_id', '=', 'participants.id')
                    ->join('bills', 'bills.id', '=', 'inscription_locations.bill_id')
                    ->where('bills.status_id', $billStatus);
            }

            $accounts = $query->distinct()->get();


            // Format les infos envoyée pour le JS dynamique.
            $formated_accounts = [];
            foreach ($accounts as $account) {
                $newRow = [];
                $newRow['id'] = $account->id;
                $newRow['family_name'] = $account->family_name;
                $newRow['given_name'] = $account->given_name;
                $newRow['email'] = $account->email;
                $newRow['phone'] = $account->phone;
                $newRow['contacts'] = 0;
                foreach ($account->contacts as $contact) {
                    $newRow['contacts']++;
                }
                $newRow['participants'] = 0;
                foreach ($account->participants as $participant) {
                    $newRow['participants']++;
                }
                $formated_accounts[] = $newRow;
            }


            return response()->json(['accounts' => $formated_accounts]);
        }
        return response()->json(['errors' => __("Route non autorisée.")], 403);
    }


    /**
     * @throws ValidationException
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->intended('/')->with('warning', "Vous devez être connecter.");
        }

        if ($request->routeIs('users.update.password')) {

            $validator = $this->passwordValidator($request);

            if ($validator->fails()) {
                return back()->withErrors($validator->errors())->withInput();
            }

            if (!Hash::check($request->old_password, $user->password)) {
                return back()->withErrors(['Credentials does not match']);
            }

            $user->update(['password' => Hash::make($request->new_password)]);

            return back();



        } else if ($request->routeIs('users.update.info')) {

            $validator = $this->infoValidator($request);

            if ($validator->fails()) {
                return back()->withErrors($validator->errors())->withInput();
            }

            $values = $validator->validated();

            $user->fill($values);
            //Si aucune valeur n'a été changé
            if (!$user->isDirty())
                return back();

            $user->save();

            return back();
        }
        return back()->withErrors(['Route not found']);
    }

    //suppression du compte
    public function destroy(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->intended('/')->with('warning', "Vous devez être connecter.");
        }
        if ($user->role === UserRole::Admin) { //TODO à tester
            if ($request->input('id') === $user->id) {
                return back()->withErrors(['Une erreur s\'est produite']);
            }
            try {
                $account = User::find($request->input('id'));
                if (!$account) {
                    return back()->withErrors(['Une erreur s\'est produite']);
                }
                $account->delete();
                return redirect()->intended('/accounts');
            }
            catch (QueryException $e) {
                return back()->withErrors(['Une erreur s\'est produite']);
            }
        }
        else if ($user->id === $request->input('id')) {
            try {
                $user->delete();
                return redirect()->intended('/');
            } catch (QueryException $e) {
                return back()->withErrors(['Une erreur s\'est produite']);
            }
        }

        return back()->withErrors(['Une erreur s\'est produite']);
    }
}
