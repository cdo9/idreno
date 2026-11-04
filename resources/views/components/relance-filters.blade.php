@props(['secteurs', 'title' => 'Filtres de relance'])

<div>
    <form method="POST" wire:submit.prevent="filtrer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end bg-white p-6 rounded-xl shadow">
        <!-- Date début -->
        <div class="flex flex-col">
            <label for="date_debut" class="mb-1 text-sm font-semibold text-gray-700">Date début</label>
            <input id="date_debut" wire:model.live="date_debut" type="date" placeholder="Date début" class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary focus:outline-none">
        </div>

        <!-- Date fin -->
        <div class="flex flex-col">
            <label for="date_fin" class="mb-1 text-sm font-semibold text-gray-700">Date fin</label>
            <input id="date_fin" wire:model.live="date_fin" type="date" placeholder="Date fin" class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary focus:outline-none">
        </div>

        <!-- Secteur -->
        <div class="flex flex-col">
            <label for="secteur_id" class="mb-1 text-sm font-semibold text-gray-700">Secteur</label>
            <select id="secteur_id" wire:model.live="secteur_id" class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary focus:outline-none">
                <option value="">Tous les secteurs</option>
                @foreach($secteurs as $secteur)
                    <option value="{{ $secteur->id }}">{{ $secteur->nom }}</option>
                @endforeach
            </select>
        </div>

        <!-- Bouton Réinitialiser -->
        <div class="flex flex-col justify-end">
            <button type="button" wire:click="resetFilters" class="bg-gray-500 text-white px-6 py-2 rounded-full hover:bg-gray-600 transition-colors font-semibold">
                Réinitialiser
            </button>
        </div>
    </form>
</div>
