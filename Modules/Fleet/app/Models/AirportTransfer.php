<?php

namespace Modules\Fleet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AirportTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'airport_name',
        'location',
        'vehicle_id',
        'vehicle_type_id',
        'price',
        'description',
        'is_active',
        'transfer_type',
        'category',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function scopeIsPickup($query)
    {
        return $query->whereIn('transfer_type', ['pickup', 'both']);
    }

    public function scopeIsDropoff($query)
    {
        return $query->whereIn('transfer_type', ['dropoff', 'both']);
    }
}
