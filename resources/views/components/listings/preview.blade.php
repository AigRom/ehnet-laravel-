<div id="listingPreviewModal"
     class="fixed inset-0 hidden z-50 items-center justify-center bg-black/70 p-4">

    <div class="w-full max-w-3xl rounded-2xl bg-white dark:bg-zinc-900 shadow-xl overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-800">
            <div class="text-base font-semibold">{{ __('Kuulutuse eelvaade') }}</div>

            <button type="button" id="closeListingPreview"
                    class="text-sm px-3 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800">
                {{ __('Sulge') }}
            </button>
        </div>

        <div class="p-4 space-y-4">
            <div>
                <div class="text-sm font-medium mb-2">{{ __('Pildid') }}</div>
                <div id="previewImages" class="grid grid-cols-3 gap-3"></div>
            </div>

            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-800 p-4 space-y-3">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div id="previewTitle" class="text-xl font-semibold"></div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-300">
                            <span id="previewCategory"></span>
                            <span class="mx-2">•</span>
                            <span id="previewLocation"></span>
                        </div>
                    </div>

                    <div class="text-right">
                        <div id="previewPrice" class="text-lg font-semibold"></div>
                        <div class="text-xs text-zinc-500">EUR</div>
                    </div>
                </div>

                <div id="previewDescription"
                     class="prose prose-zinc dark:prose-invert max-w-none whitespace-pre-line">
                </div>
            </div>
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
