@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Journey Recommendations</h2>
                <a href="{{ route('journey.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Journeys
                </a>
            </div>

            @if($recommendations->isEmpty())
                <div class="alert alert-info">
                    No recommendations available yet. Try analyzing a journey first!
                </div>
            @else
                @foreach($recommendations as $recommendation)
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ $recommendation->journey->journey_name }}</h5>
                                <small class="text-muted">
                                    Generated {{ $recommendation->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>From:</strong> {{ $recommendation->current_location }}<br>
                                <strong>Journey Dates:</strong> 
                                {{ $recommendation->journey->start_date }} to {{ $recommendation->journey->end_date }}
                            </div>
                            
                            <div class="recommendation-content">
                                {!! nl2br(e($recommendation->recommendation)) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.recommendation-content {
    white-space: pre-line;
    font-family: 'Nunito', sans-serif;
    line-height: 1.6;
    color: #333;
}

.recommendation-content strong {
    color: #2c3e50;
    font-weight: 600;
}

.recommendation-content .bullet-point {
    margin-left: 1rem;
}

.recommendation-content .section-divider {
    border-bottom: 1px solid #eee;
    margin: 1rem 0;
}

.alert {
    border-radius: 8px;
}
</style>
@endsection 