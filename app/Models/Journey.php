<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'journey_name',
        'location',
        'start_date',
        'end_date',
        'preferred_events',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
