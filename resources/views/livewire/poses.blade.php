<div>
    <!-- Header -->
    <div class="flex flex-col lg:justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-primary mt-4 mb-2">Gestion des Poses</h2>
            <p class="text-gray-600 mb-6">Planification et suivi des interventions</p>

            <!-- Filtres -->
            <div class="flex items-center space-x-4 mb-6">
                <input
                    type="date"
                    wire:model.live="dateDebut"
                    class="rounded p-2 border border-gray-300"
                >
                <input
                    type="date"
                    wire:model.live="dateFin"
                    class="rounded p-2 border border-gray-300"
                >
                @if($isDirecteur || $isTelepro)
                    <select wire:model.live="commercial" class="rounded p-2 border border-gray-300">
                        <option value="">Tous les commerciaux</option>
                        @foreach($lstCommerciaux as $commercialItem)
                            <option value="{{ $commercialItem->id }}">
                                {{ $commercialItem->display_name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        <!-- Pourcentages Suivi -->
        <div class="flex space-x-3 text-sm mt-4">
            <div class="px-3 py-2 rounded-lg bg-lightprimary text-white font-semibold shadow">
                Visites : {{ $this->getSuiviPercentage('visite_chantier') }}%
            </div>
            <div class="px-3 py-2 rounded-lg bg-lightprimary text-white font-semibold shadow">
                Avis Client : {{ $this->getSuiviPercentage('avis_client') }}%
            </div>
            <div class="px-3 py-2 rounded-lg bg-lightprimary text-white font-semibold shadow">
                Panneau chantier : {{ $this->getSuiviPercentage('avis_facebook') }}%
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <table class="sortable min-w-full text-xs lg:text-sm table-fixed mt-4">
        <thead class="text-white">
            <tr>
                <th class="bg-primary lg:p-2 rounded-tl-lg">Client</th>
                <th class="bg-primary lg:p-2">Adresse</th>
                <th class="bg-primary lg:p-2">Période</th>
                <th class="bg-primary lg:p-2">Produits</th>
                <th class="bg-primary lg:p-2">Poseurs</th>
                @if(!$isTelepro)
                    <th class="bg-primary lg:p-2">Commerciaux</th>
                @endif
                <th class="bg-primary lg:p-2">Visite chantier</th>
                <th class="bg-primary lg:p-2">Avis client</th>
                <th class="bg-primary lg:p-2">Panneau chantier</th>
                <th class="bg-primary lg:p-2">Indicateurs</th>
                <th class="bg-primary lg:p-2 rounded-tr-lg">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($poses as $pose)
            @php
                $rowClass = $pose['class_row'] === 'valid_vente' ? 'bg-[#00ff001a]' :
                            ($pose['class_row'] === 'attente_vente' ? 'bg-[#00ff004d]' : 'bg-[#ff000033]');
            @endphp
            <tr class="hover:bg-gray-100 {{ $rowClass }} text-center">
                <!-- Client -->
                <td class="lg:p-2">
                    <div class="font-medium">{{ $pose['nom'] }} {{ $pose['prenom'] }}</div>
                    <div class="text-xs text-gray-500"><a href="tel:{{ $pose['telephone']}}"> {{ $pose['telephone'] ?? '' }}</div>
                </td>

                <!-- Adresse -->
                <td class="lg:p-2 text-gray-700">
                    {{ $pose['adresse'] }} {{ $pose['cp'] }} {{ $pose['ville'] }}
                </td>

                <!-- Période -->
                <td class="lg:p-2">
                    <div><span class="text-green-600">Début:</span> {{ $pose['date_debut'] }}</div>
                    <div><span class="text-red-600">Fin:</span> {{ $pose['date_fin'] }}</div>
                </td>

                <!-- Produits -->
                <td class="lg:p-2">
                    @foreach($pose['produits'] as $produit)
                        <span class="inline-block px-2 py-1 text-xs rounded bg-gray-200 mr-1 mb-1">
                            {{ $produit }}
                        </span>
                    @endforeach
                </td>

                <!-- Poseurs -->
                <td class="lg:p-2">
                    @if(count($pose['poseurs']) > 0)
                        {{ implode(', ', $pose['poseurs']) }}
                    @else
                        <span class="text-gray-400">Non assigné</span>
                    @endif
                </td>

                <!-- Commerciaux -->
                @if(!$isTelepro)
                    <td class="lg:p-2">
                        @foreach($pose['commerciaux'] as $commercial)
                            @if($commercial->active)
                                <div class="{{ $commercial->active == 0 ? 'text-red-600' : '' }}">
                                    {{ $commercial->display_name }}
                                </div>
                            @endif
                        @endforeach
                    </td>
                @endif

                <!-- Suivi -->
                <td @if($isAdmin) wire:click="toggleVisiteChantier({{$pose['id_pose']}}, {{$pose['visite_chantier'] ? 0 : 1}})" class="cursor-pointer" @endif class="lg:p-2">
                    <div class="{{ $pose['visite_chantier'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $pose['visite_chantier'] ? 'OUI' : 'NON' }}
                    </div>
                </td>
                <td @if($isAdmin) wire:click="toggleAvisClient({{$pose['id_pose']}}, {{$pose['avis_client'] ? 0 : 1}})" class="cursor-pointer" @endif class="lg:p-2">
                    <div class="{{ $pose['avis_client'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $pose['avis_client'] ? 'OUI' : 'NON' }}
                    </div>
                </td>
                <td @if($isAdmin) wire:click="toggleAvisFacebook({{$pose['id_pose']}}, {{$pose['avis_facebook'] ? 0 : 1}})" class="cursor-pointer" @endif class="lg:p-2">
                    <div class="{{ $pose['avis_facebook'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $pose['avis_facebook'] ? 'OUI' : 'NON' }}
                    </div>
                </td>
                <!-- Indicateurs -->
                <td @if($isAdmin) wire:click="toggleIndicateur({{$pose['id_pose']}}, {{$pose['indicateur'] ? 0 : 1}})" class="cursor-pointer" @endif class="lg:p-2">
                    <div class="{{ $pose['indicateur'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $pose['indicateur'] ? 'OUI' : 'NON' }}
                    </div>
                </td>

                <!-- Actions -->
                <td class="lg:p-2 justify-center lg:space-x-2">
                    @if($isAdmin)
                        <a href="{{ route('nouvelle-pose', ['id' => $pose['id_pose']]) }}" class="lg:p-1 cursor-pointer text-green-600 hover:text-green-800">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="" wire:click.prevent="confirmDelete({{ $pose['id_pose'] }})" onclick="event.stopPropagation(); if(!confirm('Supprimer la pose ?')) { event.preventDefault(); }"
                           class="lg:p-1 cursor-pointer text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </a>
                    @endif

                    <!-- Google Calendar -->
                    @if(!empty($pose['google_calendar_url']))
                        <a href="{{ $pose['google_calendar_url'] }}" target="_blank"
                           class="lg:p-1 cursor-pointer text-blue-600 hover:text-blue-800">
                            <i class="fas fa-calendar-plus"></i>
                        </a>
                    @endif
                </td>

            </tr>
        @empty
            <tr>
                <td colspan="{{ $isTelepro ? '8' : '9' }}" class="text-center p-4">
                    Aucune pose trouvée.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
    <script src="{{ asset('js/tableSorter.js') }}"></script>
</div>
