<?php

namespace App\Http\Resources;

use App\Models\CourseClass;
use App\Models\Room;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Schedule $schedule */
        $schedule = $this->resource;

        /** @var CourseClass|null $courseClass */
        $courseClass = $schedule->relationLoaded('class') ? $schedule->class : null;

        /** @var Room|null $room */
        $room = $schedule->relationLoaded('room') ? $schedule->room : null;

        $class = [
            'id' => $courseClass?->id,
            'capacity' => $courseClass?->capacity,
            'course' => $courseClass?->relationLoaded('course') ? [
                'id' => $courseClass->course?->id,
                'name' => $courseClass->course?->name,
                'code' => $courseClass->course?->code,
            ] : null,
            'teacher' => $courseClass?->relationLoaded('teacher') ? [
                'id' => $courseClass->teacher?->id,
                'name' => $courseClass->teacher?->name,
                'email' => $courseClass->teacher?->email,
            ] : null,
        ];

        if ($courseClass?->relationLoaded('students')) {
            $class['students'] = $courseClass->students
                ->map(fn ($student): array => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                ])
                ->values();
        }

        return [
            'id' => $schedule->id,
            'day_of_week' => $schedule->day_of_week,
            'start_time' => (string) $schedule->start_time,
            'end_time' => (string) $schedule->end_time,
            'room' => $room ? [
                'id' => $room->id,
                'name' => $room->name,
            ] : null,
            'class' => $class,
        ];
    }
}
