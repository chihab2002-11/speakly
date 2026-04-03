<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Only students can view their timetable
        abort_unless($request->user()->hasRole('student'), 403);

        $student = $request->user();

        // ✅ Get all classes the student is enrolled in with relationships
        $enrolledClasses = $student->enrolledClasses()
            ->with([
                'course',
                'schedules.room',
                'teacher:id,name,email',
            ])
            ->orderBy('created_at')
            ->get();

        // ✅ Build timetable grouped by day of week
        $timetable = $this->buildTimetable($enrolledClasses);

        return view('timetable.index', [
            'enrolledClasses' => $enrolledClasses,
            'timetable' => $timetable,
        ]);
    }

    /**
     * Build timetable grouped by day of week
     *
     * @param  Collection  $enrolledClasses
     * @return array<string, array>
     */
    private function buildTimetable($enrolledClasses): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $timetable = array_fill_keys($days, []);

        foreach ($enrolledClasses as $class) {
            foreach ($class->schedules as $schedule) {
                $timetable[$schedule->day_of_week][] = [
                    'course_name' => $class->course->name,
                    'course_code' => $class->course->code,
                    'class_id' => $class->id,
                    'room_name' => $schedule->room?->name ?? 'TBA',
                    'teacher_name' => $class->teacher?->name,
                    'teacher_email' => $class->teacher?->email,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
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
