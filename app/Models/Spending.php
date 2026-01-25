<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spending extends Model
{
    public $timestamps = false;
    protected $table = 'spendings';
    protected $fillable = [
        'name',
        'amount',
        'event_id'
    ];

    public function event():BelongsTo {
        return $this->belongsTo('App\Models\Event');
    }
}
