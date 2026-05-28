<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripComplaintMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_complaint_id',
        'user_id',
        'message',
        'image_path',
    ];

    public function complaint()
    {
        return $this->belongsTo(TripComplaint::class, 'trip_complaint_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
