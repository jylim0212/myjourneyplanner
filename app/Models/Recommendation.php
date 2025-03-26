<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $fillable = [
        'journey_id',
        'current_location',
        'recommendation'
    ];

    public function journey()
    {
        return $this->belongsTo(Journey::class);
    }
} 