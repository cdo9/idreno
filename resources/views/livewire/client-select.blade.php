<div class="relative">
    <input
        type="text"
        wire:model.live.debounce.300ms="query"
        wire:focus="$set('showDropdown', true)"
        wire:blur="hideDropdown"
        placeholder="{{ $placeholder }}"
        @readonly($isReadonly)
        autocomplete="off"
        class="w-full rounded border px-3 py-2 ml-1"
    >

    @if(!$isReadonly && $query)
        <button
            type="button"
            wire:click="clearClient"
            class="absolute right-2 top-2 text-sm text-gray-500"
        >
            ×
        </button>
    @endif

    @if($showDropdown)
        <div class="absolute z-50 mt-1 w-full rounded border bg-white shadow">
            @forelse($clients as $client)
                <button
                    type="button"
                    wire:key="client-{{ $client['id'] }}"
                    wire:mousedown="selectClient({{ $client['id'] }})"
                    class="block w-full px-3 py-2 text-left hover:bg-gray-100"
                >
                    <div class="font-medium">{{ $client['text'] }}</div>
                    <div class="text-sm text-gray-500">{{ $client['detail'] }}</div>
                </button>
            @empty
                <div class="px-3 py-2 text-sm text-gray-500">
                    Aucun client trouvé
                </div>
            @endforelse
        </div>
    @endif
</div>