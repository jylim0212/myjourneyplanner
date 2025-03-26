<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin Panel</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito&display=swap" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        /* Background Image */
        body {
            background: url("{{ asset('images/travel-bg.jpg') }}") no-repeat center center fixed;
            background-size: cover;
            font-family: 'Nunito', sans-serif;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, #007bff, #00c6ff);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
        }

        .navbar .nav-link {
            color: white !important;
            font-weight: 500;
        }

        .navbar .nav-link:hover {
            color: #ffd700 !important;
        }

        /* Sidebar Styles */
        #sidebar {
            position: fixed;
            left: -250px;
            top: 0;
            width: 250px;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding-top: 20px;
            transition: 0.3s;
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
            z-index: 1002;
        }

        #sidebar a {
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            font-size: 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        #sidebar a:hover {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 5px;
        }

        #sidebar.active {
            left: 0;
        }

        /* Overlay when sidebar is open */
        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1001;
        }

        #overlay.active {
            display: block;
        }

        /* Ensures sidebar links are clickable */
        #sidebar a {
            position: relative;
            z-index: 1003;
        }

        /* Make sure overlay doesn't block sidebar */
        #overlay.active {
            pointer-events: auto;
        }

        /* Style the menu icon */
        .menu-icon {
            font-size: 28px;
            cursor: pointer;
            color: white;
            margin-right: 15px;
        }

        .menu-icon:hover {
            color: #ffd700;
        }

        /* Main Content Styling */
        main {
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 90%;
        }

        /* Card Styling */
        .card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background: linear-gradient(135deg, #007bff, #00c6ff);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: bold;
        }

        .card-body {
            padding: 20px;
        }

        /* Table Styling */
        .table {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, #007bff, #00c6ff);
            color: white;
            border: none;
        }

        .table tbody tr:hover {
            background: rgba(0, 123, 255, 0.1);
        }

        /* Button Styling */
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #00c6ff);
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3, #0099cc);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #ff4d4d);
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333, #e63946);
            transform: translateY(-1px);
        }

        /* Form Styling */
        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 10px;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Alert Styling */
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, #28a745, #34ce57);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, #dc3545, #ff4d4d);
            color: white;
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Admin Sidebar -->
        <div id="sidebar">
            <a href="javascript:void(0)" onclick="toggleSidebar()" style="text-align: right; padding-right: 15px;">&times;</a>
            <a href="{{ route('admin.dashboard') }}" onclick="closeSidebar()">📊 Dashboard</a>
            <a href="{{ route('admin.users') }}" onclick="closeSidebar()">👥 Users</a>
            <a href="{{ route('admin.weather') }}" onclick="closeSidebar()">🌤️ Weather API</a>
            <a href="{{ route('admin.gpt') }}" onclick="closeSidebar()">🤖 GPT API</a>
        </div>

        <!-- Overlay -->
        <div id="overlay" onclick="toggleSidebar()"></div>

        <nav class="navbar navbar-expand-md">
            <div class="container">
                <span class="menu-icon" onclick="toggleSidebar()">&#9776;</span>

                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }} - Admin Panel
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @if(session('success'))
                <div class="container">
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="container">
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
    function toggleSidebar() {
        let sidebar = document.getElementById("sidebar");
        let overlay = document.getElementById("overlay");

        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
    }

    function closeSidebar() {
        let sidebar = document.getElementById("sidebar");
        let overlay = document.getElementById("overlay");

        sidebar.classList.remove("active");
        overlay.classList.remove("active");
    }
    </script>
</body>
</html> 