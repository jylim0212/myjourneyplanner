<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>MyJourney Planner</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

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
            z-index: 1002; /* Ensure sidebar is above overlay */
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
            z-index: 1001; /* Below sidebar */
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
    </style>
</head>
<body>
    <div id="app">
        <!-- Sidebar -->
        <div id="sidebar">
            <a href="javascript:void(0)" onclick="toggleSidebar()" style="text-align: right; padding-right: 15px;">&times;</a>
            <a href="{{ route('journey.index') }}" onclick="closeSidebar()">🏠 Home</a>
            <a href="{{ route('recommendations.index') }}" onclick="closeSidebar()">🤖 AI Recommendation</a>
            @auth
                <a href="{{ route('profile.index') }}" onclick="closeSidebar()">👤 Profile</a>
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" onclick="closeSidebar()">⚙️ Admin Panel</a>
                @endif
            @endauth
        </div>

        <!-- Overlay -->
        <div id="overlay" onclick="toggleSidebar()"></div>

        <nav class="navbar navbar-expand-md">
            <div class="container">
                <!-- Show Menu Icon Only After Login -->
                @auth
                    <span class="menu-icon" onclick="toggleSidebar()">&#9776;</span>
                @endauth

                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'MyJourney Planner') }}
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <span class="nav-link">{{ Auth::user()->name }}</span>
                            </li>
                            <li class="nav-item">
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="nav-link btn btn-link" style="text-decoration: none;">
                                        {{ __('Logout') }}
                                    </button>
                                </form>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')

    <script>
    function toggleSidebar() {
        let sidebar = document.getElementById("sidebar");
        let overlay = document.getElementById("overlay");

        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
    }

    // Ensure clicking inside sidebar does NOT close it
    document.getElementById("sidebar").addEventListener("click", function (event) {
        event.stopPropagation();
    });

    // Clicking on overlay closes sidebar
    document.getElementById("overlay").addEventListener("click", function () {
        toggleSidebar();
    });

    // Initialize dropdowns
    document.addEventListener('DOMContentLoaded', function() {
        // Get all dropdown toggles
        var dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        
        // Initialize each dropdown
        dropdownToggles.forEach(function(toggle) {
            new bootstrap.Dropdown(toggle, {
                autoClose: true
            });
        });
    });
</script>
</body>
</html>
