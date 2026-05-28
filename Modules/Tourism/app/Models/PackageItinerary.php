<?php

namespace Modules\Tourism\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tourism\Database\Factories\PackageItineraryFactory;

class PackageItinerary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'package_id',
        'day_number',
        'title',
        'description',
        'image',
    ];

    public function package()
    {
        return $this->belongsTo(TourismPackage::class, 'package_id');
    }
}
