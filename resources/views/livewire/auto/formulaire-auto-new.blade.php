<div>
        <div class="min-w-full flex items-center">
            <h1 class="text-3xl font-bold text-gray-800 text-center py-6">
              Nouveau véhicule
            </h1>
        </div>

    <div class="bg-white p-6 rounded-md shadow-sm border border-gray-200 mb-6">
        <div class="flex flex-col md:flex-row md:justify-between gap-6">
            <!-- Colonne gauche -->
            <div class="flex flex-col gap-6 w-full md:w-1/2">
                <div class="flex flex-col">
                    <label class="text-xl font-bold text-primary mb-2">Immatriculation</label>
                    <input type="text" wire:model="immat" placeholder="Entrez l'immatriculation..."
                           class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
                    @error('immat') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col">
                    <label class="text-xl font-bold text-primary mb-2">Modèle</label>
                    <input type="text" wire:model="modele" placeholder="Entrez le modèle..."
                           class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
                    @error('modele') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="flex flex-col gap-6 w-full md:w-1/2">
                <div class="flex flex-col">
                    <label class="text-xl font-bold text-primary mb-2">Conducteur</label>
                    <select wire:model="selectedConducteur"
                            class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
                        <optgroup label="Commerciaux">
                            @foreach($conducteurs['commerciaux'] as $conducteur)
                                <option value="{{ $conducteur }}">{{ $conducteur }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Poseurs">
                            @foreach($conducteurs['poseurs'] as $conducteur)
                                <option value="{{ $conducteur }}">{{ $conducteur }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    @error('selectedConducteur') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col">
                    <label class="text-xl font-bold text-primary mb-2">Date d'attribution</label>
                    <input type="date" wire:model="date_attribution"
                           class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
                    @error('date_attribution') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Secteur -->
        <div class="mt-6">
            <label class="text-xl font-bold text-primary mb-2 block">Secteur</label>
            <select wire:model="secteur"
                    class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
                <option value="">Choisissez un secteur --</option>
				@foreach($secteurs as $secteur)
                    <option value="{{ $secteur->id}}">{{$secteur->nom}}</option>
                @endforeach
            </select>
        </div>
    </div>


    <div class="flex justify-center items-center mt-6 flex-row">
            <button type="button" wire:click="save"
                    class="hover:bg-green-600 bg-[#77BC23] text-white w-50 font-semibold py-2 px-6 cursor-pointer rounded-full shadow-md transition duration-300 text-xl ease-in-out cursor-pointer">
                Enregistrer
            </button>
            <button wire:click="cancel" class="ml-5 hover:bg-[#77BC23] hover:text-white bg-gray-100 text-gray-500 w-50 font-semibold py-2 px-6 cursor-pointer rounded-full shadow-md transition duration-300 text-xl ease-in-out">
            Annuler
            </button>
        </div>
</div>
