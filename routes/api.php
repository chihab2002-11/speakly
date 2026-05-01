<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TimetableController;
use App\Http\Middleware\EnsureApproved;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', EnsureApproved::class])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/student/timetable', [TimetableController::class, 'student'])
        ->middleware('role:student');

    Route::get('/parent/timetable', [TimetableController::class, 'parent'])
        ->middleware('role:parent');

    Route::get('/admin/timetables', [TimetableController::class, 'admin'])
        ->middleware('role:admin');
});
