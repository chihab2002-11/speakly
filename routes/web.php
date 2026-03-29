<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApprovalController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureApproved::class])
    ->name('dashboard');

Route::view('/pending-approval', 'livewire.pending-approval')
    ->middleware('auth')
    ->name('pending-approval');

// Approvals (NO 'role:' middleware here, Laravel 12 doesn't have Kernel aliases by default)
Route::middleware([
    'auth',
    'verified',
    \App\Http\Middleware\EnsureApproved::class,
])->group(function () {
    Route::get('/approvals', [ApprovalController::class, 'index'])
        ->name('approvals.index');

    Route::post('/approvals/{user}/approve', [ApprovalController::class, 'approve'])
        ->whereNumber('user')
        ->name('approvals.approve');
});

require __DIR__.'/settings.php';
