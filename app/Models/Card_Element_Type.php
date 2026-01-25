<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card_Element_Type extends Model
{
    protected $table = 'card_element_types';
    protected $fillable = [
        'name',
        'description',
    ];
}
