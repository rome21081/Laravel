<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'accident_id',
        'plate_number',
        'vehicle_type_id',
        'brand',
        'model',
        'year',
        'color',
        'notes'
    ];

    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }
}
