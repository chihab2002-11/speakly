<?php

namespace Database\Seeders;

use App\Models\CourseClass;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $classes = CourseClass::query()
            ->with('course:id,name')
            ->get();

        $students = User::query()
            ->where(function ($query): void {
                $query->where('requested_role', 'student')
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'student'));
            })
            ->orderBy('id')
            ->take(10)
            ->get();

        if ($students->isEmpty()) {
            return;
        }

        $templates = [
            [
                'rating_score' => 5.0,
                'likes_count' => 50,
                'dislikes_count' => 0,
                'review_text' => 'I reached my target certification faster than expected. The teachers explain everything clearly and give practical feedback every week.',
            ],
            [
                'rating_score' => 4.8,
                'likes_count' => 43,
                'dislikes_count' => 2,
                'review_text' => 'The class structure is excellent and the group sessions keep me motivated. I improved speaking confidence a lot.',
            ],
            [
                'rating_score' => 3.6,
                'likes_count' => 20,
                'dislikes_count' => 4,
                'review_text' => 'Good experience overall with strong materials, but I wish there were more evening slots for practice.',
            ],
            [
                'rating_score' => 2.1,
                'likes_count' => 7,
                'dislikes_count' => 16,
                'review_text' => 'The curriculum is useful but pacing felt too fast for me and I needed more revision support.',
            ],
            [
                'rating_score' => 0.7,
                'likes_count' => 2,
                'dislikes_count' => 28,
                'review_text' => 'I had difficulty with schedule consistency and missed several sessions because of timing conflicts.',
            ],
        ];

        foreach ($templates as $index => $template) {
            $student = $students[$index % $students->count()];
            $class = $classes->isNotEmpty() ? $classes[$index % $classes->count()] : null;
            $groupName = (string) ($class?->course?->name ?? 'General Group');

            Review::query()->updateOrCreate(
                [
                    'student_id' => (int) $student->id,
                    'review_text' => $template['review_text'],
                ],
                [
                    'student_name' => (string) $student->name,
                    'student_group' => $groupName,
                    'profile_picture_url' => $student->avatar ?? null,
                    'rating_score' => $template['rating_score'],
                    'likes_count' => $template['likes_count'],
                    'dislikes_count' => $template['dislikes_count'],
                    'uploaded_at' => now()->subDays($index),
                ]
            );
        }
    }
}
