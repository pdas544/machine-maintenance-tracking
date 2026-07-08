<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard',[TicketController::class, 'index'])->name('dashboard');


    Route::get('/tickets/{ticket}/print', function ($ticket) {
        // We will build this printable view later
        return view('print-job-card', compact('ticket'));
    })->name('tickets.print');

    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
//    Route::get('/tickets', [TicketController::class, 'index']); // Track status

    // Mechanics
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);


    Route::get('/tickets/{ticket}/print', [TicketController::class, 'print'])->name('tickets.print');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
