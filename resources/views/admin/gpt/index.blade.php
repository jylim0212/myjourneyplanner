@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>GPT API Management</h2>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">API Configuration</h5>
                    <form action="{{ route('admin.gpt.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Key</label>
                            <input type="password" class="form-control" id="api_key" name="api_key" value="{{ old('api_key', config('services.openai.api_key')) }}">
                        </div>
                        <div class="mb-3">
                            <label for="model" class="form-label">Default Model</label>
                            <select class="form-select" id="model" name="model">
                                <option value="gpt-3.5-turbo" {{ config('services.openai.model') === 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                                <option value="gpt-4" {{ config('services.openai.model') === 'gpt-4' ? 'selected' : '' }}>GPT-4</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="max_tokens" class="form-label">Max Tokens</label>
                            <input type="number" class="form-control" id="max_tokens" name="max_tokens" value="{{ old('max_tokens', config('services.openai.max_tokens')) }}">
                        </div>
                        <div class="mb-3">
                            <label for="temperature" class="form-label">Temperature</label>
                            <input type="number" step="0.1" min="0" max="2" class="form-control" id="temperature" name="temperature" value="{{ old('temperature', config('services.openai.temperature')) }}">
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
                                    <th>Tokens Used</th>
                                    <th>Cost</th>
                                    <th>Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Today</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>$0.00</td>
                                    <td>0%</td>
                                </tr>
                                <tr>
                                    <td>This Week</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>$0.00</td>
                                    <td>0%</td>
                                </tr>
                                <tr>
                                    <td>This Month</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>$0.00</td>
                                    <td>0%</td>
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