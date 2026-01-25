<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card_Type extends Model
{
    protected $table = 'card_types';
    protected $fillable = [
        'name',
    ];
}
