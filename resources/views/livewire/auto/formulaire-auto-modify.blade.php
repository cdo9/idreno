<div class="px-4 py-6 max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 text-center mb-6">Modification Véhicule</h1>

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

    <!-- Tableau kilométrage -->
    <div class="bg-white p-6 rounded-md shadow-md overflow-x-auto">
        <h2 class="text-2xl font-semibold mb-4">Kilométrage mensuel - 10 derniers mois</h2>
        <table class="min-w-full table-auto border-collapse rounded-lg overflow-hidden">
            <thead>
            <tr class="bg-[#77BC23]">
                <th class="px-4 py-2 text-white text-left">Mois</th>
                <th class="px-4 py-2 text-white text-left">Km parcourus</th>
            </tr>
            </thead>
            <tbody>
            @foreach($kmMensuels as $index => $kmData)
                <tr class="odd:bg-gray-100">
                    <td class="px-4 py-2">{{ $kmData['label'] }}</td>
                    <td class="px-4 py-2">
                        <input type="number" min="0" wire:model.defer="kmMensuels.{{ $index }}.km"
                               class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-white w-full max-w-[120px]">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Boutons -->
    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mt-8">
        <button type="button" wire:click="save"
                class="hover:bg-green-600  cursor-pointer bg-[#77BC23] text-white font-semibold py-3 px-6 rounded-full shadow-md transition duration-300 text-xl ease-in-out w-full sm:w-auto">
            Enregistrer
        </button>
        <button wire:click="cancel"
                class="hover:bg-[#77BC23] hover:text-white cursor-pointer bg-gray-100 text-gray-500 font-semibold py-3 px-6 rounded-full shadow-md transition duration-300 text-xl ease-in-out w-full sm:w-auto">
            Annuler
        </button>
    </div>
</div>
