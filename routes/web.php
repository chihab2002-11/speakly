<?php

use App\Http\Controllers\AdminClassroomController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminEmployeeController;
use App\Http\Controllers\AdminEmployeePaymentController;
use App\Http\Controllers\AdminLanguageProgramController;
use App\Http\Controllers\AdminScheduleController;
use App\Http\Controllers\AdminTimetableHubController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ParentChildPortalController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ParentFinancialController;
use App\Http\Controllers\ParentSettingsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SecretaryDashboardController;
use App\Http\Controllers\SecretaryOperationsController;
use App\Http\Controllers\SecretaryTimetableController;
use App\Http\Controllers\StudentAcademicController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentFinancialController;
use App\Http\Controllers\StudentMaterialsController;
use App\Http\Controllers\StudentSettingsController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherResourceController;
use App\Http\Controllers\TeacherSettingsController;
use App\Http\Controllers\TeacherTimetableController;
use App\Http\Controllers\TimetableController;
use App\Http\Middleware\EnsureApproved;
use App\Models\Course;
use App\Models\LanguageProgram;
use App\Models\Review;
use App\Models\User;
use App\Support\DashboardRedirector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$supportedRoles = ['student', 'teacher', 'secretary', 'parent', 'admin'];
$supportedRolesMiddleware = 'role:student|teacher|secretary|parent|admin';

Route::get('/', function (Request $request) {
    // If user is logged in, redirect to appropriate page
    if (Auth::check()) {
        $user = User::query()->findOrFail((int) Auth::id());
        // If not approved yet, go to pending-approval
        if (is_null($user->approved_at)) {
            return redirect()->route('pending-approval');
        }

        return redirect()->route(
            DashboardRedirector::routeNameFor($user),
            DashboardRedirector::routeParametersFor($user)
        );
    }

    $languagePrograms = config('visitor.programs', []);

    if (Schema::hasTable('language_programs')) {
        $languagePrograms = LanguageProgram::query()
            ->ordered()
            ->where('is_active', true)
            ->get()
            ->map(fn (LanguageProgram $program): array => [
                'id' => $program->id,
                'code' => $program->code,
                'name' => $program->name,
                'title' => $program->title,
                'description' => $program->description,
                'full_description' => $program->full_description,
                'flag_url' => $program->flag_url,
                'sort_order' => $program->sort_order,
                'is_active' => $program->is_active,
                'certifications' => $program->certifications ?? [],
            ])
            ->values()
            ->all();
    }

    $reviews = collect();
    $votedReviewIds = [];

    if (Schema::hasTable('reviews')) {
        $reviews = Review::query()
            ->with(['student:id,name'])
            ->orderByDesc('rating_score')
            ->orderByDesc('created_at')
            ->get();

        $votedReviewIds = collect(json_decode((string) $request->cookie('visitor_review_votes', '[]'), true))
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    return view('visitor', [
        'languagePrograms' => $languagePrograms,
        'reviews' => $reviews,
        'votedReviewIds' => $votedReviewIds,
    ]);
})->name('home');

Route::post('/reviews/{review}/vote', [ReviewController::class, 'vote'])
    ->whereNumber('review')
    ->name('reviews.vote');

Route::get('/register-login', function () {
    return view('register-login-page', [
        'availablePrograms' => Schema::hasTable('language_programs')
            ? LanguageProgram::query()
                ->ordered()
                ->where('is_active', true)
                ->get(['id', 'name', 'code'])
            : collect(),
        'availableCourses' => Schema::hasTable('courses') && Schema::hasTable('language_programs')
            ? Course::query()
                ->available()
                ->whereNotNull('program_id')
                ->whereHas('program', function ($query): void {
                    $query->where('is_active', true);
                })
                ->orderBy('name')
                ->get(['id', 'program_id', 'name', 'code', 'price'])
            : collect(),
    ]);
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
        Route::get('/academic', [StudentAcademicController::class, 'index'])->name('academic');

        // Financial Information page
        Route::get('/financial', [StudentFinancialController::class, 'index'])->name('financial');
        Route::get('/financial/payments/{payment}/pdf', [StudentFinancialController::class, 'receiptPdf'])
            ->whereNumber('payment')
            ->name('financial.payments.pdf');

        Route::get('/materials', [StudentMaterialsController::class, 'index'])->name('materials');
        Route::get('/materials/{resource}/download', [StudentMaterialsController::class, 'download'])
            ->whereNumber('resource')
            ->name('materials.download');
        Route::get('/materials/{resource}/print', [StudentMaterialsController::class, 'print'])
            ->whereNumber('resource')
            ->name('materials.print');

        Route::get('/settings', [StudentSettingsController::class, 'edit'])->name('settings');
        Route::post('/settings', [StudentSettingsController::class, 'updateProfile'])->name('settings.update');

        Route::get('/password', [StudentSettingsController::class, 'editPassword'])->name('password');
        Route::post('/password', [StudentSettingsController::class, 'updatePassword'])->name('password.update');

        Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

        // Student Notifications page
        Route::get('/notifications', function () {
            $user = User::query()->findOrFail((int) Auth::id());
            $notifications = $user->notifications()->latest()->get();

            return view('student.notifications', [
                'user' => $user,
                'notifications' => $notifications,
            ]);
        })->name('notifications');

        // Mark notification as read
        Route::post('/notifications/{id}/read', function ($id) {
            $notification = User::query()->findOrFail((int) Auth::id())->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();

            return back()->with('success', 'Notification marked as read');
        })->name('notifications.read');

        // Mark all notifications as read
        Route::post('/notifications/read-all', function () {
            User::query()->findOrFail((int) Auth::id())->unreadNotifications->markAsRead();

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
        // Helper function to get parent-linked children data
        $getChildren = function () {
            $parent = User::query()->findOrFail((int) Auth::id());

            return User::query()
                ->where('parent_id', $parent->id)
                ->whereNotNull('approved_at')
                ->whereHas('roles', function ($query): void {
                    $query->where('name', 'student');
                })
                ->orderBy('name')
                ->get(['id', 'name'])
                ->values()
                ->map(function (User $child, int $index): array {
                    $theme = $index % 2 === 0
                        ? ['color' => 'var(--lumina-child-1)', 'textColor' => 'var(--lumina-child-1-text)']
                        : ['color' => 'var(--lumina-child-2)', 'textColor' => 'var(--lumina-child-2-text)'];

                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'initials' => $child->initials(),
                        'grade' => 'Student',
                        'stream' => 'Language Track',
                        'gpa' => '-',
                        'status' => 'Active',
                        'color' => $theme['color'],
                        'textColor' => $theme['textColor'],
                    ];
                })
                ->all();
        };

        // Parent Financial Information
        Route::get('/financial', [ParentFinancialController::class, 'index'])->name('financial');
        Route::post('/financial/scholarships/activate', [ParentFinancialController::class, 'activateScholarship'])
            ->name('financial.scholarships.activate');
        Route::get('/financial/receipts/{payment}/download', [ParentFinancialController::class, 'downloadReceipt'])
            ->whereNumber('payment')
            ->name('financial.receipts.download');

        // Parent Child Portal (student experience without financial page)
        Route::get('/children/{child}/dashboard', [ParentChildPortalController::class, 'dashboard'])
            ->whereNumber('child')
            ->name('child.dashboard');

        Route::get('/children/{child}/academic', [ParentChildPortalController::class, 'academic'])
            ->whereNumber('child')
            ->name('child.academic');

        Route::get('/children/{child}/materials', [ParentChildPortalController::class, 'materials'])
            ->whereNumber('child')
            ->name('child.materials');

        Route::get('/children/{child}/materials/{resource}/download', [ParentChildPortalController::class, 'downloadMaterial'])
            ->whereNumber('child')
            ->whereNumber('resource')
            ->name('child.materials.download');

        Route::get('/children/{child}/materials/{resource}/print', [ParentChildPortalController::class, 'printMaterial'])
            ->whereNumber('child')
            ->whereNumber('resource')
            ->name('child.materials.print');

        Route::get('/children/{child}/messages', [ParentChildPortalController::class, 'messages'])
            ->whereNumber('child')
            ->name('child.messages');

        Route::get('/children/{child}/messages/{conversation}', [ParentChildPortalController::class, 'messages'])
            ->whereNumber('child')
            ->whereNumber('conversation')
            ->name('child.messages.conversation');

        Route::post('/children/{child}/messages', [ParentChildPortalController::class, 'storeMessage'])
            ->whereNumber('child')
            ->name('child.messages.store');

        Route::get('/children/{child}/settings', [ParentChildPortalController::class, 'settings'])
            ->whereNumber('child')
            ->name('child.settings');

        Route::post('/children/{child}/settings', [ParentChildPortalController::class, 'updateSettings'])
            ->whereNumber('child')
            ->name('child.settings.update');

        Route::get('/children/{child}/password', [ParentChildPortalController::class, 'password'])
            ->whereNumber('child')
            ->name('child.password');

        Route::post('/children/{child}/password', [ParentChildPortalController::class, 'updatePassword'])
            ->whereNumber('child')
            ->name('child.password.update');

        Route::get('/children/{child}/notifications', [ParentChildPortalController::class, 'notifications'])
            ->whereNumber('child')
            ->name('child.notifications');

        Route::post('/children/{child}/notifications/{id}/read', [ParentChildPortalController::class, 'markNotificationAsRead'])
            ->whereNumber('child')
            ->name('child.notifications.read');

        Route::post('/children/{child}/notifications/read-all', [ParentChildPortalController::class, 'markAllNotificationsAsRead'])
            ->whereNumber('child')
            ->name('child.notifications.read-all');

        // Parent Settings
        Route::get('/settings', [ParentSettingsController::class, 'edit'])->name('settings');
        Route::post('/settings', [ParentSettingsController::class, 'update'])->name('settings.update');

        // Parent Password Change
        Route::get('/password', function () use ($getChildren) {
            $user = User::query()->findOrFail((int) Auth::id());
            $children = $getChildren();

            return view('parent.password', [
                'user' => $user,
                'children' => $children,
            ]);
        })->name('password');

        // Parent Notifications page
        Route::get('/notifications', function () use ($getChildren) {
            $user = User::query()->findOrFail((int) Auth::id());
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
            $notification = User::query()->findOrFail((int) Auth::id())->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();

            return back()->with('success', 'Notification marked as read');
        })->name('notifications.read');

        // Mark all notifications as read
        Route::post('/notifications/read-all', function () {
            User::query()->findOrFail((int) Auth::id())->unreadNotifications->markAsRead();

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
            $user = User::query()->findOrFail((int) Auth::id());
            $notifications = $user->notifications()->latest()->get();

            return view('teacher.notifications', [
                'user' => $user,
                'notifications' => $notifications,
            ]);
        })->name('notifications');

        // Mark notification as read
        Route::post('/notifications/{id}/read', function ($id) {
            $notification = User::query()->findOrFail((int) Auth::id())->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();

            return back()->with('success', 'Notification marked as read');
        })->name('notifications.read');

        // Mark all notifications as read
        Route::post('/notifications/read-all', function () {
            User::query()->findOrFail((int) Auth::id())->unreadNotifications->markAsRead();

            return back()->with('success', 'All notifications marked as read');
        })->name('notifications.read-all');

        // Get available users for new conversations (API endpoint)
        Route::get('/messages/recipients', function () {
            $user = User::query()->findOrFail((int) Auth::id());

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

Route::middleware(['auth', 'verified', EnsureApproved::class, 'role:secretary|admin'])
    ->prefix('secretary')
    ->name('secretary.')
    ->group(function () {
        Route::middleware('permission:registrations.manage')->group(function () {
            Route::get('/registrations', [SecretaryOperationsController::class, 'registrations'])->name('registrations');
            Route::post('/registrations', [SecretaryOperationsController::class, 'storeRegistration'])->name('registrations.store');
        });

        Route::middleware('permission:payments.manage')->group(function () {
            Route::get('/payments', [SecretaryOperationsController::class, 'payments'])->name('payments');
            Route::post('/payments', [SecretaryOperationsController::class, 'storePayment'])->name('payments.store');
        });

        Route::middleware('permission:groups.manage')->group(function () {
            Route::get('/groups', [SecretaryOperationsController::class, 'groups'])->name('groups');
            Route::post('/groups', [SecretaryOperationsController::class, 'storeGroup'])->name('groups.store');
            Route::patch('/groups/{group}', [SecretaryOperationsController::class, 'updateGroup'])
                ->whereNumber('group')
                ->name('groups.update');
            Route::delete('/groups/{group}', [SecretaryOperationsController::class, 'destroyGroup'])
                ->whereNumber('group')
                ->name('groups.destroy');
            Route::post('/groups/enroll', [SecretaryOperationsController::class, 'enrollStudent'])->name('groups.enroll');
        });

        Route::middleware('permission:accounts.manage')->group(function () {
            Route::get('/accounts', [SecretaryOperationsController::class, 'accounts'])->name('accounts');
            Route::patch('/accounts/{account}', [SecretaryOperationsController::class, 'updateAccount'])
                ->whereNumber('account')
                ->name('accounts.update');
            Route::delete('/accounts/{account}', [SecretaryOperationsController::class, 'destroyAccount'])
                ->whereNumber('account')
                ->name('accounts.destroy');
        });

        Route::middleware('permission:announcements.publish')->group(function () {
            Route::get('/publish-notifications', [SecretaryOperationsController::class, 'publishNotifications'])
                ->name('publish-notifications');
            Route::post('/publish-notifications', [SecretaryOperationsController::class, 'sendPublishedNotification'])
                ->name('publish-notifications.send');
        });

        Route::get('/settings', [SecretaryOperationsController::class, 'settings'])->name('settings');
        Route::patch('/settings', [SecretaryOperationsController::class, 'updateSettings'])->name('settings.update');
        Route::patch('/settings/security', [SecretaryOperationsController::class, 'updateSecurity'])->name('settings.security.update');

        Route::get('/notifications', function () {
            $user = User::query()->findOrFail((int) Auth::id());
            $notifications = $user->notifications()->latest()->get();

            return view('secretary.notifications', [
                'user' => $user,
                'notifications' => $notifications,
            ]);
        })->name('notifications');

        Route::post('/notifications/{id}/read', function ($id) {
            $notification = User::query()->findOrFail((int) Auth::id())->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();

            return back()->with('success', 'Notification marked as read');
        })->name('notifications.read');

        Route::post('/notifications/read-all', function () {
            User::query()->findOrFail((int) Auth::id())->unreadNotifications->markAsRead();

            return back()->with('success', 'All notifications marked as read');
        })->name('notifications.read-all');

        Route::get('/messages/recipients', function () {
            $user = User::query()->findOrFail((int) Auth::id());

            $users = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['student', 'parent', 'teacher', 'admin', 'secretary']);
            })
                ->where('id', '!=', $user->id)
                ->whereNotNull('approved_at')
                ->orderBy('name')
                ->get()
                ->map(function ($candidate) {
                    return [
                        'id' => $candidate->id,
                        'name' => $candidate->name,
                        'email' => $candidate->email,
                        'role' => $candidate->roles->isNotEmpty() ? $candidate->roles->first()->name : null,
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
            $user = User::query()->findOrFail((int) Auth::id());
            $notifications = $user->notifications()->latest()->get();

            return view('admin.notifications', [
                'user' => $user,
                'notifications' => $notifications,
            ]);
        })->name('notifications');

        // Mark notification as read
        Route::post('/notifications/{id}/read', function ($id) {
            $notification = User::query()->findOrFail((int) Auth::id())->notifications()->where('id', $id)->firstOrFail();
            $notification->markAsRead();

            return back()->with('success', 'Notification marked as read');
        })->name('notifications.read');

        // Mark all notifications as read
        Route::post('/notifications/read-all', function () {
            User::query()->findOrFail((int) Auth::id())->unreadNotifications->markAsRead();

            return back()->with('success', 'All notifications marked as read');
        })->name('notifications.read-all');

        // Admin can access a page to start new conversations with anyone
        Route::get('/messages/new', function () {
            $user = User::query()->findOrFail((int) Auth::id());

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

        Route::middleware('permission:language-programs.manage')->group(function () {
            Route::post('/programs', [AdminLanguageProgramController::class, 'store'])->name('programs.store');
            Route::patch('/programs/reorder', [AdminLanguageProgramController::class, 'reorder'])->name('programs.reorder');
            Route::patch('/programs/{program}', [AdminLanguageProgramController::class, 'update'])
                ->whereNumber('program')
                ->name('programs.update');
            Route::patch('/programs/{program}/toggle', [AdminLanguageProgramController::class, 'toggleStatus'])
                ->whereNumber('program')
                ->name('programs.toggle');
            Route::patch('/programs/{program}/move/{direction}', [AdminLanguageProgramController::class, 'move'])
                ->whereNumber('program')
                ->whereIn('direction', ['up', 'down'])
                ->name('programs.move');
            Route::delete('/programs/{program}', [AdminLanguageProgramController::class, 'destroy'])
                ->whereNumber('program')
                ->name('programs.destroy');
        });

        Route::middleware('permission:employees.manage')->group(function () {
            Route::get('/employees', [AdminEmployeeController::class, 'index'])->name('employees.index');
            Route::get('/employee-payments', [AdminEmployeePaymentController::class, 'index'])->name('employee-payments.index');
            Route::patch('/employee-payments/{employee}', [AdminEmployeePaymentController::class, 'update'])
                ->whereNumber('employee')
                ->name('employee-payments.update');

            Route::post('/employees/secretaries', [AdminEmployeeController::class, 'storeSecretary'])->name('employees.secretaries.store');
            Route::patch('/employees/secretaries/{secretary}', [AdminEmployeeController::class, 'updateSecretary'])
                ->whereNumber('secretary')
                ->name('employees.secretaries.update');
            Route::delete('/employees/secretaries/{secretary}', [AdminEmployeeController::class, 'destroySecretary'])
                ->whereNumber('secretary')
                ->name('employees.secretaries.destroy');

            Route::post('/employees/teachers', [AdminEmployeeController::class, 'storeTeacher'])->name('employees.teachers.store');
            Route::patch('/employees/teachers/{teacher}', [AdminEmployeeController::class, 'updateTeacher'])
                ->whereNumber('teacher')
                ->name('employees.teachers.update');
            Route::patch('/employees/teachers/{teacher}/assign-language', [AdminEmployeeController::class, 'assignTeacherLanguage'])
                ->whereNumber('teacher')
                ->name('employees.teachers.assign-language');
            Route::delete('/employees/teachers/{teacher}', [AdminEmployeeController::class, 'destroyTeacher'])
                ->whereNumber('teacher')
                ->name('employees.teachers.destroy');
        });

        Route::middleware('permission:courses.manage')->group(function () {
            Route::get('/courses', [AdminCourseController::class, 'index'])->name('courses.index');
            Route::post('/courses', [AdminCourseController::class, 'store'])->name('courses.store');
            Route::patch('/courses/{course}', [AdminCourseController::class, 'update'])
                ->whereNumber('course')
                ->name('courses.update');
            Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy'])
                ->whereNumber('course')
                ->name('courses.destroy');
        });

        Route::middleware('permission:classrooms.manage')->group(function () {
            Route::get('/classrooms', [AdminClassroomController::class, 'index'])->name('classrooms.index');
            Route::post('/classrooms', [AdminClassroomController::class, 'store'])->name('classrooms.store');
            Route::patch('/classrooms/{room}', [AdminClassroomController::class, 'update'])
                ->whereNumber('room')
                ->name('classrooms.update');
            Route::delete('/classrooms/{room}', [AdminClassroomController::class, 'destroy'])
                ->whereNumber('room')
                ->name('classrooms.destroy');
        });

        Route::middleware('permission:schedules.manage')->group(function () {
            Route::get('/schedule', [AdminScheduleController::class, 'index'])->name('schedule.index');
            Route::get('/schedule/timetable-hub', [AdminTimetableHubController::class, 'index'])->name('schedule.timetable-hub');
            Route::post('/schedule', [AdminScheduleController::class, 'store'])->name('schedule.store');
            Route::patch('/schedule/{schedule}', [AdminScheduleController::class, 'update'])
                ->whereNumber('schedule')
                ->name('schedule.update');
            Route::delete('/schedule/{schedule}', [AdminScheduleController::class, 'destroy'])
                ->whereNumber('schedule')
                ->name('schedule.destroy');
        });
    });

Route::get('/pending-approval', function () {
    // If user is already approved, redirect to dashboard
    $user = User::query()->findOrFail((int) Auth::id());
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
        ->middleware('permission:timetables.explore')
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
            ->middleware('permission:approvals.approve.standard|approvals.reject.standard|approvals.approve.office|approvals.reject.office')
            ->name('approvals.index');

        Route::post('/approvals/{user}/approve', [ApprovalController::class, 'approve'])
            ->middleware('permission:approvals.approve.standard|approvals.approve.office')
            ->whereNumber('user')
            ->name('approvals.approve');

        Route::post('/approvals/{user}/reject', [ApprovalController::class, 'reject'])
            ->middleware('permission:approvals.reject.standard|approvals.reject.office')
            ->whereNumber('user')
            ->name('approvals.reject');
    });

Route::middleware([
    'auth',
    'verified',
    EnsureApproved::class,
    'role:admin|secretary',
    'permission:approvals.approve.standard|approvals.reject.standard|approvals.approve.office|approvals.reject.office',
])->group(function () {
    Route::get('/approvals', function (Request $request) {
        return redirect()->route('approvals.index', DashboardRedirector::routeParametersFor($request->user()));
    })->name('approvals.redirect');

    Route::get('/aprovals', function (Request $request) {
        return redirect()->route('approvals.index', DashboardRedirector::routeParametersFor($request->user()));
    });
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
        $notifications = User::query()->findOrFail((int) Auth::id())->notifications()->latest()->get();

        return view('notifications.index', compact('notifications'));
    })->name('notifications.index');

    Route::post('/notifications/{id}/read', function ($id) {
        $n = User::query()->findOrFail((int) Auth::id())->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        return back();
    })->name('notifications.read');
});

require __DIR__.'/settings.php';
