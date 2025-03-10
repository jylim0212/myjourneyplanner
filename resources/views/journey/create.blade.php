@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center">Create Journey</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('journey.store') }}">
        @csrf

        <div class="form-group">
            <label for="journey_name">Journey Name</label>
            <input id="journey_name" type="text" class="form-control" name="journey_name" required>
        </div>

        <div class="form-group">
            <label>Locations</label>
            <div id="location-fields">
                <div class="input-group mb-2">
                    <input type="text" name="locations[]" class="form-control" required>
                    <button type="button" class="btn btn-danger remove-location d-none">Remove</button>
                </div>
            </div>
            <button type="button" id="add-location" class="btn btn-secondary mt-2">+ Add Location</button>
        </div>

        <div class="form-group">
            <label>Start Date & End Date</label>
            <div class="d-flex">
                <input id="start_date" type="date" class="form-control me-2" name="start_date" required>
                <input id="end_date" type="date" class="form-control" name="end_date" required>
            </div>
        </div>

        <div class="form-group">
            <label for="preferred_events">Preferred Events (Optional)</label>
            <input id="preferred_events" type="text" class="form-control" name="preferred_events">
        </div>

        <div class="form-group text-center mt-3">
            <button type="submit" class="btn btn-success">Create Journey</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('add-location').addEventListener('click', function() {
        let div = document.createElement('div');
        div.classList.add('input-group', 'mb-2');

        div.innerHTML = `
            <input type="text" name="locations[]" class="form-control" required>
            <button type="button" class="btn btn-danger remove-location">Remove</button>
        `;

        document.getElementById('location-fields').appendChild(div);
    });

    // Event delegation to handle dynamically added remove buttons
    document.getElementById('location-fields').addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-location')) {
            event.target.parentElement.remove();
        }
    });
});
</script>

@endsection
