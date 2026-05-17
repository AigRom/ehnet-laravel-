<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteRegistrationRequest;
use App\Mail\CompleteRegistrationMail;
use App\Models\PendingRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class EmailRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'terms' => ['accepted', 'required'],
        ]);

        $token = Str::random(64);

        PendingRegistration::updateOrCreate(
            ['email' => $data['email']],
            [
                'token' => $token,
                'expires_at' => now()->addHours(24),
            ]
        );

        $link = route('register.complete', $token);

        Mail::to($data['email'])->send(new CompleteRegistrationMail($link));

        return back()
            ->with('status', 'Kinnituse link on saadetud sinu e-posti aadressile.')
            ->withInput($request->only('email'));
    }

    public function showCompleteForm(string $token)
    {
        $pending = PendingRegistration::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $pending) {
            return redirect()
                ->route('register')
                ->with('error', 'See registreerimislink on vigane või aegunud.');
        }

        return view('livewire.auth.complete-registration', [
            'token' => $token,
            'email' => $pending->email,
        ]);
    }

    public function complete(string $token, CompleteRegistrationRequest $request, CreatesNewUsers $creator)
    {
        $pending = PendingRegistration::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $pending) {
            return redirect()
                ->route('register')
                ->with('error', 'See registreerimislink on vigane või aegunud.');
        }

        $data = $request->validated();

        $input = [
            'type' => $data['type'],
            'email' => $pending->email,
            'name' => $data['name'],
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'phone' => $data['phone'],
            'location_id' => (int) $data['location_id'],
            'company_name' => $data['company_name'] ?? null,
            'company_reg_no' => $data['company_reg_no'] ?? null,
            'contact_first_name' => $data['contact_first_name'] ?? null,
            'contact_last_name' => $data['contact_last_name'] ?? null,
            'password' => $data['password'],
            'password_confirmation' => $request->input('password_confirmation'),
            'terms' => true,
        ];

        $user = $creator->create($input);

        $pending->delete();

        auth()->login($user);

        Cookie::queue(cookie(
            name: 'ehnet_last_auth_user_id',
            value: (string) $user->id,
            minutes: 60 * 24 * 30,
            path: '/',
            domain: null,
            secure: null,
            httpOnly: true,
            raw: false,
            sameSite: 'lax'
        ));

        return redirect()->route('dashboard');
    }
}