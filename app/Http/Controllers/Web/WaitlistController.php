<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreWaitlistRequest;
use App\Models\WaitlistSubscriber;
use Illuminate\Support\Str;
use Spatie\Honeypot\Http\Middleware\ProtectAgainstSpam;

class WaitlistController extends Controller
{
    public function __construct()
    {
        $this->middleware(ProtectAgainstSpam::class);
        $this->middleware('throttle:5,1');
    }

    public function store(StoreWaitlistRequest $request): \Illuminate\Http\JsonResponse
    {
        WaitlistSubscriber::create([
            'id' => (string) Str::uuid(),
            'email' => $request->validated('email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referral_source' => $request->header('referer'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully added to waitlist!',
        ]);
    }
}
