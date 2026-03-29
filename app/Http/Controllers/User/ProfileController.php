<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\User\DeleteUserAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Profiili muutmise vaade.
     */
    public function edit(Request $request): View
    {
        return view('settings.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Konto kustutamise vaade.
     */
    public function delete(): View
    {
        return view('settings.delete-account');
    }

    /**
     * Uuendab kasutaja profiiliandmeid ja avatari.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $removeAvatar = (bool) ($validated['remove_avatar'] ?? false);

        unset($validated['avatar'], $validated['remove_avatar']);

        $user->fill($validated);

        if ($removeAvatar) {
            $this->deleteAvatarIfExists($user->avatar_path);
            $user->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            $this->deleteAvatarIfExists($user->avatar_path);

            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return back()->with('status', 'Andmed on edukalt uuendatud.');
    }

    /**
     * Kustutab kasutaja konto teenuse kaudu.
     *
     * Teenus vastutab konto anonümiseerimise / eemaldamise äriloogika eest.
     */
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

    /**
     * Kustutab vana avatari failisüsteemist, kui see on olemas.
     */
    private function deleteAvatarIfExists(?string $avatarPath): void
    {
        if ($avatarPath) {
            Storage::disk('public')->delete($avatarPath);
        }
    }
}