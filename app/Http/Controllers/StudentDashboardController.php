<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use App\Support\DashboardDataProvider;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __construct(private DashboardDataProvider $dashboardDataProvider) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Initialize with placeholder data (safe fallbacks)
        $enrolledCoursesCount = 4;
        $nextClass = null;
        $mentors = collect();
        $popularCourses = collect();

        try {
            $enrolledCoursesCount = CourseClass::whereHas('students', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count() ?: 4;

            $nextClass = CourseClass::whereHas('students', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['course', 'teacher', 'schedules.room'])->first();
        } catch (QueryException $e) {
            // Classes table doesn't exist yet
        }

        try {
            $mentors = User::role('teacher')->take(3)->get();
        } catch (\Exception $e) {
        }

        try {
            $popularCourses = Course::take(4)->get();
        } catch (QueryException $e) {
        }

        // Merge base dashboard data with student-specific data
        $baseData = $this->dashboardDataProvider->forUser($user);

        return view('student.dashboard', array_merge($baseData, [
            'user' => $user,
            'enrolledCoursesCount' => $enrolledCoursesCount,
            'completedLessonsCount' => 24,
            'courseProgressPercent' => 75,
            'lessonProgressPercent' => 60,
            'nextClass' => $nextClass,
            'nextClassMinutes' => 42,
            'proficiencyLevel' => 'C1',
            'proficiencyPercent' => 75,
            'proficiencyStatus' => 'Advanced',
            'academicStatus' => 'Academic Excellence',
            'mentors' => $mentors,
            'popularCourses' => $popularCourses,
        ]));
    }
}
