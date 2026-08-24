<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserFeedback;
use Illuminate\Http\Request;

class FeedbackAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = UserFeedback::with('user')->latest();

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $feedbacks = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => UserFeedback::count(),
            'this_week' => UserFeedback::where('created_at', '>=', now()->subDays(7))->count(),
            'unique_users' => UserFeedback::distinct('user_id')->count('user_id'),
        ];

        return view('admin.horizon.feedback', compact('feedbacks', 'stats'));
    }

    public function destroy(UserFeedback $feedback)
    {
        $feedback->delete();

        return back()->with('success', 'Feedback entry deleted successfully.');
    }
}
