<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class CharacterController extends Controller
{
    public function showProfile()
    {
        $character = Auth::guard('character')->user();
        $participant = $character->participant;

        if (!$character) {
            abort(403, 'No character is currently authenticated.');
        }

        return Inertia::render('Characters/Show', [
            'character' => $character,
            'statistics' => $character->statistics(),
            'inventory' => $character->inventory(),
            'participant' => $participant,
        ]);
    }

    public function updatePoints(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'skill_point' => 'sometimes|filled|integer|between:0,255',
            'knowledge_point' => 'sometimes|filled|integer|between:0,255',
        ], [
            'skill_point.filled' => 'Le nombre de points de compétence ne peut pas être vide',
            'skill_point.integer' => 'Le nombre de points de compétence ne peut qu\'être un nombre',
            'skill_point.between' => 'Le nombre de points de compétence doit être un nombre de 0 à 255.',
            'knowledge_point.filled' => 'Le nombre de points d\'apprentissage ne peut pas être vide',
            'knowledge_point.integer' => 'Le nombre de points d\'apprentissage ne peut qu\'être un nombre',
            'knowledge_point.between' => 'Le nombre de points d\'apprentissage doit être un nombre de 0 à 255.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $character = Character::findOrFail($id);

        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé.'
            ], 403);
        }

        $character->fill($validator->validated());

        if ($character->isDirty()) {
            $character->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Points du personnage mis à jour avec succès.',
            'character' => $character
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'lore' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $character = Character::findOrFail($id);
        $character->fill($validator->validated());

        if ($character->isDirty()) {
            $character->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Histoire mise à jour avec succès.',
            'character' => $character
        ]);
    }
}
