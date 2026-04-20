<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->withCount('classes')
            ->orderBy('name')
            ->get();

        $totalCourses = $courses->count();
        $totalClasses = (int) $courses->sum('classes_count');
        $totalTuition = (int) $courses->sum('price');
        $avgCoursePrice = $totalCourses > 0 ? (int) round($totalTuition / $totalCourses) : 0;

        return view('admin.courses', [
            'courses' => $courses,
            'totalCourses' => $totalCourses,
            'totalClasses' => $totalClasses,
            'totalTuition' => $totalTuition,
            'avgCoursePrice' => $avgCoursePrice,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'integer', 'min:1', 'max:100000000'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Course::query()->create([
            'name' => $validated['name'],
            'code' => $this->generateCourseCode($validated['name']),
            'price' => (int) $validated['price'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required', 'integer', 'min:1', 'max:100000000'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $course->update([
            'name' => $validated['name'],
            'price' => (int) $validated['price'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        if ($course->classes()->exists()) {
            return redirect()
                ->route('admin.courses.index')
                ->with('error', 'Cannot delete course with existing classes/schedules.');
        }

        if (
            User::query()->where('requested_course_id', $course->id)->exists()
            || $course->studentTuitions()->exists()
        ) {
            return redirect()
                ->route('admin.courses.index')
                ->with('error', 'Cannot delete course linked to pending or approved student registrations.');
        }

        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    private function generateCourseCode(string $name): string
    {
        $alpha = Str::upper((string) preg_replace('/[^A-Za-z]/', '', $name));
        $prefix = Str::substr($alpha !== '' ? $alpha : 'CRS', 0, 3);

        if (Str::length($prefix) < 3) {
            $prefix = Str::padRight($prefix, 3, 'X');
        }

        $suffix = 1;

        do {
            $candidate = $prefix.str_pad((string) $suffix, 3, '0', STR_PAD_LEFT);
            $suffix++;
        } while (Course::query()->where('code', $candidate)->exists());

        return $candidate;
    }
}
