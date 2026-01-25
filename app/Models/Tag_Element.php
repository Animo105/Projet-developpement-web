<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tag_Element extends Model
{
    protected $table = 'tag_elements';

    protected $fillable = [
        'tag_id',
        'element_id',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'id');
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(Element::class, 'element_id', 'id');
    }
}
