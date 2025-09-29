<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recommendation extends Model
{
    use HasFactory;

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