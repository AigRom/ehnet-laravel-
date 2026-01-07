<div id="listingPreviewModal" class="fixed inset-0 hidden z-50 items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-4xl rounded-2xl bg-white dark:bg-zinc-900 shadow-xl overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-800">
            <div class="text-base font-semibold">{{ __('Kuulutuse eelvaade') }}</div>

            <button type="button" id="closeListingPreview"
                    class="text-sm px-3 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                {{ __('Sulge') }}
            </button>
        </div>

        <div class="p-4">
            {{--  sama standardvaade, lihtsalt preview režiimis --}}
            <x-listings.detail mode="preview" />
        </div>

        <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 flex gap-3 justify-end">
            <button type="button" id="editListing"
                    class="px-4 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800">
                {{ __('Muuda') }}
            </button>

            <flux:button type="submit" variant="primary">
                {{ __('Lisa kuulutus') }}
            </flux:button>
        </div>
    </div>
</div>
