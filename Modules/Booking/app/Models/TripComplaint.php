<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripComplaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'subject',
        'message',
        'image_path',
        'admin_response',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function messages()
    {
        return $this->hasMany(TripComplaintMessage::class, 'trip_complaint_id')->orderBy('created_at', 'asc');
    }
}
