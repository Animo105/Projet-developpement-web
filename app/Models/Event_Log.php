<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event_Log extends Model
{
    protected $table = 'event_log';
    protected $fillable = [
        'date',
        'is_xp',
        'event_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_xp' => 'boolean',
        ];
    }
}
