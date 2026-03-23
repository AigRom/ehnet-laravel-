<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    /**
     * Kuvab kasutaja vestluste listi.
     *
     * Desktopis kasutatakse sama vaadet ka aktiivse vestluse kõrvale kuvamiseks.
     * Mobiilis näidatakse siin ainult vestluste nimekirja.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->with([
                'listing',
                'listing.images',
                'seller:id,name,created_at',
                'buyer:id,name,created_at',
                'latestMessage.sender:id,name',
            ])
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($user) {
                    // Loeme lugemata ainult teise osapoole sõnumid
                    $query->whereNull('read_at')
                        ->where('sender_id', '!=', $user->id);
                },
            ])
            // Näitame ainult vestlusi, mida kasutaja ei ole peitnud:
            // - kui kasutaja on seller, siis seller_hidden_at peab olema null
            // - kui kasutaja on buyer, siis buyer_hidden_at peab olema null
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('seller_id', $user->id)
                        ->whereNull('seller_hidden_at');
                })->orWhere(function ($q) use ($user) {
                    $q->where('buyer_id', $user->id)
                        ->whereNull('buyer_hidden_at');
                });
            })
            ->latest('updated_at')
            ->get();

        // Desktopi jaoks võtame esimese vestluse aktiivseks,
        // et paremas veerus oleks midagi kohe näidata
        $activeConversation = $conversations->first();

        return view('user.messages.index', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
        ]);
    }

    /**
     * Kuvab ühe konkreetse vestluse.
     *
     * Tingimused:
     * - kasutaja peab olema vestluse osaline
     * - vestlus ei tohi olla tema jaoks peidetud
     */
    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();

        // Kontrollime, et kasutaja kuulub sellesse vestlusesse
        abort_unless(
            $conversation->hasParticipant($user),
            404
        );

        // Kui kasutaja on vestluse enda jaoks peitnud,
        // siis tava-UI kaudu seda avada ei saa
        abort_if(
            $conversation->isHiddenFor($user),
            404
        );

        // Märgime loetuks ainult teise osapoole lugemata sõnumid
        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->update([
                'read_at' => now(),
            ]);

        // Laeme aktiivse vestluse detailid
        $conversation->load([
            'listing.images',
            'seller:id,name,created_at',
            'buyer:id,name,created_at',
            'messages.sender:id,name',
            'messages.attachments',
        ]);

        // Laeme vasaku veeru jaoks uuesti ainult nähtavad vestlused
        $conversations = Conversation::query()
            ->with([
                'listing',
                'listing.images',
                'seller:id,name,created_at',
                'buyer:id,name,created_at',
                'latestMessage.sender:id,name',
            ])
            ->withCount([
                'messages as unread_messages_count' => function ($query) use ($user) {
                    // Loeme lugemata ainult teise osapoole sõnumid
                    $query->whereNull('read_at')
                        ->where('sender_id', '!=', $user->id);
                },
            ])
            // Näitame ainult vestlusi, mida kasutaja ei ole peitnud:
            // - kui kasutaja on seller, siis seller_hidden_at peab olema null
            // - kui kasutaja on buyer, siis buyer_hidden_at peab olema null
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('seller_id', $user->id)
                        ->whereNull('seller_hidden_at');
                })->orWhere(function ($q) use ($user) {
                    $q->where('buyer_id', $user->id)
                        ->whereNull('buyer_hidden_at');
                });
            })
            ->latest('updated_at')
            ->get();

        return view('user.messages.show', [
            'conversation' => $conversation,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Avab vestluse kuulutuse detailvaatest.
     *
     * Kui vestlus juba eksisteerib, kasutame sama vestlust edasi.
     * Kui kasutaja oli selle vestluse varem peitnud, taastame nähtavuse talle uuesti.
     */
    public function openFromListing(Request $request, Listing $listing): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user, 403);

        // Omaenda kuulutusele ei saa sõnumit saata
        if ($user->id === $listing->user_id) {
            return back()->with('error', 'Enda kuulutusele ei saa sõnumit saata.');
        }

        // Leiame olemasoleva vestluse või loome uue
        $conversation = Conversation::firstOrCreate([
            'listing_id' => $listing->id,
            'seller_id' => $listing->user_id,
            'buyer_id' => $user->id,
        ]);

        // Kui kasutaja oli vestluse varem peitnud,
        // teeme selle talle uuesti nähtavaks
        $conversation->unhideFor($user);

        return redirect()->route('messages.show', $conversation);
    }

    /**
     * Peidab vestluse ainult kasutaja vaatest.
     *
     * Vestlust ei kustutata andmebaasist päriselt ära.
     * Selle asemel täidetakse vastav hidden_at väli:
     * - seller_hidden_at või
     * - buyer_hidden_at
     *
     * Kui mõlemad pooled on vestluse peitnud, määratakse ka fully_hidden_at.
     */
    public function destroy(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        // Kontrollime, et kasutaja kuulub sellesse vestlusesse
        abort_unless(
            $conversation->hasParticipant($user),
            404
        );

        // Kui vestlus on juba peidetud, ei ole vaja midagi uuesti teha
        if (!$conversation->isHiddenFor($user)) {
            $conversation->hideFor($user);
        }

        // Pärast peitmist viime kasutaja tagasi vestluste nimekirja
        return redirect()
            ->route('messages.index')
            ->with('success', 'Vestlus peideti sinu vaatest.');
    }
}