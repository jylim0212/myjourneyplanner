<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'journey_id',
        'location',
        'forecast_date',
        'description',
        'icon',
        'temperature',
        'humidity',
        'wind_speed',
        'raw_data',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'raw_data' => 'array',
    ];

    public function journey()
    {
        return $this->belongsTo(Journey::class);
    }
}
