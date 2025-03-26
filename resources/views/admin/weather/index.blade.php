@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Weather API Management</h2>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">API Configuration</h5>
                    <form action="{{ route('admin.weather.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Key</label>
                            <input type="password" class="form-control" id="api_key" name="api_key" value="{{ old('api_key', config('services.weather.api_key')) }}">
                        </div>
                        <div class="mb-3">
                            <label for="base_url" class="form-label">Base URL</label>
                            <input type="text" class="form-control" id="base_url" name="base_url" value="{{ old('base_url', config('services.weather.base_url')) }}">
                        </div>
                        <div class="mb-3">
                            <label for="units" class="form-label">Units</label>
                            <select class="form-select" id="units" name="units">
                                <option value="metric" {{ config('services.weather.units') === 'metric' ? 'selected' : '' }}>Metric</option>
                                <option value="imperial" {{ config('services.weather.units') === 'imperial' ? 'selected' : '' }}>Imperial</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Configuration</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">API Usage Statistics</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Requests</th>
                                    <th>Success Rate</th>
                                    <th>Average Response Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Today</td>
                                    <td>0</td>
                                    <td>0%</td>
                                    <td>0ms</td>
                                </tr>
                                <tr>
                                    <td>This Week</td>
                                    <td>0</td>
                                    <td>0%</td>
                                    <td>0ms</td>
                                </tr>
                                <tr>
                                    <td>This Month</td>
                                    <td>0</td>
                                    <td>0%</td>
                                    <td>0ms</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 