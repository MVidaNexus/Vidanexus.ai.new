<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendMassEmailCampaignJob;
use App\Mail\CampaignCustomMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailCampaignController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'low_balance_users' => User::whereHas('wallet', fn($q) => $q->where('balance_credits', '<', 5))->count(),
            'zero_balance_users' => User::whereHas('wallet', fn($q) => $q->where('balance_credits', '<=', 0))->count(),
            'active_recently' => User::where('updated_at', '>=', now()->subDays(30))->count(),
        ];

        $defaultTemplates = $this->getDefaultCampaignTemplates();

        return view('admin.horizon.email-campaigns', compact('stats', 'defaultTemplates'));
    }

    public function estimateAudience(Request $request): JsonResponse
    {
        $audienceType = $request->input('audience_type', 'all');
        $threshold = (float) $request->input('balance_threshold', 5);

        $count = 0;
        $sample = [];

        switch ($audienceType) {
            case 'all':
                $count = User::count();
                $sample = User::select('name', 'email')->latest()->take(5)->get()->toArray();
                break;

            case 'low_balance':
                $query = User::whereHas('wallet', fn($q) => $q->where('balance_credits', '<', $threshold));
                $count = $query->count();
                $sample = $query->with('wallet:id,user_id,balance_credits')->latest()->take(5)->get()->map(fn($u) => [
                    'name' => $u->name,
                    'email' => $u->email,
                    'balance' => $u->wallet->balance_credits ?? 0,
                ])->toArray();
                break;

            case 'custom_list':
                $emails = $this->parseCustomEmailInput($request->input('custom_emails', ''));
                $count = count($emails);
                $sample = array_slice($emails, 0, 5);
                break;
        }

        return response()->json([
            'success' => true,
            'count' => $count,
            'sample' => $sample,
        ]);
    }

    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'test_email' => 'nullable|email',
        ]);

        $testEmail = $validated['test_email'] ?: ($request->user()->email ?: 'info@vidanexus.net');
        $name = $request->user()->name ?: 'Admin';
        $siteUrl = config('app.url', 'https://vidanexus.ai');
        $appName = config('app.name', 'VidaNexus AI');

        $parsedHtml = str_replace(
            ['{name}', '{email}', '{balance}', '{site_url}', '{app_name}', '{{ $name }}', '{{ $email }}', '{{ $balance }}'],
            [$name, $testEmail, '150.00', $siteUrl, $appName, $name, $testEmail, '150.00'],
            $validated['content']
        );

        $parsedSubject = '[TEST PREVIEW] ' . str_replace(
            ['{name}', '{balance}', '{app_name}'],
            [$name, '150.00', $appName],
            $validated['subject']
        );

        try {
            Mail::to($testEmail)->send(new CampaignCustomMail($parsedSubject, $parsedHtml, [
                'name' => $name,
                'email' => $testEmail,
                'balance' => 150.00,
            ]));

            return response()->json([
                'success' => true,
                'message' => "Test email delivered successfully to {$testEmail}!",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Failed to send test email: " . $e->getMessage(),
            ], 422);
        }
    }

    public function sendCampaign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'audience_type' => 'required|string|in:all,low_balance,custom_list',
            'balance_threshold' => 'nullable|numeric|min:0',
            'custom_emails' => 'nullable|string',
            'csv_file' => 'nullable|file|mimes:txt,csv|max:5120',
        ]);

        $recipients = [];
        $audienceType = $validated['audience_type'];

        if ($audienceType === 'all') {
            $users = User::with('wallet:id,user_id,balance_credits')->select('id', 'name', 'email')->get();
            foreach ($users as $u) {
                if (filter_var($u->email, FILTER_VALIDATE_EMAIL)) {
                    $recipients[] = [
                        'email' => $u->email,
                        'name' => $u->name,
                        'balance' => $u->wallet->balance_credits ?? 0,
                    ];
                }
            }
        } elseif ($audienceType === 'low_balance') {
            $threshold = (float) ($validated['balance_threshold'] ?? 5);
            $users = User::whereHas('wallet', fn($q) => $q->where('balance_credits', '<', $threshold))
                ->with('wallet:id,user_id,balance_credits')
                ->select('id', 'name', 'email')
                ->get();

            foreach ($users as $u) {
                if (filter_var($u->email, FILTER_VALIDATE_EMAIL)) {
                    $recipients[] = [
                        'email' => $u->email,
                        'name' => $u->name,
                        'balance' => $u->wallet->balance_credits ?? 0,
                    ];
                }
            }
        } elseif ($audienceType === 'custom_list') {
            $parsedText = $this->parseCustomEmailInputDetailed($validated['custom_emails'] ?? '');
            $allCandidates = $parsedText['recipients'];
            $totalRawCount = $parsedText['total_raw'];

            if ($request->hasFile('csv_file')) {
                $fileContent = file_get_contents($request->file('csv_file')->getRealPath());
                $parsedFile = $this->parseCustomEmailInputDetailed($fileContent);
                $allCandidates = array_merge($allCandidates, $parsedFile['recipients']);
                $totalRawCount += $parsedFile['total_raw'];
            }

            // Deduplicate by email
            $seen = [];
            foreach ($allCandidates as $r) {
                $em = strtolower($r['email']);
                if (!isset($seen[$em])) {
                    $seen[$em] = true;
                    $recipients[] = $r;
                }
            }

            $duplicatesCount = $totalRawCount - count($recipients);
            if ($duplicatesCount > 0) {
                $statsNote = " ({$duplicatesCount} duplicate entries automatically merged)";
            }
        }

        if (empty($recipients)) {
            return back()->withErrors(['audience' => 'No valid email addresses were found in the provided list.'])->withInput();
        }

        $totalRecipients = count($recipients);

        // For lists <= 30 recipients, send synchronously for instant confirmation
        if ($totalRecipients <= 30) {
            foreach ($recipients as $recipient) {
                SendMassEmailCampaignJob::dispatchSync([$recipient], $validated['subject'], $validated['content']);
            }
            return redirect()->route('admin.horizon.email-campaigns.index')
                ->with('success', "Campaign sent successfully! {$totalRecipients} emails delivered immediately{$statsNote}.");
        }

        // For larger lists, send first batch (25 emails) immediately and queue remaining batches in background
        $firstBatch = array_slice($recipients, 0, 25);
        $remaining = array_slice($recipients, 25);

        SendMassEmailCampaignJob::dispatchSync($firstBatch, $validated['subject'], $validated['content']);

        $chunks = array_chunk($remaining, 25);
        foreach ($chunks as $chunk) {
            SendMassEmailCampaignJob::dispatch($chunk, $validated['subject'], $validated['content']);
        }

        return redirect()->route('admin.horizon.email-campaigns.index')
            ->with('success', "Campaign started! {$totalRecipients} unique recipients queued{$statsNote}. (First 25 delivered immediately, remaining being dispatched in batches).");
    }

    private function parseCustomEmailInput(string $input): array
    {
        return $this->parseCustomEmailInputDetailed($input)['recipients'];
    }

    /**
     * Advanced sanitizer handling CSV lines, RTL Unicode marks, accidental spaces, trailing dots, etc.
     */
    private function parseCustomEmailInputDetailed(string $input): array
    {
        // Split by newlines first
        $lines = preg_split('/[\r\n]+/', $input);
        $recipients = [];
        $seen = [];
        $totalRaw = 0;

        foreach ($lines as $line) {
            // Split further if multiple emails exist per line separated by commas or semicolons
            $tokens = preg_split('/[,;]+/', $line);

            foreach ($tokens as $token) {
                // Strip invisible unicode marks (LTR/RTL \u200e \u200f, Zero-width space \u200b, BOM \ufeff, Non-breaking space \u00a0)
                $clean = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{200E}\x{200F}\x{00A0}]/u', '', $token);
                $clean = trim($clean, " \t\n\r\0\x0B,.'\"<>");

                // Fix accidental spaces around '@' (e.g. 'user @gmail.com' or 'user@ gmail.com')
                $clean = preg_replace('/\s*@\s*/', '@', $clean);
                // Strip trailing dots
                $clean = rtrim($clean, '.');

                if (empty($clean)) {
                    continue;
                }

                $totalRaw++;

                // Check for "Name <email>" format
                if (preg_match('/^(.*?)\s*<([^>]+)>$/', $clean, $m)) {
                    $name = trim($m[1], " \"'");
                    $email = trim($m[2]);
                } else {
                    $email = $clean;
                    $name = 'User';
                }

                $email = trim($email, " \t\n\r\0\x0B,.'\"<>");
                $email = rtrim($email, '.');

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $lower = strtolower($email);
                    if (!isset($seen[$lower])) {
                        $seen[$lower] = true;
                        $recipients[] = [
                            'email' => $email,
                            'name' => $name,
                            'balance' => 0,
                        ];
                    }
                }
            }
        }

        return [
            'recipients' => $recipients,
            'total_raw' => $totalRaw,
        ];
    }

    private function getDefaultCampaignTemplates(): array
    {
        return [
            [
                'id' => 'announcement',
                'name' => '🚀 Platform Announcement / Update',
                'subject' => 'Exciting New Features & Updates on VidaNexus AI!',
                'html' => $this->buildBaseHtmlTemplate(
                    'Platform Announcement',
                    'Hello {name},',
                    'We are thrilled to announce powerful new updates and AI intelligence tools designed to accelerate your content creation and viral search tracking.',
                    'Explore the New Tools',
                    '{site_url}/dashboard'
                ),
            ],
            [
                'id' => 'low_balance',
                'name' => '💳 Low Credits Recharge Reminder',
                'subject' => 'Keep Your AI Workflow Running — Your Balance is Low',
                'html' => $this->buildBaseHtmlTemplate(
                    'Credit Balance Alert',
                    'Hello {name},',
                    'Your current wallet balance is <strong>{balance} Credits</strong>. To ensure uninterrupted access to AI Keyword Radar and Pro Article Writer, recharge your account today with our special bonus packages.',
                    'Top-Up My Wallet Now',
                    '{site_url}/dashboard#billing'
                ),
            ],
            [
                'id' => 'custom',
                'name' => '📝 Minimal Clean Newsletter',
                'subject' => 'Important Message from VidaNexus AI',
                'html' => $this->buildBaseHtmlTemplate(
                    'VidaNexus Newsletter',
                    'Hello {name},',
                    'Write your custom marketing message or company news here.',
                    'Open Dashboard',
                    '{site_url}/dashboard'
                ),
            ],
        ];
    }

    private function buildBaseHtmlTemplate(string $badge, string $heading, string $body, string $btnText, string $btnUrl): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { margin: 0; padding: 0; background-color: #0b0f17; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; color: #f1f5f9; }
        .card { max-width: 580px; margin: 30px auto; background-color: #111827; border: 1px solid #1e293b; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .btn { display: inline-block; padding: 14px 34px; background: linear-gradient(135deg, #0ea5e9, #6366f1); color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 800; border-radius: 12px; }
    </style>
</head>
<body style="background-color: #0b0f17; padding: 20px 10px;">
    <div class="card">
        <div style="padding: 30px 25px; background: linear-gradient(135deg, #0b1120 0%, #1e1b4b 100%); text-align: center; border-bottom: 1px solid #1f2937;">
            <div style="display: inline-block; padding: 6px 16px; background: rgba(14,165,233,0.1); border: 1px solid rgba(14,165,233,0.3); border-radius: 20px; font-size: 12px; font-weight: 800; color: #38bdf8; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 10px;">✦ ' . $badge . '</div>
            <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #ffffff;">VidaNexus AI</h1>
        </div>
        <div style="padding: 35px 30px;">
            <p style="margin: 0 0 16px; font-size: 16px; color: #e2e8f0; font-weight: 700;">' . $heading . '</p>
            <p style="margin: 0 0 26px; font-size: 15px; line-height: 25px; color: #94a3b8;">' . $body . '</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . $btnUrl . '" class="btn" target="_blank">' . $btnText . ' →</a>
            </div>
            <p style="margin: 30px 0 0; font-size: 13px; color: #64748b; border-top: 1px solid #1e293b; padding-top: 20px;">
                You received this email because you are a registered member of VidaNexus AI.
            </p>
        </div>
        <div style="padding: 20px; text-align: center; background-color: #0d131f; border-top: 1px solid #1e293b; font-size: 11px; color: #475569;">
            © ' . date('Y') . ' VidaNexus AI. All rights reserved. • <a href="https://vidanexus.ai" style="color: #64748b; text-decoration: none;">vidanexus.ai</a>
        </div>
    </div>
</body>
</html>';
    }
}
