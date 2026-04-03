<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MessageController;
use App\Http\Middleware\EnsureApproved;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // If user is logged in, redirect to appropriate page
    if (auth()->check()) {
        $user = auth()->user();
        // If not approved yet, go to pending-approval
        if (is_null($user->approved_at)) {
            return redirect()->route('pending-approval');
        }

        // If approved, go to dashboard
        return redirect()->route('dashboard');
    }

    return view('visitor');
})->name('home');

Route::get('/register-login', function () {
    // If user is logged in, redirect to appropriate page
    if (auth()->check()) {
        $user = auth()->user();
        // If not approved yet, go to pending-approval
        if (is_null($user->approved_at)) {
            return redirect()->route('pending-approval');
        }

        // If approved, go to dashboard
        return redirect()->route('dashboard');
    }

    return view('register-login-page');
})->name('register-login');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', EnsureApproved::class])
    ->name('dashboard');

Route::get('/pending-approval', function () {
    // If user is already approved, redirect to dashboard
    $user = auth()->user();
    if (! is_null($user->approved_at)) {
        return redirect()->route('dashboard');
    }

    return view('pending-approval');
})->middleware('auth')->name('pending-approval');
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
