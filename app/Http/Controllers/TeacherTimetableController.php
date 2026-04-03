<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class TeacherTimetableController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Only teachers can view their timetable
        abort_unless($request->user()->hasRole('teacher'), 403);

        $teacher = $request->user();

        // ✅ Get all classes the teacher teaches with relationships
        $taughtClasses = $teacher->taughtClasses()
            ->with([
                'course',
                'schedules.room',
            ])
            ->orderBy('created_at')
            ->get();

        // ✅ Build timetable grouped by day of week
        $timetable = $this->buildTimetable($taughtClasses);

        return view('timetable.teacher', [
            'taughtClasses' => $taughtClasses,
            'timetable' => $timetable,
        ]);
    }

    /**
     * Build timetable grouped by day of week for a teacher
     *
     * @param  Collection  $taughtClasses
     * @return array<string, array>
     */
    private function buildTimetable($taughtClasses): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $timetable = array_fill_keys($days, []);

        foreach ($taughtClasses as $class) {
            foreach ($class->schedules as $schedule) {
                $timetable[$schedule->day_of_week][] = [
                    'course_name' => $class->course->name,
                    'course_code' => $class->course->code,
                    'class_id' => $class->id,
                    'room_name' => $schedule->room?->name ?? 'TBA',
                    'room_capacity' => $schedule->room?->capacity ?? 'N/A',
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'student_count' => $class->students()->count(),
                ];
            }
        }

        // Sort each day's schedule by start time
        foreach ($timetable as &$daySchedules) {
            usort($daySchedules, function ($a, $b) {
                return strtotime($a['start_time']) <=> strtotime($b['start_time']);
            });
        }

        return $timetable;
    }
}
