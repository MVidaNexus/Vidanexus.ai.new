<?php

namespace App\Services\Dashboard;

use App\Http\Requests\Dashboard\UpdateDashboardSettingsRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserProfileService
{
    public function updateFromRequest(User $user, UpdateDashboardSettingsRequest $request): void
    {
        if ($request->filled('password')) {
            $user->password = Hash::make($request->validated('password'));
        }

        $user->name = $request->validated('name');
        $user->phone = $request->validated('phone');
        $user->country = $request->validated('country');
        $user->save();
    }
}
