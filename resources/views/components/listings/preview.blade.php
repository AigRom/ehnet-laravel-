<div id="listingPreviewModal" class="fixed inset-0 hidden z-50 bg-black/70" aria-hidden="true">
    {{-- Scroll wrapper: kui sisu on pikk, tekib kerimine --}}
    <div class="flex min-h-full w-full items-start justify-center overflow-y-auto p-3 sm:items-center sm:p-6">

        {{-- Card --}}
        <div class="w-full max-w-4xl rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900">

            {{-- Header (sticky, et Sulge oleks alati käepärast) --}}
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-200 bg-white/90 p-4 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/90">
                <div class="text-base font-semibold">{{ __('Kuulutuse eelvaade') }}</div>

                <button
                    type="button"
                    id="closeListingPreview"
                    class="rounded-lg bg-zinc-100 px-3 py-2 text-sm dark:bg-zinc-800"
                >
                    {{ __('Sulge') }}
                </button>
            </div>

            {{-- Body --}}
            <div class="p-4">
                {{-- sama standardvaade, lihtsalt preview režiimis --}}
                <x-listings.detail mode="preview" />
            </div>

            {{-- Footer (sticky) --}}
            <div class="sticky bottom-0 z-10 flex flex-wrap justify-end gap-3 border-t border-zinc-200 bg-white/90 p-4 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/90">
                <button
                    type="button"
                    id="editListing"
                    class="rounded-xl bg-zinc-100 px-4 py-2 dark:bg-zinc-800"
                >
                    {{ __('Muuda') }}
                </button>

                {{-- Mustand --}}
                <button
                    type="submit"
                    name="action"
                    value="draft"
                    class="rounded-xl bg-zinc-100 px-4 py-2 text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100"
                >
                    {{ __('Salvesta mustandina') }}
                </button>

                {{-- Avalda --}}
                <button
                    type="submit"
                    name="action"
                    value="publish"
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:focus:ring-emerald-900/40"
                >
                    {{ __('Lisa kuulutus') }}
                </button>
            </div>

        </div>
    </div>
</div>