<div>
    <h2 class="text-2xl font-bold mb-4 text-primary">Rendez-vous</h2>

    <!-- Filtres -->
    <div class="flex justify-between flex-col sm:flex-row">
        <div class="flex items-center flex-wrap space-x-4 mb-6">
            <select wire:model.live="m" class="rounded p-2">
                <option value="0">Toute l'année</option>
                @foreach ($months as $name => $number)
                    <option value="{{ $number }}">{{ $name }}</option>
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
        <div class="flex gap-2 mb-6">
            <div class="flex border-1 text-green-500 border-green-500 text-sm lg:text-base text-nowrap px-2 lg:px-4 py-2 rounded">Confirmé :&nbsp;<div>{{ $this->countConfirmed; }}</div></div>
            <div class="flex border-1 text-yellow-500 border-yellow-500 text-sm lg:text-base text-nowrap px-2 lg:px-4 py-2 rounded">A confirmer :&nbsp;<div>{{ $this->countToConfirm; }}</div></div>
            <div class="flex border-1 text-orange-500 border-orange-500 text-sm lg:text-base text-nowrap px-2 lg:px-4 py-2 rounded">A repositionner :&nbsp;<div>{{ $this->countRescheduled; }}</div></div>
            <div class="flex border-1 text-red-500 border-red-500 text-sm lg:text-base text-nowrap px-2 lg:px-4 py-2 rounded">Annulé :&nbsp;<div>{{ $this->countCancel; }}</div></div>
        </div>
    </div>

    <!-- Message de confirmation -->
    @if ($message)
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ $message }}
        </div>
    @endif
   
    <!-- Tableau -->
    <table class="sortable min-w-full text-xs lg:text-sm table-fixed">
        <thead class=" text-white">
        <tr>
            <th class="lg:p-2 bg-primary rounded-tl-lg">Date Prise de RDV</th>
            <th class="lg:p-2 bg-primary ">État</th>
            <th class="lg:p-2 bg-primary ">Date Confirmation</th>
            <th class="lg:p-2 bg-primary ">Date RDV</th>
            <th class="lg:p-2 bg-primary ">Client</th>
            <th class="lg:p-2 bg-primary ">Téléphonne</th>
            <th class="lg:p-2 bg-primary ">Ville</th>
            <th class="lg:p-2 bg-primary ">Projets</th>
            <th class="lg:p-2 bg-primary ">Source</th>
            <th class="lg:p-2 bg-primary ">Commerciaux</th>
            <th class="lg:p-2 bg-primary rounded-tr-lg">Actions</th>

        </tr>
        </thead>
        <tbody>
        @forelse ($rdvs as $rdv)
            <tr class="hover:bg-gray-50 odd:bg-gray-200 text-center">
                <td class="lg:p-2">{{ $rdv['date_priseRDV'] }}</td>
                <td class="lg:p-2">
                    @if ($rdv['annule'] == 1)
                        <div class="text-red-500">
                            Annulé

                            @if ($isDirecteur)
                                <a class="icon-action cursor-pointer"
                                   wire:click="reactiver({{ $rdv['id'] }})"
                                   wire:confirm="Voulez-vous réactiver ce rendez-vous ?">
                                    <span class='fa fa-rotate-left'></span>
                                </a>
                            @endif
                        </div>

                    @else
                        @if ( $rdv['etat'] == 1)
                            <div class="text-green-500">
                                Confirmé
                                <a class="icon-action cursor-pointer"
                                   wire:click="repositionner({{ $rdv['id'] }})"
                                   wire:confirm="Voulez-vous repositionner ce rendez-vous ?">
                                    <span class='fa fa-rotate-left'></span>
                                </a>
                            </div>
                        @elseif ($rdv['etat'] == 2)
                            <div class="text-orange-500">
                                À repositionner
                            </div>
                        @else
                            <div class="text-yellow-500">
                                À confirmer
                                <a class="icon-action cursor-pointer"
                                   wire:click="confirmer({{ $rdv['id'] }})"
                                   wire:confirm="Voulez-vous confirmer ce rendez-vous ?">
                                    <span class='fa fa-check'></span>
                                </a>
                                <a class="icon-action cursor-pointer"
                                   wire:click="repositionner({{ $rdv['id'] }})"
                                   wire:confirm="Voulez-vous repositionner ce rendez-vous ?">
                                    <span class='fa fa-rotate-left'></span>
                                </a>
                            </div>
                        @endif
                    @endif
                </td>
                <td class="lg:p-2">{{ $rdv['date_confirmation'] }} </td>
                <td class="lg:p-2">{{ $rdv['date'] }} {{$rdv['heure']}} </td>
                <td class="lg:p-2">{{ $rdv['client'] }} </td>
                <td class="lg:p-2"><a href="tel:{{ $rdv['telephone'] }}">{{ $rdv['telephone'] }}</a> </td>
                <td class="lg:p-2">{{ $rdv['ville'] }}</td>
                <td class="lg:p-2">{!! $rdv['projets'] !!}</td>
                <td class="lg:p-2">{{ $rdv['source'] }}</td>
                <td class="lg:p-2">
					@if($rdv['commercial'])
						{{ $rdv['commercial']->display_name }}
					@endif
                    @if($rdv['partage'])
                        <br>{{ $rdv['partage']->display_name }}
                    @endif
                </td>
                <td class="p-2">
                    <a class="p-1 cursor-pointer" href="{{ url('/rendez-vous/' . $rdv['id'] . '/edit') }}">
                        <span class="fa fa-edit text-green-500"></span>
                    </a>
                    <a class="icon-action cursor-pointer text-red-500"
                        wire:click="annuler({{ $rdv['id'] }})"
                        wire:confirm="Voulez-vous annuler ce rendez-vous ?">
                        <span class='fa fa-cancel'></span>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center p-4">Aucun rendez-vous trouvé.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    
    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
    <script src="{{ asset('js/tableSorter.js') }}"></script>
</div>
