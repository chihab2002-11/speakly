<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MessageController;
use App\Http\Middleware\EnsureApproved;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', EnsureApproved::class])
    ->name('dashboard');

Route::view('/pending-approval', 'pending-approval')
    ->middleware('auth')
    ->name('pending-approval');
Route::middleware([
    'auth',
    'verified',
    EnsureApproved::class,
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

Route::middleware([
    'auth',
    'verified',
    EnsureApproved::class,
])->group(function () {
    Route::get('/messages/inbox', [MessageController::class, 'inbox'])->name('messages.inbox');
    Route::get('/messages/sent', [MessageController::class, 'sent'])->name('messages.sent');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::patch('/messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.read');
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
