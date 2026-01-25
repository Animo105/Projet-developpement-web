<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_name',
        'given_name',
        'birthdate',
        'condition',
        'password',
        'user_id',
        'character_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array {
        return [
            'birthdate' => 'date',
            'password' => 'hashed',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function characters()
    {
        return $this->hasMany(Character::class);
    }

    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->given_name} {$this->family_name}";
    }

    public function is_adult(): bool
    {
        return Carbon::parse($this->birthdate)->age > 18;
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscriptions::class, 'participant_id', 'id');
    }

}
