<?php

namespace Modules\Tourism\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tourism\Database\Factories\TourInterestFactory;

class TourInterest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'package_id',
        'name',
        'email',
        'phone',
        'notes',
        'token',
    ];

    public function package()
    {
        return $this->belongsTo(TourismPackage::class, 'package_id');
    }

    public function booking()
    {
        return $this->hasOne(\Modules\Booking\Models\Booking::class, 'interest_token', 'token');
    }

    protected static function booted()
    {
        static::creating(function ($interest) {
            $interest->token = (string) \Illuminate\Support\Str::uuid();
        });
    }

    // protected static function newFactory(): TourInterestFactory
    // {
    //     // return TourInterestFactory::new();
    // }
}
