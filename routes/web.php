<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JourneyController;

Route::get('/', function () {
    return redirect()->route('journey.index');
});


Route::middleware(['auth'])->group(function () {
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
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

#Admin password: Admin123
#User password: Abc12345