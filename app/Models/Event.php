<?php

namespace App\Models;

use App\Enums\TarifType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use phpDocumentor\Reflection\Location;

class Event extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'pamphlet_path',
        'end_date_registration',
        'event_id',
    ];

    static public function futureEvents() {
        return Event::all()->where('start_date', '>', date('Y-m-d'));
    }

    public function tarifs():HasMany
    {
        return $this->hasMany(Tarif::class);
    }

    public function hasTarif(TarifType | string $type):bool {
        return $this->tarifs()->where('type', $type)->exists();
    }

    public function getTarif(TarifType | string $type) {
        return $this->tarifs()->where('type', $type)->get()->first();
    }

    public function spendings():HasMany {
        return $this->hasMany(Spending::class);
    }

    public function bills(): Collection
    {
        return $this->tarifs()
            ->with('inscriptions.bill')
            ->get()
            ->flatMap(fn($tarif) => $tarif->inscriptions->pluck('bill'))
            ->unique()
            ->values();
    }

    public function entries() {

        return $this->tarifs
            ->flatMap(fn($tarif) =>
            $tarif->inscriptions
                ->filter(fn($i) => $i->participant) // évite les nulls
                ->map(fn($i) => [
                    'participant' => $i->participant,
                    'type' => $tarif->type
                ])
            )
            ->groupBy(fn($x) => $x['participant']->id) // regroupe par id unique
            ->map(fn($group) => [
                'participant' => $group->first()['participant'],
                'types' => $group->pluck('type')->unique()->values()
            ])
            ->sortBy(function ($x) {
                return $x['participant']->given_name . ' ' . $x['participant']->family_name;
            })
            ->values();
    }

    public function canApplyEventDiscountForParticipant( int $Pid):bool {
        if ($this->event_id == null) return false;
        try {
            $otherEvent = Event::findOrFail($this->event_id);
        } catch (ModelNotFoundException $e) {
            return false;
        }
        foreach ($otherEvent->entries() as $entry) {
            if ($entry['participant']->id == $Pid) {
                if ($entry['types']->contains(TarifType::RabaisEvent)) {
                    return false;
                }
                else return true;
            }
        }
        return false;
    }

    public function amount() {
        $amount = 0.00;
        foreach ($this->bills() as $bill) {
            $amount += $bill->amount();
        }
        return $amount;
    }

    public function amountSpent() {
        $amount = 0.00;
        foreach ($this->spendings as $spending) {
            $amount += $spending->amount;
        }
        return $amount;
    }

    public function profit() {
        return $this->amount() - $this->amountSpent();
    }

    public function dateFormat(): string
    {
        Carbon::setLocale('fr');
        $start = $this->start_date->translatedFormat('F d');
        $end = $this->end_date->translatedFormat('F d');
        $year = $this->end_date->translatedFormat('Y');
        return $start. ' - '. $end . ' (' . $year . ')';
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'end_date_registration' => 'date',
        ];
    }
}
