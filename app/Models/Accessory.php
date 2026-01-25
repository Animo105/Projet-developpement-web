<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accessory extends Model
{
    protected $fillable = [
        'name',
        'character_id',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
