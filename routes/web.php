<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\SecretaryDashboardController;
use App\Http\Controllers\SecretaryTimetableController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherResourceController;
use App\Http\Controllers\TeacherSettingsController;
use App\Http\Controllers\TeacherTimetableController;
use App\Http\Controllers\TimetableController;
use App\Http\Middleware\EnsureApproved;
use App\Models\User;
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
    return view('register-login-page');
})->middleware('guest')->name('register-login');

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

// ============================================================
// Student-specific routes (academic, financial, materials, settings, password)
// ============================================================
Route::middleware(['auth', 'verified', EnsureApproved::class, 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        // Academic Information page
        Route::get('/academic', function () {
            $user = auth()->user();

            // Placeholder attendance data (4 weeks)
            // 1 = present, 0 = absent, null = no class
            $attendance = [
                ['week' => 'W1', 'days' => [1, 1, 1, 1, 1, null, null]],
                ['week' => 'W2', 'days' => [1, 1, 0, 1, 1, null, null]],
                ['week' => 'W3', 'days' => [1, 1, 1, 1, 1, null, null]],
                ['week' => 'W4', 'days' => [1, 1, 1, 1, null, null, null]],
            ];

            // Placeholder evaluations data
            $evaluations = [
                [
                    'subject' => 'French B2',
                    'assessment' => 'Oral Presentation',
                    'score' => 92,
                    'feedback' => 'Excellent pronunciation and fluency. Continue practicing complex grammar structures.',
                ],
                [
                    'subject' => 'French B2',
                    'assessment' => 'Written Essay',
                    'score' => 88,
                    'feedback' => 'Strong vocabulary usage. Work on paragraph transitions.',
                ],
                [
                    'subject' => 'Spanish A2',
                    'assessment' => 'Listening Comprehension',
                    'score' => 85,
                    'feedback' => 'Good understanding of native speakers. Focus on regional accents.',
                ],
                [
                    'subject' => 'Spanish A2',
                    'assessment' => 'Grammar Quiz',
                    'score' => 78,
                    'feedback' => 'Review subjunctive mood conjugations.',
                ],
            ];

            // Placeholder schedule data
            $schedule = [
                'Mon' => [
                    '09:00' => ['course' => 'French B2', 'color' => '#006A41', 'room' => 'Room 101'],
                    '14:30' => ['course' => 'Spanish A2', 'color' => '#5E70BB', 'room' => 'Room 203'],
                ],
                'Tue' => [
                    '11:30' => ['course' => 'French B2', 'color' => '#006A41', 'room' => 'Room 101'],
                ],
                'Wed' => [
                    '09:00' => ['course' => 'Spanish A2', 'color' => '#5E70BB', 'room' => 'Room 203'],
                    '14:30' => ['course' => 'French B2', 'color' => '#006A41', 'room' => 'Room 101'],
                ],
                'Thu' => [
                    '09:00' => ['course' => 'Tutorial', 'color' => '#64748B', 'room' => 'Lab 2'],
                    '11:30' => ['course' => 'French B2', 'color' => '#006A41', 'room' => 'Room 101'],
                ],
                'Fri' => [
                    '09:00' => ['course' => 'Spanish A2', 'color' => '#5E70BB', 'room' => 'Room 203'],
                ],
            ];

            return view('student.academic', [
                'user' => $user,
                'currentStreak' => 12, // Placeholder - backend will calculate
                'attendance' => $attendance,
                'evaluations' => $evaluations,
                'schedule' => $schedule,
                'classesPerWeek' => 8,
                'hoursPerWeek' => 12,
            ]);
        })->name('academic');

        // Financial Information page
        Route::get('/financial', function () {
            $user = auth()->user();

            // Placeholder ledger items
            $ledgerItems = [
                [
                    'name' => 'Advanced Business English - Q3',
                    'type' => 'Course Fee',
                    'period' => 'Jul - Sep 2024',
                    'amount' => 1240.00,
                    'status' => 'outstanding',
                    'icon' => 'course',
                ],
                [
                    'name' => 'TOEFL Preparation Intensive',
                    'type' => 'Workshop Fee',
                    'period' => 'Jun 2024',
                    'amount' => 450.00,
                    'status' => 'paid',
                    'icon' => 'workshop',
                ],
                [
                    'name' => 'Digital Learning Materials Bundle',
                    'type' => 'License Fee',
                    'period' => 'Annual',
                    'amount' => 185.00,
                    'status' => 'paid',
                    'icon' => 'materials',
                ],
                [
                    'name' => 'Digital Learning Materials Bundle',
                    'type' => 'License Fee',
                    'period' => 'Annual',
                    'amount' => 254.00,
                    'status' => 'paid',
                    'icon' => 'materials',
                ],
            ];

            // Placeholder receipts
            $receipts = [
                [
                    'invoice' => 'INV-2024-082',
                    'amount' => 450.00,
                    'date' => 'May 12, 2024',
                    'method' => 'Visa',
                    'last4' => '4221',
                ],
                [
                    'invoice' => 'INV-2024-045',
                    'amount' => 1240.00,
                    'date' => 'Feb 05, 2024',
                    'method' => 'Bank Transfer',
                    'last4' => null,
                ],
                [
                    'invoice' => 'INV-2023-911',
                    'amount' => 185.00,
                    'date' => 'Nov 28, 2023',
                    'method' => 'Visa',
                    'last4' => '4221',
                ],
            ];

            return view('student.financial', [
                'user' => $user,
                'totalOutstanding' => 1240.00,
                'academicYear' => '2024',
                'ledgerItems' => $ledgerItems,
                'grossTuition' => 1458.82,
                'scholarshipCredit' => 218.82,
                'scholarshipDiscount' => 15,
                'netDue' => 1240.00,
                'receipts' => $receipts,
            ]);
        })->name('financial');

        Route::get('/materials', function () {
            $user = auth()->user();

            return view('student.materials', [
                'user' => $user,
            ]);
        })->name('materials');

        // Account Settings page
        Route::get('/settings', function () {
            $user = auth()->user();

            return view('student.settings', [
                'user' => $user,
                'studentId' => 'LUM-2024-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'proficiencyLevel' => 'C1', // Placeholder - backend will implement
                'proficiencyPercent' => 84, // Placeholder - backend will implement
                'proficiencyStatus' => 'Advanced',
                'passwordLastChanged' => '3 months ago', // Placeholder
                'twoFactorEnabled' => $user->two_factor_confirmed_at !== null,
            ]);
        })->name('settings');

        // Change Password page
        Route::get('/password', function () {
            $user = auth()->user();

            return view('student.password', [
                'user' => $user,
            ]);
        })->name('password');

        // Student Notifications page
        Route::get('/notifications', function () {
            $user = auth()->user();
            $notifications = $user->notifications()->latest()->get();

            return view('student.notifications', [
                'user' => $user,
                'notifications' => $notifications,
            ]);
        })->name('notifications');

        // Mark notification as read
        Route::post('/notifications/{id}/read', function ($id) {
            $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();

            return back()->with('success', 'Notification marked as read');
        })->name('notifications.read');

        // Mark all notifications as read
        Route::post('/notifications/read-all', function () {
            auth()->user()->unreadNotifications->markAsRead();

            return back()->with('success', 'All notifications marked as read');
        })->name('notifications.read-all');
    });

// ============================================================
// Parent-specific routes (financial, messages, calendar, settings, password)
// ============================================================
Route::middleware(['auth', 'verified', EnsureApproved::class, 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        // Helper function to get placeholder children data
        $getChildren = function () {
            return [
                [
                    'id' => 1,
                    'name' => 'Alex Johnson',
                    'initials' => 'A',
                    'grade' => 'Grade 10',
                    'stream' => 'Science Stream',
                    'gpa' => '3.8',
                    'status' => 'On Track',
                    'color' => 'var(--lumina-child-1)',
                    'textColor' => 'var(--lumina-child-1-text)',
                ],
                [
                    'id' => 2,
                    'name' => 'Sophie Johnson',
                    'initials' => 'S',
                    'grade' => 'Grade 8',
                    'stream' => 'Arts Stream',
                    'gpa' => '3.6',
                    'status' => 'On Track',
                    'color' => 'var(--lumina-child-2)',
                    'textColor' => 'var(--lumina-child-2-text)',
                ],
            ];
        };

        // Parent Financial Information
        Route::get('/financial', function () use ($getChildren) {
            $user = auth()->user();
            $children = $getChildren();

            // Placeholder financial data in Algerian Dinars
            $invoices = [
                [
                    'id' => 'INV-2024-001',
                    'child' => 'Alex Johnson',
                    'description' => 'Term 3 Tuition Fee',
                    'amount' => 122500,
                    'dueDate' => 'April 15, 2024',
                    'status' => 'pending',
                ],
                [
                    'id' => 'INV-2024-002',
                    'child' => 'Sophie Johnson',
                    'description' => 'Term 3 Tuition Fee',
                    'amount' => 122500,
                    'dueDate' => 'April 15, 2024',
                    'status' => 'pending',
                ],
                [
                    'id' => 'INV-2024-003',
                    'child' => 'Alex Johnson',
                    'description' => 'Lab Materials Fee',
                    'amount' => 15000,
                    'dueDate' => 'April 30, 2024',
                    'status' => 'pending',
                ],
            ];

            $paymentHistory = [
                [
                    'id' => 'PAY-2024-001',
                    'child' => 'Alex Johnson',
                    'description' => 'Term 2 Tuition Fee',
                    'amount' => 122500,
                    'paidDate' => 'January 10, 2024',
                    'method' => 'Bank Transfer',
                ],
                [
                    'id' => 'PAY-2024-002',
                    'child' => 'Sophie Johnson',
                    'description' => 'Term 2 Tuition Fee',
                    'amount' => 122500,
                    'paidDate' => 'January 10, 2024',
                    'method' => 'Bank Transfer',
                ],
                [
                    'id' => 'PAY-2023-015',
                    'child' => 'Alex Johnson',
                    'description' => 'Term 1 Tuition Fee',
                    'amount' => 120000,
                    'paidDate' => 'September 5, 2023',
                    'method' => 'Cash',
                ],
            ];

            return view('parent.financial', [
                'user' => $user,
                'children' => $children,
                'invoices' => $invoices,
                'paymentHistory' => $paymentHistory,
                'totalOutstanding' => 260000,
                'totalPaid' => 365000,
            ]);
        })->name('financial');

        // Parent Calendar (Timetable)
        Route::get('/calendar', function () use ($getChildren) {
            $user = auth()->user();
            $children = $getChildren();

            return view('parent.calendar', [
                'user' => $user,
                'children' => $children,
                'selectedChild' => $children[0],
                'currentWeek' => 'April 1 - 7, 2024',
            ]);
        })->name('calendar');

        // Parent Settings
        Route::get('/settings', function () use ($getChildren) {
            $user = auth()->user();
            $children = $getChildren();

            return view('parent.settings', [
                'user' => $user,
                'children' => $children,
                'twoFactorEnabled' => $user->two_factor_confirmed_at !== null,
            ]);
        })->name('settings');

        // Parent Password Change
        Route::get('/password', function () use ($getChildren) {
            $user = auth()->user();
            $children = $getChildren();

            return view('parent.password', [
                'user' => $user,
                'children' => $children,
            ]);
        })->name('password');

        // Parent Notifications page
        Route::get('/notifications', function () use ($getChildren) {
            $user = auth()->user();
            $children = $getChildren();
            $notifications = $user->notifications()->latest()->get();

            return view('parent.notifications', [
                'user' => $user,
                'children' => $children,
                'notifications' => $notifications,
            ]);
        })->name('notifications');

        // Mark notification as read
        Route::post('/notifications/{id}/read', function ($id) {
            $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();

            return back()->with('success', 'Notification marked as read');
        })->name('notifications.read');

        // Mark all notifications as read
        Route::post('/notifications/read-all', function () {
            auth()->user()->unreadNotifications->markAsRead();

            return back()->with('success', 'All notifications marked as read');
        })->name('notifications.read-all');
    });

// ============================================================
// Teacher-specific routes (attendance, resources, settings, notifications)
// ============================================================
Route::middleware(['auth', 'verified', EnsureApproved::class, 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/attendance', [TeacherAttendanceController::class, 'index'])->name('attendance');
        Route::get('/attendance/export', [TeacherAttendanceController::class, 'export'])->name('attendance.export');
        Route::post('/attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');

        Route::get('/resources', [TeacherResourceController::class, 'index'])->name('resources');
        Route::post('/resources', [TeacherResourceController::class, 'store'])->name('resources.store');
        Route::get('/resources/{resource}/download', [TeacherResourceController::class, 'download'])
            ->whereNumber('resource')
            ->name('resources.download');
        Route::patch('/resources/{resource}', [TeacherResourceController::class, 'update'])
            ->whereNumber('resource')
            ->name('resources.update');
        Route::delete('/resources/{resource}', [TeacherResourceController::class, 'destroy'])
            ->whereNumber('resource')
            ->name('resources.destroy');

        Route::get('/settings', [TeacherSettingsController::class, 'edit'])->name('settings');
        Route::patch('/settings', [TeacherSettingsController::class, 'update'])->name('settings.update');

        // Teacher Notifications page
        Route::get('/notifications', function () {
            $user = auth()->user();
            $notifications = $user->notifications()->latest()->get();

            return view('teacher.notifications', [
                'user' => $user,
                'notifications' => $notifications,
            ]);
        })->name('notifications');

        // Mark notification as read
        Route::post('/notifications/{id}/read', function ($id) {
            $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();

            return back()->with('success', 'Notification marked as read');
        })->name('notifications.read');

        // Mark all notifications as read
        Route::post('/notifications/read-all', function () {
            auth()->user()->unreadNotifications->markAsRead();

            return back()->with('success', 'All notifications marked as read');
        })->name('notifications.read-all');

        // Get available users for new conversations (API endpoint)
        Route::get('/messages/recipients', function () {
            $user = auth()->user();

            // Teachers can message: students, parents, other teachers, admins
            $users = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['student', 'parent', 'teacher', 'admin']);
            })
                ->where('id', '!=', $user->id)
                ->whereNotNull('approved_at')
                ->orderBy('name')
                ->get()
                ->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'role' => $u->roles->isNotEmpty() ? $u->roles->first()->name : null,
                    ];
                });

            return response()->json(['users' => $users]);
        })->name('messages.recipients');
    });

// ============================================================
// Admin-specific routes (notifications)
// ============================================================
Route::middleware(['auth', 'verified', EnsureApproved::class, 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Admin Notifications page
        Route::get('/notifications', function () {
            $user = auth()->user();
            $notifications = $user->notifications()->latest()->get();

            return view('admin.notifications', [
                'user' => $user,
                'notifications' => $notifications,
            ]);
        })->name('notifications');

        // Mark notification as read
        Route::post('/notifications/{id}/read', function ($id) {
            $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();

            return back()->with('success', 'Notification marked as read');
        })->name('notifications.read');

        // Mark all notifications as read
        Route::post('/notifications/read-all', function () {
            auth()->user()->unreadNotifications->markAsRead();

            return back()->with('success', 'All notifications marked as read');
        })->name('notifications.read-all');

        // Admin can access a page to start new conversations with anyone
        Route::get('/messages/new', function () {
            $user = auth()->user();

            // Get all users except current admin (students, parents, teachers, secretaries)
            $users = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['student', 'parent', 'teacher', 'secretary']);
            })
                ->where('id', '!=', $user->id)
                ->whereNotNull('approved_at')
                ->orderBy('name')
                ->get();

            return view('admin.messages-new', [
                'user' => $user,
                'users' => $users,
            ]);
        })->name('messages.new');
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

    Route::get('/secretary/timetable', [SecretaryTimetableController::class, 'index'])
        ->name('secretary.timetable.index');
});

Route::middleware([
    'auth',
    'verified',
    EnsureApproved::class,
    'role:admin|secretary',
    'route.role',
])->prefix('{role}')
    ->whereIn('role', ['admin', 'secretary'])
    ->group(function () {
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
