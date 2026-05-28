<?php

namespace Modules\Tourism\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Tourism\Database\Factories\TourismCategoryFactory;

class TourismCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
    ];

    public function packages()
    {
        return $this->hasMany(TourismPackage::class, 'category_id');
    }
}
