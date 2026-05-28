<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'chauffeur_id',
        'user_id',
        'rating',
        'comment',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function chauffeur()
    {
        return $this->belongsTo(\Modules\Fleet\Models\Chauffeur::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
