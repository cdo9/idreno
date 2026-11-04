<div class="flex flex-col items-center justify-center min-h-screen bg-background-grey gap-8 pb-10">
    <div class="flex flex-col gap-6 p-10 bg-background rounded-xl w-full max-w-4xl">
        <h1 class="text-2xl font-bold text-primary mb-4">
            {{ $isEdit ? 'Modifier le rendez-vous' : 'Nouveau rendez-vous' }}
        </h1>
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

       

        <form wire:submit="save" class="flex flex-col gap-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Client Selection -->
                <div class="relative">
                    <label class="block text-primary font-bold mb-2">
                        Client <span class="text-warning">*</span>
                    </label>
                    <input type="text"
                           wire:model.live="clientSearch"
                           class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey"
                           placeholder="Rechercher un client..."
                           {{ $isEdit ? 'readonly' : '' }}
                           autocomplete="off">

                    @if($showClientDropdown && !$isEdit)
                        <div class="absolute z-10 w-full bg-background border border-gray-300 rounded-lg mt-1 max-h-60 overflow-y-auto shadow-lg">
                            @forelse($clients as $client)
                                <div wire:click="selectClient({{ $client->id }})"
                                     class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors">
                                    <div class="flex flex-col">
                                        <div class="font-medium text-gray-900">
                                            {{ $client->nom }} {{ $client->prenom }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $client->adresse }} - {{ $client->ville }}
                                        </div>
                                        @if($client->telephone1 || $client->telephone2)
                                            <div class="text-xs text-gray-400 mt-1">
                                                @if($client->telephone1)
                                                    <span>Tél: {{ $client->telephone1 }}</span>
                                                @endif
                                                @if($client->telephone2)
                                                    <span class="ml-2">Mobile: {{ $client->telephone2 }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="px-4 py-3 text-text-muted text-center">
                                    Aucun client trouvé pour "{{ $clientSearch }}"
                                </div>
                            @endforelse
                        </div>
                    @endif

                    @error('selectedClientId')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Date de prise de RDV -->
                <div>
                    <label class="block text-primary font-bold mb-2">
                        Date de prise de rendez-vous <span class="text-warning">*</span>
                    </label>
                    <input type="date"
                           wire:model="date_prise_rdv"
                           class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey"
                           readonly>
                    @error('date_prise_rdv')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Date du RDV -->
                <div>
                    <label class="block text-primary font-bold mb-2">
                        Date du rendez-vous <span class="text-warning">*</span>
                    </label>
                    <input type="date"
                           wire:model="date"
                           class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey">
                    @error('date')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Heure du RDV -->
                <div>
                    <label class="block text-primary font-bold mb-2">
                        Heure du rendez-vous <span class="text-warning">*</span>
                    </label>
                    <select class='w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey'
                    required wire:model="heure_rdv" name='heure-rdv'>
                        <option value="" disabled>Choisir une heure</option>
                        <option value="09:00">09:00</option>
                        <option value="09:30">09:30</option>
                        <option value="10:00">10:00</option>
                        <option value="10:30">10:30</option>
                        <option value="11:00">11:00</option>
                        <option value="11:30">11:30</option>
                        <option value="12:00">12:00</option>
                        <option value="12:30">12:30</option>
                        <option value="13:00">13:00</option>
                        <option value="13:30">13:30</option>
                        <option value="14:00">14:00</option>
                        <option value="14:30">14:30</option>
                        <option value="15:00">15:00</option>
                        <option value="15:30">15:30</option>
                        <option value="16:00">16:00</option>
                        <option value="16:30">16:30</option>
                        <option value="17:00">17:00</option>
                        <option value="17:30">17:30</option>
                        <option value="18:00">18:00</option>
                        <option value="18:30">18:30</option>
                        <option value="19:00">19:00</option>
                        <option value="19:30">19:30</option>
                    </select>
                    @error('heure_rdv')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Projet -->
                <div>
                    <label class="block text-primary font-bold mb-2">
                        Prioritée travaux 1 <span class="text-warning">*</span>
                    </label>
                    <select wire:model="projet"
                            class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey"
                            required>
                        <option value='' disabled selected hidden>Prioritée travaux</option>
                        @foreach ($projects as $categorie => $projets)
                            <optgroup label="{{ $categorie }}">
                                @foreach ($projets as $slug => $projet)
                                    <option value='{{ $projet }}'>
                                        {{ $projet }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('projet')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Quantité 1 -->
                <div>
                    <label class="block text-primary font-bold mb-2">
                        Quantité/Surface 1
                    </label>
                    <input type="number"
                           wire:model="quantite1"
                           class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey"
                           min="0"
                           step="1">
                    @error('quantite1')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Projet 2 -->
                <div>
                    <label class="block text-primary font-bold mb-2">Prioritée travaux 2</label>
                    <select wire:model="projet2"
                            class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey">
                        <option value=''>Sélectionner une prioritée travaux</option>
                        @foreach ($projects as $categorie => $projets)
                            <optgroup label="{{ $categorie }}">
                                @foreach ($projets as $slug => $projet)
                                    <option value='{{ $projet }}'>
                                        {{ $projet }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('projet2')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Quantité 2 -->
                <div>
                    <label class="block text-primary font-bold mb-2">Quantité/Surface 2</label>
                    <input type="number"
                           wire:model="quantite2"
                           class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey"
                           min="0"
                           step="1">
                    @error('quantite2')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                <!-- Couple présent -->
                <div>
                    <label class="block text-primary font-bold mb-2">
                        Couple présent <span class="text-warning">*</span>
                    </label>
                    <select wire:model="couple_present"
                            class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey"
                            required>
                        <option value="oui" selected>Oui</option>
                        <option value="non">Non</option>
                        <option value="seul">Personne seule</option>
                    </select>
                    @error('couple_present')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 1h30 à consacrer -->
                <div>
                    <label class="block text-primary font-bold mb-2">
                        1h30 à consacrer <span class="text-warning">*</span>
                    </label>
                    <select wire:model="temps_disponible"
                            class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey"
                            required>
                        <option value="oui" selected>Oui</option>
                        <option value="non">Non</option>
                    </select>
                    @error('temps_disponible')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Date de réalisation du projet -->
                <div>
                    <label class="block text-primary font-bold mb-2">
                        Date de réalisation du projet <span class="text-warning">*</span>
                    </label>

                    <select wire:model="date_realisation_du_projet"
                        class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey">
                        <option value="" disabled {{ !$isEdit ? 'selected' : '' }} hidden>Choisir...</option>
                        <option value="1">< 6 mois</option>
                        <option value="2">6 mois à 1 an</option>
                        <option value="3">> 1 an</option>
                    </select>
                    @error('date_realisation_du_projet')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Pourquoi ce projet -->
                <div class="md:col-span-2">
                    <label class="block text-primary font-bold mb-2">
                        Pourquoi ce projet <span class="text-warning">*</span>
                    </label>
                    <select wire:model="pourquoi"
                            class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey">
                        <option value="" disabled {{ !$isEdit ? 'selected' : '' }} hidden>Choisir...</option>
                        <option value="confort_thermique">Confort thermique</option>
                        <option value="economies">Economies</option>
                        <option value="valoriser_bien">Valoriser mon bien</option>
                        <option value="panne">Résoudre une panne</option>
                        <option value="vetuste">Vétusté</option>
                        <option value="securite">Sécurité</option>
                    </select>
                    @error('pourquoi')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Partagé avec -->
                <div class="md:col-span-2">
                    <label class="block text-primary font-bold mb-2">
                        Partagé avec
                    </label>
                    <select wire:model="partage_avec"
                            class="w-full border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey">
                        <option value="">Sélectionner un commercial</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->display_name }}</option>
                        @endforeach
                    </select>
                    @error('partage_avec')
                        <div class="text-error text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Compte rendu -->
                <div>
                    <label class="block text-primary font-bold mb-2">Compte rendu</label>
                    <textarea wire:model="compte_rendu" 
                            rows="4"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey"></textarea>
                </div>
            </div>

            <!-- Hidden sector_id field -->
            <input type="hidden" id="secteur" wire:model="secteur" value="{{ $secteur }}">

            <!-- Actions -->
            <div class="flex gap-4 mt-8">
                <button type="submit"
                        class="bg-primary text-white px-6 py-3 rounded-full hover:bg-lightprimary transition-colors">
                    {{ $isEdit ? 'Mettre à jour' : 'Créer' }}
                </button>
                <button type="button"
                        wire:click="cancel"
                        class="bg-gray-500 text-white px-6 py-3 rounded-full hover:bg-gray-600 transition-colors">
                    Annuler
                </button>
            </div>
             @if($googleCalendarUrl)
                <a  target="_blank" class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-primary text-white font-semibold transition-colors shadow" href="{{$googleCalendarUrl}}">Ajouter à l'agenda</a>
            @endif
        </form>
    </div>
</div>

<script>

// Livewire event listeners
document.addEventListener('livewire:init', () => {
    Livewire.on('rendez-vous-saved', (event) => {
        const message = event[0]?.message || event.message || 'RDV sauvegardé';
        console.log('Rendez-vous saved:', message);
    });

    Livewire.on('rendez-vous-error', (event) => {
        const message = event[0]?.message || event.message || 'Une erreur est survenue';
        console.log('Rendez-vous saved:', message);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative')) {
            @this.set('showClientDropdown', false);
        }
    });

    // Listen for sector changes
    window.addEventListener('secteurChanged', function() {
        console.log('Sector changed event received');
        setSecteur();
    });
});
</script>
