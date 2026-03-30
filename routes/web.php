<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApprovalController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureApproved::class])
    ->name('dashboard');

Route::view('/pending-approval', 'pending-approval')
    ->middleware('auth')
    ->name('pending-approval');
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

    Route::post('/approvals/{user}/reject', [ApprovalController::class, 'reject'])
        ->whereNumber('user')
        ->name('approvals.reject');
});

Route::middleware('auth')->group(function () {
    Route::get('/notifications', function () {
        $notifications = auth()->user()->notifications()->latest()->get();
        return view('notifications.index', compact('notifications'));
    })->name('notifications.index');

    Route::post('/notifications/{id}/read', function ($id) {
        $n = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();
        return back();
    })->name('notifications.read');
});

require __DIR__.'/settings.php';
