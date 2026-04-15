<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTimetableHubController extends Controller
{
    public function index(Request $request): View
    {
        $mode = in_array((string) $request->query('mode', 'rooms'), ['rooms', 'classes', 'teacher', 'student'], true)
            ? (string) $request->query('mode', 'rooms')
            : 'rooms';

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $teachers = User::query()->role('teacher')->whereNotNull('approved_at')->orderBy('name')->get(['id', 'name']);
        $students = User::query()->role('student')->whereNotNull('approved_at')->orderBy('name')->get(['id', 'name']);
        $classes = CourseClass::query()
            ->with('course:id,name,code')
            ->whereHas('schedules')
            ->orderBy('id')
            ->get(['id', 'course_id']);
        $rooms = Room::query()->orderBy('name')->get(['id', 'name']);

        $selectedTeacherId = $request->integer('teacher_id') > 0 ? $request->integer('teacher_id') : null;
        $selectedStudentId = $request->integer('student_id') > 0 ? $request->integer('student_id') : null;
        $selectedClassId = $request->integer('class_id') > 0 ? $request->integer('class_id') : null;
        $selectedRoomId = $request->integer('room_id') > 0 ? $request->integer('room_id') : null;

        $query = Schedule::query()
            ->with([
                'class.course:id,name,code',
                'class.teacher:id,name',
                'class.students:id,name',
                'room:id,name',
            ]);

        if ($mode === 'teacher') {
            if ($selectedTeacherId !== null) {
                $query->whereHas('class', function ($innerQuery) use ($selectedTeacherId): void {
                    $innerQuery->where('teacher_id', $selectedTeacherId);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($mode === 'student') {
            if ($selectedStudentId !== null) {
                $query->whereHas('class.students', function ($innerQuery) use ($selectedStudentId): void {
                    $innerQuery->where('users.id', $selectedStudentId);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($mode === 'classes') {
            if ($selectedClassId !== null) {
                $query->where('class_id', $selectedClassId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($mode === 'rooms' && $selectedRoomId !== null) {
            $query->where('room_id', $selectedRoomId);
        }

        $schedules = $query
            ->orderByRaw("CASE day_of_week WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3 WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6 WHEN 'sunday' THEN 7 END")
            ->orderBy('start_time')
            ->get();

        $timeSlots = $schedules
            ->sortBy('start_time')
            ->map(fn (Schedule $schedule): array => [
                'key' => (string) $schedule->start_time.'|'.(string) $schedule->end_time,
                'start_time' => (string) $schedule->start_time,
                'end_time' => (string) $schedule->end_time,
            ])
            ->unique('key')
            ->values();

        $indexedSchedules = $schedules->groupBy(fn (Schedule $schedule): string => (string) $schedule->day_of_week.'|'.(string) $schedule->start_time.'|'.(string) $schedule->end_time);

        $scheduleGrid = [];
        foreach ($days as $day) {
            foreach ($timeSlots as $slot) {
                $gridKey = $day.'|'.$slot['start_time'].'|'.$slot['end_time'];
                $scheduleGrid[$day][$slot['key']] = $indexedSchedules->get($gridKey, collect());
            }
        }

        return view('admin.timetable-hub', [
            'mode' => $mode,
            'selectedTeacherId' => $selectedTeacherId,
            'selectedStudentId' => $selectedStudentId,
            'selectedClassId' => $selectedClassId,
            'selectedRoomId' => $selectedRoomId,
            'teachers' => $teachers,
            'students' => $students,
            'classes' => $classes,
            'rooms' => $rooms,
            'visibleDays' => $days,
            'timeSlots' => $timeSlots,
            'scheduleGrid' => $scheduleGrid,
            'totalSchedules' => $schedules->count(),
        ]);
    }
}
