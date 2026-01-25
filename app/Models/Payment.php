<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'date',
        'amount',
        'note',
        'bill_id',
        'method_id',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
