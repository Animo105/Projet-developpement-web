<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Card_Element extends Model
{
    protected $table = 'card_elements';
    public $timestamps = false;

    protected $fillable = [
        'card_id',
        'element_id',
        'type_id',
        'amount',
    ];

    public function element(): BelongsTo {
        return $this->belongsTo(Element::class, 'element_id', 'id');
    }

    public function card(): BelongsTo {
        return $this->belongsTo(Card::class, 'card_id');
    }

    public function tag_elements(): HasMany {
        return $this->hasMany(Tag_Element::class, 'element_id', 'element_id');
    }

}
