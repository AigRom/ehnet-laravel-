<div id="listingPreviewModal" class="fixed inset-0 hidden z-50 bg-black/70" aria-hidden="true">
    {{-- Scroll wrapper: kui sisu on pikk, tekib kerimine --}}
    <div class="min-h-full w-full overflow-y-auto p-3 sm:p-6 flex items-start sm:items-center justify-center">

        {{-- Card --}}
        <div class="w-full max-w-4xl rounded-2xl bg-white dark:bg-zinc-900 shadow-xl border border-zinc-200 dark:border-zinc-800">

            {{-- Header (sticky, et Sulge oleks alati käepärast) --}}
            <div class="sticky top-0 z-10 flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-800
                        bg-white/90 dark:bg-zinc-900/90 backdrop-blur">
                <div class="text-base font-semibold">{{ __('Kuulutuse eelvaade') }}</div>

                <button type="button" id="closeListingPreview"
                        class="text-sm px-3 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    {{ __('Sulge') }}
                </button>
            </div>

            {{-- Body --}}
            <div class="p-4">
                {{-- sama standardvaade, lihtsalt preview režiimis --}}
                <x-listings.detail mode="preview" />
            </div>

            {{-- Footer (sticky) --}}
            <div class="sticky bottom-0 z-10 p-4 border-t border-zinc-200 dark:border-zinc-800 flex flex-wrap gap-3 justify-end
                        bg-white/90 dark:bg-zinc-900/90 backdrop-blur">
                <button type="button" id="editListing"
                        class="px-4 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800">
                    {{ __('Muuda') }}
                </button>

                {{-- Mustand --}}
                <button
                    type="submit"
                    name="action"
                    value="draft"
                    class="px-4 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                >
                    {{ __('Salvesta mustandina') }}
                </button>

                {{-- Avalda --}}
                <flux:button
                    type="submit"
                    variant="primary"
                    name="action"
                    value="publish"
                >
                    {{ __('Lisa kuulutus') }}
                </flux:button>
            </div>

        </div>
    </div>
</div>