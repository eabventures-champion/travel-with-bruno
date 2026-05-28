<?php

namespace Modules\Tourism\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tourism\Database\Factories\TourismPackageImageFactory;

class TourismPackageImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'package_id',
        'user_id',
        'image_path',
        'caption',
    ];

    public function package()
    {
        return $this->belongsTo(TourismPackage::class, 'package_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
