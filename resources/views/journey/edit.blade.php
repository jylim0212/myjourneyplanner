@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center">Edit Journey</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('journey.update', $journey->id) }}">
        @csrf

        <div class="form-group">
            <label for="journey_name">Journey Name</label>
            <input id="journey_name" type="text" class="form-control @error('journey_name') is-invalid @enderror" name="journey_name" value="{{ old('journey_name', $journey->journey_name) }}" required>
            @error('journey_name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label>Locations</label>
            <div class="row">
                <div class="col-md-6">
                    <div id="location-fields">
                        <div class="input-group mb-2">
                            <input type="text" id="location-input" class="form-control" placeholder="Search for a location">
                            <button type="button" class="btn btn-primary" id="add-location-btn">Add Location</button>
                        </div>
                        <div id="selected-locations">
                            @foreach($journey->locations as $location)
                                <div class="input-group mb-2">
                                    <input type="text" name="locations[]" class="form-control" value="{{ $location->location }}" readonly>
                                    <button type="button" class="btn btn-danger remove-location" data-location="{{ $location->location }}">Remove</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="map" style="height: 400px; width: 100%;"></div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Start Date & End Date</label>
            <div class="d-flex">
                <input id="start_date" type="date" class="form-control @error('start_date') is-invalid @enderror me-2" name="start_date" value="{{ old('start_date', $journey->start_date) }}" required>
                <input id="end_date" type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date', $journey->end_date) }}" required>
            </div>
            @error('start_date')
                <small class="text-danger">{{ $message }}</small>
            @enderror
            @error('end_date')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="preferred_events">Preferred Events (Optional)</label>
            <input id="preferred_events" type="text" class="form-control" name="preferred_events" value="{{ old('preferred_events', $journey->preferred_events) }}">
        </div>

        <div class="form-group text-center mt-3">
            <button type="submit" class="btn btn-success">Update Journey</button>
        </div>
    </form>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places"></script>

<script>
let map;
let markers = [];
let autocomplete;
let selectedLocations = @json($journey->locations->pluck('location'));

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map centered on Malaysia
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 3.140853, lng: 101.693207 }, // Kuala Lumpur coordinates
        zoom: 6
    });

    // Initialize the autocomplete
    const input = document.getElementById('location-input');
    autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['(cities)'],
        componentRestrictions: { country: 'my' } // Restrict to Malaysia
    });

    // Add click listener to the map
    map.addListener('click', function(event) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: event.latLng }, (results, status) => {
            if (status === 'OK') {
                // Find the most relevant result that's in Malaysia
                const malaysiaResult = results.find(result => 
                    result.address_components.some(component => 
                        component.types.includes('country') && 
                        component.short_name === 'MY'
                    )
                );

                if (malaysiaResult) {
                    // Get a clean location name without plus codes
                    const locationName = getCleanLocationName(malaysiaResult);
                    document.getElementById('location-input').value = locationName;
                }
            }
        });
    });

    // Handle adding location
    document.getElementById('add-location-btn').addEventListener('click', function() {
        const locationInput = document.getElementById('location-input');
        const location = locationInput.value.trim();
        
        if (location && !selectedLocations.includes(location)) {
            selectedLocations.push(location);
            addMarker(location);
            updateSelectedLocations();
            locationInput.value = '';
        }
    });

    // Single event listener for removing locations
    document.getElementById('selected-locations').addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-location')) {
            const location = event.target.getAttribute('data-location');
            selectedLocations = selectedLocations.filter(loc => loc !== location);
            removeMarker(location);
            updateSelectedLocations();
        }
    });

    // Initialize markers for existing locations
    selectedLocations.forEach(location => {
        addMarker(location);
    });
});

function getCleanLocationName(result) {
    // Extract relevant components for a clean location name
    const components = result.address_components;
    const city = components.find(c => c.types.includes('locality'))?.long_name;
    const state = components.find(c => c.types.includes('administrative_area_level_1'))?.long_name;
    
    if (city && state) {
        return `${city}, ${state}`;
    }
    return result.formatted_address;
}

function addMarker(location) {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: location }, (results, status) => {
        if (status === 'OK') {
            const malaysiaResult = results.find(result => 
                result.address_components.some(component => 
                    component.types.includes('country') && 
                    component.short_name === 'MY'
                )
            );

            if (malaysiaResult) {
                const marker = new google.maps.Marker({
                    position: malaysiaResult.geometry.location,
                    map: map,
                    title: location
                });
                markers.push(marker);
            }
        }
    });
}

function removeMarker(location) {
    const marker = markers.find(m => m.getTitle() === location);
    if (marker) {
        marker.setMap(null);
        markers = markers.filter(m => m !== marker);
    }
}

function updateSelectedLocations() {
    const container = document.getElementById('selected-locations');
    container.innerHTML = '';
    
    selectedLocations.forEach(location => {
        const div = document.createElement('div');
        div.classList.add('input-group', 'mb-2');
        div.innerHTML = `
            <input type="text" name="locations[]" class="form-control" value="${location}" readonly>
            <button type="button" class="btn btn-danger remove-location" data-location="${location}">Remove</button>
        `;
        container.appendChild(div);
    });
}
</script>

<style>
#map {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#selected-locations {
    margin-top: 10px;
}

.remove-location {
    cursor: pointer;
}
</style>
@endsection
