@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Journeys</h2>
                <a href="{{ route('journey.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Journey
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($journeys->isEmpty())
                <div class="alert alert-info">
                    You haven't created any journeys yet. Click the button above to create your first journey!
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Journey Name</th>
                                <th>Locations</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journeys as $journey)
                                <tr>
                                    <td>{{ $journey->journey_name }}</td>
                                    <td>
                                        @foreach($journey->locations as $location)
                                            <span class="badge bg-primary me-1">{{ $location->location }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $journey->start_date }}</td>
                                    <td>{{ $journey->end_date }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('journey.show', $journey) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-cloud-sun"></i> Weather
                                            </a>
                                            <a href="{{ route('journey.edit', $journey) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('journey.destroy', $journey) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this journey?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.table {
    background-color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

.table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    padding: 12px;
}

.table tbody td {
    padding: 12px;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-group {
    gap: 5px;
}

.btn-group .btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-group .btn i {
    font-size: 0.9em;
}

.badge {
    font-size: 0.85em;
    padding: 0.5em 0.75em;
}

.alert {
    border-radius: 8px;
}

/* Add Font Awesome icons if not already included */
@if(!isset($fa_included))
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endif
</style>
@endsection
