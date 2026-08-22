<?php

namespace App\Mail;

use App\Models\UserFeedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserFeedbackSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public UserFeedback $feedback) {}

    public function build(): self
    {
        return $this
            ->subject('[VidaNexus Feedback] '.($this->feedback->subject ?: 'New message'))
            ->view('emails.feedback-submitted');
    }
}
