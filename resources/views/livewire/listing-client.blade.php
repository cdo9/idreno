<div class="flex flex-col items-center justify-center min-h-screen bg-background-grey gap-8 pb-10 ">
    <div class="flex flex-col gap-6 p-6 bg-background rounded-xl w-full max-w-9xl shadow-md">

        <h2 class="text-xl font-bold text-primary">{{ $clients->total() }} clients</h2>

        <div class="titre_avec_filtres">
            <form method="POST" wire:submit.prevent="filtrer"
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                <select wire:model.live="type"
                        class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary">
                    <option value="">Tous les types</option>
                    @foreach ($clientsTypes as $key => $label)
                        <option value="{{ $key }}">{{$label}}</option>
                    @endforeach
                </select>
                <select wire:model.live="vendeur"
                        class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary">
                    <option value="">Tous les vendeurs</option>
                    @foreach ($vendeurs as $v)
                        <option value="{{ $v['id'] }}">{{ $v->display_name }}</option>
                    @endforeach
                </select>
                <input type="date" wire:model.live="date_debut"
                       class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary">
                <input type="date" wire:model.live="date_fin"
                       class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary">

                <input type="search" wire:model.live="search" placeholder="Rechercher..."
                       class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary">

                @if ($commune)
                    <input type="hidden" name="commune" value="{{ $commune['id'] }}">
                    <input type="text" disabled value="Commune: {{ $commune['nom'] }}"
                           class="w-full border border-gray-300 rounded-full px-4 py-2 bg-gray-100 text-gray-500">
                @endif
            </form>
        </div>
        <div>
            <div class="w-full overflow-x-auto">
                <table class="min-w-full border-collapse text-xs table-auto rounded-md">
                    <thead class="  text-white">
                    <tr>
                        <th class="bg-primary lg:px-4 py-2 w-120 rounded-tl-lg"><a href="#" wire:click="sortBy('type')"
                                                                    class="flex items-center gap-1 h-8 overflow-hidden">Type {!! $this->sortiIcon('type') !!}</a>
                        </th>
                        <th class="bg-primary lg:px-4 py-2 w-40"><a href="#" wire:click="sortBy('nom')"
                                                    class="flex items-center gap-1 h-8 overflow-hidden">Nom {!! $this->sortiIcon('nom') !!}</a></th>
                        <th class="bg-primary lg:px-4 py-2 w-40"><a href="#" wire:click="sortBy('prenom')"
                                                    class="flex items-center gap-1 h-8 overflow-hidden">Prénom {!! $this->sortiIcon('prenom') !!}</a>
                        </th>
                        <th class="bg-primary lg:px-4 py-2 w-56"><a href="#" wire:click="sortBy('adresse')"
                                                    class="flex items-center gap-1 h-8 overflow-hidden">Adresse {!! $this->sortiIcon('adresse') !!}</a>
                        </th>
                        <th class="bg-primary lg:px-4 py-2 w-32"><a href="#" wire:click="sortBy('cp')"
                                                    class="flex items-center gap-1 h-8 overflow-hidden">Code Postal
                                {!! $this->sortiIcon('codePostal') !!}</a></th>
                        <th class="bg-primary lg:px-4 py-2 w-40"><a href="#" wire:click="sortBy('ville')"
                                                    class="flex items-center gap-1 h-8 overflow-hidden">Ville {!! $this->sortiIcon('ville') !!}</a>
                        </th>
                        <th class="bg-primary lg:px-4 py-2 w-66"><a href="#" wire:click="sortBy('telephone1')"
                                                    class="flex items-center gap-1 h-8 overflow-hidden">Téléphone
                                {!! $this->sortiIcon('telephone1') !!}</a></th>
						<th class="bg-primary lg:px-4 py-2 w-30"><a href="#" wire:click="sortBy('mail')"
                                                    class="flex items-center gap-1 h-8 overflow-hidden">Email
                                {!! $this->sortiIcon('mail') !!}</a></th>
                        <th class="bg-primary lg:px-4 py-2 min-w-60"><a href="#" wire:click="sortBy('debrief')"
                                                    class="flex items-center gap-1 h-8 overflow-auto">Commentaire {!! $this->sortiIcon('debrief') !!}</a>
                        </th>
                        <th class="bg-primary lg:px-4 py-2 w-40"><a href="#" wire:click="sortBy('projet')"
                                                    class="flex items-center gap-1 h-8 overflow-hidden">Projet {!! $this->sortiIcon('projet') !!}</a>
                        </th>
                        <th class="bg-primary lg:px-4 py-2 w-120"><a href="#" wire:click="sortBy('date_dernier_contact')"
                                                    class="flex items-center gap-1 h-8 overflow-hidden">Dernier Contact
                                {!! $this->sortiIcon('dernier_contact') !!}</a></th>
                        <th class="bg-primary lg:px-4 py-2 w-40"><a href="#" wire:click="sortBy('dernier_paiement')" class="flex items-center gap-1 h-8 overflow-hidden"> Paiement {!! $this->sortiIcon('dernier_paiement') !!}</a></th>
                        <th class="bg-primary lg:px-4 py-2 w-40"><a href="#" wire:click="sortBy('rentre_par')" class="flex items-center gap-1 h-8 overflow-hidden">Rentré par {!! $this->sortiIcon('rentre_par') !!}</a></th>
                        <th class="bg-primary lg:px-4 py-2 w-40"><a href="#" wire:click="sortBy('rentre_lesort')" class="flex items-center gap-1 h-8 overflow-hidden">Rentré Le {!! $this->sortiIcon('rentre_le') !!}</a></th>
                        <th class="bg-primary lg:px-4 py-2 w-32 rounded-tr-lg">Actions</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($clients as $client)
                        <tr class="odd:bg-gray-200">
                            <td class="{{ $this->getTypeClass($client['type']) }} lg:px-4 lg:py-2 overflow-hidden">
                                <div class="max-h-10 overflow-hidden">
                                    @if (in_array($client['type'], $clientsTypesPrecontact))
                                        <select wire:change="updateClientType({{ $client['id'] }}, $event.target.value)"
                                                class="rounded border border-gray-300 bg-background-grey px-2 py-1 text-xs">
                                            <option value="0" @selected($client['type'] == 0)>Précontact</option>
                                            <option value="6" @selected($client['type'] == 6)>Refus</option>
                                            <option value="7" @selected($client['type'] == 7)>Absent</option>
                                            <option value="8" @selected($client['type'] == 8)>Relance</option>
                                        </select>
                                    @else
                                        {{ $clientsTypes[$client['type']] ?? '' }}
                                    @endif
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 overflow-hidden ">
                                <div class="max-h-10 overflow-x-auto overflow-y-hidden">
                                    {{ $client['nom'] }}
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 overflow-hidden">
                                <div class="max-h-10 overflow-x-auto overflow-y-hidden">
                                    {{ $client['prenom'] }}
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2">
                                <div class=" overflow-x-auto ">
                                    {{ $client['adresse'] }}
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 overflow-hidden">
                                <div class="max-h-10 overflow-x-auto overflow-y-hidden">
                                    {{ $client['cp'] }}
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 overflow-hidden">
                                <div class="overflow-x-auto overflow-y-hidden">
                                    {{ $client['ville'] }}
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 overflow-hidden whitespace-nowrap">
                                <div class="max-h-10 max-w-32 overflow-x-auto overflow-y-hidden">
                                    @if ($client->telephone1)
                                        <a href="tel:{{ $client->telephone1 }}" class="text-primary underline">
                                            {{ $client->telephone1 }}
                                        </a>
                                    @endif
                                    @if ($client->telephone2 && $client->telephone1)
                                        <br>
                                    @endif
                                    @if ($client->telephone2)
                                        <a href="tel:{{ $client->telephone2 }}" class="text-primary underline">
                                            {{ $client->telephone2 }}
                                        </a>
                                    @endif
                                </div>
                            </td>
							<td class="lg:px-4 lg:py-2 ">
                                <div class="max-w-20 break-words">
                                    {{ $client['mail'] }}
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 ">
                                <div class="block">
                                    {{ $client['debrief'] }}
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 overflow-hidden">
                                <div class="overflow-hidden">
                                    {{ $client['projet'] }}
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 overflow-hidden">
                                <div class="">
                                    @if ($client['date_dernier_commentaire'])
                                        {{ \Carbon\Carbon::parse($client['date_dernier_commentaire'])->format('d/m/Y') }}
                                    @endif
                                    <!--<a href="#" wire:click.prevent="refreshContact({{ $client->id }})" class="fa fa-repeat icon-action text-primary ml-1"></a>-->
                                    @if ($client->dernier_commentaire)
                                        <span class="cursor-pointer" wire:click="voirCommentaires({{ $client->id }})">
                                            {{ $client->dernier_commentaire}}
                                        </span>
                                    @endif
                                    <a href="#" wire:click.prevent="addComment({{ $client->id }})" class="ml-2 text-green-600">
                                        <span class="fa fa-repeat"></span>
                                    </a>
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 overflow-hidden text-center">
                                <div class="max-h-10">
                                    @if (is_null($client->dernier_paiement) && is_null($client->duree_restante))
                                        <div>—</div>
                                    @else
                                        {{ $client->dernier_paiement ?? '—' }}
                                        <br>

                                        @if (is_null($client->duree_restante))
                                            <div class="text-red-600">
                                                Non renseigné
                                            </div>
                                        @elseif ($client->duree_restante <= 0)
                                            <div class="text-green-600">
                                                Terminé
                                            </div>
                                        @else
                                            <div class="text-green-600">
                                                {{ $client->duree_restante }} Jours restants
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </td>

                            <td class="lg:px-4 lg:py-2 overflow-hidden">
                                <div class="max-h-30 overflow-auto">
                                    {{ $client['rentre_par_str']}}
                                    <div class="text-xs">
                                        @if($client['partage_avec'])
                                            ({{ $client['partage_str'] }})
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 overflow-hidden">
                                <div class="max-h-10 overflow-x-auto overflow-y-auto">
                                    @if($isAdmin && $client['rentre_le2'])
                                        {{ \Carbon\Carbon::parse($client['rentre_le2'])->format('d/m/Y H:i') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($client['rentre_le'])->format('d/m/Y') }}
                                    @endif
                                </div>
                            </td>
                            <td class="lg:px-4 lg:py-2 overflow-hidden text-center flex flex-row justify-center items-center mt-3">
                                <div class=" overflow-hidden px-4 py-2">
                                    <a href="{{ route('nouveau-rendez-vous')}}?id_client={{$client->id }}" class="text-primary underline">
                                        <i class="fa-solid fa-clipboard-check"></i>
                                    </a>
                                </div>
                                <a href="/nouveau-contact?client_id={{$client->id}}" class="text-primary">
                                    <span class="fa fa-edit"></span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Bouton export CSV --}}
			@if($isAdmin)
				<button type="button" wire:click="exportCsv" class="mt-5 bg-primary text-white px-6 py-2 rounded-full hover:bg-green-700 transition-colors">
					Exporter CSV
				</button>
			@endif

            {{-- Pagination personnalisée --}}
            <div class="mt-6 flex flex-col items-center justify-center">
                {{ $clients->links() }}
                <div>
                    <span>Page : </span>
                    <input 
                        class='mt-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] transition-colors duration-150 cursor-pointer'
                        wire:model.live="goToPage"
                        wire:keydown.enter="jumpToPage"
                        x-on:keydown.enter.prevent="$wire.jumpToPage()"
                    >
                    <button 
                        wire:click="jumpToPage"
                        class='mt-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] transition-colors duration-150 cursor-pointer'
                        >Charger
                    </button>
                    @if($pageErrorMessage)
                        <span class="text-red-500 text-sm bg-red-100 px-3 py-1 rounded">
                            {{ $pageErrorMessage }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if ($showModal)
        <div class="fixed inset-0 flex items-center justify-center bg-white z-50">
            <div class="bg-white p-6 rounded-xl shadow-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto text-gray-800 border">
                <h2 class="text-lg font-semibold mb-4">Commentaires du client #{{ $clientIdModal }}</h2>

                @if (count($commentaires) > 0)
                    <ul class="space-y-4">
                        @foreach ($commentaires as $commentaire)
                            <li class="border-b border-gray-200 pb-2">
                                <div class="text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($commentaire['date'])->format('d/m/Y') }}
                                </div>
                                <div class="text-base">
                                    {{ $commentaire['commentaire'] }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500 italic">Aucun commentaire.</p>
                @endif

                <div class="text-right">
                    <button wire:click="$set('showModal', false)"
                            class="mt-6 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded shadow">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    @endif
    @if ($showCommentModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded-xl shadow-xl max-w-2xl w-full">
                <h2 class="text-lg font-semibold mb-4">Ajouter un commentaire</h2>

                <textarea wire:model.defer="newComment" rows="5"
                          class="w-full border border-gray-300 rounded p-2 mb-4"
                          placeholder="Écrivez votre commentaire ici..."></textarea>

                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showCommentModal', false)"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                    <button wire:click="saveComment"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif

</div>


