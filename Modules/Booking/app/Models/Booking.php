<?php

namespace Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Booking extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'payment_status',
                'total_amount',
                'chauffeur_id',
                'scheduled_at',
                'notes',
                'customer_name',
                'customer_phone',
                'cancellation_reason',
                'previous_status'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'country',
        'guest_type',
        'group_name',
        'booking_reference',
        'total_amount',
        'partial_amount',
        'payment_status',
        'status',
        'scheduled_at',
        'notes',
        'cancellation_reason',
        'previous_status',
        'is_self_drive',
        'chauffeur_id',
        'trip_started_at',
        'trip_ended_at',
        'cycle_1_completed_at',
        'cycle_2_completed_at',
        'trip_duration',
        'trip_status',
        'trip_end_code',
        'driver_schedule_status',
        'driver_schedule_feedback',
        'customer_schedule_status',
        // Return trip fields
        'trip_leg',
        'return_trip_status',
        'return_started_at',
        'return_ended_at',
        'return_duration',
        'return_end_code',
        'return_driver_schedule_status',
        'return_customer_schedule_status',
        'return_scheduled_at',
        'interest_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'trip_started_at' => 'datetime',
        'trip_ended_at' => 'datetime',
        'cycle_1_completed_at' => 'datetime',
        'cycle_2_completed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'return_started_at' => 'datetime',
        'return_ended_at' => 'datetime',
        'return_scheduled_at' => 'datetime',
    ];

    /**
     * Check if this booking is for a tourism package (fixed or organized).
     */
    public function isTourismBooking(): bool
    {
        return $this->items->contains(function ($item) {
            return $item->bookable_type === 'Modules\Tourism\Models\TourismPackage';
        });
    }

    /**
     * Check if both outbound and return trips are fully completed.
     */
    public function isFullyCompleted(): bool
    {
        if (!$this->isTourismBooking()) {
            return $this->trip_status === 'completed';
        }
        return $this->trip_status === 'completed' && $this->return_trip_status === 'completed';
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function chauffeur()
    {
        return $this->belongsTo(\Modules\Fleet\Models\Chauffeur::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments()
    {
        return $this->hasMany(\Modules\Booking\Models\BookingPayment::class)->orderBy('created_at', 'asc');
    }



    public function reports()
    {
        return $this->hasMany(TripReport::class);
    }

    public function documents()
    {
        return $this->hasMany(BookingDocument::class);
    }

    public function complaints()
    {
        return $this->hasMany(TripComplaint::class);
    }

    public function rating()
    {
        return $this->hasOne(DriverRating::class);
    }

    public function changeRequests()
    {
        return $this->hasMany(BookingChangeRequest::class);
    }

    public function getTotalDurationAttribute()
    {
        if (!$this->isTourismBooking() || !$this->return_duration) {
            return $this->trip_duration;
        }

        // Simple string parsing and addition for duration strings like "X hrs Y mins"
        $totalMinutes = 0;

        foreach ([$this->trip_duration, $this->return_duration] as $dur) {
            if (!$dur) continue;
            
            if (preg_match('/(\d+)\s*hrs/', $dur, $m)) {
                $totalMinutes += (int)$m[1] * 60;
            }
            if (preg_match('/(\d+)\s*mins/', $dur, $m)) {
                $totalMinutes += (int)$m[1];
            }
        }

        if ($totalMinutes === 0) return 'N/A';

        $h = floor($totalMinutes / 60);
        $m = $totalMinutes % 60;

        return ($h > 0 ? $h . ' hrs ' : '') . $m . ' mins';
    }
    public function getMainTitle()
    {
        $firstItem = $this->items->first();
        if (!$firstItem || !$firstItem->bookable) return 'Other Bookings';
        
        $type = $firstItem->bookable_type;
        if ($type === 'Modules\Tourism\Models\TourismPackage') {
            return $firstItem->bookable->title;
        } elseif ($type === 'Modules\Fleet\Models\AirportTransfer') {
            return $firstItem->bookable->airport_name;
        } else {
            return ($firstItem->bookable->make ?? '') . ' ' . ($firstItem->bookable->model ?? 'Vehicle');
        }
    }

    /**
     * Get bookings in the same tourism group.
     */
    public function getSiblingBookings()
    {
        if (!$this->isTourismBooking()) {
            return collect([$this]);
        }

        $packageId = $this->items->first()?->bookable_id;
        $date = $this->scheduled_at ? $this->scheduled_at->format('Y-m-d') : null;

        if (!$packageId || !$date) {
            return collect([$this]);
        }

        return Booking::where('chauffeur_id', $this->chauffeur_id)
            ->whereHas('items', function($q) use ($packageId) {
                $q->where('bookable_id', $packageId)
                  ->where('bookable_type', 'Modules\Tourism\Models\TourismPackage');
            })
            ->whereDate('scheduled_at', $date)
            ->get();
    }
}

