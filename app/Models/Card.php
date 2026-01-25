<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Card extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'stock',
        'title_f',
        'stock_f',
        'description',
        'level',
        'version',
        'type_id',
        'node_id',
    ];
    public function cardElements() : HasMany {
        return $this->hasMany(Card_Element::class, 'card_id');
    }

    public function cardManas()
    {
        return $this->cardElements()
            ->where('type_id', 3)
            ->whereHas('element.tags', fn($q) => $q->where('tag_id', 3))
            ->with('element')
            ->get()
            ->pluck('element');
    }

    public function characterCards() : HasMany
    {
        return $this->hasMany(CharacterCard::class, 'card_id');
    }

    //a garder? (c'étais dans le main avant la pull request) -Émeric
    public function elements(): BelongsToMany
    {
        return $this->belongsToMany(Element::class, 'card_elements', 'card_id', 'element_id')
            ->withPivot('type_id', 'amount');
    }


    public function node() : hasOne {
        return $this->hasOne(Node::class);
    }

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_cards', 'card_id', 'character_id');
    }


    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'character_cards')
            ->withPivot('is_owned')
            ->using(CharacterCard::class);
    }
}
