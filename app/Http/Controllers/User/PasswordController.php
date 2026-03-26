<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('settings.password');
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()
                ->withErrors([
                    'current_password' => __('Praegune parool ei ole õige.'),
                ]);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('status', 'Parool uuendatud');
    }
}