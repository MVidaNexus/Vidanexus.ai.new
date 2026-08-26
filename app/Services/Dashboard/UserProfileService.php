<?php

namespace App\Services\Dashboard;

use App\Http\Requests\Dashboard\UpdateDashboardSettingsRequest;
use App\Models\User;
use App\Services\Media\AvatarUploadService;
use Illuminate\Support\Facades\Hash;

class UserProfileService
{
    public function __construct(
        protected AvatarUploadService $avatarUploadService
    ) {}

    public function updateFromRequest(User $user, UpdateDashboardSettingsRequest $request): void
    {
        if ($request->filled('password')) {
            $user->password = Hash::make($request->validated('password'));
        }

        $user->name = $request->validated('name');
        $user->phone = $request->validated('phone');
        $user->country = $request->validated('country');

        if ($request->boolean('remove_avatar')) {
            $this->avatarUploadService->removeAvatar($user);
        } elseif ($request->hasFile('avatar')) {
            $this->avatarUploadService->uploadAvatar($user, $request->file('avatar'));
        }

        $user->save();
    }
}
