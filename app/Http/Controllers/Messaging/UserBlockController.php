<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserBlockController extends Controller
{
    /**
     * Blokeerib teise kasutaja.
     *
     * Mõju:
     * - kasutajad ei saa enam omavahel uusi sõnumeid saata
     * - olemasolev vestlus jääb alles
     * - blokeerimine on ühepoolne tegevus (A blokeerib B)
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        $authUser = $request->user();

        // Ei luba iseennast blokeerida
        if ($authUser->id === $user->id) {
            return back()->with('error', 'Iseennast ei saa blokeerida.');
        }

        // Kui blokk juba eksisteerib, ei tee midagi
        $alreadyBlocked = UserBlock::query()
            ->where('blocker_id', $authUser->id)
            ->where('blocked_user_id', $user->id)
            ->exists();

        if (!$alreadyBlocked) {
            UserBlock::create([
                'blocker_id' => $authUser->id,
                'blocked_user_id' => $user->id,
            ]);
        }

        return back()->with('success', 'Kasutaja on blokeeritud.');
    }

    /**
     * Eemaldab blokeeringu.
     *
     * Mõju:
     * - kasutajad saavad jälle omavahel sõnumeid vahetada
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $authUser = $request->user();

        UserBlock::query()
            ->where('blocker_id', $authUser->id)
            ->where('blocked_user_id', $user->id)
            ->delete();

        return back()->with('success', 'Blokeering eemaldati.');
    }
}