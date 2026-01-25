<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Node extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'x',
        'y',
        'parent_id',
        'locked_by_id',
        'element_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function element()
    {
        return $this->belongsTo(Element::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'locked_by_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'node_id');
    }

}
