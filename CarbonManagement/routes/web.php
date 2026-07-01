<?php

use App\Http\Controllers\CarbonCalculatorController;

Route::get('/', [CarbonCalculatorController::class, 'index'])->name('home');
Route::get('/calculator', [CarbonCalculatorController::class, 'create'])->name('records.create');
Route::post('/calculator', [CarbonCalculatorController::class, 'store'])->name('records.store');
Route::get('/records', [CarbonCalculatorController::class, 'history'])->name('records.history');
Route::get('/records/{id}', [CarbonCalculatorController::class, 'show'])->name('records.show');
Route::delete('/emission-records/{id}', [CarbonCalculatorController::class, 'destroy'])->name('records.destroy');
