<?php

namespace Modules\Fleet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Fleet\Database\Factories\ChauffeurFactory;

class Chauffeur extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'license_number',
        'license_expiry',
        'years_of_experience',
        'bio',
        'status',
        'is_online',
        'license_front_path',
        'id_card_path',
        'license_verified_at',
        'id_verified_at',
    ];

    protected $casts = [
        'license_verified_at' => 'datetime',
        'id_verified_at' => 'datetime',
        'license_expiry' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function bookings()
    {
        return $this->hasMany(\Modules\Booking\Models\Booking::class);
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class);
    }

    public static function syncStatus($chauffeurId)
    {
        if (!$chauffeurId) return;
        $chauffeur = self::find($chauffeurId);
        if (!$chauffeur) return;

        $bookings = \Modules\Booking\Models\Booking::where('chauffeur_id', $chauffeurId)
            ->whereNotIn('status', ['completed', 'cancelled', 'declined'])
            ->get();

        // 1. Check for live trips OR tourism bookings in progress (including waiting for return)
        $isEngaged = $bookings->contains(function($b) {
            if ($b->trip_status === 'in_progress' || $b->return_trip_status === 'in_progress') return true;
            if ($b->isTourismBooking()) {
                if ($b->trip_status !== 'idle' && $b->return_trip_status !== 'completed') return true;
            }
            return false;
        });

        if ($isEngaged) {
            if ($chauffeur->status !== 'engaged') {
                $chauffeur->update(['status' => 'engaged']);
            }
            return;
        }

        // 2. Check for accepted schedules for upcoming trips
        $hasAcceptedSchedule = $bookings->contains(function($b) {
            if ($b->trip_status === 'idle' && $b->driver_schedule_status === 'accepted') return true;
            return false;
        });

        if ($hasAcceptedSchedule) {
            if ($chauffeur->status !== 'schedule_accepted') {
                $chauffeur->update(['status' => 'schedule_accepted']);
            }
            return;
        }

        // 3. Otherwise available (excluding suspended/off_duty/etc if we want to preserve those, but let's check: only override available/schedule_accepted/engaged)
        if (in_array($chauffeur->status, ['available', 'schedule_accepted', 'engaged']) || !$chauffeur->status) {
            if ($chauffeur->status !== 'available') {
                $chauffeur->update(['status' => 'available']);
            }
        }
    }
}
