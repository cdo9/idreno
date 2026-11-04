<div class="flex flex-col gap-8 pb-10">
    <x-loading-spinner />

    <div class="flex flex-col gap-6 p-6 bg-background rounded-xl w-full max-w-9xl shadow-md overflow-x-auto">
        <h2 class="text-xl font-bold text-primary">{{ $clients->total() ?? 0 }} relances argumentations</h2>

        <x-relance-filters :secteurs="$secteurs" />

        @if($clients->isEmpty())
            <div class="w-full flex justify-center mb-4">
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl max-w-2xl w-full text-center">
                    Aucun résultat trouvé.
                </div>
            </div>
        @else
            <div class="w-full overflow-x-auto">
                <table class="min-w-full border-collapse text-sm table-fixed">
                    <thead class="text-left bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-2 w-40"><a href="#" wire:click="sortBy('nom')" class="flex items-center gap-1 h-8 overflow-hidden">Client {!! $this->sortiIcon('nom') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-40"><a href="#" wire:click="sortBy('telephone')" class="flex items-center gap-1 h-8 overflow-hidden">Téléphone {!! $this->sortiIcon('telephone') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('ville')" class="flex items-center gap-1 h-8 overflow-hidden">Ville {!! $this->sortiIcon('ville') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('date_argu')" class="flex items-center gap-1 h-8 overflow-hidden">Date d'argu {!! $this->sortiIcon('date_argu') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-24"><a href="#" wire:click="sortBy('resultat')" class="flex items-center gap-1 h-8 overflow-hidden">Vendu {!! $this->sortiIcon('resultat') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('montant_devis')" class="flex items-center gap-1 h-8 overflow-hidden">Montant Devis {!! $this->sortiIcon('montant_devis') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('montant_tarif')" class="flex items-center gap-1 h-8 overflow-hidden">Montant Tarif {!! $this->sortiIcon('montant_tarif') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('projet')" class="flex items-center gap-1 h-8 overflow-hidden">Projet {!! $this->sortiIcon('projet') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('commercial')" class="flex items-center gap-1 h-8 overflow-hidden">Commercial {!! $this->sortiIcon('commercial') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('groupe_nom')" class="flex items-center gap-1 h-8 overflow-hidden">Groupe {!! $this->sortiIcon('groupe_nom') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('date_relance')" class="flex items-center gap-1 h-8 overflow-hidden">Date Relance {!! $this->sortiIcon('date_relance') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-24"><a class="flex items-center gap-1 h-8 overflow-hidden">Relancé</a></th>
                            <th class="px-4 py-2 w-24"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($clients as $client)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 h-12 overflow-hidden w-40">
                                    <div class="max-h-10 overflow-x-auto overflow-y-hidden">
                                        {{ $client->client->nom }} {{ $client->client->prenom }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-40 whitespace-nowrap">
                                    <div class="max-h-10 max-w-40 overflow-x-auto overflow-y-hidden">
                                        @if($client->client->telephone1)
                                            <a href="tel:{{ $client->client->telephone1 }}" class="text-primary underline">
                                                {{ $client->client->telephone1 }}
                                            </a>
                                        @endif
                                        @if($client->client->telephone1 && $client->client->telephone2)
                                            <br>
                                        @endif
                                        @if($client->client->telephone2)
                                            <a href="tel:{{ $client->client->telephone2 }}" class="text-primary underline">
                                                {{ $client->client->telephone2 }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-x-auto overflow-y-hidden">
                                        {{ $client->client->ville }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-hidden">
                                        {{ $client->date_argu ? \Carbon\Carbon::parse($client->date_argu)->format('d/m/Y') : '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-24">
                                    <div class="max-h-10 overflow-hidden">
                                        {{ $client->resultat ?? '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-hidden">
                                        {{ $client->montant_devis ? number_format($client->montant_devis, 0, ',', ' ') . ' €' : '0 €' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-hidden">
                                        {{ $client->montant_tarif ? number_format($client->montant_tarif, 0, ',', ' ') . ' €' : '0 €' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-x-auto overflow-y-auto">
                                        @php
                                            $labels = \App\Services\ProjectService::listProductsFlat();
                                            $produits = [];
                                            if (is_array($client->produits)) {
                                                $produits = $client->produits;
                                            } elseif (is_string($client->produits)) {
                                                // Gère les séparateurs ';' ou ','
                                                $produits = preg_split('/[;,]+/', $client->produits, -1, PREG_SPLIT_NO_EMPTY);
                                            }
                                        @endphp
                                        @foreach($produits as $slug)
                                            @if(isset($labels[$slug]))
                                                <span>
                                                    {{ $labels[$slug] }}
                                                </span>
                                            @else
                                                <span >
                                                    {{ $slug }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-x-auto overflow-y-hidden">
                                        {{ ($client->client->user) ? $client->client->user->display_name : 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-x-auto overflow-y-hidden">
                                        {{ $client->groupe_nom ?? '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-hidden">
                                        {{ $client->date_relance ? \Carbon\Carbon::parse($client->date_relance)->format('d/m/Y') : '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-24">
                                    <div class="max-h-10 overflow-hidden">
                                        @if($client->rdv_relance)
                                           <button
                                                class="w-14 px-2 py-1 text-white bg-green-600 rounded-full text-xs"
                                                disabled title="Déjà relancé">
                                                OUI
                                            </button>
                                        @else
                                            <button wire:click="openRelanceModal({{ $client->id }}, {{ $client->rdv_id }})"
                                                class="w-14 px-2 py-1 text-white bg-red-600 rounded-full text-xs hover:bg-red-700 transition-colors cursor-pointer"
                                                title="Cliquer pour marquer comme relancé">
                                                NON
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden text-left w-24">
                                    <div class="flex gap-1">
                                        <button wire:click="editRendezVous({{ $client->rdv_id }})" class="text-primary" title="Éditer l'argumentation">
                                            <span class="fa fa-edit"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex justify-center mt-6">
                    {{ $clients->links('pagination::tailwind') }}
                </div>
            </div>
        @endif
    </div>

<x-relance-modal />

<x-relance-notifications />
</div>
