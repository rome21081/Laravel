<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accident extends Model
{
    protected $primaryKey = 'accident_id';

    protected $fillable = [
        'case_number',
        'type_id',
        'accident_date',
        'latitude',
        'longitude',
        'address',
        'description',
        'severity',
        'reported_by'
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'accident_id');
    }

    public function images()
    {
        return $this->hasMany(AccidentImage::class, 'accident_id');
    }

    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'type_id');
    }
}
