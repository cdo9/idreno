<div>
    <h2 class="text-2xl font-bold text-primary mt-4 mb-2 ml-30 sm:ml-0">Argumentations</h2>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center space-x-4 mb-6">
        <select wire:model.live="m" class="rounded p-2">
            <option value="0" {{ $m == 0 ? 'selected' : '' }}>Toute l'année</option>
            @foreach ($months as $name => $number)
                <option value="{{ $number }}" {{ $m == $number ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>

        <select wire:model.live="y" class="rounded p-2">
            @foreach ($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>

        @if($isDirecteur || $isResponsable)
            <select wire:model.live="commercial" class="rounded p-2">
                <option value="">Tous les commerciaux</option>
                @foreach ($lstCommerciaux as $commercial)
                    <option value="{{ $commercial->id }}">
                        {{ $commercial->display_name }}
                    </option>
                @endforeach
            </select>
        @endif
        |
        <livewire:client-select wire:model.live="selectedClientId" />
    </div>

    <!-- Message de confirmation -->
    @if ($message)
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ $message }}
        </div>
    @endif

    <!-- Tableau -->
    <table class="sortable min-w-full text-sm table-fixed">
        <thead class="bg-primary text-white">
        <tr>
            <th class="p-2 rounded-tl-lg w-1/15">Date</th>
            <th class="p-2">Client</th>
            <th class="p-2 w-1/15">Prix devis</th>
            <th class="p-2 w-1/15">Prix tarif</th>
            <th class="p-2 w-1/15">Vendu</th>
            <th class="p-2">Compte rendu</th>
            <th class="p-2">Commerciaux</th>
            <th class="p-2 rounded-tr-lg">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($argus as $argu)
            <tr class="hover:bg-gray-50 odd:bg-gray-200 text-center">
                <td class="p-2">{{ $argu['date'] }}</td>
                <td class="p-2">{{ $argu['client'] }}</td>
                <td class="p-2">{{ $argu['devis'] }} €</td>
                <td class="p-2">{{ $argu['tarif'] }} €</td>
                <td class="p-2 {{ $argu['resultat'] == 'Vendu' ? 'text-green-500' : 'text-red-500' }}">
                    {{ $argu['resultat'] }}
                </td>
                <td class="p-2">{{ $argu['commentaire'] }}</td>
                <td class="p-2">
                    {{ $argu['commercial'] }}
                    @if($argu['partage'])
                        <br>{{ $argu['partage'] }}
                    @endif
                    @if($argu['com3'])
                        <br>{{ $argu['com3'] }}
                    @endif
                </td>
                <td class="text-nowrap p-2">
                    @if(($argu['statut']??1) == 1 || $isAdmin)
                        <a class="p-1 cursor-pointer" href="{{ url('/relances/argumentation/' . $argu['id'] . '/edit') }}">
                            <span class="fa fa-edit text-green-500"></span>
                        </a>
						@if($isAdmin || $isAdministratif)
                        <a class="p-1 cursor-pointer" onclick="if (confirm('Confirmez-vous la suppression ?')) { @this.delete({{ $argu['id'] }}) }">
                            <span class="fa fa-trash text-red-500"></span>
                        </a>
						@endif
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center p-4">Aucune argumentation trouvée.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
    
    <script src="{{ asset('js/tableSorter.js') }}"></script>
</div>
