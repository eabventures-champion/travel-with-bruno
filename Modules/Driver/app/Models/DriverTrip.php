<?php

namespace Modules\Driver\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Driver\Database\Factories\DriverTripFactory;

class DriverTrip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): DriverTripFactory
    // {
    //     // return DriverTripFactory::new();
    // }
}
