<div class="flex flex-col gap-8 pb-10">
    <x-loading-spinner />

    <div class="flex flex-col gap-6 p-6 bg-background rounded-xl w-full max-w-9xl shadow-md overflow-x-auto">
        <h2 class="text-xl font-bold text-primary">{{ $clients->total() ?? 0 }} relances contacts</h2>

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
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('telephone')" class="flex items-center gap-1 h-8 overflow-hidden">Téléphone {!! $this->sortiIcon('telephone') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('ville')" class="flex items-center gap-1 h-8 overflow-hidden">Ville {!! $this->sortiIcon('ville') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('rentre_le')" class="flex items-center gap-1 h-8 overflow-hidden">Date Précontact {!! $this->sortiIcon('rentre_le') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('projet')" class="flex items-center gap-1 h-8 overflow-hidden">Projet {!! $this->sortiIcon('projet') ?? '' !!}</a></th>
                            <th class="px-4 py-2 w-32"><a href="#" wire:click="sortBy('commercial')" class="flex items-center gap-1 h-8 overflow-hidden">Commercial {!! $this->sortiIcon('commercial') ?? '' !!}</a></th>
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
                                        {{ $client->nom }} {{ $client->prenom }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32 whitespace-nowrap">
                                    <div class="max-h-10 max-w-32 overflow-x-auto overflow-y-hidden">
                                        @if($client->telephone1)
                                            <a href="tel:{{ $client->telephone1 }}" class="text-primary underline">
                                                {{ $client->telephone1 }}
                                            </a>
                                        @endif
                                        @if($client->telephone2 && $client->telephone1)
                                            <br>
                                        @endif
                                        @if($client->telephone2)
                                            <a href="tel:{{ $client->telephone2 }}" class="text-primary underline">
                                                {{ $client->telephone2 }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-x-auto overflow-y-hidden">
                                        {{ $client->ville }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-hidden">
                                        {{ $client->rentre_le ? \Carbon\Carbon::parse($client->rentre_le)->format('d/m/Y') : '' }}
                                    </div>
                                </td>
                                  <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-x-auto overflow-y-auto">
                                        {{ ($client->projet) }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-x-auto overflow-y-hidden">
                                        {{ $client->user ? $client->user->prenom . ' ' . $client->user->nom : '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-32">
                                    <div class="max-h-10 overflow-hidden">
                                        {{ $client->date_relance ? \Carbon\Carbon::parse($client->date_relance)->format('d/m/Y') : '' }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden w-24">
                                    <div class="max-h-10 overflow-hidden">
                                        @if($client->date_relance && $client->date_relance === null)
                                            <button
                                                class="w-14 px-2 py-1 text-white bg-green-600 rounded-full text-xs"
                                                disabled title="Déjà relancé">
                                                OUI
                                            </button>
                                        @else
                                            <button wire:click="openRelanceModal({{ $client->id }})"
                                                class="w-14 px-2 py-1 text-white bg-red-600 rounded-full text-xs hover:bg-red-700 transition-colors cursor-pointer"
                                                title="Cliquer pour marquer comme relancé">
                                                NON
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 h-12 overflow-hidden text-left w-24">
                                    <div class="flex gap-1">
                                        <button wire:click="editClient({{ $client->id }})" class="text-primary" title="Éditer le client">
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
