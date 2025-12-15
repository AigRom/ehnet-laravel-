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

        // 1) Üldine validatsioon (kõigile) – ainult ühised asjad on required
        $data = $request->validate([
            'type' => ['required', Rule::in(['customer', 'business'])],

            'phone'  => ['required', 'string', 'max:50'],
            'region' => ['required', 'string', 'max:255'],
            'city'   => ['required', 'string', 'max:255'],

            'date_of_birth' => ['nullable', 'date'],

            // eraisik (nõuame all)
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name'  => ['nullable', 'string', 'max:255'],

            // ettevõte (nõuame all)
            'contact_first_name' => ['nullable', 'string', 'max:255'],
            'contact_last_name'  => ['nullable', 'string', 'max:255'],
            'company_name'       => ['nullable', 'string', 'max:255'],
            'company_reg_no'     => ['nullable', 'string', 'max:50'],

            'password' => ['required', 'confirmed'],
        ]);

        // 2) Rollipõhine kohustuslikkus
        if ($data['type'] === 'customer') {
            $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name'  => ['required', 'string', 'max:255'],
            ]);
        }

        if ($data['type'] === 'business') {
            $request->validate([
                'company_name'       => ['required', 'string', 'max:255'],
                'company_reg_no'     => ['required', 'string', 'max:50'],
                'contact_first_name' => ['required', 'string', 'max:255'],
                'contact_last_name'  => ['required', 'string', 'max:255'],
            ]);
        }

        // 3) Arvutame EHNET "name" automaatselt:
        // - eraisik: eesnimi
        // - ettevõte: ettevõtte nimi
        $displayName = $data['type'] === 'business'
            ? $request->input('company_name')
            : $request->input('first_name');

        // 4) Sisend Fortify CreateNewUser actionile
        $input = [
            'type'  => $data['type'],
            'email' => $pending->email,

            'name' => $displayName,

            'first_name'    => $request->input('first_name'),
            'last_name'     => $request->input('last_name'),
            'date_of_birth' => $request->input('date_of_birth'),

            'phone'  => $data['phone'],
            'region' => $data['region'],
            'city'   => $data['city'],

            'company_name'       => $request->input('company_name'),
            'company_reg_no'     => $request->input('company_reg_no'),
            'contact_first_name' => $request->input('contact_first_name'),
            'contact_last_name'  => $request->input('contact_last_name'),

            'password'              => $data['password'],
            'password_confirmation' => $data['password'],
            'terms'                 => true, // 1. sammus aktsepteeritud
        ];

        // 5) Loome kasutaja läbi CreateNewUser actioni
        $user = $creator->create($input);

        // 6) Kustutame pending kirje
        $pending->delete();

        // 7) Logime sisse ja suuname dashboardile
        auth()->login($user);

        return redirect()->route('dashboard');
    }
}
