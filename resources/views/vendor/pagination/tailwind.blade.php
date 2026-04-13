@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Lehekülgede navigeerimine') }}" class="mt-6">
        {{-- Mobile --}}
        <div class="flex items-center justify-center gap-3 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-300 cursor-not-allowed">
                    ‹
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    aria-label="{{ __('Eelmine leht') }}"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-emerald-600 transition hover:bg-emerald-50 hover:border-emerald-200"
                >
                    ‹
                </a>
            @endif

            <span class="text-sm font-medium text-zinc-600">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    aria-label="{{ __('Järgmine leht') }}"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-emerald-600 transition hover:bg-emerald-50 hover:border-emerald-200"
                >
                    ›
                </a>
            @else
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-300 cursor-not-allowed">
                    ›
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:items-center sm:justify-center">
            <div>
                <span class="inline-flex items-center gap-2">
                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <span
                            aria-disabled="true"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-300 cursor-not-allowed"
                        >
                            ‹
                        </span>
                    @else
                        <a
                            href="{{ $paginator->previousPageUrl() }}"
                            rel="prev"
                            aria-label="{{ __('Eelmine leht') }}"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-emerald-600 transition hover:bg-emerald-50 hover:border-emerald-200"
                        >
                            ‹
                        </a>
                    @endif

                    {{-- Elements --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="px-2 text-zinc-400">
                                {{ $element }}
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span
                                        aria-current="page"
                                        class="inline-flex h-10 min-w-[40px] items-center justify-center rounded-xl bg-emerald-600 px-3 text-sm font-semibold text-white"
                                    >
                                        {{ $page }}
                                    </span>
                                @else
                                    <a
                                        href="{{ $url }}"
                                        aria-label="{{ __('Mine lehele :page', ['page' => $page]) }}"
                                        class="inline-flex h-10 min-w-[40px] items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-sm font-medium text-zinc-700 transition hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700"
                                    >
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <a
                            href="{{ $paginator->nextPageUrl() }}"
                            rel="next"
                            aria-label="{{ __('Järgmine leht') }}"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-emerald-600 transition hover:bg-emerald-50 hover:border-emerald-200"
                        >
                            ›
                        </a>
                    @else
                        <span
                            aria-disabled="true"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-300 cursor-not-allowed"
                        >
                            ›
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif