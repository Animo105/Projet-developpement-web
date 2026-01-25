<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $fillable = [
        'note',
        'due_date',
        'status_id',
        'account_id',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->belongsToMany(
            Participant::class,
            'inscriptions', // table pivot
            'bill_id',               // clé étrangère du modèle actuel (Bill)
            'participant_id'         // clé étrangère du modèle cible (Participant)
        )->distinct();
    }

    public function event()
    {
        return $this->inscriptions()
            ->with('tarif.event')
            ->first()
            ?->tarif
            ?->event;
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscriptions::class, 'bill_id', 'id');
    }

    public function tarifs(): BelongsToMany
    {
        return $this->belongsToMany(Tarif::class, 'inscriptions', 'bill_id', 'tarif_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'bill_id', 'id');
    }

    public function inscriptionsForParticipant(int $pId): HasMany
    {
        return $this->inscriptions()->where('participant_id', $pId);
    }
    public function inscriptionParticipantTarif(int $pId, int $tId) {
        return $this->inscriptions()->where('participant_id', $pId)
            ->where('tarif_id', $tId)->first();
    }

    public function getEventAttribute()
    {
        return $this->inscriptions()
            ->with('tarif.event')
            ->first()
            ?->tarif
            ?->event;
    }

    public function getAmountAttribute()
    {
        return $this->inscriptions()->sum('pay_price');
    }

    public function getEvent(): Event|null
    {
        return $this->inscriptions()->first()?->tarif->event;
    }

    public function amount()
    {
        $amount = 0.00;
        foreach ($this->inscriptions() as $inscription) {
            $amount += $inscription->pay_price;
        }
        return $amount;
    }

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }
}
