@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>{{ $journey->journey_name }}</h1>

            <div class="card mb-4">
                <div class="card-header">
                    <h2>Journey Details</h2>
                </div>
                <div class="card-body">
                    <p><strong>Start Date:</strong> {{ $journey->start_date }}</p>
                    <p><strong>End Date:</strong> {{ $journey->end_date }}</p>
                    <p><strong>Starting Location:</strong> <span class="badge bg-info">{{ $journey->starting_location }}</span></p>
                    @if($journey->preferred_events)
                        <p><strong>Preferred Events:</strong> {{ $journey->preferred_events }}</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2>Locations and Weather</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($journey->locations as $location)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h3 class="mb-0">{{ $location->location }}</h3>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($weatherData[$location->location]))
                                            <div class="weather-forecast">
                                                @foreach($weatherData[$location->location] as $date => $data)
                                                    <div class="weather-day mb-4">
                                                        <h4 class="text-primary mb-3">{{ $date }}</h4>
                                                        <div class="weather-content">
                                                            <div class="weather-main mb-3">
                                                                <div class="weather-icon">
                                                                    @if(strpos(strtolower($data['description']), 'rain') !== false)
                                                                        <span class="weather-emoji">🌧️</span>
                                                                    @elseif(strpos(strtolower($data['description']), 'cloud') !== false)
                                                                        <span class="weather-emoji">☁️</span>
                                                                    @elseif(strpos(strtolower($data['description']), 'clear') !== false)
                                                                        <span class="weather-emoji">☀️</span>
                                                                    @else
                                                                        <span class="weather-emoji">🌤️</span>
                                                                    @endif
                                                                </div>
                                                                <div class="temperature">
                                                                    <span class="temp-value">{{ $data['temperature'] }}°C</span>
                                                                </div>
                                                            </div>
                                                            <div class="weather-details">
                                                                <div class="detail-item">
                                                                    <i class="fas fa-cloud"></i>
                                                                    <span>{{ $data['description'] }}</span>
                                                                </div>
                                                                <div class="detail-item">
                                                                    <i class="fas fa-tint"></i>
                                                                    <span>{{ $data['humidity'] }}% humidity</span>
                                                                </div>
                                                                <div class="detail-item">
                                                                    <i class="fas fa-wind"></i>
                                                                    <span>{{ $data['wind_speed'] }} m/s wind</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(!$loop->last)
                                                        <hr class="weather-divider">
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i>
                                                @php
                                                    $journeyStart = \Carbon\Carbon::parse($journey->start_date);
                                                    $journeyEnd = \Carbon\Carbon::parse($journey->end_date);
                                                    $now = \Carbon\Carbon::now();
                                                @endphp

                                                @if($journeyEnd->isPast())
                                                    This journey has already ended. Weather forecast is only available for future dates.
                                                @elseif($journeyStart->isPast() && $journeyEnd->isFuture())
                                                    This journey is currently in progress. Weather forecast is only available for upcoming dates.
                                                @elseif($journeyStart->isFuture())
                                                    No weather data available.
                                                @else
                                                    Unable to fetch weather data for this location. Please try again later.
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mb-4">
                        <a href="{{ route('journey.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Journeys
                        </a>
                        <a href="{{ route('journey.edit', $journey) }}" class="btn btn-primary me-2">
                            <i class="fas fa-edit"></i> Edit Journey
                        </a>
                        <form action="{{ route('journey.destroy', $journey) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to delete this journey?')">
                                <i class="fas fa-trash"></i> Delete Journey
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.weather-forecast {
    padding: 10px;
}

.weather-day {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
}

.weather-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.weather-main {
    display: flex;
    align-items: center;
    gap: 20px;
}

.weather-icon {
    text-align: center;
}

.weather-emoji {
    font-size: 2.5em;
}

.temperature {
    text-align: center;
}

.temp-value {
    font-size: 2em;
    font-weight: bold;
    color: #0d6efd;
}

.weather-details {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #6c757d;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

hr {
    margin: 1rem 0;
    border-color: #dee2e6;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch weather data for all locations
    @foreach($journey->locations as $location)
        fetch(`/journeys/{{ $journey->id }}/weather?location={{ urlencode($location->location) }}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            const weatherContainer = document.getElementById('weather-{{ $location->id }}');
            
            // Display weather data
            let weatherHtml = '';
            Object.entries(data).forEach(([date, forecast]) => {
                weatherHtml += `
                    <div class="weather-day mb-3">
                        <h6 class="mb-2">${new Date(date).toLocaleDateString('en-US', { 
                            weekday: 'long', 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        })}</h6>
                        <div class="d-flex align-items-center mb-2">
                            <img src="http://openweathermap.org/img/w/${forecast.icon}.png" 
                                 alt="Weather icon" class="weather-icon me-2">
                            <span class="temperature">${forecast.temperature}°C</span>
                        </div>
                        <p class="mb-1">
                            <strong>Conditions:</strong> 
                            ${forecast.description.charAt(0).toUpperCase() + forecast.description.slice(1)}
                        </p>
                        <p class="mb-1">
                            <strong>Humidity:</strong> 
                            ${forecast.humidity}%
                        </p>
                        <p class="mb-0">
                            <strong>Wind Speed:</strong> 
                            ${forecast.wind_speed} m/s
                        </p>
                    </div>
                `;
            });
            
            weatherContainer.innerHTML = weatherHtml;
        })
        .catch(error => {
            const weatherContainer = document.getElementById('weather-{{ $location->id }}');
            weatherContainer.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load weather data: ${error.message}
                </div>
            `;
        });
    @endforeach
});
</script>
@endpush
@endsection 