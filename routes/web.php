<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\SecretaryDashboardController;
use App\Http\Controllers\SecretaryTimetableController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherTimetableController;
use App\Http\Controllers\TimetableController;
use App\Http\Middleware\EnsureApproved;
use App\Support\DashboardRedirector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$supportedRoles = ['student', 'teacher', 'secretary', 'parent', 'admin'];
$supportedRolesMiddleware = 'role:student|teacher|secretary|parent|admin';

Route::get('/', function () {
    // If user is logged in, redirect to appropriate page
    if (auth()->check()) {
        $user = auth()->user();
        // If not approved yet, go to pending-approval
        if (is_null($user->approved_at)) {
            return redirect()->route('pending-approval');
        }

        return redirect()->route(
            DashboardRedirector::routeNameFor($user),
            DashboardRedirector::routeParametersFor($user)
        );
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

        return redirect()->route(
            DashboardRedirector::routeNameFor($user),
            DashboardRedirector::routeParametersFor($user)
        );
    }

    return view('register-login-page');
})->name('register-login');

Route::get('/dashboard', function (Request $request) {
    return redirect()->route('role.dashboard', DashboardRedirector::routeParametersFor($request->user()));
})
    ->middleware(['auth', 'verified', EnsureApproved::class])
    ->name('dashboard');

Route::middleware(['auth', 'verified', EnsureApproved::class, $supportedRolesMiddleware, 'route.role'])
    ->prefix('{role}')
    ->whereIn('role', $supportedRoles)
    ->group(function () {
        Route::get('/dashboard', function (Request $request, string $role) {
            return match ($role) {
                'student' => app(StudentDashboardController::class)->index($request),
                'teacher' => app(TeacherDashboardController::class)->index($request),
                'secretary' => app(SecretaryDashboardController::class)->index($request),
                'parent' => app(ParentDashboardController::class)->index($request),
                'admin' => app(AdminDashboardController::class)->index($request),
                default => abort(404),
            };
        })->name('role.dashboard');

        Route::get('/messages', [MessageController::class, 'index'])->name('role.messages.index');
        Route::post('/messages', [MessageController::class, 'store'])->name('role.messages.store');
        Route::patch('/messages/{message}/read', [MessageController::class, 'markAsRead'])
            ->whereNumber('message')
            ->name('role.messages.read');

        Route::get('/messages/inbox', [MessageController::class, 'inbox'])->name('role.messages.inbox');
        Route::get('/messages/sent', [MessageController::class, 'sent'])->name('role.messages.sent');
        Route::get('/messages/create', [MessageController::class, 'create'])->name('role.messages.create');
        Route::get('/messages/message/{message}', [MessageController::class, 'show'])
            ->whereNumber('message')
            ->name('role.messages.show');
        Route::get('/messages/{conversation}', [MessageController::class, 'conversation'])
            ->whereNumber('conversation')
            ->name('role.messages.conversation');
    });

Route::get('/pending-approval', function () {
    // If user is already approved, redirect to dashboard
    $user = auth()->user();
    if (! is_null($user->approved_at)) {
        return redirect()->route(
            DashboardRedirector::routeNameFor($user),
            DashboardRedirector::routeParametersFor($user)
        );
    }

    return view('pending-approval');
})->middleware('auth')->name('pending-approval');
Route::middleware([
    'auth',
    'verified',
    EnsureApproved::class,
])->group(function () {
    Route::get('/timetable', [TimetableController::class, 'index'])
        ->name('timetable.index');

    Route::get('/teacher/timetable', [TeacherTimetableController::class, 'index'])
        ->name('timetable.teacher');

    Route::get('/approvals', [ApprovalController::class, 'index'])
        ->name('approvals.index');

    Route::post('/approvals/{user}/approve', [ApprovalController::class, 'approve'])
        ->whereNumber('user')
        ->name('approvals.approve');

    Route::post('/approvals/{user}/reject', [ApprovalController::class, 'reject'])
        ->whereNumber('user')
        ->name('approvals.reject');

    Route::get('/secretary/timetable', [SecretaryTimetableController::class, 'index'])
        ->name('secretary.timetable.index');
});

Route::middleware([
    'auth',
    'verified',
    EnsureApproved::class,
])->group(function () {
    Route::get('/messages', function (Request $request) {
        return redirect()->route('role.messages.index', DashboardRedirector::routeParametersFor($request->user()));
    });

    Route::get('/messages/inbox', function (Request $request) {
        return redirect()->route('role.messages.inbox', DashboardRedirector::routeParametersFor($request->user()));
    })->name('messages.inbox');

    Route::get('/messages/sent', function (Request $request) {
        return redirect()->route('role.messages.sent', DashboardRedirector::routeParametersFor($request->user()));
    })->name('messages.sent');

    Route::get('/messages/create', function (Request $request) {
        return redirect()->route('role.messages.create', DashboardRedirector::routeParametersFor($request->user()));
    })->name('messages.create');

    Route::get('/messages/{message}', function (Request $request, string $message) {
        return redirect()->route(
            'role.messages.show',
            DashboardRedirector::routeParametersFor($request->user(), ['message' => $message])
        );
    })->whereNumber('message')->name('messages.show');

    Route::get('/messages/conversation/{user}', function (Request $request, string $user) {
        return redirect()->route(
            'role.messages.conversation',
            DashboardRedirector::routeParametersFor($request->user(), ['conversation' => $user])
        );
    })->whereNumber('user')->name('messages.conversation');
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
