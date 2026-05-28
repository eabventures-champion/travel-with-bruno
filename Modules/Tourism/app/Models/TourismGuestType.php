<?php

namespace Modules\Tourism\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tourism\Database\Factories\TourismGuestTypeFactory;

class TourismGuestType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'status'];

    // protected static function newFactory(): TourismGuestTypeFactory
    // {
    //     // return TourismGuestTypeFactory::new();
    // }
}
