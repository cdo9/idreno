<div>
    <!-- Modal for contact form -->
    <div id="map-modal-content" name="formwindowcontent" class="map-modal-content" >
        <div class="px-5 py-2 w-full max-w-md bg-white border border-gray-200 rounded-md shadow-lg">
            <!-- Form -->
            <form action="{{ route('formulaire-contact') }}" method="POST" class="space-y-4">
                @csrf
                <!-- General error message -->
                @error('general')
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded error-messages">
                        {{ $message }}
                    </div>
                @enderror

                
                <div class="space-y-3">
                    <!--<h4 class="text-md font-medium text-gray-700 border-l-4 border-homiso-green pl-3">
                        Informations personnelles
                    </h4>-->

                    <!--<div>
                        <label for="choix" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                            Genre <span class="text-red-500">*</span>
                        </label>
                        <select name="choix" id="choix" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            <option value=''>Sélectionnez le genre</option>
                            <option value='monsieur'>Monsieur</option>
                            <option value='madame'>Madame</option>
                            <option value='m. & mme'>M. & Mme</option>
                            <option value='sci'>SCI</option>
                            <option value='société'>Société</option>
                        </select>
                        @error('choix') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>-->

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="nom" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">Nom</label>
                            <input name="nom" id="nom" type='text' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            @error('nom') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
						<div>
                            <label for="appart" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">Appartement / Etage</label>
                            <input name="appart" id="appart" type='text' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            @error('appart') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <!--<div>
                            <label for="prenom" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                                Prénom <span class="text-red-500">*</span>
                            </label>
                            <input name="prenom" id="prenom" type='text' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            @error('prenom') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>-->
                    </div>
                </div>

                <!-- Address Section -->
                <div class="space-y-3">
                    <!--<h4 class="text-md font-medium text-gray-700 border-l-4 border-homiso-orange pl-3">Adresse</h4>-->

                    <div>
                        <label for="adresse" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                            Adresse <span class="text-red-500">*</span>
                        </label>
                        <input name="adresse" id="adresse" type='text' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                        @error('adresse') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="code_postal" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                                Code postal <span class="text-red-500">*</span>
                            </label>
                            <input name="code_postal" id="code_postal" type='text' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            @error('code_postal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="ville" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                                Ville <span class="text-red-500">*</span>
                            </label>
                            <input name="ville" id="ville" type='text' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            @error('ville') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Section -->
                <div class="space-y-3">
                    <!--<h4 class="text-md font-medium text-gray-700 border-l-4 border-blue-500 pl-3">Contact</h4>-->

                    <!--<div>
                        <label for="telephone1" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                            Téléphone principal <span class="text-red-500">*</span>
                        </label>
                        <input name="telephone1" id="telephone1" type='text' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                        @error('telephone1') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="telephone2" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">Téléphone secondaire</label>
                        <input name="telephone2" id="telephone2" type='text' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                        @error('telephone2') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>-->

                    <div>
                        <label for="signe" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">Commentaire</label>
                        <input name="signe" id="signe" type='text' class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                        @error('signe') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Project Information Section -->
                <div class="space-y-3">
                    <!--<h4 class="text-md font-medium text-gray-700 border-l-4 border-purple-500 pl-3">Informations projet</h4>-->

                    <div>
                        <label for="source" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                            Source <span class="text-red-500">*</span>
                        </label>
                        <select name="source" id="source" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            @foreach($sources as $sourceType)
                                @foreach($sourceType as $id=>$source)
                                    <option value='{{ $id }}' @if($id == 1) selected @endif>{{ $source }}</option>
                                @endforeach
                            @endforeach
                        </select>
                        @error('source') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!--<div>
                        <label for="projet" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">Projet</label>
                        <select name="projet" id="projet" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            <option value=''>Sélectionnez le projet</option>
                            @foreach($priorities as $categorie => $priorites)
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
                        <label for="type_habitation" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                            Type d'habitation <span class="text-red-500">*</span>
                        </label>
                        <select name="type_habitation" id="type_habitation" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            <option value=''>Sélectionnez le type</option>
                            <option value='Maison Individuelle'>Maison Individuelle</option>
                            <option value='Appartement'>Appartement</option>
                        </select>
                        @error('type_habitation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="proprietaire" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                            Propriétaire <span class="text-red-500">*</span>
                        </label>
                        <select name="proprietaire" id="proprietaire" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            <option value=''>Sélectionnez</option>
                            <option value='1'>Oui</option>
                            <option value='0'>Non</option>
                        </select>
                        @error('proprietaire') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="profil_client" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                            Habitude d'achat <span class="text-red-500">*</span>
                        </label>
                        <select name="profil_client" id="profil_client" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            <option value=''>Sélectionnez le profil</option>
                            <option value='Intéressé'>Intéressé</option>
                            <option value='Peu intéressé'>Peu intéressé</option>
                            <option value='Très intéressé'>Très intéressé</option>
                            <option value='À recontacter'>À recontacter</option>
                        </select>
                        @error('profil_client') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>-->

                    <div>
                        <label for="type" class="block text-[0.625rem] md:text-xs font-medium text-gray-700 mb-1">
                            Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-homiso-green focus:border-homiso-green text-[0.625rem] md:text-xs">
                            <option value=''>Sélectionnez le type</option>
                            <option value='0'>Précontact</option>
                            <option value='6'>Refus</option>
                            <option value='7'>Absent</option>
                            <option value='8'>Relance</option>
                        </select>
                        @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <input type="hidden" name="gps_lat" id="gps_lat"/>
                <input type="hidden" name="gps_lng" id="gps_lng"/>
                <input type="hidden" name="user_lng" id="user_lng"/>
                <input type="hidden" name="user_lat" id="user_lat"/>
                <input type="hidden" name="secteur" id="secteur"  value={{session('selectedSecteur')}}/>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-gray-200">
                    <input type="text" id="secteur" name="secteur" value="{{ session('selectedSecteur') }}" hidden>
                    <button type="submit" id="submitContact"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-md transition duration-200 ease-in-out transform hover:scale-105 shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Valider le contact</span>
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
