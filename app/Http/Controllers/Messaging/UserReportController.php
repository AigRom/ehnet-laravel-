<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\UserReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    /**
     * Salvestab teate kasutaja kohta.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Valideerime sisendi
        $validated = $request->validate([
            'reported_user_id' => ['required', 'exists:users,id'],
            'conversation_id' => ['nullable', 'exists:conversations,id'],
            'reason' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string', 'max:2000'],
        ]);

        // Turvakontroll – ei saa iseenda kohta reportida
        if ((int) $validated['reported_user_id'] === (int) $user->id) {
            return back()->with('error', 'Iseenda kohta ei saa teadet esitada.');
        }

        // Salvestame reporti
        UserReport::create([
            'reporter_id' => $user->id,
            'reported_user_id' => $validated['reported_user_id'],
            'conversation_id' => $validated['conversation_id'] ?? null,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'new',
        ]);

        return back()->with('success', 'Teade saadeti edukalt.');
    }
}