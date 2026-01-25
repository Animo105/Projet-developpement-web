<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CharacterCard extends Pivot
{
    public $timestamps = false;
    protected $table = 'character_cards';
    protected $fillable = [
        'card_id',
        'character_id',
        'is_owned',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    protected function casts(): array
    {
        return [
            'is_owned' => 'boolean',
        ];
    }
}
