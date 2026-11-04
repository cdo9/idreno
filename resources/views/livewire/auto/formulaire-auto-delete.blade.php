
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow mt-6">
        <h2 class="text-xl font-semibold mb-4">Confirmer la suppression</h2>

        <p>Voulez-vous vraiment supprimer ce véhicule ? </p>

        <form method="POST" action="{{ url('/supprimer-vehicule/' . $id) }}" class="mt-6 flex justify-between">
            @csrf
            <a href="{{ url('/parc-auto') }}" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                Annuler
            </a>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 cursor-pointer">
                Supprimer
            </button>
        </form>
    </div>

