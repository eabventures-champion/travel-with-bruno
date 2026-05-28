<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Auth\Database\Factories\UserProfileFactory;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'bio',
        'address',
        'emergency_contact',
        'nationality',
        'id_document_type',
        'id_document_number',
        'date_of_birth',
        'travel_preferences',
    ];

    protected $casts = [
        'travel_preferences' => 'json',
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
