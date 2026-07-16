<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SewingDepartmentController;
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
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');

    Route::post('/notifications/{notification}/read', [TicketController::class, 'markNotificationAsRead'])->name('notifications.read');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Sewing Department — Line Balancing (Industrial Engineers only)
Route::middleware(['auth', 'ie'])->group(function () {
    Route::get('/sewing', [SewingDepartmentController::class, 'index'])->name('sewing.index');
    Route::post('/sewing/upload', [SewingDepartmentController::class, 'upload'])->name('sewing.upload');
    Route::post('/sewing/upload-performance', [SewingDepartmentController::class, 'uploadPerformance'])->name('sewing.upload.performance');
    Route::post('/sewing/balance', [SewingDepartmentController::class, 'balance'])->name('sewing.balance');
});

require __DIR__.'/auth.php';
