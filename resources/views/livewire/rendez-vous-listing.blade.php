<div class="flex flex-col" style="font-size: 14px;">
    <div class="flex flex-row md:gap-8 pb-10 pt-10 flex-wrap">
        @foreach($confirmationStats as $key => $stat)
            <button wire:click="filterByDay('{{ $key === 'j0' ? 'today' : $key }}')"
               class="group flex flex-row {{ $stat['active'] ? 'bg-primary text-white' : 'bg-background hover:bg-primary' }} rounded-xl gap-4 md:gap-8 p-3 md:p-5 cursor-pointer min-w-fit transition-colors">
                <p class="{{ $stat['active'] ? 'text-white' : 'text-text-muted group-hover:text-white' }} font-bold text-sm md:text-base">
                    Rendez-vous {{ $key === 'j0' ? 'J' : str_replace('j', 'J+', $key) }}
                    <span class="ml-2 text-xs md:text-sm">({{ $stat['count'] }})</span>
                </p>
            </button>
        @endforeach
    </div>

    <div class="flex flex-col gap-6 lg:p-10 bg-background rounded-xl justify-around">
        <div class="overflow-x-auto flex flex-col gap-4">
            <table class="sortable min-w-full text-xs lg:text-sm">
                <thead>
                    <tr class=" p-1">
                        <th class="text-white  bg-primary rounded-tl-lg lg:pl-4 text-center">
                            <p>CLIENT</p>
                        </th>
                        <th class="text-white bg-primary text-center">
                            <p>TELEPHONE</p>
                        </th>
                        <th class="text-white bg-primary text-center">
                            <p>VILLE</p>
                        </th>
                        <th class="text-white bg-primary text-center">
                            <p>PROJETS</p>
                        </th>
                        <th class="text-white bg-primary text-center">
                            <p>DATE DE PRISE DE RDV</p>
                        </th>
                        <th class="text-white bg-primary text-center">
                            <p>COMMERCIAL PRINCIPAL</p>
                        </th>
                        <th class="text-white bg-primary text-center">
                            <p>COMMERCIAL SECONDAIRE</p>
                        </th>
                        <th class="text-white bg-primary text-center">
                            <p>DATE RDV</p>
                        </th>
                        <th class="text-white bg-primary text-center">
                            <p>DATE REALISATION PROJET</p>
                        </th>
                        <th class="text-white bg-primary text-center">
                            <p>CONFIRMÉ</p>
                        </th>
                        <th class="text-white bg-primary rounded-tr-lg text-center">
                            <p>ACTIONS</p>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rendezVous as $rdv)
                        <tr class="items-center bg-background-grey rounded-xl hover:bg-gray-100 transition-colors duration-200" wire:key="rdv-{{ $rdv->id }}">
                            <td class="text-primary font-bold lg:pl-4 pl-1 py-1 flex items-center justify-start gap-2">                    @php
                                
                                @endphp
                                <a
                                        href="{{ $rdv->agendaLink }}"
                                        target="_blank" 
                                        class="flex items-center justify-center bg-primary hover:bg-primary/80 rounded-full w-7 h-7 mr-1 transition-colors"
                                        title="Ajouter à Google Calendar"
                                >
                                    <i class="fas fa-plus text-white"></i>
                                </a>
                                <a 
                                    href="/nouveau-contact?client_id={{$rdv->client->id}}"
                                    class="text-primary font-bold">
                                    {{ $rdv->client->nom ?? 'N/A' }} {{ $rdv->client->prenom ?? '' }}
                                </a>

                            </td>

                            <td class="text-primary font-bold text-center">
                                <a href="tel:{{ $rdv->client->telephone1 }}">{{ $rdv->client->telephone1_formatted ?? 'N/A' }}</a>
                            </td>

                            <td class="text-text-muted text-center">
                                <p>{{ $rdv->client->ville ?? 'N/A' }}</p>
                            </td>

                            <td class="text-text-muted text-center">
                                @php
                                    $projets = array_filter([$rdv->projet, $rdv->projet2, $rdv->projet3]);
                                @endphp
                                @if(count($projets) > 0)
                                    <div class="flex flex-col">
                                        @foreach($projets as $projet)
                                            <p>{{ $projet }}</p>
                                        @endforeach
                                    </div>
                                @else
                                    <p>N/A</p>
                                @endif
                            </td>

                            <td class="text-text-muted text-center">
                                <p>{{ $rdv->date_ajout ? \Carbon\Carbon::parse($rdv->date_ajout)->format('d/m/Y') . ' ' . $rdv->heure : 'N/A' }}</p>
                            </td>

                            <td class="text-text-muted text-center">
                                <p>{{ $rdv->main_commercial_name ?? '' }}</p>
                            </td>

                            <td class="text-text-muted text-center">
                                <p>{{ $rdv->shared_commercial_name ?? '' }}</p>
                            </td>

                            <td class="text-text-muted text-center">
                                <p>{{ $rdv->date ? \Carbon\Carbon::parse($rdv->date)->format('d/m/Y') : 'N/A' }} {{ $rdv->heure }}</p>
                            </td>

                            <td class="text-text-muted text-center">
                                <p>{{ $rdv->date_realisation ? \Carbon\Carbon::parse($rdv->date_realisation)->format('d/m/Y') : 'N/A' }}</p>
                            </td>

                            <td class="flex justify-center">
                                @if($rdv->rdv_confirme === 2)
                                    <div class="flex items-center bg-orange-500 rounded-lg px-1 py-1"
                                        title="{{ $rdv->compte_rendu ? 'Compte rendu: ' . $rdv->compte_rendu : 'À repositionner' }}">
                                        <span class="text-white font-bold">A REPO</span>
                                    </div>
                                @else
                                    <button wire:click="updateConfirmation({{ $rdv->id }})"
                                            class="flex items-center {{ $rdv->rdv_confirme ? 'bg-success' : 'bg-error' }} rounded-lg px-1 py-1 cursor-pointer hover:opacity-80 transition-opacity"
                                            title="Cliquez pour changer le statut">
                                        <span class="text-white font-bold">{{ $rdv->rdv_confirme ? 'OUI' : 'NON' }}</span>
                                    </button>
                                @endif
                            </td>

                            <td class="justify-center">
                                <div class="flex gap-1">
                                    <button wire:click="editRendezVous({{ $rdv->id }})"
                                            class="group rounded-lg bg-background cursor-pointer p-1 h-7 w-7 shadow-md hover:shadow-lg transition-shadow border border-gray-200"
                                            title="Modifier">
                                        <i class="fa-solid fa-pen-to-square text-primary group-hover:text-secondary text-base"></i>
                                    </button>
                                    <button wire:click="openRelanceModal({{ $rdv->id }})"
                                            class="group rounded-lg bg-background cursor-pointer p-1 h-7 w-7 shadow-md hover:shadow-lg transition-shadow border border-gray-200"
                                            title="Relancer">
                                        <i class="fa-solid fa-rotate-right text-primary group-hover:text-secondary text-base"></i>
                                    </button>
                                    <button wire:click="deleteRendezVous({{ $rdv->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir annuler ce rendez-vous ?"
                                            class="group rounded-lg bg-background cursor-pointer p-1 h-7 w-7 shadow-md hover:shadow-lg transition-shadow border border-gray-200"
                                            title="Annuler">
                                        <i class="fa-solid fa-trash text-error group-hover:text-error/80 text-base"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="text-center py-10 overflow-x-auto">
                            <td colspan="10">
                                <p class="text-text-muted text-lg">Aucun rendez-vous trouvé pour cette période</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <script src="{{ asset('js/jquery.js') }}"></script>
            <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
            <script src="{{ asset('js/tableSorter.js') }}"></script>
        </div>

    <!-- Modal de relance -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 {{ $showRelanceModal ? '' : 'hidden' }}" wire:key="relance-modal">
        <div class="bg-white rounded-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-primary">Relance de rendez-vous</h3>
                <button wire:click="closeRelanceModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <label for="compteRendu" class="block text-sm font-medium text-gray-700 mb-1">
                    Compte rendu
                </label>
                <textarea
                    id="compteRendu"
                    wire:model="compteRendu"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                    rows="3"
                    placeholder="Entrez le compte rendu..."></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button
                    wire:click="closeRelanceModal"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    Annuler
                </button>
                <button
                    wire:click="relanceRendezVous"
                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/80 transition-colors">
                    Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Livewire event listeners for notifications
document.addEventListener('livewire:init', () => {
    Livewire.on('confirmation-updated', (event) => {
        console.log('Confirmation updated:', event.message);
        // You can add a toast notification here if needed
    });

    Livewire.on('confirmation-error', (event) => {
        alert('Erreur: ' + event.message);
    });

    Livewire.on('rendez-vous-deleted', (event) => {
        console.log('Rendez-vous deleted:', event.message);
        // You can add a toast notification here if needed
    });

    Livewire.on('deletion-error', (event) => {
        alert('Erreur: ' + event.message);
    });

    Livewire.on('relance-success', (event) => {
        console.log('Rendez-vous relancé:', event.message);
        // You can add a toast notification here if needed
    });

    Livewire.on('relance-error', (event) => {
        alert('Erreur: ' + event.message);
    });
});
</script>
