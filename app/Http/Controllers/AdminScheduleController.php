<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $day = strtolower((string) $request->query('day', ''));
        $teacherId = (int) $request->query('teacher_id', 0);
        $classId = (int) $request->query('class_id', 0);
        $roomId = (int) $request->query('room_id', 0);

        $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        if (! in_array($day, $validDays, true)) {
            $day = '';
        }

        $query = Schedule::query()
            ->with([
                'class.course:id,name,code',
                'class.teacher:id,name',
                'room:id,name',
            ])
            ->when($day !== '', fn ($q) => $q->where('day_of_week', $day))
            ->when($roomId > 0, fn ($q) => $q->where('room_id', $roomId))
            ->when($classId > 0, fn ($q) => $q->where('class_id', $classId))
            ->when($teacherId > 0, function ($q) use ($teacherId): void {
                $q->whereHas('class', fn ($classQuery) => $classQuery->where('teacher_id', $teacherId));
            });

        $schedules = $query
            ->orderByRaw("CASE day_of_week WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3 WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6 WHEN 'sunday' THEN 7 END")
            ->orderBy('start_time')
            ->paginate(20)
            ->withQueryString();

        return view('admin.schedule', [
            'schedules' => $schedules,
            'teachers' => User::query()->role('teacher')->orderBy('name')->get(['id', 'name']),
            'groups' => CourseClass::query()
                ->with(['course:id,name,code', 'teacher:id,name'])
                ->orderBy('id')
                ->get(['id', 'course_id', 'teacher_id', 'capacity']),
            'rooms' => Room::query()->orderBy('name')->get(['id', 'name']),
            'selectedDay' => $day,
            'selectedTeacherId' => $teacherId,
            'selectedClassId' => $classId,
            'selectedRoomId' => $roomId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSchedulePayload($request);

        $class = CourseClass::query()
            ->with('teacher:id')
            ->findOrFail((int) $validated['class_id']);

        $this->assertNoSchedulingConflicts($validated, $class->teacher_id !== null ? (int) $class->teacher_id : null);

        Schedule::query()->create([
            'class_id' => $class->id,
            'room_id' => $validated['room_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return redirect()
            ->route('admin.schedule.index')
            ->with('success', 'Schedule slot created successfully.');
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $this->validateSchedulePayload($request);

        $class = CourseClass::query()
            ->with('teacher:id')
            ->findOrFail((int) $validated['class_id']);

        $this->assertNoSchedulingConflicts(
            $validated,
            $class->teacher_id !== null ? (int) $class->teacher_id : null,
            $schedule->id
        );

        $schedule->update([
            'class_id' => $class->id,
            'room_id' => $validated['room_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return redirect()
            ->route('admin.schedule.index')
            ->with('success', 'Schedule slot updated successfully.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()
            ->route('admin.schedule.index')
            ->with('success', 'Schedule slot deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSchedulePayload(Request $request): array
    {
        return $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'day_of_week' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertNoSchedulingConflicts(array $payload, ?int $teacherId = null, ?int $ignoreScheduleId = null): void
    {
        $baseConflictQuery = Schedule::query()
            ->where('day_of_week', $payload['day_of_week'])
            ->where('start_time', '<', $payload['end_time'])
            ->where('end_time', '>', $payload['start_time']);

        if ($ignoreScheduleId !== null) {
            $baseConflictQuery->where('id', '!=', $ignoreScheduleId);
        }

        if (! empty($payload['room_id'])) {
            $roomConflict = (clone $baseConflictQuery)
                ->where('room_id', $payload['room_id'])
                ->exists();

            if ($roomConflict) {
                throw ValidationException::withMessages([
                    'room_id' => 'Room is already booked for this time range.',
                ]);
            }
        }

        $classConflict = (clone $baseConflictQuery)
            ->where('class_id', (int) $payload['class_id'])
            ->exists();

        if ($classConflict) {
            throw ValidationException::withMessages([
                'class_id' => 'This group already has a schedule in this time range.',
            ]);
        }

        if ($teacherId !== null) {
            $teacherConflict = (clone $baseConflictQuery)
                ->whereHas('class', function ($query) use ($teacherId): void {
                    $query->where('teacher_id', $teacherId);
                })
                ->exists();

            if ($teacherConflict) {
                throw ValidationException::withMessages([
                    'class_id' => 'Selected group teacher is already booked for this time range.',
                ]);
            }
        }
    }
}
