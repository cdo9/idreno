<div>
    <div class="flex items-center justify-between flex-wrap bg-white px-4 py-3 border-b border-gray-200 mb-4 shadow-sm rounded-md">
        <div class="flex items-center space-x-3">
            <select wire:model.live="month" id="month" class="border border-gray-300 text-sm rounded-md p-1 focus:ring-primary focus:border-primary">
                @foreach ($months as $num => $name)
                    <option value="{{ $num }}">{{ $name }}</option>
                @endforeach
            </select>

            <select wire:model.live="year" id="year" class="border border-gray-300 text-sm rounded-md p-1 focus:ring-primary focus:border-primary">
                @foreach ($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
            
            <div
                wire:loading
                class="inset-0 z-50 flex items-center justify-center bg-white/70 backdrop-blur"
                aria-live="polite"
                aria-label="Chargement en cours"
            >
                <div class="flex flex-col items-center space-y-2">
                    <svg class="w-8 h-8 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="red" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="flex space-x-2 mb-4">
            <button wire:click="switchTab('chiffres')"
                    class="px-4 py-2 rounded-t-md font-semibold text-sm border-b-2 transition
                        {{ $activeTab === 'chiffres' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-primary cursor-pointer' }}">
                Tableau Chiffres
            </button>

            <button wire:click="switchTab('rdv')"
                    class="px-4 py-2 rounded-t-md font-semibold text-sm border-b-2 transition
                        {{ $activeTab === 'rdv' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-primary cursor-pointer' }}">
                Tableau Rendez-vous
            </button>

            <button wire:click="switchTab('argus')"
                    class="px-4 py-2 rounded-t-md font-semibold text-sm border-b-2 transition
                        {{ $activeTab === 'argus' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-primary cursor-pointer' }}">
                <span class="block md:hidden">Tableau Argus</span>
                <span class="hidden md:block">Tableau Argumentations</span>
            </button>
        </div>
    </div>
            
    @if ($activeTab === 'argus')
        <livewire:tableau-chiffre.tableau-argus  :month="$month" :year="$year"/>
    @endif
    @if ($activeTab === 'chiffres')
        <livewire:tableau-chiffre.tableau-chiffres  :month="$month" :year="$year"/>
    @endif
    @if ($activeTab === 'rdv')
        <livewire:tableau-chiffre.tableau-rdv  :month="$month" :year="$year"/>
    @endif
</div>
