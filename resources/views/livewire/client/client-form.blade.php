<div>
    @if($showForm)
        <!-- Modal Overlay -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50" wire:click="closeClientForm">
            <!-- Modal Content -->
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto m-4" wire:click.stop>
                <!-- Modal Header -->
                <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium">
                            @if($isEditMode)
                                Modifier le client
                            @else
                                Nouveau client
                            @endif
                        </h3>
                        <button wire:click="closeClientForm" class="text-white hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <!-- Success/Error Messages -->
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

                    <form wire:submit.prevent="save">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Section Identité -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Informations personnelles</h4>
                            </div>

                            <!-- Civilité -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Civilité *</label>
                                <select wire:model="genre" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Sélectionner...</option>
                                    @foreach($civilites as $civilite)
                                        <option value="{{ $civilite }}">{{ $civilite }}</option>
                                    @endforeach
                                </select>
                                @error('genre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Nom -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                                <input type="text" wire:model="nom" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('nom') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Prénom -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                                <input type="text" wire:model="prenom" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('prenom') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" wire:model="mail" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('mail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Section Adresse -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2 mt-6">Adresse</h4>
                            </div>

                            <!-- Adresse -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Adresse *</label>
                                <input type="text" wire:model="adresse" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('adresse') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Code postal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Code postal *</label>
                                <input type="text" wire:model="code_postal" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('code_postal') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Ville -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ville *</label>
                                <input type="text" wire:model="ville" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('ville') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Section Contact -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2 mt-6">Contact</h4>
                            </div>

                            <!-- Téléphone 1 -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone principal *</label>
                                <input type="tel" wire:model="telephone1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('telephone1') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Téléphone 2 -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone secondaire</label>
                                <input type="tel" wire:model="telephone2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('telephone2') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Section Informations commerciales -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2 mt-6">Informations commerciales</h4>
                            </div>

                            <!-- Source -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Source *</label>
                                <select wire:model="id_source" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Sélectionner...</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source->id }}">{{ $source->nom }}</option>
                                    @endforeach
                                </select>
                                @error('id_source') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Type de client -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Type de client *</label>
                                <select wire:model="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="0">Précontact</option>
                                    <option value="1">Prospect</option>
                                    <option value="2">Client</option>
                                    <option value="3">RDV pris</option>
                                    <option value="4">RDV effectué</option>
                                    <option value="5">Devis envoyé</option>
                                    <option value="6">Refus</option>
                                    <option value="7">Absent</option>
                                    <option value="8">Relance</option>
                                </select>
                                @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Commercial -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Commercial</label>
                                <select wire:model="commercial_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach($commercials as $commercial)
                                        <option value="{{ $commercial->id }}">{{ $commercial->prenom }} {{ $commercial->nom }}</option>
                                    @endforeach
                                </select>
                                @error('commercial_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Partage avec -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Partager avec</label>
                                <select wire:model="partage_avec" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Aucun partage</option>
                                    @foreach($commercials as $commercial)
                                        <option value="{{ $commercial->id }}">{{ $commercial->prenom }} {{ $commercial->nom }}</option>
                                    @endforeach
                                </select>
                                @error('partage_avec') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Section Habitation -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2 mt-6">Informations habitation</h4>
                            </div>

                            <!-- Type d'habitation -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Type d'habitation *</label>
                                <select wire:model="type_habitation" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Sélectionner...</option>
                                    @foreach($typeHabitations as $typeHab)
                                        <option value="{{ $typeHab }}">{{ $typeHab }}</option>
                                    @endforeach
                                </select>
                                @error('type_habitation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Propriétaire -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Propriétaire *</label>
                                <select wire:model="proprietaire" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Sélectionner...</option>
                                    <option value="oui">Oui</option>
                                    <option value="non">Non</option>
                                </select>
                                @error('proprietaire') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Profession Monsieur -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profession (Monsieur)</label>
                                <input type="text" wire:model="profession" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('profession') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Profession Madame -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profession (Madame)</label>
                                <input type="text" wire:model="profession2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('profession2') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Profil client -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Habitude d'achat *</label>
                                <input type="text" wire:model="profil_clients" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Famille, Particulier...">
                                @error('profil_clients') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Secteur -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Secteur</label>
                                <select wire:model="secteur" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach($secteurs as $secteurItem)
                                        <option value="{{ $secteurItem->id }}">{{ $secteurItem->nom }}</option>
                                    @endforeach
                                </select>
                                @error('secteur') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Section Projet -->
                            <div class="md:col-span-2">
                                <h4 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2 mt-6">Projet et suivi</h4>
                            </div>

                            <!-- Projet -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Projet</label>
                                <input type="text" wire:model="projet" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('projet') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Signe particulier -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signe particulier</label>
                                <input type="text" wire:model="signe" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('signe') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Date de relance -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date de relance</label>
                                <input type="date" wire:model="date_relance" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('date_relance') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Coordonnées GPS -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Coordonnées GPS</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" wire:model="gps_lat" placeholder="Latitude" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" readonly>
                                    <input type="text" wire:model="gps_lng" placeholder="Longitude" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" readonly>
                                </div>
                                <small class="text-gray-500">Les coordonnées GPS sont définies automatiquement lors de la création depuis la carte.</small>
                            </div>

                            <!-- Commentaire -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Commentaire</label>
                                <textarea wire:model="commentaire" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                @error('commentaire') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex justify-between items-center pt-6 mt-6 border-t border-gray-200">
                            <div>
                                @if($isEditMode)
                                    <button type="button" wire:click="deleteClient" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition-colors"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
                                        Supprimer
                                    </button>
                                @endif
                            </div>
                            <div class="flex space-x-3">
                                <button type="button" wire:click="closeClientForm" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md transition-colors">
                                    Annuler
                                </button>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors">
                                    @if($isEditMode)
                                        Mettre à jour
                                    @else
                                        Créer
                                    @endif
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
