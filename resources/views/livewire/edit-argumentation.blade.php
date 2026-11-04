<div class="flex flex-col gap-6 p-6 bg-background rounded-xl w-full max-w-6xl shadow-md">
    <div class="flex items-center justify-between mb-6 flex-col md:flex-row">
        <h1 class="text-2xl sm:text-3xl font-bold text-primary">
            <i class="fa-solid fa-calendar-edit mr-3"></i>
            @if(isset($isCreate) && $isCreate)
                Nouvelle argumentation
            @else
                Édition Relance Argumentation
            @endif
        </h1>
        <div class="mt-5 sm:mt-0 flex flex-col items-center">
        <a href="{{ route('relances', ['type' => 'argumentations']) }}"
            class="px-4 py-2 m-5 md:mb-5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Retour Relance
        </a>
        <a href="{{ route('argumentations') }}"
           class="px-4 m-5 sm:m-0  ml-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Retour Liste Argumentations
        </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Formulaire de relance -->
    <form wire:submit.prevent="save" class="bg-white p-6 rounded-lg shadow-md">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Colonne 1 -->
            <div class="space-y-6">
                <div class="relative">
                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">Nom du client</label>
                    <input type="text" id="client_search" wire:model.live.debounce.250ms="client_search"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                        placeholder="Rechercher un client...">
                    <input type="hidden" id="client_id" wire:model="client_id">
                    <div class="mt-2">
                        @if ($client_search && !empty($clients) && strlen($client_search) >= 2)
                            <ul
                                class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                @forelse($clients as $client)
                                    <li wire:click="selectClient({{ $client->id }})"
                                        class="px-3 py-2 hover:bg-gray-100 cursor-pointer flex flex-col">
                                        <span class="font-semibold text-primary">
                                            {{ $client->prenom }} {{ $client->nom }}
                                        </span>
                                        <span class="text-xs text-gray-600">
                                            {{ $client->adresse }} {{ $client->ville }} {{ $client->cp }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            @if ($client->telephone1)
                                                {{ $client->telephone1 }}
                                            @endif
                                            @if ($client->telephone2)
                                                {{ $client->telephone2 }}
                                            @endif
                                            @if ($client->mail)
                                                | <span class="text-primary">{{ $client->mail }}</span>
                                            @endif
                                            @if ($client->type_habitation)
                                                | <span class="text-gray-700">{{ $client->type_habitation }}</span>
                                            @endif
                                            @if ($client->dernier_rdv)
                                                | <span class="text-gray-700">Dernier rdv : {{ $client->dernier_rdv }}</span>
                                            @endif
                                        </span>
                                    </li>
                                @empty
                                    <li class="px-3 py-2 text-gray-500">Aucun client trouvé</li>
                                @endforelse
                            </ul>
                        @endif

                        @if ($client_id && $client_fullname)
                            @php
                                $selectedClient = \App\Models\Client::find($client_id);
                            @endphp
                            <div
                                class="mt-2 p-3 border border-primary bg-green-50 text-primary rounded font-semibold flex flex-col">
                                <span>
                                    <i class="fa-solid fa-user mr-2"></i>
                                    Client sélectionné : {{ $client_fullname }}
                                </span>
                                @if ($selectedClient)
                                    <div class="text-xs text-gray-700 mt-1">
                                        {{ $selectedClient->adresse }} {{ $selectedClient->ville }}
                                        {{ $selectedClient->cp }}
                                        @if ($selectedClient->telephone1)
                                            | <a href="tel:{{ $selectedClient->telephone1 }}"
                                                class="text-primary">{{ $selectedClient->telephone1 }}</a>
                                        @endif
                                        @if ($selectedClient->telephone2)
                                            | <a href="tel:{{ $selectedClient->telephone2 }}"
                                                class="text-primary">{{ $selectedClient->telephone2 }}</a>
                                        @endif
                                        @if ($selectedClient->mail)
                                            | <a href="mailto:{{ $selectedClient->mail }}"
                                                class="text-primary">{{ $selectedClient->mail }}</a>
                                        @endif
                                        @if ($selectedClient->type_habitation)
                                            | <span class="text-gray-700">{{ $selectedClient->type_habitation }}</span>
                                        @endif
                                    </div>
                                @endif
                                <button type="button" wire:click="clearClient()"
                                    class="text-xs text-red-600 hover:text-red-800 mt-1 self-start">
                                    <i class="fa-solid fa-times mr-1"></i>Désélectionner
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                    <input type="date" id="date" wire:model="date"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <!-- Rendez-vous réalisé avec -->
                <div>
                    <x-commercial-select
                        :commerciaux="$commerciaux"
                        :selected="$partage_avec"
                        wireModel="partage_avec"
                        id="partage_avec"
                        label="Rendez-vous réalisé avec"
                        :required="false"
                        :showStatus="false"
                        :showLegend="false"
                        :showDeactivated="!$isCreate"
                    />
                </div>

                <!-- Vendeur 3 -->
                @if($vente_ok === 'ok')
                    <div>
                        <x-commercial-select
                            :commerciaux="$commerciaux"
                            :selected="$id_commercial3"
                            wireModel="id_commercial3"
                            id="id_commercial3"
                            label="Vendeur 3"
                            :required="false"
                            :showStatus="false"
                            :showLegend="false"
                            :hide="!$isAdmin && !$isDirecteur"
                            :showDeactivated="!$isCreate"
                        />
                    </div>
                @endif

                <!-- Date de relance -->
                <div>
                    <label for="date_relance" class="block text-sm font-medium text-gray-700 mb-2 ">Date de
                        relance *</label>
                    <input type="date" id="date_relance" wire:model="date_relance"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('date_relance') !border-red-500 @enderror">
                    @error('date_relance')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                    <!-- Vente réalisée -->
                <div>
                    <label for="vente_ok" class="block text-sm font-medium text-gray-700 mb-2">Avez-vous réalisé la vente ?</label>
                    <select id="vente_ok" wire:model.live="vente_ok"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="nok">Non</option>²
                        <option value="ok">Oui</option>
                    </select>
                </div>

                <!-- Paiement -->
                <div>
                    <label for="paiement" class="block text-sm font-medium text-gray-700 mb-2">Paiement *</label>
                    <select id="paiement" wire:model.live="paiement"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @foreach ($this->paiementOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Report et durée du prêt -->
                @if ($vente_ok === 'ok' && in_array($paiement, ['Financement', 'Comptant et financement']))
                    <!-- Report -->
                    <div>
                        <label for="postpone" class="block text-sm font-medium text-gray-700 mb-2">Report</label>
                        <select id="postpone"
                                wire:model="postpone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">Sélectionner</option>
                            <option value="1">1 mois</option>
                            <option value="6">6 mois</option>
                        </select>
                    </div>
                    <!-- Durée du prêt -->
                    <div>
                        <label for="duree_pret" class="block text-sm font-medium text-gray-700 mb-2">Durée du prêt (mois)</label>
                        <input type="number"
                               id="duree_pret"
                               wire:model="duree_pret"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <!-- Mensualité du prêt -->
                    <div>
                        <label for="mensualite" class="block text-sm font-medium text-gray-700 mb-2">Mensualité du prêt</label>
                        <input type="number"
                               id="mensualite"
                               wire:model="mensualite"
                               @focus="if ($wire.mensualite == 0) $wire.mensualite = ''"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                @endif
                <!-- Complément travaux -->
                <div>
                    <label for="complement" class="block text-sm font-medium text-gray-700 mb-2">Complément
                        travaux *</label>
                    <select id="complement" wire:model="complement"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="" disabled hidden selected></option>
                        @foreach ($priorites as $categorie => $options)
                            <optgroup label="{{ $categorie }}">
                                @foreach ($options as $slug => $priorite)
                                    <option value="{{ $priorite }}">{{ $priorite }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                        <option value="none">Aucun complément</option>
                    </select>
                    @error('complement')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Devis total HT -->
                <div>
                    <label for="devis_total_ht" class="block text-sm font-medium text-gray-700 mb-2">Devis total HT (€) *</label>
                    <input type="number" id="devis_total_ht" wire:model.live="devis_total_ht"
                        @focus="if ($wire.devis_total_ht == 0) $wire.devis_total_ht = ''"
                        @if(($vente['statut'] ?? 0) == 2 && !hasRole('directeur')) readonly @endif
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @error('devis_total_ht')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Tarif total HT -->
                <div>
                    <label for="tarif_total_ht" class="block text-sm font-medium text-gray-700 mb-2">Prix tarif total HT (€) *</label>
                    <input type="number" id="tarif_total_ht" wire:model.live="tarif_total_ht"
                        @focus="if ($wire.tarif_total_ht == 0) $wire.tarif_total_ht = ''"
                        @if(($vente['statut'] ?? 0) == 2 && !hasRole('directeur')) readonly @endif
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @error('tarif_total_ht')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Date anniversaire -->
                @if ($vente_ok === 'ok')
                <div>
                    <label for="date_anniversaire" class="block text-sm font-medium text-gray-700 mb-2">Date d'anniversaire *</label>
                    <input type="date" id="date_anniversaire" wire:model.live="date_anniversaire" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @error('date_anniversaire')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif
            </div>

            <!-- Colonne 2 -->
            <div class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail *</label>
                    <input type="email" id="email" wire:model="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Commercial -->
                <div>
                    <x-commercial-select
                        :commerciaux="$commerciaux"
                        :selected="$id_commercial"
                        wireModel="id_commercial"
                        id="id_commercial"
                        label="Commercial"
                        :required="true"
                        :showStatus="false"
                        :showLegend="false"
                        :showDeactivated="!$isCreate"
                    />
                </div>
                <!-- Source -->
                <div>
                    <label for="id_source" class="block text-sm font-medium text-gray-700 mb-2">Source *</label>
                    <select id="id_source" wire:model="id_source" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('id_source') !border-red-500 @enderror">
                        <option value="">Aucune</option>
                        @foreach($sources as $category)
                            @foreach($category as $source_id => $source)
                                <option value="{{ $source_id }}">{{ $source }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                @if($vente_ok === 'ok')
                    <!-- 1er vendeur -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="nom_commercial_1" class="block text-sm font-medium text-gray-700 mb-2">1er vendeur :</label>
                            <input type="text"
                                id="nom_commercial_1"
                                readonly
                                value="{{ optional($commerciaux->firstWhere('id', $id_commercial))->prenom ?? '' }} {{ optional($commerciaux->firstWhere('id', $id_commercial))->nom ?? '' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        </div>
                        <div>
                            <label for="repartition_1" class="block text-sm font-medium text-gray-700 mb-2">Taux répartition (%) *</label>
                            <input type="number"
                                id="repartition_1"
                                wire:model.live="repartition_1"
                                {{ !in_array(auth()->user()->role, ['directeur', 'admin']) ? 'readonly' : '' }}
                                class="w-full px-3 py-2 border border-gray-300 rounded-md {{ !in_array(auth()->user()->role, ['directeur', 'admin']) ? 'bg-gray-50' : '' }}">
                                @error('repartition_1')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                        </div>
                    </div>
                @endif

                @if($vente_ok === 'ok' && $partage_avec)
                    <!-- 2e vendeur -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="nom_commercial_2" class="block text-sm font-medium text-gray-700 mb-2">2e vendeur :</label>
                            <input type="text"
                                id="nom_commercial_2"
                                readonly
                                value="{{ optional($commerciaux->firstWhere('id', $partage_avec))->prenom ?? '' }} {{ optional($commerciaux->firstWhere('id', $partage_avec))->nom ?? '' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        </div>
                        <div>
                            <label for="repartition_2" class="block text-sm font-medium text-gray-700 mb-2">Taux répartition (%) *</label>
                            <input type="number"
                                id="repartition_2"
                                wire:model.live="repartition_2"
                                {{ !in_array(auth()->user()->role, ['directeur', 'admin']) ? 'readonly' : '' }}
                                class="w-full px-3 py-2 border border-gray-300 rounded-md {{ !in_array(auth()->user()->role, ['directeur', 'admin']) ? 'bg-gray-50' : '' }}">
                                @error('repartition_2')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                        </div>
                    </div>
                @endif

                
                <!-- 3e vendeur -->
                @if($vente_ok === 'ok' && $id_commercial3)
                    <div class="grid-cols-2 gap-4 @if ($isAdmin || $isDirecteur || (isset($isCreate) && !$isAdmin && !$isDirecteur && (!$isCreate && $id_commercial3))) grid @else hidden @endif">
                        <div>
                            <label for="nom_commercial_3" class="block text-sm font-medium text-gray-700 mb-2">3e vendeur :</label>
                            <input type="text"
                                id="nom_commercial_3"
                                readonly
                                value="{{ optional($commerciaux->firstWhere('id', $id_commercial3))->prenom ?? '' }} {{ optional($commerciaux->firstWhere('id', $id_commercial3))->nom ?? '' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                        </div>
                        <div>
                            <label for="repartition_3" class="block text-sm font-medium text-gray-700 mb-2">Taux répartition (%) *</label>
                            <input type="number"
                                id="repartition_3"
                                wire:model.live="repartition_3"
                                {{ !in_array(auth()->user()->role, ['directeur', 'admin']) ? 'readonly' : '' }}
                                class="w-full px-3 py-2 border border-gray-300 rounded-md {{ !in_array(auth()->user()->role, ['directeur', 'admin']) ? 'bg-gray-50' : '' }}">
                                @error('repartition_3')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                        </div>
                    </div>
                @endif

                @if($vente_ok === 'ok')
                    <!-- Détails de la vente -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Détails de la vente</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="ca_ht" class="block text-sm font-medium text-gray-700 mb-2">Devis HT (€)</label>
                                <input type="number"
                                    id="ca_ht"
                                    wire:model="devis_ht"
                                    readonly
                                    value="{{ $devis_ht  ?? '' }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                            </div>
                            <div>
                                <label for="tarif_ht" class="block text-sm font-medium text-gray-700 mb-2">Prix tarif HT (€)</label>
                                <input type="number"
                                    id="tarif_ht"
                                    wire:model="tarif_ht"
                                    readonly
                                    value="{{ $tarif_ht ?? '' }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                            </div>
                            <div>
                                <label for="montant" class="block text-sm font-medium text-gray-700 mb-2">Marge réalisée (€)</label>
                                <input type="number"
                                    id="montant"
                                    wire:model="marge"
                                    readonly
                                    value="{{ $montant ?? '' }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                            </div>
                            <div>
                                <label for="remise" class="block text-sm font-medium text-gray-700 mb-2">Taux de remise (%)</label>
                                <input type="number"
                                    id="remise"
                                    wire:model="remise"
                                    readonly
                                    value="{{ $remise ?? '' }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                            </div>
                        </div>
                    </div>
                @endif

                @if($vente_ok === 'ok')
                    <!-- Commande -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Commande</label>
                        <div class="flex gap-6">
                            <label for="commande-0" class="flex items-center  px-4 py-2 rounded-lg border border-gray-200 bg-gray-50 hover:bg-gray-100 transition {{ !$this->isAdmin ? 'bg-gray-200 cursor-not-allowed' : 'cursor-pointer' }}">
                                <input id="commande-0"
                                    type="radio"
                                    name="commande"
                                    value="0"
                                    wire:model.live="commande"
                                    {{ !$this->isAdmin ? 'disabled readonly' : '' }}
                                    class="hidden">
                                <span class="flex items-center">
                                    <span class="w-5 h-5 mr-2 flex items-center justify-center">
                                        <i class="fa-regular fa-clock text-[#77BC23] text-lg"></i>
                                    </span>
                                    <span class="text-sm text-gray-700">En attente</span>
                                </span>
                                <span class="ml-2">
                                    <span class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center bg-white">
                                        @if($commande == '0')
                                            <span class="w-2 h-2 rounded-full bg-[#77BC23] block"></span>
                                        @endif
                                    </span>
                                </span>
                            </label>
                            <label for="commande-1" class="flex items-center px-4 py-2 rounded-lg border border-gray-200 bg-gray-50 hover:bg-gray-100 transition {{ !$this->isAdmin ? 'bg-gray-200 cursor-not-allowed' : 'cursor-pointer' }}">
                                <input id="commande-1"
                                    type="radio"
                                    name="commande"
                                    value="1"
                                    wire:model.live="commande"
                                    {{ !$this->isAdmin ? 'disabled readonly' : '' }}
                                    class="hidden">
                                <span class="flex items-center">
                                    <span class="w-5 h-5 mr-2 flex items-center justify-center">
                                        <i class="fa-solid fa-box text-[#77BC23] text-lg"></i>
                                    </span>
                                    <span class="text-sm text-gray-700">Commandé</span>
                                </span>
                                <span class="ml-2">
                                    <span class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center bg-white">
                                        @if($commande == '1')
                                            <span class="w-2 h-2 rounded-full bg-[#77BC23] block"></span>
                                        @endif
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <!-- Attente -->
                    <div>
                        <label for="attente" class="block text-sm font-medium text-gray-700 mb-2">Attente</label>
                        <div class="grid grid-cols-1 gap-2">
                            @foreach($this->getAttenteOptionsProperty() as $option)
                                <label class="flex items-center {{ !$this->isAdmin && !$this->isDirecteur ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                    <input type="checkbox"
                                        wire:model="attente"
                                        value="{{ $option }}"
                                        id="attente_{{ Str::slug($option) }}"
                                        {{ !$this->isAdmin && !$this->isDirecteur ? 'disabled' : '' }}
                                        class="h-4 w-4 text-[#77BC23] focus:ring-[#77BC23] border-gray-300 rounded {{ !$this->isAdmin && !$this->isDirecteur ? 'bg-gray-200 cursor-not-allowed' : '' }}">
                                    @if ($option == "Metre décalé")
                                        <span class="ml-2 text-sm text-gray-700">non commandable après métré (8 jours)</span>
                                    @else
                                        <span class="ml-2 text-sm text-gray-700">{{ $option }}</span>
                                    @endif
                                </label>
                            @endforeach
                            
                            <textarea id="attente_autre" wire:model="attente_autre" {{ !$this->isAdmin && !$this->isDirecteur ? 'disabled readonly' : '' }} class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] resize-none @error('commentaire') !border-red-500 @enderror"></textarea>
                        </div>
                        @error('attente')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
        </div>

        <!-- Sections pleine largeur -->
        <div class="space-y-6 mt-6">
            
            @if ($vente_ok === 'ok')
                <!-- Produits -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-4">Produits *</label>
                    @foreach($this->getProduitsGroupedOptionsProperty() as $groupe => $list)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <b>{{$groupe}}</b>
                            <br/>
                            @foreach($list as $key => $label)
                            <div class="flex items-center">
                                <input type="checkbox"
                                    wire:model="produits"
                                    value="{{ $key }}"
                                    id="produit_{{ $key }}"
                                    class="h-4 w-4 text-[#77BC23] focus:ring-[#77BC23] border-gray-300 rounded">
                                <label for="produit_{{ $key }}" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                    {{ $label }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    @error('produits')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-4 bg-green-50 border border-green-200 rounded-md">
                    <p class="text-green-800 font-medium">
                        <i class="fa-solid fa-check mr-2"></i>
                        Vente réalisée !
                    </p>
                </div>
                <label for="commentaire" class="block text-sm font-medium text-gray-700 mb-2">
                    Ajouter un commentaire à la vente (optionnel)
                </label>
            @else
                    <label for="commentaire" class="block text-sm font-medium text-gray-700 mb-2">
                        Pourquoi vous n'avez pas réussi à vendre *
                    </label>
                @endif
                <textarea id="commentaire" wire:model="commentaire"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] resize-none @error('commentaire') !border-red-500 @enderror"
                    rows="4" placeholder="Décrivez le résultat de votre relance d'argumentation..."></textarea>
                @error('commentaire')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
        </div>

        <div class="flex justify-end gap-4 mt-8">
            <a href="{{ session('return_url', url()->previous()) }}"
                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                Annuler
            </a>
            <button type="submit"
                class="px-6 py-2 bg-primary text-white rounded-md hover:bg-lightprimary transition-colors">
                <i class="fa-solid fa-save mr-2"></i>
                    Enregistrer
            </button>
        </div>
    </form>
</div>
