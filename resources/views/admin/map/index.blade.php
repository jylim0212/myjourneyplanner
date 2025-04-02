@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Google Maps API Management</h2>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">API Configuration</h5>
                    <form action="{{ route('admin.map.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="api_key" class="form-label">Google Maps API Key</label>
                            <input type="text" class="form-control" id="api_key" name="api_key" value="{{ old('api_key', env('GOOGLE_MAPS_API_KEY')) }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Save API Key</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 