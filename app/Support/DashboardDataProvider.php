<?php

namespace App\Support;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardDataProvider
{
    /**
     * Build dashboard payload while reusing the existing messaging/notification data model.
     *
     * @return array{
     *     unreadMessagesCount:int,
     *     recentConversations:Collection<int, array{user:User,lastMessage:?Message,unreadCount:int}>,
     *     unreadNotificationsCount:int,
     *     unreadNotifications:Collection,
     *     latestNotifications:Collection
     * }
     */
    public function forUser(User $user): array
    {
        $currentUserId = $user->id;

        $conversationPartners = User::query()->whereIn('id', function ($query) use ($currentUserId) {
            $query->selectRaw('DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as user_id', [$currentUserId])
                ->from('messages')
                ->where(function ($innerQuery) use ($currentUserId) {
                    $innerQuery->where('sender_id', $currentUserId)
                        ->orWhere('receiver_id', $currentUserId);
                });
        })
            ->orderBy('name')
            ->get();

        $recentConversations = $conversationPartners
            ->map(function (User $partner) use ($currentUserId): array {
                $lastMessage = Message::query()->whereRaw(
                    '(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)',
                    [$currentUserId, $partner->id, $partner->id, $currentUserId]
                )
                    ->orderBy('created_at', 'desc')
                    ->first();

                $unreadCount = Message::query()
                    ->where('sender_id', $partner->id)
                    ->where('receiver_id', $currentUserId)
                    ->whereNull('read_at')
                    ->count();

                return [
                    'user' => $partner,
                    'lastMessage' => $lastMessage,
                    'unreadCount' => $unreadCount,
                ];
            })
            ->sortByDesc(fn (array $conversation) => $conversation['lastMessage']?->created_at?->getTimestamp() ?? 0)
            ->take(5)
            ->values();

        return [
            'unreadMessagesCount' => Message::query()
                ->where('receiver_id', $currentUserId)
                ->whereNull('read_at')
                ->count(),
            'recentConversations' => $recentConversations,
            'unreadNotificationsCount' => $user->unreadNotifications()->count(),
            'unreadNotifications' => $user->unreadNotifications()->latest()->take(5)->get(),
            'latestNotifications' => $user->notifications()->latest()->take(5)->get(),
        ];
    }
}
