<?php

namespace App\Mail;

use App\Models\PatientHistoryConversation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConversationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $conversation;
    public $senderName;
    public $recipient; // Tumeongeza hii hapa

    public function __construct(PatientHistoryConversation $conversation, $senderName, User $recipient)
    {
        $this->conversation = $conversation;
        $this->senderName = $senderName;
        $this->recipient = $recipient;
    }

    public function build()
    {
        return $this->subject('New Message Regarding Patient History')
                    ->view('emails.conversation_notification');
    }
}