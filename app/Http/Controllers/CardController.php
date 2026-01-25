<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Card_Element;
use App\Models\Card_Element_Type;
use App\Models\Card_Type;
use App\Models\Character;
use App\Models\CharacterCard;
use App\Models\Element;
use App\Models\Event;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CardController extends Controller
{
    # Affiche la liste des cartes.
    public function index(Request $request)
    {
        // Prepare $filterList pour envoyer les infos pour les listes déroulantes
        $filterList = [];
        $filterList['events'] = Event::all();
        $filterList['level'] = [0, 1, 2, 3];
        $filterList['type'] = DB::table('card_types')->get();
        $filterList['mana'] = Element::whereHas('tags', function ($q) {
            $q->where('tag_id', 3);
        })->whereNotIn('id', [6, 7])->select('id', 'name')->get()->toArray();
        $filterList['metier'] = Element::whereHas('tags', function ($q) {
            $q->where('tag_id', 4);
        })->select('id', 'name')->get()->toArray();
        $filterList['perks'] = Element::whereHas('tags', function ($q) {
            $q->where('tag_id', 1);
        })->select('id', 'name')->get()->toArray();
        $filterList['node_access'] = [0, 1];
        $filterList['order_by'] = [0, 1, 2];

        // Prépare le filtre
        $offset = $request['offset'];
        $limit = $request['limit'];
        $cardDB = [];
        $search = $request['search'];
        $compositions = $request->input('compositions', []);
        $types = $request->input('types', []);
        $levels = $request->input('levels', []);
        $perks = $request->input('perks', []);
        $characterId = Auth::guard('character')->user()->id ?? 0;
        $query = Card::query();

        $event = $request['event'] ?? 0;
        $stockNeeded = [];

        // Filtre pour le tri par text
        if (!empty($search)) {
            $like = '%' . trim($search) . '%';
            $query->where('title', 'LIKE', $like);
        } else if ($event != 0 || "") {
            // Pour obtenir les participants de l'évènement.
            $characterList = Character::whereHas('participant.inscriptions.tarif', function ($q) use ($event) {
                $q->where('event_id', $event);
            })->with('participant.inscriptions.tarif')->get();

            $characterCards = CharacterCard::whereIn('character_id', $characterList->pluck('id'))->get();

            $stockNeeded = $characterCards->groupBy('card_id')->map(function ($item) {
                return $item->count();
            });

            $query->whereIn('id', $characterCards->pluck('card_id'));
        } else {
            // Filter pour les cartes acquisent [ACQUIS]
            if (isset($characterId) && $characterId != 0) {
                $query->whereHas('characterCards', function ($query) use ($characterId) {
                    $query->where('character_id', $characterId);
                });
            }

            // Filtre par le niveau choisit
            if (!empty($levels)) {
                $query->whereIn('level', $levels);
            }

            // Filtre par le type de carte choisit
            if (!empty($types)) {
                $query->whereIn('type_id', $types);
            }

            // Filtre par le mana et le métier de carte choisit
            if (!empty($compositions)) {
                $query->whereHas('cardElements.element', function ($query) use ($compositions) {
                    $query->whereIn('id', $compositions);
                });
            }

            // Filtre par le perk choisit
            if (!empty($perks)) {
                $query->whereHas('cardElements', function ($q) use ($perks) {
                    $q->where('type_id', 2)->whereHas('element', function ($query) use ($perks) {
                        $query->whereIn('id', $perks);
                    });
                });
            }

            if (isset($filters['order_by']) && $filters['order_by'] == 2) {
                $query->orderBy('title', 'desc');
            } else {
                $query->orderBy('title', 'asc');
            }

            if (isset($filters['offset']) && $filters['offset'] > 0) {
                $query->offset($filters['offset']);
            }

            if (isset($filters['limit']) && $filters['limit'] > 0) {
                $query->limit($filters['limit']);
            }
        }

        // Utilise les filtres pour ressortir x cartes à partir de offset y.
        $cardDB = $query->get();

        // Fonction web
        if ($request->route()->getName() == 'cards.index' || $request->route()->getName() == 'cards.given') {
            return inertia('Cards', ['cardList' => $cardDB, "filterList" => $filterList, "event" => $event, "characterId" => $characterId]);
        } // Fonction api
        elseif ($request->route()->getName() == 'cards.filters') {
            return response()->json(['cardList' => $cardDB, "stockNeeded" => $stockNeeded, "currentEvent" => $event, "characterId" => $characterId], 200);
        } // if event, returns stock stuff
        elseif ($request->route()->getName() == "cards.attach") {
            return inertia('Cards', ['cardList' => $cardDB, "filterList" => $filterList, "event" => $event, "nodeId" => $request->route('nodeId'), "characterId" => $characterId]);
        } elseif ($request->route()->getName() == "cards.give") {
            return inertia('Cards', ['cardList' => $cardDB, "filterList" => $filterList, "event" => $event, "characterId" => $request->route('characterId'), "isGiving" => true]);
        }
    }

    # Affiche le formulaire de création d'une carte.

    public function store(Request $request)
    {
        Log::debug($request);
        // Validation des informations reçues.
        $validation = validator::make($request->all(), [
            "title" => 'required|max:32|unique:cards,title',
            'image' => ['nullable', 'regex:/^data:image\/(jpeg|png|jpg);base64,/'],
            "level" => 'required|numeric|max:4',
            "type_id" => 'required',
            "compositions" => "array",
            "compositions.*" => 'nullable|integer',
            "costs" => "array",
            "costs.*.id" => 'required|integer',
            "costs.*.amount" => 'required|integer',
            "perks" => "array",
            "perks.*.id" => 'required|integer',
            "perks.*.amount" => 'required|integer'
        ], [
            'title.required' => __("Veuillez entrer le nom de la carte."),
            'title.max' => __("Le nom de la carte ne doit pas dépasser 32 caractères."),
            'title.unique' => __('Le nom de la carte existe déjà'),
            'image.regex' => __('Le fichier n\'est pas de type .jpg, .jpeg ou png.'),
            'level.required' => __('Veuillez entrer le niveau minimum pour utiliser la carte.'),
            'level.numeric' => __('Le niveau de la carte doit être un chiffre.'),
            'level.max' => __('Le niveau de la carte ne doit pas dépasser 4.'),
            'type_id.required' => __('Veuillez choisir un type.'),
            'compositions.array' => __('Compositions doit être de type array'),
            'compositions.*.integer' => __('Compositions doit être de type integer'),
            'costs.array' => __('Costs doit être de type array'),
            'costs.*.id.required' => __('L\'ID du coût est requis.'),
            'costs.*.id.integer' => __('L\'ID du coût doit être un entier'),
            'costs.*.amount.required' => __('La valeur du coût est requise.'),
            'costs.*.amount.integer' => __('La valeur du coût doit êre un entier'),
            'perks.array' => __('Perks doit être de type array'),
            'perks.*.id.required' => __('Le ID du bonus est requis.'),
            'perks.*.id.integer' => __('Le ID du bonus doit être un entier.'),
            'perks.*.amount.required' => __('La valeur du bonus est requis.'),
            'perks.*.amount.integer' => __('La valeur du bonus doit être un entier.')
        ]);
        // validate image sent
        $validation->after(function ($validation) use (&$request) {
            if ($request->filled('image')) {

                $data = $request->input('image');

                // strip off the "data:image/png;base64," part
                [$meta, $content] = explode(',', $data);
                $decoded = base64_decode($content);
                if (!$decoded) {
                    $validation->errors()->add('image', __('Le fichier n\'est pas valide.'));
                }

                // check image size
                $maxSizeMB = 2; // change this to change image size in mb.
                $maxSizeBytes = $maxSizeMB * 1024 * 1024;
                if (strlen($decoded) > $maxSizeBytes) {
                    $validation->errors()->add('image', __('L\'image ne doit pas être plus grande que ' . $maxSizeMB . 'mb.'));
                }

                // verify if image is actually an image of type selected
                $finfo = finfo_open();
                $mimeType = finfo_buffer($finfo, $decoded, FILEINFO_MIME_TYPE);
                finfo_close($finfo);
                if (!in_array($mimeType, array("image/png", "image/jpeg", "image/jpg"))) {
                    $validation->errors()->add('image', __('Le fichier n\'est pas de type .jpg, .jpeg ou png.'));
                }

                $extension = explode('/', explode(';', $meta)[0])[1]; // e.g. "png"
                $filename = uniqid() . "." . $extension;

                Storage::disk('public')->put("uploads/cards/$filename", $decoded);
                $imagePath = "uploads/cards/$filename";

                $request->merge(['image_path' => $imagePath]);

                // Valide nom de la carte
                $cardScan = Card::where('title', $request['title'])->first();
                if ($cardScan) {
                    $validation->errors()->add('title', __('Une existe existe déjà avec ce nom.'));
                }
            }
        });
        // Si la validation échoue, Web: retour au formulaire avec les messages d'erreurs / Api: réponse json d'erreur.
        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors()], 422);
        }

        $cardList = Card::orderBy('title', 'asc')->get();
        // Resort les données validées et set la version à 1.
        $validated = $validation->validated();
        $validated['version'] = 1;
        $validated['stock'] = 0;

        // Attentat de l'ajout de la carte dans le système.
        try {
            // Création de la carte avec les données validées dans la BD.
            $card = Card::create(["title" => $validated["title"], "image_path" => $request["image_path"] ?? "", "stock" => $validated["stock"], "level" => $validated["level"], "version" => 1, "type_id" => $validated["type_id"], "description" => $validated["description"] ?? ""]);
            $cardList = $cardList->prepend($card);

            // Resort le id de la nouvelle carte ajouté et les CardElements approprié.
            $card_id = $card->id;

            // Prépare la liste des CardElements à ajouter dans la base de donnée.
            $element_array = [];

            // Préparation des essences de la carte.
            $essences = ($validated['compositions']);
            if (!empty($essences)) {
                foreach ($essences as $essence) {
                    if ($essence != null) {
                        $element_array[] = new Card_Element(['card_id' => $card_id, 'element_id' => $essence, 'type_id' => 3, 'amount' => 1]);
                    }
                }
            }

            // Préparation des coûts de la carte.
            $costs = $validated['costs'];
            if (!empty($costs)) {
                foreach ($costs as $cost) {
                    $element_array[] = new Card_Element(['card_id' => $card_id, 'element_id' => $cost['id'], 'type_id' => 1, 'amount' => $cost['amount']]);
                }
            }

            // Préparation des bonuses de la carte.
            $perks = $validated['perks'];
            if (!empty($perks)) {
                foreach ($perks as $perk) {
                    $element_array[] = new Card_Element(['card_id' => $card_id, 'element_id' => $perk['id'], 'type_id' => 2, 'amount' => $perk['amount']]);
                }
            }

            // Ajout des CardElements dans la base de donnée.
            if (!empty($element_array)) {
                foreach ($element_array as $cardElement) {
                    $cardElement->save();
                }
            }
        } // Si un ajout est problèmatique, Web: retour au formulaire avec les données et les messages d'erreurs, Api: Réponse json d'erreur.
        catch (Exception $e) {
            report($e);
            if ($request->route()->getName() == 'cards.store') {
                return back()->withErrors($e->getMessage())->withInput();
            } else if ($request->route()->getName() == 'api.cards.store') {
                return response()->json($e->getMessage(), 400);
            }
        }

        // Après avoir complèté les requètes requises, Web: retour à la liste / Api: Réponse json de succès.
        return response()->json(['cardList' => $cardList], 200);
    }

    # Ajout d'une carte dans la BD. Ceci ajoute la catre et les éléments de la cartes (cout, perk, etc) qui lui sont en paires.

    public function create()
    {
        $card_types = Card_Type::all();
        $card_element_types = Card_Element_Type::all();
        $perks = DB::table('tag_elements')->where('tag_id', 1)->get();
        $metiers = DB::table('tag_elements')->where('tag_id', 4)->get();

        $perk_array = [];
        foreach ($perks as $perk) {
            $perk = Element::where('id', $perk->element_id)->first();
            $perk_array[] = $perk;
        }

        $metier_array = [];
        foreach ($metiers as $metier) {
            $metier = Element::where('id', $metier->element_id)->first();
            $metier_array[] = $metier;
        }

        return view('cards.create', ['card_types' => $card_types, 'perks' => $perk_array, 'element_types' => $card_element_types, 'metiers' => $metier_array]);
    }

    public function updateForm(Request $request, $id)
    {

        // Récupère la carte sélectionné et ces éléments pour la modification
        $card = Card::find($id);
        $cardElements = $card->cardElements;

        // Préparations des infos pour l'envoi JSON
        $id = $card->id;
        $title = $card->title;
        $compositions = []; // array: id
        $type_id = $card->type_id;
        $level_id = $card->level;
        $costs = []; // array: id, amount
        $perks = []; // array: id, amount

        foreach ($cardElements as $cardElement) {
            if ($cardElement->type_id == 3) {
                $element = $cardElement->element;
                $compositions[] = $element->id;
            } else if ($cardElement->type_id == 1) {
                $element = $cardElement->element;
                $costs[] = [$element->id => $cardElement->amount];
            } else if ($cardElement->type_id == 2) {
                $element = $cardElement->element;
                $perks[] = [$element->id => $cardElement->amount];
            }
        }

        return response()->json(['id' => $id, 'title' => $title, 'compositions' => $compositions, 'type_id' => $type_id, 'level_id' => $level_id, 'costs' => $costs, 'perks' => $perks]);
    }

    public function update(Request $request)
    {
        Log::debug($request);

        // Fonction API
        if ($request->route()->getName() == "cards.update") {
            // Vérification de l'existance de la carte.
            $card = Card::where('id', $request['id'])->first();
            if (!$card) {
                return response()->json([__('Erreur'), __('Le numéro d\'identification de la carte est invalide')], 404);
            }

            // Validation des changements proposés.
            $validation = validator::make($request->all(), [
                "title" => ['required', 'max:32', Rule::unique('cards', 'title')->ignore($card->id)],
                'image' => ['nullable', 'regex:/^data:image\/(jpeg|png|jpg);base64,/'],
                "level" => 'required|numeric|max:4',
                "type_id" => 'required',
                "compositions" => "array",
                "compositions.*" => 'integer',
                "costs" => "array",
                "costs.*.id" => 'required|integer',
                "costs.*.amount" => 'required|integer',
                "perks" => "array",
                "perks.*.id" => 'required|integer',
                "perks.*.amount" => 'required|integer'
            ], [
                'title.required' => __("Veuillez entrer le nom de la carte."),
                'title.max' => __("Le nom de la carte ne doit pas dépasser 32 caractères."),
                'title.unique' => __('Le nom de la carte existe déjà.'),
                'image.regex' => __('Le fichier n\'est pas de type .jpg, .jpeg ou png.'),
                'level.required' => __('Veuillez entrer le niveau minimum pour utiliser la carte.'),
                'level.numeric' => __('Le niveau de la carte doit être un chiffre.'),
                'level.max' => __('Le niveau de la carte ne doit pas dépasser 4.'),
                'type_id.required' => __('Veuillez un type valide.'),
                'compositions.array' => __('Compositions doit être de type array'),
                'compositions.*.integer' => __('Compositions doit être de type integer'),
                'costs.array' => __('Costs doit être de type array'),
                'costs.*.id.required' => __('L\'ID du coût est requis.'),
                'costs.*.id.integer' => __('L\'ID du coût doit être un entier'),
                'costs.*.amount.required' => __('La valeur du coût est requise.'),
                'costs.*.amount.integer' => __('La valeur du coût doit êre un entier'),
                'perks.array' => __('Perks doit être de type array'),
                'perks.*.id.required' => __('Le ID du bonus est requis.'),
                'perks.*.id.integer' => __('Le ID du bonus doit être un entier.'),
                'perks.*.amount.required' => __('La valeur du bonus est requis.'),
                'perks.*.amount.integer' => __('La valeur du bonus doit être un entier.')
            ]);
            // validate image sent
            $validation->after(function ($validation) use (&$request, &$card) {
                if ($request->filled('image')) {

                    $data = $request->input('image');

                    // strip off the "data:image/png;base64," part
                    [$meta, $content] = explode(',', $data);
                    $decoded = base64_decode($content);
                    if (!$decoded) {
                        $validation->errors()->add('image', __('Le fichier n\'est pas valide.'));
                    }

                    // check image size
                    $maxSizeMB = 2; // change this to change image size in mb.
                    $maxSizeBytes = $maxSizeMB * 1024 * 1024;
                    if (strlen($decoded) > $maxSizeBytes) {
                        $validation->errors()->add('image', __('L\'image ne doit pas être plus grande que ' . $maxSizeMB . 'mb.'));
                    }

                    // verify if image is actually an image of type selected
                    $finfo = finfo_open();
                    $mimeType = finfo_buffer($finfo, $decoded, FILEINFO_MIME_TYPE);
                    finfo_close($finfo);
                    if (!in_array($mimeType, array("image/png", "image/jpeg", "image/jpg"))) {
                        $validation->errors()->add('image', __('Le fichier n\'est pas de type .jpg, .jpeg ou png.'));
                    }

                    $extension = explode('/', explode(';', $meta)[0])[1]; // e.g. "png"
                    $filename = uniqid() . "." . $extension;

                    Storage::disk('public')->put("uploads/cards/$filename", $decoded);
                    $imagePath = "uploads/cards/$filename";

                    $request->merge(['image_path' => $imagePath]);

                    // Valide nom de la carte existe déjà autre que celle qui est modifié
                    $cardScan = Card::where('title', $request['title'])->where('id', '!=', $card->id)->exists();
                    if ($cardScan) {
                        $validation->errors()->add('title', __('Une existe existe déjà avec ce nom.'));
                    }
                }
            });
            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 422);
            }

            // Ajustement de la version de la carte, +1
            $validated = $validation->validated();
            $validated['version'] = $card->version + 1;

            // Liste des CardElements de la carte
            $existing_elements = $card->cardElements()->get();

            // -- Liste des new CardElements de la carte
            $element_array = [];

            // Préparation des essences de la carte.
            $essences = ($validated['compositions']);
            if (!empty($essences)) {
                foreach ($essences as $essence) {
                    $element_array[] = new Card_Element(['card_id' => $card->id, 'element_id' => $essence, 'type_id' => 3, 'amount' => 1]);
                }
            }

            // Préparation des coûts de la carte.
            $costs = $validated['costs'];
            if (!empty($costs)) {
                foreach ($costs as $cost) {
                    $element_array[] = new Card_Element(['card_id' => $card->id, 'element_id' => $cost['id'], 'type_id' => 1, 'amount' => $cost['amount']]);
                }
            }

            // Préparation des bonuses de la carte.
            $perks = $validated['perks'];
            if (!empty($perks)) {
                foreach ($perks as $perk) {
                    $element_array[] = new Card_Element(['card_id' => $card->id, 'element_id' => $perk['id'], 'type_id' => 2, 'amount' => $perk['amount']]);
                }
            }

            // Attentat de modification ou d'ajout dans la base de donnée.
            try {
                $card->title = $validated['title'];

                // Si une image est détectée, supprime l\'ancienne du disque.
                if (isset($request['image_path'])) {
                    $oldImage = $card->image_path;
                    Storage::disk('public')->delete("$oldImage");
                    $card->image_path = $request['image_path'];
                }
                $card->level = $validated['level'];
                $card->type_id = $validated['type_id'];
                $card->save();

                // Liste de cardElements formattés
                $updatedElements = [];

                // Traitement des CardElements
                foreach ($existing_elements as $existing_element) {
                    $exist = false;
                    foreach ($element_array as $key => $element) {

                        // CardElement existant?
                        $sameCard = $existing_element->card_id == $element->card_id;
                        $sameType = $existing_element->type_id == $element->type_id;
                        $sameElement = $existing_element->element_id == $element->element_id;
                        $sameAmount = $existing_element->amount == $element->amount;
                        if ($sameCard && $sameType && $sameElement) {
                            $exist = true;
                            // update amount, tag existingElement as updated and delete new CardElement
                            if (!$sameAmount) {
                                $existing_element->amount = $element->amount;
                            }
                            unset($element_array[$key]);
                        }
                    }

                    // If updated, add existing element to the updated array
                    if ($exist) {
                        $updatedElements[] = $existing_element;
                    } // else it doesn't exist anymore, this delete
                    else {
                        $existing_element->delete();
                    }
                }

                // add the rest of the new cardElements into updatedElements array.
                foreach ($element_array as $element) {
                    $updatedElements[] = $element;
                }

                // loop through updated CardElements to update old cardelementsand save new cardelements
                foreach ($updatedElements as $element) {
                    $element->save();
                }

                $refreshCard = Card::find($card->id);

                return response()->json(['messages' => [__('Succès'), __('Les détails de la carte on été mis à jour.')], "refreshedCard" => $refreshCard], 200);
            } catch (Exception $e) {

                // Supprime l\'image si la modification a échoué.
                if ($request->image_path) {
                    Storage::disk('public')->delete("uploads/$request->image_path");
                }

                report($e);
                return response()->json(['Erreur', $e->getMessage()], 500);
            }
        }
    }

    public function updateStock(Request $request)
    {

        // Vérification de l'existance de la carte.
        $card = Card::where('id', $request['id'])->first();
        if (!$card) {
            return response()->json([__('Erreur'), __('Le numéro d\'identification de la carte est invalide')], 404);
        }

        $validation = Validator::make($request->all(), [
            'stock' => 'required|numeric|min:0|max:255'
        ], [
            'stock.required' => __('Le champ est obligatoire.'),
            'stock.numeric' => __('Le champ est un chiffre.'),
            'stock.min' => __('Le champ ne doit pas être plus bas que 0.'),
            'stock.max' => __('Le champ ne doit pas être plus grand que 255.')
        ]);
        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors()], 422);
        }

        $validated = $validation->validated();

        try {
            $card->stock = $validated['stock'];
            $card->save();
            return response()->json([__('Succès'), __('Le stock a été modifié')], 200);
        } catch (Exception $e) {
            report($e);
            return response()->json(['Erreur', $e->getMessage()], 500);
        }
    }

    # Supprime une carte et ces éléments attribué de la BD.
    public function destroy(Request $request, $id)
    {
        // find card to delete image
        $card = Card::findOrFail($id);

        // delete the elements, the card and then the image
        try {
            DB::table('card_elements')->where('card_id', '=', $id)->delete();
            DB::table('cards')->where('id', $id)->delete();
            Storage::disk('public')->delete($card->image_path);
        } catch (Exception $e) {
            report($e);
            return response()->json(['Erreur', $e->getMessage()], 500);
        }

        // to update the visual
        $cardList = Card::all();

        return response()->json(['Succès' => 'La carte et ses éléments on été supprimé', 'cardList' => $cardList], 200);
    }

    public function giveCard(int $characterId, int $cardId)
    {
        $character = Character::find($characterId);
        $character->cards()->attach($cardId);
        return redirect()->route('impersonate.start', ['character' => $characterId, 'asAdmin' => true])->with('success', 'Carte donnée avec succès.');
    }
}
