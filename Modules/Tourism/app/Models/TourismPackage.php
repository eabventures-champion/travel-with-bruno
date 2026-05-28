<?php

namespace Modules\Tourism\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tourism\Database\Factories\TourismPackageFactory;

class TourismPackage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $appends = ['organized_status', 'registered_guests', 'available_spaces', 'is_full', 'is_booking_cutoff_reached'];

    protected $fillable = [
        'category_id',
        'package_type',
        'title',
        'slug',
        'short_description',
        'description',
        'price',
        'duration',
        'location',
        'departure_date',
        'return_date',
        'max_guests',
        'image',
        'gallery',
        'includes',
        'excludes',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'gallery' => 'array',
        'includes' => 'array',
        'excludes' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'departure_date' => 'date',
        'return_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(TourismCategory::class, 'category_id');
    }

    public function itineraries()
    {
        return $this->hasMany(PackageItinerary::class, 'package_id');
    }

    public function uploads()
    {
        return $this->hasMany(TourismPackageImage::class, 'package_id');
    }

    /**
     * Get the dynamic status for organized/scheduled tours.
     */
    public function getOrganizedStatusAttribute()
    {
        if ($this->package_type !== 'scheduled' || !$this->departure_date) {
            return null;
        }

        $now = now()->toDateString();
        $departure = $this->departure_date->toDateString();
        $return = $this->return_date ? $this->return_date->toDateString() : $departure;

        if ($departure > $now) {
            return 'upcoming';
        } elseif ($departure <= $now && $return >= $now) {
            $isLive = $this->items()->whereHas('booking', function($q) {
                $q->where('payment_status', 'paid')
                  ->whereIn('status', ['confirmed', 'completed'])
                  ->where(function($subq) {
                      $subq->where('trip_status', 'in_progress')
                           ->orWhere('return_trip_status', 'in_progress')
                           ->orWhere(function($sq) {
                               $sq->where('trip_status', 'completed')
                                  ->where('return_trip_status', 'idle');
                           });
                  });
            })->exists();

            return $isLive ? 'ongoing' : 'upcoming';
        } else {
            return 'completed';
        }
    }

    public function getRegisteredGuestsAttribute()
    {
        return $this->items()
            ->whereHas('booking', function($q) {
                $q->whereIn('status', ['confirmed', 'completed'])
                  ->where('payment_status', 'paid');
            })
            ->sum('quantity');
    }

    public function getAvailableSpacesAttribute()
    {
        if ($this->package_type !== 'scheduled') {
            return 999; // Effectively infinite for fixed tours
        }
        return max(0, $this->max_guests - $this->registered_guests);
    }

    public function getIsFullAttribute()
    {
        if ($this->package_type !== 'scheduled') {
            return false;
        }
        return $this->registered_guests >= $this->max_guests;
    }

    public function getIsBookingCutoffReachedAttribute()
    {
        if ($this->package_type !== 'scheduled' || !$this->departure_date) {
            return false;
        }

        // Cutoff is 1 day before departure
        $cutoffDate = $this->departure_date->copy()->subDay();
        return now()->greaterThanOrEqualTo($cutoffDate->startOfDay());
    }


    public function scopeUpcoming($query)
    {
        return $query->where('package_type', 'scheduled')
            ->where('departure_date', '>', now()->toDateString());
    }

    public function scopeOngoing($query)
    {
        return $query->where('package_type', 'scheduled')
            ->where('departure_date', '<=', now()->toDateString())
            ->where(function($q) {
                $q->where('return_date', '>=', now()->toDateString())
                  ->orWhereNull('return_date');
            })
            ->whereHas('items.booking', function($q) {
                $q->where('payment_status', 'paid')
                  ->whereIn('status', ['confirmed', 'completed'])
                  ->where(function($subq) {
                      $subq->where('trip_status', 'in_progress')
                           ->orWhere('return_trip_status', 'in_progress')
                           ->orWhere(function($sq) {
                               $sq->where('trip_status', 'completed')
                                  ->where('return_trip_status', 'idle');
                           });
                  });
            });
    }

    public function scopeCompletedOrganized($query)
    {
        return $query->where('package_type', 'scheduled')
            ->where(function($q) {
                $q->where('return_date', '<', now()->toDateString())
                  ->orWhere(function($sq) {
                      $sq->whereNull('return_date')->where('departure_date', '<', now()->toDateString());
                  });
            });
    }

    public function getFormattedDateRangeAttribute()
    {
        if (!$this->departure_date) return 'N/A';
        if (!$this->return_date || $this->departure_date->equalTo($this->return_date)) {
            return $this->departure_date->format('M d, Y');
        }

        if ($this->departure_date->format('M Y') === $this->return_date->format('M Y')) {
            return $this->departure_date->format('M d') . ' - ' . $this->return_date->format('d, Y');
        }

        return $this->departure_date->format('M d') . ' - ' . $this->return_date->format('M d, Y');
    }

    public function items()
    {
        return $this->morphMany(\Modules\Booking\Models\BookingItem::class, 'bookable');
    }

    protected static function booted()
    {
        static::created(function ($package) {
            if ($package->package_type === 'scheduled') {
                $users = \App\Models\User::whereHas('roles', function($q) {
                    $q->whereIn('name', ['Customer', 'Corporate Account']);
                })->get();
                foreach ($users as $user) {
                    \Mail::to($user->email)->queue(new \Modules\Tourism\Mail\NewTourNotification($package));
                }
            }
        });
    }
}
