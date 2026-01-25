<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $table = 'albums';
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
