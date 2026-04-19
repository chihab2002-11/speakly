<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecretaryTimetableController extends Controller
{
    /**
     * Display the secretary timetable explorer with filtering capabilities.
     *
     * @return View
     */
    public function index(Request $request)
    {
        // 🔍 Start from Schedule model with eager loading
        $query = Schedule::with([
            'class.course',
            'class.teacher',
            'room',
        ]);

        // 🏷️ Apply filters only if request has values

        // Filter by teacher_id
        if ($request->filled('teacher_id')) {
            $query->whereHas('class', function ($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id);
            });
        }

        // Filter by class_id
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by course_id
        if ($request->filled('course_id')) {
            $query->whereHas('class', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        // Filter by room_id
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by day_of_week
        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        // Filter by student_id (student enrolled in the class)
        if ($request->filled('student_id')) {
            $query->whereHas('class', function ($q) use ($request) {
                $q->whereHas('students', function ($studentQuery) use ($request) {
                    $studentQuery->where('users.id', $request->student_id);
                });
            });
        }

        // 📋 Order schedules chronologically by day_of_week, then by start_time
        $schedules = $query
            ->orderByRaw("CASE day_of_week WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3 WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6 WHEN 'sunday' THEN 7 END")
            ->orderBy('start_time')
            ->get();

        // 🗓️ Group results by day_of_week
        $groupedSchedules = $schedules->groupBy('day_of_week');

        // 📊 Prepare dropdown data for filters
        $teachers = User::role('teacher')->orderBy('name')->get();
        $students = User::role('student')->orderBy('name')->get();
        $courses = Course::orderBy('name')->get();
        $classes = CourseClass::with('course', 'teacher')->orderBy('id')->get();
        $rooms = Room::orderBy('name')->get();

        return view('secretary.timetable.index', [
            'groupedSchedules' => $groupedSchedules,
            'teachers' => $teachers,
            'students' => $students,
            'courses' => $courses,
            'classes' => $classes,
            'rooms' => $rooms,
        ]);
    }
}
