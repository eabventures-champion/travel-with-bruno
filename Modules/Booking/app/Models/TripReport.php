<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'chauffeur_id',
        'type',
        'description',
        'image_path',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function chauffeur()
    {
        return $this->belongsTo(\Modules\Fleet\Models\Chauffeur::class);
    }
}
