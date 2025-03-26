@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Admin Dashboard</h2>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Users Management</h5>
                            <p class="card-text">Manage user accounts, view user details, and delete users.</p>
                            <a href="{{ route('admin.users') }}" class="btn btn-primary">Manage Users</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Weather API</h5>
                            <p class="card-text">Configure and manage weather API settings and usage.</p>
                            <a href="{{ route('admin.weather') }}" class="btn btn-primary">Manage Weather API</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">GPT API</h5>
                            <p class="card-text">Configure and manage GPT API settings and usage.</p>
                            <a href="{{ route('admin.gpt') }}" class="btn btn-primary">Manage GPT API</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 