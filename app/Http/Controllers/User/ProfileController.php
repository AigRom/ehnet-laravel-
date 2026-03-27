<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Services\User\DeleteUserAccountService;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('settings.profile', [
            'user' => auth()->user(),
        ]);
    }

    public function delete(): View
    {
        return view('settings.delete-account');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        $removeAvatar = (bool) ($validated['remove_avatar'] ?? false);

        unset($validated['avatar'], $validated['remove_avatar']);

        $user->fill($validated);

        if ($removeAvatar && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return back()->with('status', 'Andmed on edukalt uuendatud.');
    }

    public function destroy(Request $request, DeleteUserAccountService $service): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $service->handle($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}