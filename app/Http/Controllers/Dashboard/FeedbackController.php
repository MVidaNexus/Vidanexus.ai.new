<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Mail\UserFeedbackSubmittedMail;
use App\Models\Setting;
use App\Models\UserFeedback;
use App\Services\Logging\EmailLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function __construct(
        protected EmailLogService $emailLogService
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $user = $request->user();

        $feedback = UserFeedback::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'ip_address' => $request->ip(),
        ]);

        $to = Setting::get('admin_feedback_email', config('mail.from.address'));

        try {
            Mail::to($to)->queue(new UserFeedbackSubmittedMail($feedback));
        } catch (\Throwable $e) {
            Log::channel('mail')->error('Feedback mail failed: '.$e->getMessage(), [
                'feedback_id' => $feedback->id,
            ]);
            $this->emailLogService->logFailure(
                $user->id,
                $to,
                'Feedback submission notification',
                $e->getMessage(),
                ['feedback_id' => $feedback->id]
            );
        }

        return redirect('/dashboard#feedback')->with('success', 'Thank you — your feedback was sent to our team.');
    }
}
