<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{

    protected $fillable = [
        'name',
        'description'
    ];

    public function Tag_Element() : hasMany {
        return $this->hasMany(Tag_Element::class);
    }
}
