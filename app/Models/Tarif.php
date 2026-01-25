<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Tarif extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'type',
        'price',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscriptions::class);
    }
}
