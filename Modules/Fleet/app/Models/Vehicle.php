<?php

namespace Modules\Fleet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Fleet\Database\Factories\VehicleFactory;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'vehicle_type_id',
        'make',
        'model',
        'year',
        'license_plate',
        'color',
        'vin',
        'transmission',
        'fuel_type',
        'seats',
        'luggage_capacity',
        'image',
        'features',
        'status',
        'chauffeur_id',
    ];

    protected $casts = [
        'features' => 'array',
    ];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class);
    }
}
