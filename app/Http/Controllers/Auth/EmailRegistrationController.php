<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteRegistrationRequest;
use App\Models\PendingRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class EmailRegistrationController extends Controller
{
    /**
     * Registreerimise 1. samm:
     * küsime e-posti ja tingimustega nõustumise,
     * loome pending registration kirje ning saadame e-kirjaga lingi.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'terms' => ['accepted', 'required'],
        ], [
            'email.required' => 'E-posti aadress on kohustuslik.',
            'email.email' => 'Sisesta korrektne e-posti aadress.',
            'email.max' => 'E-posti aadress on liiga pikk.',
            'terms.accepted' => 'Kasutustingimustega nõustumine on kohustuslik.',
            'terms.required' => 'Kasutustingimustega nõustumine on kohustuslik.',
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

        Mail::raw(
            "Tere!\n\nKliki alloleval lingil, et lõpetada EHNET konto registreerimine:\n\n{$link}\n\nKui sina ei alustanud registreerimist, võid selle kirja tähelepanuta jätta.",
            function ($message) use ($data) {
                $message
                    ->to($data['email'])
                    ->subject('EHNET – lõpeta registreerimine');
            }
        );

        return back()
            ->with('status', 'Kinnituse link on saadetud sinu e-posti aadressile.')
            ->withInput($request->only('email'));
    }

    /**
     * Kuvame registreerimise lõpetamise vormi,
     * kui token on olemas ja ei ole aegunud.
     */
    public function showCompleteForm(string $token)
    {
        $pending = PendingRegistration::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $pending) {
            return redirect()->route('register')
                ->with('status', 'See registreerimislink on vigane või aegunud.');
        }

        return view('livewire.auth.complete-registration', [
            'token' => $token,
            'email' => $pending->email,
        ]);
    }

    /**
     * Registreerimise 2. samm:
     * valideerime kõik andmed, loome kasutaja ja logime ta sisse.
     */
    public function complete(string $token, CompleteRegistrationRequest $request, CreatesNewUsers $creator)
    {
        $pending = PendingRegistration::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $pending) {
            return redirect()->route('register')
                ->with('status', 'See registreerimislink on vigane või aegunud.');
        }

        $data = $request->validated();

        $input = [
            'type' => $data['type'],
            'email' => $pending->email,

            // Kasutajanimi / kuvatav nimi
            'name' => $data['name'],

            // Eraisiku väljad
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,

            // Ühised väljad
            'phone' => '+' . $data['phone'],
            'location_id' => (int) $data['location_id'],

            // Ettevõtte väljad
            'company_name' => $data['company_name'] ?? null,
            'company_reg_no' => $data['company_reg_no'] ?? null,
            'contact_first_name' => $data['contact_first_name'] ?? null,
            'contact_last_name' => $data['contact_last_name'] ?? null,

            // Parool
            'password' => $data['password'],
            'password_confirmation' => $request->input('password_confirmation'),

            // Tingimused olid 1. sammus juba aktsepteeritud
            'terms' => true,
        ];

        $user = $creator->create($input);

        // Pending registration enam ei vaja
        $pending->delete();

        auth()->login($user);

        return redirect()->route('dashboard');
    }
}