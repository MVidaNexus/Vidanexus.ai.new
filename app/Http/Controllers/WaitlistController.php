<?php

namespace App\Http\Controllers;

use App\Models\WaitlistSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Spatie\Honeypot\Http\Middleware\ProtectAgainstSpam;

class WaitlistController extends Controller
{
    public function __construct()
    {
        // Rate limiting and Honeypot protection
        $this->middleware(ProtectAgainstSpam::class);
        $this->middleware('throttle:5,1'); // max 5 per minute per IP
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:waitlist_subscribers,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This email is already on the waitlist or invalid.'
            ], 422);
        }

        WaitlistSubscriber::create([
            'id' => (string) Str::uuid(),
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referral_source' => $request->header('referer'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully added to waitlist!'
        ]);
    }
}
