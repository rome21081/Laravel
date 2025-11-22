<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccidentImage extends Model
{
    protected $fillable = [
        'accident_id',
        'file_path'
    ];
}
