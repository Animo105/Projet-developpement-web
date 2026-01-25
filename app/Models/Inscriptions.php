<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscriptions extends Model
{
    public $timestamps = false;
    protected $table = 'inscriptions';
    protected $fillable = [
        'pay_price',
        'date',
        'bill_id',
        'participant_id',
        'tarif_id',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill_id', 'id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_id', 'id');
    }

    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class);
    }

    public function tarifType() {
        return $this->tarif->type;
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
