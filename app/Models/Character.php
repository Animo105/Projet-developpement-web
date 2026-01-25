<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Element; // Assuming Element model is in App\Models

class Character extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lore',
        'skill_point',
        'knowledge_point',
        'participant_id',
    ];

    protected $hidden = [
        'remember_token',
    ];

    public function participant() : belongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function cards() : BelongsToMany {
        return $this->belongsToMany(Card::class, 'character_cards', 'character_id', 'card_id');
    }


    /**
     * Get all 'Statistique' elements and calculate their total count from card perks.
     *
     * @return \Illuminate\Support\Collection
     */
    public function statistics()
    {
        // Tag ID for 'Statistique' is 6
        $statisticTagId = 6;
        // Card Element Type ID for 'Perk' is 2
        $perkTypeId = 2;
        // Use $this->id to get the current character's ID
        $characterId = $this->id;

        return Element::query()
            ->with('icon')
            ->whereHas('tags', function ($query) use ($statisticTagId) {
                $query->where('tags.id', $statisticTagId);
            })
            ->withSum([
                'cardElements' => function ($query) use ($characterId, $perkTypeId) {
                    $query->where('type_id', $perkTypeId)
                        ->whereHas('card.characters', function ($q) use ($characterId) {
                            $q->where('characters.id', $characterId);
                        });
                }
            ], 'amount')
            ->get()
            ->map(function ($element) {
                $element->count = $element->card_elements_sum_amount ?? 0;
                unset($element->card_elements_sum_amount);
                return $element;
            })
            ->sortByDesc('count')
            ->values();
    }


    /**
     * Get all 'Inventaire' elements and calculate their total count from card perks.
     *
     * @return \Illuminate\Support\Collection
     */
    public function inventory()
    {
        // Tag ID for 'Inventaire' is 7
        $inventoryTagId = 7;
        // Card Element Type ID for 'Perk' is 2
        $perkTypeId = 2;
        // Use $this->id to get the current character's ID
        $characterId = $this->id;

        return Element::query()
            ->with('icon')
            ->whereHas('tags', function ($query) use ($inventoryTagId) {
                $query->where('tags.id', $inventoryTagId);
            })
            ->withSum([
                'cardElements' => function ($query) use ($characterId, $perkTypeId) {
                    $query->where('type_id', $perkTypeId)
                        ->whereHas('card.characters', function ($q) use ($characterId) {
                            $q->where('characters.id', $characterId);
                        });
                }
            ], 'amount')
            ->get()
            ->map(function ($element) {
                $element->count = $element->card_elements_sum_amount ?? 0;
                unset($element->card_elements_sum_amount);
                return $element;
            })
            ->sortByDesc('count')
            ->values();
    }

}
