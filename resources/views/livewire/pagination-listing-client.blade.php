@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center">
        <div class="flex items-center space-x-2">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 cursor-not-allowed rounded-md">
                    Précédent
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled"
                        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] transition-colors duration-150">
                    Précédent
                </button>
            @endif

            <div class="flex items-center space-x-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-3 py-2 text-sm font-medium text-gray-500">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="px-3 py-2 text-sm font-medium text-white bg-[#77BC23] border border-[#77BC23] rounded-md">
                                    {{ $page }}
                                </span>
                            @else
                                <button wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled"
                                        class=" cursor-pointer px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:border-[#77BC23] focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] transition-colors duration-150">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled"
                        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] transition-colors duration-150 cursor-pointer">
                    Suivant
                </button>
            @else
                <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 cursor-not-allowed rounded-md">
                    Suivant
                </span>
            @endif
        </div>
    </nav>
@endif
