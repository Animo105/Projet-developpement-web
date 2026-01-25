<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Event;
use App\Models\Inscriptions;
use App\Models\Participant;
use App\Models\Tarif;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ParticipantController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Participant::with(['characters', 'character']);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }
        $query->orderBy('given_name')->orderBy('family_name');

        $participants = $query->get()->map(fn($p) => [
            'id' => $p->id,
            'name' => "{$p->given_name} {$p->family_name}",
            'birthdate' => $p->birthdate?->translatedformat('j M Y'),
            'age' => (string)((int)$p->birthdate?->diffInYears(now())) . " ans",
            'condition' => $p->condition,
            'character' => $p->character_id ? [
                'id' => $p->character->id,
                'name' => $p->character->name,
                'skill_point' => $p->character->skill_point,
                'knowledge_point' => $p->character->knowledge_point,
            ] : null,
        ]);

        $events = Event::select('id', 'name')->get()->toArray();
        $tarifs = Tarif::where('type', "participant")->select('id', 'event_id')->get()->toArray();
        $inscriptions = Inscriptions::select('participant_id', 'tarif_id')->get()->toArray();


        return Inertia::render('Participants/Index', [
            'participants' => $participants,
            'events' => $events,
            'tarifs' => $tarifs,
            'inscriptions' => $inscriptions,
        ]);
    }

    public function show($id)
    {
        $participant = Participant::with('character')->findOrFail($id);
        return response()->json($participant);
    }

    public function update(Request $request, int $id)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'given_name' => 'sometimes|filled|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ]+(?:-[A-Za-zÀ-ÖØ-öø-ÿ]+)*$/|max:64|min:2',
                'family_name' => 'sometimes|filled|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ]+(?:-[A-Za-zÀ-ÖØ-öø-ÿ]+)*$/|max:64|min:2',
                'birthdate' => 'sometimes|filled|date|before:today',
                'condition' => 'sometimes|max:255',
                'lore' => 'sometimes',
                'skill_point' => 'sometimes|filled|integer|between:0,255',
                'knowledge_point' => 'sometimes|filled|integer|between:0,255',
            ],
            [
                'given_name.filled' => __('Le prénom ne peut pas être vide'),
                'given_name.regex' => __('Le prénom contient des caractères non autorisés.'),
                'given_name.max' => __('Le prénom ne peut pas dépasser 64 caractères'),
                'given_name.min' => __('Le prénom ne peut pas avoir moins de 2 caractères'),
                'family_name.filled' => __('Le nom de famille ne peut pas être vide'),
                'family_name.regex' => __('Le nom de famille contient des caractères non autorisés.'),
                'family_name.max' => __('Le nom de famille ne peut pas dépasser 64 caractères'),
                'family_name.min' => __('Le nom de famille ne peut pas avoir moins de 2 caractères'),
                'birthdate.filled' => __('La date de naissance ne peut pas être vide'),
                'birthdate.date' => __('La date de naissance doit être une date'),
                'birthdate.before' => __('La date de naissance doit être avant aujourd\'hui'),
                'condition.max' => __('La condition ne peut pas dépasser 255 caractères'),
                'skill_point.filled' => __('Le nombre de points de compétence ne peut pas être vide'),
                'skill_point.integer' => __('Le nombre de points de compétence ne peut qu\'être un nombre'),
                'skill_point.between' => __('Le nombre de points de compétence doit être un nombre de 0 à 255.'),
                'knowledge_point.filled' => __('Le nombre de points d\'apprentissage ne peut pas être vide'),
                'knowledge_point.integer' => __('Le nombre de points d\'apprentissage ne peut qu\'être un nombre'),
                'knowledge_point.between' => __('Le nombre de points d\'apprentissage doit être un nombre de 0 à 255.'),
            ]
        );

        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors()], 422);
        }

        $participant = Participant::findOrFail($id);
        $participant->fill($validation->validated());

        if ($participant->isDirty()) {
            $participant->save();
        }

        return response()->json([
            'success' => true,
            'message' => __('Les informations du participant ont été mises à jour avec succès.'),
            'participant' => $participant,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validation = Validator::make(
            $request->all(),
            [
                'given_name' => 'required|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ]+(?:-[A-Za-zÀ-ÖØ-öø-ÿ]+)*$/|max:64|min:2',
                'family_name' => 'required|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ]+(?:-[A-Za-zÀ-ÖØ-öø-ÿ]+)*$/|max:64|min:2',
                'birthdate' => 'required|date|before:today|before_or_equal:' . now()->subYears(5)->format('Y-m-d'),
                'condition' => 'nullable|max:255',
                'character_username' => 'required|string|min:2|max:50|unique:characters,name',
            ],
            [
                'given_name.required' => __('Le prénom est obligatoire'),
                'given_name.regex' => __('Le prénom contient des caractères non autorisés.'),
                'given_name.max' => __('Le prénom ne peut pas dépasser 64 caractères'),
                'given_name.min' => __('Le prénom doit contenir au moins 2 caractères'),
                'family_name.required' => __('Le nom de famille est obligatoire'),
                'family_name.regex' => __('Le nom de famille contient des caractères non autorisés.'),
                'family_name.max' => __('Le nom de famille ne peut pas dépasser 64 caractères'),
                'family_name.min' => __('Le nom de famille doit contenir au moins 2 caractères'),
                'birthdate.required' => __('La date de naissance est obligatoire'),
                'birthdate.date' => __('La date de naissance doit être une date valide'),
                'birthdate.before' => __('La date de naissance doit être avant aujourd\'hui'),
                'birthdate.before_or_equal' => __('Le participant doit avoir au moins 5 ans'),
                'condition.max' => __('La condition ne peut pas dépasser 255 caractères'),
                'character_username.required' => __('Le nom du personnage est obligatoire'),
                'character_username.min' => __('Le nom du personnage doit contenir au moins 2 caractères'),
                'character_username.max' => __('Le nom du personnage ne peut pas dépasser 50 caractères'),
                'character_username.unique' => __('Ce nom de personnage est déjà utilisé'),
            ]
        );

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation->errors())->withInput();
        }

        try {
            $character = Character::create([
                'name' => $request->input('character_username'),
                'skill_point'=> 1,
            ]);

            $participant = Participant::create([
                'user_id' => $user->id,
                'character_id' => $character->id,
                'given_name' => $request->input('given_name'),
                'family_name' => $request->input('family_name'),
                'birthdate' => $request->input('birthdate'),
                'condition' => $request->input('condition'),
            ]);

            $character->participant_id = $participant->id;
            $character->save();

            return redirect()->back()->with([
                'success' => true,
                'message' => __('Le participant a été créé avec succès.'),
            ]);

        } catch (QueryException $e) {
            report($e);
            return redirect()->back()->withErrors([
                'general' => __('Une erreur est survenue lors de la création du participant.')
            ])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $account = $request->user();
        if (!$account) {
            return response()->json(['message' => 'Non authentifié.'], 403);
        }

        $participant = Participant::find($id);
        if (!$participant) {
            return response()->json(['message' => __('Le participant à supprimer n’existe pas.')], 404);
        }

        if ($account->isAdmin()) {
            try {
                $participant->delete();
            } catch (QueryException $e) {
                report($e);
                return response()->json(['message' => __('Suppression échouée.')], 500);
            }

            return response()->json([
                'success' => true,
                'message' => __('Participant supprimé avec succès.'),
                'participant_id' => $id,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'confirm_password' => 'required',
        ], [
            'confirm_password.required' => __('Le mot de passe actuel est obligatoire.'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->input('confirm_password'), $account->password)) {
            return response()->json(['errors' => ['confirm_password' => __('Le mot de passe actuel est incorrect.')]], 422);
        }

        if ($participant->user_id !== $account->id) {
            return response()->json(['message' => __('Action non autorisée.')], 403);
        }

        try {
            $participant->delete();
        } catch (QueryException $e) {
            report($e);
            return response()->json(['message' => __('Suppression échouée.')], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('Participant supprimé avec succès.'),
            'participant_id' => $id,
        ]);
    }

    public function updatePassword(Request $request, $id)
    {
        $validation = Validator::make($request->all(),
            [
                'account_password' => 'required',
                'new_password' => 'nullable|string',
                'confirm_password' => 'nullable|string|same:new_password|required_with:new_password',
            ],
            [
                'account_password.required' => __('Le mot de passe du compte est obligatoire.'),
                'new_password.required' => __('Le nouveau mot de passe est obligatoire.'),
                'new_password.confirmed' => __('La confirmation du mot de passe doit correspondre.'),
                'confirm_password.required' => __('La confirmation du mot de passe est obligatoire.'),
                'confirm_password.required_with' => __('La confirmation du mot de passe est obligatoire.'),
                'confirm_password.same' => __('Les mots de passe doivent correspondre.'),
            ]
        );

        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors()], 422);
        }

        $account = Auth::user();
        $participant = Participant::findOrFail($id);

        if (!Hash::check($request->input('account_password'), $account->password)) {
            return response()->json(['message' => __('Le mot de passe est incorrect')], 403);
        }

        try {
            $participant->password = Hash::make($request->input('new_password'));
            $participant->save();
        } catch (QueryException $e) {
            report($e);
            return response()->json(['message' => __('Échec de la modification de mot de passe')], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('Mot de passe changé avec succès.'),
            'participant_id' => $id,
        ]);
    }
}
