<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferZone extends Model
{
    protected $fillable = [
        'name',
        'additional_price',
        'is_active',
    ];
}
