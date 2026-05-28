<?php

namespace Modules\Driver\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Driver\Database\Factories\DriverNotificationFactory;

class DriverNotification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): DriverNotificationFactory
    // {
    //     // return DriverNotificationFactory::new();
    // }
}
