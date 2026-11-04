<div class="flex flex-col gap-6 p-6 bg-background rounded-xl w-full max-w-4xl shadow-md">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-[#77BC23]">
            <i class="fa-solid fa-user-edit mr-3"></i>
            Édition Contact
        </h1>
        <a href="{{ session('return_url', url()->previous()) }}" 
           class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Retour
        </a>
    </div>

    <!-- Formulaire d'édition du contact -->
    <form wire:submit.prevent="save" class="bg-white p-6 rounded-lg shadow-md">
        <!-- Messages d'erreur globaux -->
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session()->has('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session()->has('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        <h2 class="text-xl font-semibold text-gray-800 mb-6">Informations du contact</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informations personnelles -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-700 border-b pb-2">Informations personnelles</h3>
                
                 <div>
                    <label for="genre" class="block text-sm font-medium text-gray-700 mb-2">Civilité</label>
                    <select id="genre" wire:model="genre" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] @error('genre') !border-red-500 @enderror">
                        <option value="">Non renseigné</option>
                        @foreach($this->civilites as $civilite)
                            <option value="{{ $civilite }}">{{ $civilite }}</option>
                        @endforeach
                    </select>
                    @error('genre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="id_source" class="block text-sm font-medium text-gray-700 mb-2">Source</label>
                    <select id="id_source" wire:model="id_source"
                            class="capitalize w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] @error('id_source') !border-red-500 @enderror">
                        <option value="">Non renseigné</option>
                        @foreach($this->sources as $source)
                            <option value="{{ $source->id }}">{{ $source->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                    <input type="text" id="nom" wire:model="nom" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] @error('nom') !border-red-500 @enderror">
                    @error('nom')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                    <input type="text" id="prenom" wire:model="prenom" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] @error('prenom') !border-red-500 @enderror">
                    @error('prenom')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="age" class="block text-sm font-medium text-gray-700 mb-2">Âge</label>
                    <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                        {{ $this->age ?? 'Rentrez une date de naissance' }}
                    </div>
                </div>

                <div>
                    <label for="date_anniversaire" class="block text-sm font-medium text-gray-700 mb-2">Date d'anniversaire</label>
                    <input type="date" id="date_anniversaire" wire:model="date_anniversaire" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>

                <div>
                    <label for="profession" class="block text-sm font-medium text-gray-700 mb-2">Profession (Mr)</label>
                    <input type="text" id="profession" wire:model="profession" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>

                <div>
                    <label for="profession2" class="block text-sm font-medium text-gray-700 mb-2">Profession (Mme)</label>
                    <input type="text" id="profession2" wire:model="profession2" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>

                <div>
                    <label for="signe" class="block text-sm font-medium text-gray-700 mb-2">Signe distinctif</label>
                    <input type="text" id="signe" wire:model="signe" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>
            </div>

            <!-- Contact et adresse -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-700 border-b pb-2">Contact et adresse</h3>
                
                <div>
                    <label for="telephone1" class="block text-sm font-medium text-gray-700 mb-2">Téléphone 1 *</label>
                    <input type="tel" id="telephone1" wire:model="telephone1" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] @error('telephone1') !border-red-500 @enderror"
                        placeholder="Ex: 01 23 45 67 89">
                    @error('telephone1')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telephone2" class="block text-sm font-medium text-gray-700 mb-2">Téléphone 2</label>
                    <input type="tel" id="telephone2" wire:model="telephone2" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] @error('telephone2') !border-red-500 @enderror"
                        placeholder="Ex: 06 12 34 56 78">
                    @error('telephone2')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" wire:model="email" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] @error('email') !border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="relative">
                    <label for="searchAdress" class="block text-sm font-medium text-gray-700 mb-2">Rechercher une adresse</label>
                    <input type="text" id="searchAdress" wire:model.live.debounce.250ms="searchAdress.address_string"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] @error('adresse') !border-red-500 @enderror"
                            placeholder="Tapez pour rechercher une adresse...">
                    @if(!empty($adressesAutoComplete))
                        <div id="autocomplete-list" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-auto">
                            @foreach($adressesAutoComplete as $index => $adresse)
                                <div wire:click="selectAdress({{ $index }})"
                                     class="cursor-pointer hover:bg-gray-100 px-3 py-2 border-b border-gray-100 last:border-b-0">
                                    {{ $adresse['address_string'] }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @error('searchAdress')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="adresse" class="block text-sm font-medium text-gray-700 mb-2">Adresse *</label>
                    <input type="text" id="adresse" wire:model="adresse" disabled
                            class="w-full px-3 py-2 border border-gray-300 bg-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] @error('adresse') !border-red-500 @enderror">
                    @error('adresse')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="cp" class="block text-sm font-medium text-gray-700 mb-2">Code postal *</label>
                        <input type="text" id="cp" wire:model="cp" disabled
                               class="w-full px-3 py-2 border border-gray-300 bg-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        @error('cp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ville" class="block text-sm font-medium text-gray-700 mb-2">Ville *</label>
                        <input type="text" id="ville" wire:model="ville" disabled
                               class="w-full px-3 py-2 border border-gray-300 bg-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        @error('ville')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="gps_lat" class="block text-sm font-medium text-gray-700 mb-2">Latitude GPS</label>
                        <input type="number" step="any" id="gps_lat" wire:model="gps_lat" disabled
                               class="w-full px-3 py-2 border border-gray-300 bg-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                    </div>

                    <div>
                        <label for="gps_lng" class="block text-sm font-medium text-gray-700 mb-2">Longitude GPS</label>
                        <input type="number" step="any" id="gps_lng" wire:model="gps_lng" disabled
                               class="w-full px-3 py-2 border border-gray-300 bg-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                    </div>
                </div>

            </div>
        </div>

        <!-- Section Habitation -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-700 border-b pb-2 mb-4">Informations habitation</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="type_hab" class="block text-sm font-medium text-gray-700 mb-2">Type d'habitation</label>
                    <select id="type_hab" wire:model="type_hab" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        <option value="">Non renseigné</option>
                        @foreach($this->type_habitations as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="surface" class="block text-sm font-medium text-gray-700 mb-2">Surface habitable (m²)</label>
                    <input type="number" id="surface" wire:model="surface" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>

                <div>
                    <label for="proprietaire" class="block text-sm font-medium text-gray-700 mb-2">Propriétaire</label>
                    <select id="proprietaire" wire:model="proprietaire" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        <option value="">Non renseigné</option>
                        <option value="1">Oui</option>
                        <option value="0">Non</option>
                    </select>
                </div>

                <div>
                    <label for="duree_proprietaire" class="block text-sm font-medium text-gray-700 mb-2">Durée propriétaire (années)</label>
                    <input type="text" id="duree_proprietaire" wire:model="duree_proprietaire" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>
            </div>
        </div>

        <!-- Section Énergie -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-700 border-b pb-2 mb-4">Informations énergétiques</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                    <label for="isolation" class="block text-sm font-medium text-gray-700 mb-2">Isolation</label>
                    <select id="isolation" wire:model="isolation" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        <option value="">Non renseigné</option>
                        @foreach($this->niveauxIsolation as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>            
                </div>

                 <div>
                    <label for="nb_foyer" class="block text-sm font-medium text-gray-700 mb-2">Nombre de personnes au foyer</label>
                    <select id="nb_foyer" wire:model="nb_foyer" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        @foreach($this->nbFoyer as $nb)
                        <option value="{{ $nb }}" {{ $nb_foyer == $nb ? 'selected' : '' }}>{{ $nb }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="type_chauffage" class="block text-sm font-medium text-gray-700 mb-2">Type de chauffage</label>
                    <select id="type_chauffage" wire:model="type_chauffage" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        <option value="">Non renseigné</option>
                        @foreach($this->typesChauffage as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="mode_conso" class="block text-sm font-medium text-gray-700 mb-2">Méthode d'achat</label>
                    <input type="text" id="mode_conso" wire:model="mode_conso" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>

                <div>
                    <label for="conso_chauffage_qualite" class="block text-sm font-medium text-gray-700 mb-2">Consommation chauffage annuelle</label>
                    <select id="conso_chauffage_qualite" wire:model="conso_chauffage_qualite" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        <option value="">Non renseigné</option>
                        @foreach($this->consoChauffageAnnuelle as $qualite)
                            <option value="{{ $qualite }}">{{ $qualite }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="conso_chauffage" class="block text-sm font-medium text-gray-700 mb-2">Consommation chauffage annuelle (€)</label>
                    <input type="text" id="conso_chauffage" wire:model="conso_chauffage" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>

                <div>
                    <label for="eau_chaude" class="block text-sm font-medium text-gray-700 mb-2">Production d'eau chaude</label>
                    <select id="eau_chaude" wire:model="eau_chaude" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        <option value="">Non renseigné</option>
                        @foreach ($this->productionEauChaude as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="age_materiel" class="block text-sm font-medium text-gray-700 mb-2">Âge du matériel</label>
                    <select id="age_materiel" wire:model="age_materiel" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        <option value="">Non renseigné</option>
                        @foreach ($this->listeAgeMateriel as $age)
                            <option value="{{ $age }}">{{ $age }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Section Projet et finances -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-700 border-b pb-2 mb-4">Projet et finances</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="projet" class="block text-sm font-medium text-gray-700 mb-2">Projet</label>
                    <select wire:model="projet"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-[#77BC23] focus:outline-none">
                        <option value=''>Sélectionner une prioritée travaux</option>
                        @foreach ($this->projects as $categorie => $projets)
                            <optgroup label="{{ $categorie }}">
                                @foreach ($projets as $slug => $projet)
                                    <option value='{{ $projet }}'>
                                        {{ $projet }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="dernier_investissement" class="block text-sm font-medium text-gray-700 mb-2">Dernier investissement</label>
                    <input type="text" id="dernier_investissement" wire:model="dernier_investissement" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>

                <div>
                    <label for="credit_impot" class="block text-sm font-medium text-gray-700 mb-2">Connaissance des aides</label>
                    <input type="text" id="credit_impot" wire:model="credit_impot" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>

                <div>
                    <label for="date_relance" class="block text-sm font-medium text-gray-700 mb-2">Date de relance</label>
                    <input type="date" id="date_relance" wire:model="date_relance" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                </div>
            </div>
        </div>

        @if(Auth::user()->role === 'directeur' || Auth::user()->role === 'admin')
        <!-- Section Commerciaux (réservée aux directeurs) -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-700 border-b pb-2 mb-4">Gestion commerciale</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="rentre_par" class="block text-sm font-medium text-gray-700 mb-2">Commercial principal</label>
                    <select id="rentre_par" wire:model="rentre_par" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        <option value="">Aucun</option>
                        @foreach($commerciaux as $commercial)
                            <option value="{{ $commercial->id }}">{{ $commercial->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="partage_avec" class="block text-sm font-medium text-gray-700 mb-2">Commercial partagé</label>
                    <select id="partage_avec" wire:model="partage_avec" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23]">
                        <option value="">Aucun</option>
                        @foreach($commerciaux as $commercial)
                            <option value="{{ $commercial->id }}">{{ $commercial->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        @endif

        <!-- Section Débrief -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-700 border-b pb-2 mb-4">Débrief</h3>
            
            <div>
                <label for="debrief" class="block text-sm font-medium text-gray-700 mb-2">Notes et commentaires</label>
                <textarea id="debrief" wire:model="debrief" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#77BC23] focus:border-[#77BC23] resize-none"
                          placeholder="Ajoutez vos notes et commentaires sur ce contact..."></textarea>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex justify-end gap-4 mt-8">
            <a href="{{ session('return_url', url()->previous()) }}"
               class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-[#77BC23] text-white rounded-md hover:bg-[#6aa31f] transition-colors">
                <i class="fa-solid fa-save mr-2"></i>
                Enregistrer les modifications
            </button>
        </div>

        <div class="flex flex-col w-full mt-8 gap-4">
            <label class="text-[#77BC23] font-bold mb-2">Tableau liste des commentaires</label>
            @if(isset($comments) && count($comments) > 0)
                <div class="flex flex-col gap-4">
                    @foreach($comments as $comment)
                        <div class="bg-[#F3F3F3] rounded-xl shadow p-4 flex flex-col sm:flex-row sm:items-center gap-2 border border-[#DCDADA]">
                            <div class="flex flex-col sm:w-1/5">
                                <span class="text-xs text-gray-500 font-semibold">Date</span>
                                <span class="text-sm text-[#77BC23] font-bold">{{ \Carbon\Carbon::parse($comment->date)->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex flex-col sm:w-1/5">
                                <span class="text-xs text-gray-500 font-semibold">Utilisateur</span>
                                <span class="text-sm  font-bold">{{ $comment->client_prenom ?? '' }} {{ $comment->client_nom ?? '' }}</span>
                            </div>
                            <div class="flex-1 flex flex-col">
                                <span class="text-xs text-gray-500 font-semibold">Commentaire</span>
                                <span class="text-sm ">{{ $comment->commentaire }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">Aucun commentaire.</p>
            @endif
        </div>
    </form>

    <form action="{{ route('formulaire-contact') }}" method="POST" class="gap-6 p-4 sm:p-10 bg-[#F8FFF3] rounded-xl shadow-lg max-w-4xl mx-auto items-center mt-8 border-2 border-[#77BC23]">
        @csrf
        <input type="hidden" name="client_id" value="{{ $client->id }}">
        <div class="flex flex-col w-full gap-4">
            <h2 class="text-xl font-bold text-[#77BC23]">Ajouter un nouveau commentaire</h2>
            <div class="flex flex-col gap-4">
                <div class="flex flex-col w-full">
                    <textarea name="nouveau_commentaire" id="nouveau_commentaire" class="bg-white rounded-xl px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-[#77BC23] transition h-24 resize-y" placeholder="Écrivez votre commentaire ici..."></textarea>
                    @error('nouveau_commentaire')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="flex flex-row w-full justify-end">
                    <button type="submit" name="ajouter_commentaire" value="1" class="cursor-pointer bg-[#77BC23] text-white font-bold py-2 px-6 rounded-full hover:bg-[#69a41f] transition duration-300 shadow-md">Ajouter le commentaire</button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
    document.addEventListener('click', function(event) {
        const list = document.getElementById('autocomplete-list');
        if (list) {
            list.remove();
        }
    });
</script>