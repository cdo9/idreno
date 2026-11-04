<div class="max-w-lg mx-auto bg-white shadow-sm rounded-md border border-gray-200 p-6 mt-6">

    <h2 class="text-lg font-semibold text-primary mb-4">
        Supprimer le challenge
    </h2>

    <p class="text-sm text-primary mb-6">
        Es-tu sûr de vouloir supprimer le challenge suivant ?
    </p>

    <div class="mb-6 p-3 border border-gray-200 rounded bg-gray-50">
        <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($challenge->date)->format('d/m/Y') }}</p>
        <p><strong>Challenge :</strong> {{ $challenge->challenge }}</p>
    </div>

    <div class="flex space-x-3">
        <button wire:click="delete"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200 cursor-pointer">
            Supprimer
        </button>
        <a href="{{ url('/challenges') }}"
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm transition-colors duration-200">
            Annuler
        </a>
    </div>
</div>
