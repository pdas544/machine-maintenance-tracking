<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::middleware('auth:sanctum')->group(function () {
    // Operators/Line Leaders
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets', [TicketController::class, 'index']); // Track status

    // Mechanics
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
});
