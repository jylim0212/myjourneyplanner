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
            <label for="location">Location</label>
            <input id="location" type="text" class="form-control @error('location') is-invalid @enderror" name="location" value="{{ old('location', $journey->location) }}" required>
            @error('location')
                <small class="text-danger">{{ $message }}</small>
            @enderror
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
@endsection
