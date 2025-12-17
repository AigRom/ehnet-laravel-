<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class EmailRegistrationController extends Controller
{
    public function store(Request $request)
    {
        // 1) Valideerime emaili + tingimused
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'terms' => ['accepted', 'required'],
        ]);

        // 2) Loome/uuendame pending-reg kirje
        $token = Str::random(64);

        PendingRegistration::updateOrCreate(
            ['email' => $data['email']],
            [
                'token'      => $token,
                'expires_at' => now()->addHours(24),
            ]
        );

        // 3) Saadame meilile lingi
        $link = route('register.complete', $token);

        Mail::raw(
            "Tere!\n\nKliki alloleval lingil, et lõpetada EHNET konto registreerimine:\n\n{$link}\n\nKui sina ei alustanud registreerimist, ignori seda kirja.",
            function ($message) use ($data) {
                $message
                    ->to($data['email'])
                    ->subject('EHNET – lõpeta registreerimine');
            }
        );

        // 4) Tagastame vormile teate + jätame emaili väljale alles
        return back()
            ->with('status', 'We have emailed you a confirmation link.')
            ->withInput($request->only('email'));
    }

    public function showCompleteForm(string $token)
    {
        $pending = PendingRegistration::where('token', $token)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $pending) {
            return redirect()->route('register')
                ->with('status', 'This registration link is invalid or expired.');
        }

        return view('livewire.auth.complete-registration', [
            'token' => $token,
            'email' => $pending->email,
        ]);
    }

    public function complete(string $token, Request $request, CreatesNewUsers $creator)
    {
        $pending = PendingRegistration::where('token', $token)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $pending) {
            return redirect()->route('register')
                ->with('status', 'This registration link is invalid or expired.');
        }

        // Üks validatsiooniplokk on siin kõige puhtam (required_if reeglitega)
        $data = $request->validate([
            'type' => ['required', Rule::in(['customer', 'business'])],

            // Customer (eraisik)
            'first_name' => ['required_if:type,customer', 'nullable', 'string', 'max:255'],
            'last_name'  => ['required_if:type,customer', 'nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],

            // Business (ettevõte)
            'contact_first_name' => ['required_if:type,business', 'nullable', 'string', 'max:255'],
            'contact_last_name'  => ['required_if:type,business', 'nullable', 'string', 'max:255'],
            'company_name'       => ['required_if:type,business', 'nullable', 'string', 'max:255'],
            'company_reg_no'     => ['required_if:type,business', 'nullable', 'string', 'max:50'],

            // Common (kõigile)
            'phone'       => ['required', 'string', 'max:50'],
            'location_id' => ['required', 'exists:locations,id'],

            'password' => ['required', 'confirmed'],
        ]);

        // Sisend Fortify CreateNewUser actionile
        $input = [
            'type'  => $data['type'],
            'email' => $pending->email,

            'first_name'    => $data['first_name'] ?? null,
            'last_name'     => $data['last_name'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,

            'phone'       => $data['phone'],
            'location_id' => (int) $data['location_id'],

            'company_name'       => $data['company_name'] ?? null,
            'company_reg_no'     => $data['company_reg_no'] ?? null,
            'contact_first_name' => $data['contact_first_name'] ?? null,
            'contact_last_name'  => $data['contact_last_name'] ?? null,

            'password'              => $data['password'],
            'password_confirmation' => $data['password'], // confirmed juba kontrollis
            'terms'                 => true, // 1. sammus aktsepteeritud
        ];

        // Loome kasutaja läbi CreateNewUser actioni
        $user = $creator->create($input);

        // Pending kirje võib kustutada
        $pending->delete();

        // Logime kasutaja sisse ja suuname dashboardile
        auth()->login($user);

        return redirect()->route('dashboard');
    }
}
