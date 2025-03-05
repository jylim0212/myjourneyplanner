@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center">My Journeys</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('journey.create') }}" class="btn btn-primary">+ Create Journey</a>
    </div>

    @if($journeys->isEmpty())
        <p class="text-center">You have not created any journeys yet.</p>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Journey Name</th>
                    <th>Location</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Preferred Events</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($journeys as $journey)
                    <tr>
                        <td>{{ $journey->journey_name }}</td>
                        <td>{{ $journey->location }}</td>
                        <td>{{ $journey->start_date }}</td>
                        <td>{{ $journey->end_date }}</td>
                        <td>{{ $journey->preferred_events ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('journey.edit', $journey->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('journey.destroy', $journey->id) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this journey?");
    }
</script>
@endsection
