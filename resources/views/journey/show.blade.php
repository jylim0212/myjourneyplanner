@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h2>{{ $journey->journey_name }}</h2>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Journey Details</h5>
                        <p><strong>Start Date:</strong> {{ $journey->start_date }}</p>
                        <p><strong>End Date:</strong> {{ $journey->end_date }}</p>
                        @if($journey->preferred_events)
                            <p><strong>Preferred Events:</strong> {{ $journey->preferred_events }}</p>
                        @endif
                    </div>

                    <div class="mb-4">
                        <h5>Locations</h5>
                        <div class="row">
                            @foreach($journey->locations as $location)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $location->location }}</h6>
                                            @if(isset($weatherData[$location->location]))
                                                <div class="weather-info">
                                                    <h6 class="mb-3">Weather Forecast</h6>
                                                    @foreach($weatherData[$location->location] as $date => $forecast)
                                                        <div class="weather-day mb-3">
                                                            <h6 class="mb-2">{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</h6>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <img src="http://openweathermap.org/img/w/{{ $forecast['icon'] }}.png" 
                                                                     alt="Weather icon" class="weather-icon me-2">
                                                                <span class="temperature">{{ $forecast['temperature'] }}°C</span>
                                                            </div>
                                                            <p class="mb-1">
                                                                <strong>Conditions:</strong> 
                                                                {{ ucfirst($forecast['description']) }}
                                                            </p>
                                                            <p class="mb-1">
                                                                <strong>Humidity:</strong> 
                                                                {{ $forecast['humidity'] }}%
                                                            </p>
                                                            <p class="mb-0">
                                                                <strong>Wind Speed:</strong> 
                                                                {{ $forecast['wind_speed'] }} m/s
                                                            </p>
                                                        </div>
                                                        @if(!$loop->last)
                                                            <hr>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted">Weather data unavailable</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('journey.edit', $journey) }}" class="btn btn-primary">Edit Journey</a>
                        <form action="{{ route('journey.destroy', $journey) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this journey?')">
                                Delete Journey
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.weather-info {
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.weather-icon {
    width: 40px;
    height: 40px;
}

.temperature {
    font-size: 1.2em;
    font-weight: bold;
}

.weather-day {
    padding: 10px;
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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