<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JourneyLocation extends Model
{
    use HasFactory;
    
    protected $fillable = ['journey_id', 'location'];

    public function journey()
    {
        return $this->belongsTo(Journey::class);
    }
}
