<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;

class TestAuthMail extends Command
{
    protected $signature = 'mail:test-auth
        {email : Email address of an existing user}
        {--type=all : Which case to trigger: verify, reset, or all}
        {--sync : Bypass the queue and send immediately (good for diagnosing SMTP)}';

    protected $description = 'End-to-end probe for auth emails (verify + password reset). Fails if no user with the given email exists.';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $type = strtolower((string) $this->option('type'));
        $sync = (bool) $this->option('sync');

        if (!in_array($type, ['all', 'verify', 'reset'], true)) {
            $this->error("Invalid --type '{$type}'. Use one of: verify, reset, all.");
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("No user found with email '{$email}'. Refusing to send (use a real account).");
            return self::FAILURE;
        }

        $this->line('---- Mail config snapshot ----');
        $this->line('mail.default       : '.config('mail.default'));
        $this->line('mail.from.address  : '.config('mail.from.address'));
        $this->line('mail.from.name     : '.config('mail.from.name'));
        $this->line('queue.default      : '.config('queue.default'));
        $this->line('queue name (notif) : emails');
        $this->line('user.id            : '.$user->id);
        $this->line('user.email_verified: '.($user->email_verified_at ? 'yes ('.$user->email_verified_at.')' : 'no'));
        $this->newLine();

        if ($sync) {
            $this->warn('--sync set: dispatching synchronously (queue connection ignored). SMTP errors will surface here.');
        } else {
            $this->line('Queued mode: ensure a worker is running for queue=emails on connection='.config('queue.default').'.');
        }
        $this->newLine();

        $cases = $type === 'all' ? ['verify', 'reset'] : [$type];
        $exit = self::SUCCESS;

        foreach ($cases as $case) {
            $this->info("→ Triggering '{$case}' for {$user->email}");
            try {
                if ($sync) {
                    $this->sendSync($user, $case);
                } else {
                    $this->sendQueued($user, $case);
                }
                $this->line("  ✓ dispatched ({$case})");
            } catch (\Throwable $e) {
                $this->error("  ✗ failed ({$case}): ".$e->getMessage());
                $exit = self::FAILURE;
            }
        }

        $this->newLine();
        $this->line('Check storage/logs/mail.log for the auth_mail.dispatch entries.');
        $this->line('Check storage/logs/laravel.log (or your queue worker stdout) for actual delivery / failures.');

        return $exit;
    }

    private function sendQueued(User $user, string $case): void
    {
        if ($case === 'verify') {
            $user->sendEmailVerificationNotification();
            return;
        }

        $status = Password::sendResetLink(['email' => $user->email]);
        if ($status !== Password::RESET_LINK_SENT) {
            throw new \RuntimeException('Password broker returned: '.$status);
        }
    }

    private function sendSync(User $user, string $case): void
    {
        if ($case === 'verify') {
            $user->notifyNow(new \App\Notifications\QueuedVerifyEmail);
            return;
        }

        $token = Password::broker()->createToken($user);
        $user->notifyNow(new \App\Notifications\QueuedResetPassword($token));
    }
}
