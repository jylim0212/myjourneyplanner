<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'journey_name', 
        'start_date', 
        'end_date', 
        'preferred_events'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(JourneyLocation::class, 'journey_id');
    }

    protected static function booted()
    {
        static::deleting(function ($journey) {
            $journey->locations()->delete(); // Delete locations before deleting the journey
        });
    }
}
