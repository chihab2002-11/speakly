<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $assignedClasses = $user->enrolledClasses()
            ->with('course:id,name')
            ->get()
            ->mapWithKeys(function (CourseClass $class): array {
                $label = (string) ($class->course?->name ?: ('Class #'.$class->id));

                return [(int) $class->id => $label];
            });

        if ($assignedClasses->isEmpty()) {
            $message = 'You must be assigned to at least one group to submit a review.';

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 422);
            }

            return back()->withErrors(['review_text' => $message]);
        }

        $rules = [
            'review_text' => ['required', 'string', 'max:1200'],
        ];

        if ($assignedClasses->count() > 1) {
            $rules['class_id'] = ['required', 'integer'];
        }

        $validated = $request->validate($rules);

        $selectedClassId = $assignedClasses->count() === 1
            ? (int) $assignedClasses->keys()->first()
            : (int) ($validated['class_id'] ?? 0);

        if (! $assignedClasses->has($selectedClassId)) {
            $message = 'Selected group is invalid.';

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 422);
            }

            return back()->withErrors(['class_id' => $message]);
        }

        Review::query()->create([
            'student_id' => (int) $user->id,
            'student_name' => (string) $user->name,
            'student_group' => (string) ($assignedClasses->get($selectedClassId) ?? 'Group not set'),
            'review_text' => $validated['review_text'],
            'profile_picture_url' => $user->avatar ?? null,
            'rating_score' => 0.0,
            'likes_count' => 0,
            'dislikes_count' => 0,
            'uploaded_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Your review was submitted successfully.',
            ]);
        }

        return back()
            ->with('success', 'Your review was submitted successfully.');
    }

    public function vote(Request $request, Review $review): JsonResponse
    {
        $validated = $request->validate([
            'vote' => ['required', 'in:like,dislike'],
        ]);

        $voteType = $validated['vote'];
        $votedReviewIds = collect(json_decode((string) $request->cookie('visitor_review_votes', '[]'), true))
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($votedReviewIds->contains((int) $review->id)) {
            return response()->json([
                'status' => 'already_voted',
                'message' => 'You have already rated this review.',
            ], 409);
        }

        $updatedReview = DB::transaction(function () use ($review, $voteType): Review {
            $lockedReview = Review::query()->whereKey($review->id)->lockForUpdate()->firstOrFail();

            $score = (float) $lockedReview->rating_score;

            if ($voteType === 'like') {
                $lockedReview->likes_count++;
                $score += 0.1;
            } else {
                $lockedReview->dislikes_count++;
                $score -= 0.1;
            }

            $lockedReview->rating_score = max(0.0, min(5.0, round($score, 1)));
            $lockedReview->save();

            return $lockedReview;
        });

        $updatedVotedReviewIds = $votedReviewIds
            ->push((int) $review->id)
            ->unique()
            ->values()
            ->all();

        return response()->json([
            'status' => 'ok',
            'rating' => number_format((float) $updatedReview->rating_score, 1),
            'likes' => (int) $updatedReview->likes_count,
            'dislikes' => (int) $updatedReview->dislikes_count,
            'message' => 'Thanks for rating this review.',
        ])->cookie(cookie()->forever('visitor_review_votes', json_encode($updatedVotedReviewIds)));
    }
}
