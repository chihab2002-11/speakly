<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class LiveNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => $this->serializeNotification($notification))
            ->values();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeNotification(DatabaseNotification $notification): array
    {
        $data = (array) $notification->data;
        $message = $data['message'] ?? $data['body'] ?? $data['text'] ?? 'You have a new notification.';
        $url = $data['url'] ?? $data['action_url'] ?? '#';

        if (in_array($data['type'] ?? null, ['teacher_group_assigned', 'teacher_group_removed'], true)) {
            $url = route('role.dashboard', ['role' => 'teacher']);
        }

        return [
            'id' => (string) $notification->id,
            'title' => $data['title'] ?? $data['type'] ?? 'Notification',
            'message' => $message,
            'body' => $data['body'] ?? $message,
            'text' => $data['text'] ?? $message,
            'type' => $data['type'] ?? 'notification',
            'url' => $url,
            'action_url' => $data['action_url'] ?? $url,
            'created_at' => $notification->created_at?->toIso8601String(),
            'created_at_label' => $notification->created_at?->diffForHumans(),
            'read_at' => $notification->read_at?->toIso8601String(),
        ];
    }
}
