<div>
    <!-- Modal for contact form -->
    @if($showForm)
        <div id="map-modal-content" class="map-modal-content" style="display: block;">
            <div class="p-5 w-full max-w-md bg-white border border-gray-200 rounded-md shadow-lg">
                <!-- Modal Header -->
                <div class="text-center border-b border-gray-200 pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Nouveau Contact</h3>
                    <p class="text-sm text-gray-600 mt-1">Veuillez remplir les informations du client</p>
                    <button wire:click="closeForm" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="save" class="space-y-4">
                    <!-- General error message -->
                    @error('general')
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Personal Information Section -->
                    <div class="space-y-3">
                        <h4 class="text-md font-medium text-gray-700 border-l-4 border-homiso-green pl-3">
                            Informations personnelles
                        </h4>

                        <!-- Genre -->
                        <div>
                            <label for="choix" class="block text-sm font-medium text-gray-700 mb-1">
                                Genre <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="choix" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                <option value=''>Sélectionnez le genre</option>
                                <option value='monsieur'>Monsieur</option>
                                <option value='madame'>Madame</option>
                                <option value='m. & mme'>M. & Mme</option>
                                <option value='sci'>SCI</option>
                                <option value='société'>Société</option>
                            </select>
                            @error('choix') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nom & Prénom -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                                <input type='text' wire:model="nom" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                @error('nom') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">
                                    Prénom <span class="text-red-500">*</span>
                                </label>
                                <input type='text' wire:model="prenom" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                @error('prenom') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="space-y-3">
                        <h4 class="text-md font-medium text-gray-700 border-l-4 border-homiso-orange pl-3">Adresse</h4>

                        <div>
                            <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">
                                Adresse <span class="text-red-500">*</span>
                            </label>
                            <input type='text' wire:model="adresse" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                            @error('adresse') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="code_postal" class="block text-sm font-medium text-gray-700 mb-1">
                                    Code postal <span class="text-red-500">*</span>
                                </label>
                                <input type='text' wire:model="code_postal" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                @error('code_postal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="ville" class="block text-sm font-medium text-gray-700 mb-1">
                                    Ville <span class="text-red-500">*</span>
                                </label>
                                <input type='text' wire:model="ville" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                @error('ville') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Contact Section -->
                    <div class="space-y-3">
                        <h4 class="text-md font-medium text-gray-700 border-l-4 border-blue-500 pl-3">Contact</h4>

                        <div>
                            <label for="telephone1" class="block text-sm font-medium text-gray-700 mb-1">
                                Téléphone principal <span class="text-red-500">*</span>
                            </label>
                            <input type='text' wire:model="telephone1" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                            @error('telephone1') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="telephone2" class="block text-sm font-medium text-gray-700 mb-1">Téléphone secondaire</label>
                            <input type='text' wire:model="telephone2" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                            @error('telephone2') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="signe" class="block text-sm font-medium text-gray-700 mb-1">Signe distinctif</label>
                            <input type='text' wire:model="signe" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                            @error('signe') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Project Information Section -->
                    <div class="space-y-3">
                        <h4 class="text-md font-medium text-gray-700 border-l-4 border-purple-500 pl-3">Informations projet</h4>

                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700 mb-1">
                                Source <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="source" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                <option value=''>Sélectionnez la source</option>
                                @foreach($this->sources as $source)
                                    <option value='{{ $source->id }}'>{{ $source->nom }}</option>
                                @endforeach
                            </select>
                            @error('source') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="projet" class="block text-sm font-medium text-gray-700 mb-1">Projet</label>
                            <select wire:model="projet" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                <option value=''>Sélectionnez le projet</option>
                                @foreach($this->priorities as $categorie => $priorites)
                                    <optgroup label="{{ $categorie }}">
                                        @foreach($priorites as $slug => $priorite)
                                            <option value='{{ $priorite }}'>{{ $priorite }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('projet') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="type_habitation" class="block text-sm font-medium text-gray-700 mb-1">
                                Type d'habitation <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="type_habitation" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                <option value=''>Sélectionnez le type</option>
                                <option value='Maison Individuelle'>Maison Individuelle</option>
                                <option value='Appartement'>Appartement</option>
                            </select>
                            @error('type_habitation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="proprietaire" class="block text-sm font-medium text-gray-700 mb-1">
                                Propriétaire <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="proprietaire" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                <option value=''>Sélectionnez</option>
                                <option value='oui'>Oui</option>
                                <option value='non'>Non</option>
                            </select>
                            @error('proprietaire') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="profil_client" class="block text-sm font-medium text-gray-700 mb-1">
                                Habitude d'achat <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="profil_client" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                <option value=''>Sélectionnez le profil</option>
                                <option value='Intéressé'>Intéressé</option>
                                <option value='Peu intéressé'>Peu intéressé</option>
                                <option value='Très intéressé'>Très intéressé</option>
                                <option value='À recontacter'>À recontacter</option>
                            </select>
                            @error('profil_client') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                Type <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-sm">
                                <option value=''>Sélectionnez le type</option>
                                <option value='0'>Précontact</option>
                                <option value='6'>Refus</option>
                                <option value='7'>Absent</option>
                                <option value='8'>Relance</option>
                            </select>
                            @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-gray-200">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="w-full bg-homiso-green hover:bg-green-600 text-white font-medium py-3 px-4 rounded-md transition duration-200 ease-in-out transform hover:scale-105 shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="flex items-center justify-center">
                                <div wire:loading wire:target="save" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                                <svg wire:loading.remove wire:target="save" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span wire:loading.remove wire:target="save" wire:click="save">Valider le contact</span>
                                <span wire:loading wire:target="save">Création en cours...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <style>
            .map-modal-content{
                pointer-events: auto;
            }
        </style>
    @endif

    <!-- Success message -->
    @if(session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50" id="success-message">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(function() {
                document.getElementById('success-message').remove();
            }, 5000);
        </script>
    @endif
</div>
