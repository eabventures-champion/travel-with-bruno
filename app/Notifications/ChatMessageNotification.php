<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ChatMessage;

class ChatMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $chatMessage;

    public function __construct(ChatMessage $chatMessage)
    {
        $this->chatMessage = $chatMessage;
    }

    public function via(object $notifiable): array
    {
        // For chat, maybe just database notification is enough to avoid email spam, 
        // but user requested "both email and bell notification" for status.
        // Let's stick to database for chat for now, or add email if it feels right.
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'chat_message_id' => $this->chatMessage->id,
            'sender_id' => $this->chatMessage->sender_id,
            'message' => 'New message: ' . substr($this->chatMessage->message, 0, 50) . '...',
            'type' => 'chat_message',
        ];
    }
}
