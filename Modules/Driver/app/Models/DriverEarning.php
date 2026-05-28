<?php

namespace Modules\Driver\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Driver\Database\Factories\DriverEarningFactory;

class DriverEarning extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'chauffeur_id',
        'driver_trip_id',
        'amount',
        'type',
        'status',
        'description',
    ];

    protected $appends = ['icon', 'title'];

    public function getIconAttribute()
    {
        return match($this->type) {
            'trip' => 'fa-route',
            'bonus' => 'fa-gift',
            'tip' => 'fa-hand-holding-usd',
            'penalty' => 'fa-exclamation-circle',
            default => 'fa-money-bill-wave'
        };
    }

    public function getTitleAttribute()
    {
        return match($this->type) {
            'trip' => 'Trip Earning',
            'bonus' => 'Performance Bonus',
            'tip' => 'Customer Tip',
            'penalty' => 'Penalty Deduction',
            default => ucfirst($this->type)
        };
    }

    public function getTransactionTypeAttribute()
    {
        return in_array($this->type, ['penalty']) ? 'debit' : 'credit';
    }

    // protected static function newFactory(): DriverEarningFactory
    // {
    //     // return DriverEarningFactory::new();
    // }
}
