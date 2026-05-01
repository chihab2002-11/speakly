<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    /**
     * @var list<string>
     */
    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function student(Request $request): JsonResponse
    {
        /** @var User $student */
        $student = $request->user();

        $schedules = $this->baseScheduleQuery()
            ->whereHas('class.students', function (Builder $query) use ($student): void {
                $query->where('users.id', $student->id);
            })
            ->get();

        return $this->scheduleResponse($request, $schedules, [
            'student' => $this->studentPayload($student),
        ]);
    }

    public function parent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'child_id' => ['nullable', 'integer'],
        ]);

        /** @var User $parent */
        $parent = $request->user();

        $childQuery = User::query()
            ->role('student')
            ->where('parent_id', $parent->id)
            ->whereNotNull('approved_at')
            ->orderBy('name');

        if (isset($validated['child_id'])) {
            $childQuery->whereKey((int) $validated['child_id']);
        }

        $child = $childQuery->firstOrFail();

        $schedules = $this->baseScheduleQuery()
            ->whereHas('class.students', function (Builder $query) use ($child): void {
                $query->where('users.id', $child->id);
            })
            ->get();

        return $this->scheduleResponse($request, $schedules, [
            'student' => $this->studentPayload($child),
        ]);
    }

    public function admin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'day' => ['nullable', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
        ]);

        $query = $this->baseScheduleQuery(includeStudents: true)
            ->when(! empty($validated['day']), fn (Builder $query) => $query->where('day_of_week', $validated['day']))
            ->when(! empty($validated['teacher_id']), function (Builder $query) use ($validated): void {
                $query->whereHas('class', fn (Builder $classQuery) => $classQuery->where('teacher_id', $validated['teacher_id']));
            })
            ->when(! empty($validated['student_id']), function (Builder $query) use ($validated): void {
                $query->whereHas('class.students', fn (Builder $studentQuery) => $studentQuery->where('users.id', $validated['student_id']));
            })
            ->when(! empty($validated['class_id']), fn (Builder $query) => $query->where('class_id', $validated['class_id']))
            ->when(! empty($validated['room_id']), fn (Builder $query) => $query->where('room_id', $validated['room_id']));

        return $this->scheduleResponse($request, $query->get(), [
            'filters' => $validated,
        ]);
    }

    private function baseScheduleQuery(bool $includeStudents = false): Builder
    {
        $relationships = [
            'class.course:id,name,code',
            'class.teacher:id,name,email',
            'room:id,name',
        ];

        if ($includeStudents) {
            $relationships[] = 'class.students:id,name,email';
        }

        return Schedule::query()
            ->with($relationships)
            ->orderByRaw("CASE day_of_week WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3 WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6 WHEN 'sunday' THEN 7 END")
            ->orderBy('start_time');
    }

    /**
     * @param  Collection<int, Schedule>  $schedules
     * @param  array<string, mixed>  $meta
     */
    private function scheduleResponse(Request $request, Collection $schedules, array $meta = []): JsonResponse
    {
        $data = ScheduleResource::collection($schedules)->resolve($request);

        return response()->json([
            'data' => $data,
            'grouped_by_day' => $this->groupByDay($data),
            'meta' => array_merge([
                'total' => $schedules->count(),
            ], $meta),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $schedules
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function groupByDay(array $schedules): array
    {
        $grouped = array_fill_keys(self::DAYS, []);

        foreach ($schedules as $schedule) {
            $grouped[$schedule['day_of_week']][] = $schedule;
        }

        return $grouped;
    }

    /**
     * @return array<string, mixed>
     */
    private function studentPayload(User $student): array
    {
        return [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
        ];
    }
}
