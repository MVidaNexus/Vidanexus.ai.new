<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateDashboardSettingsRequest;
use App\Services\Dashboard\DashboardViewService;
use App\Services\Dashboard\UserProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardViewService $dashboardView,
        protected UserProfileService $userProfile
    ) {}

    public function index()
    {
        return view('dashboard', $this->dashboardView->buildIndexData(Auth::user()));
    }

    public function updateSettings(UpdateDashboardSettingsRequest $request)
    {
        $this->userProfile->updateFromRequest($request->user(), $request);

        return redirect('/dashboard#settings')->with('success', 'Account settings updated successfully.');
    }

    public function upgrade(Request $request)
    {
        return redirect('/dashboard#billing')->with('info', 'Use the billing section or tool cards to upgrade your plan or unlock tools.');
    }
}
