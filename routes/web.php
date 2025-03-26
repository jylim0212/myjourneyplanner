<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JourneyController;
use App\Http\Controllers\Auth\LoginController;

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
    Route::get('/recommendation', function () {
        return view('recommendation.index');
    })->name('recommendation.index');
    Route::get('/journeys/{journey}', [JourneyController::class, 'show'])->name('journey.show');
});

Auth::routes();

// Admin Routes (only accessible by admin users)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::delete('/users/{user}', [App\Http\Controllers\AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::put('/users/{id}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::get('/weather-api', [App\Http\Controllers\AdminController::class, 'weatherApi'])->name('admin.weather');
    Route::get('/gpt-api', [App\Http\Controllers\AdminController::class, 'gptApi'])->name('admin.gpt');
});

#Admin password: Admin123
#User password: Abc12345