<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'title',
        'file_path',
        'file_type',
        'shared_with',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
