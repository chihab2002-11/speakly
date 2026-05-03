<?php

namespace App\Notifications;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Notifications\Messages\BroadcastMessage;

trait BroadcastsDatabaseNotifications
{
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->broadcastPayload($notifiable));
    }

    /**
     * @return array<string, mixed>
     */
    protected function broadcastPayload(object $notifiable): array
    {
        $data = $this->databaseNotificationData($notifiable);
        $notificationId = property_exists($this, 'id') ? $this->id : null;
        $message = $data['message'] ?? $data['body'] ?? $data['text'] ?? '';
        $url = $data['url'] ?? $data['action_url'] ?? '#';

        return array_merge($data, [
            'id' => $notificationId,
            'notification_id' => $notificationId,
            'title' => $data['title'] ?? $data['type'] ?? 'Notification',
            'message' => $message,
            'body' => $data['body'] ?? $message,
            'text' => $data['text'] ?? $message,
            'type' => $data['type'] ?? static::class,
            'url' => $url,
            'action_url' => $data['action_url'] ?? $url,
            'action' => $data['action'] ?? null,
            'actor_id' => $data['actor_id'] ?? $data['issuer_id'] ?? null,
            'actor_name' => $data['actor_name'] ?? $data['issuer_name'] ?? null,
            'actor_role' => $data['actor_role'] ?? null,
            'related_model' => $data['related_model'] ?? null,
            'related_model_id' => $data['related_model_id'] ?? null,
            'created_at' => $data['created_at'] ?? now()->toIso8601String(),
            'read_at' => null,
            'data' => $data,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function databaseNotificationData(object $notifiable): array
    {
        if (method_exists($this, 'toDatabase')) {
            $databaseMessage = $this->toDatabase($notifiable);
            $data = $databaseMessage->data ?? $databaseMessage;

            if ($data instanceof Arrayable) {
                return $data->toArray();
            }

            return (array) $data;
        }

        return $this->toArray($notifiable);
    }
}
