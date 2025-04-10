<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JourneyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RecommendationsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WeatherController as AdminWeatherController;
use App\Http\Controllers\Admin\GptController as AdminGptController;
use App\Http\Controllers\Admin\MapController as AdminMapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecommendationController;

// Root route - redirect to login if not authenticated, otherwise to appropriate dashboard
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return auth()->user()->isAdmin() ? redirect()->route('admin.dashboard') : redirect()->route('journey.index');
});

// User Routes (only accessible by non-admin users)
Route::middleware(['auth', 'user.only'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('journey.index');
    Route::get('/journeys', [JourneyController::class, 'index'])->name('journey.index');
    Route::get('/journey/create', [JourneyController::class, 'create'])->name('journey.create');
    Route::post('/journey/store', [JourneyController::class, 'store'])->name('journey.store');
    Route::get('/journey/edit/{id}', [JourneyController::class, 'edit'])->name('journey.edit');
    Route::post('/journey/update/{id}', [JourneyController::class, 'update'])->name('journey.update');
    Route::delete('/journey/delete/{journey}', [JourneyController::class, 'destroy'])->name('journey.destroy');
    Route::get('/recommendations', [RecommendationsController::class, 'index'])->name('recommendations.index');
    Route::get('/journeys/{journey}', [JourneyController::class, 'show'])->name('journey.show');
    Route::post('/journeys/{journey}/analyze', [JourneyController::class, 'analyze'])->name('journey.analyze');
    Route::get('/journeys/{journey}/weather', [JourneyController::class, 'getWeatherData'])->name('journey.weather');
    
    // Recommendation routes
    Route::delete('/recommendations/{recommendation}', [RecommendationController::class, 'destroy'])->name('recommendations.destroy');
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
});

Auth::routes();

// Profile routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
});

// Admin Routes (only accessible by admin users)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::delete('/users/{user}', [UserController::class, 'deleteUser'])->name('users.delete');
    Route::put('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // API Management Routes
    Route::get('/weather', [AdminWeatherController::class, 'index'])->name('weather.index');
    Route::put('/weather', [AdminWeatherController::class, 'update'])->name('weather.update');
    
    Route::get('/gpt', [AdminGptController::class, 'index'])->name('gpt.index');
    Route::put('/gpt', [AdminGptController::class, 'update'])->name('gpt.update');
    Route::put('/gpt/questions', [AdminGptController::class, 'updateQuestions'])->name('gpt.questions');
    
    Route::get('/map', [AdminMapController::class, 'index'])->name('map.index');
    Route::put('/map', [AdminMapController::class, 'update'])->name('map.update');
});

#Admin password: Admin123
#User password: Abc12345