<div class="flex flex-wrap justify-center gap-6">
    @foreach ($secteurs as $secteur)
        <div class="bg-white shadow-md rounded-2xl w-64 p-5 flex flex-col items-center hover:shadow-lg transition-shadow duration-300">
            <h2 class="text-lg font-semibold text-white bg-primary rounded-full px-4 py-2 mb-4 text-center w-full">
                {{ $secteur->nom }}
            </h2>

            <div class="text-sm text-gray-600 mb-2 flex flex-col items-center gap-2">

                <input
                    type="color"
                    value="{{ $secteur->couleur }}"
                    wire:change="updateCouleur({{ $secteur->id }}, $event.target.value)"
                    class="w-55 h-12 rounded border"
                >
            </div>

            @if ($secteur->coords != null)
                <div class="text-sm text-gray-600 mt-2 text-ellipsis max-h-16 w-full text-center px-2 overflow-auto">
                    <span class="font-medium text-gray-800">Coordonnées :</span> {{ $secteur->coords }}
                </div>
            @endif
        </div>
    @endforeach
</div>
