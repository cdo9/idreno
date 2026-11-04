<div>
    @if (session()->has('message'))
        <div class="mb-4 px-4 py-3 rounded text-green-800 bg-green-100 border border-green-200">
            {{ session('message') }}
        </div>
    @endif
<div class="flex items-center justify-between flex-wrap bg-white px-4 py-3 border-b border-gray-200 mb-4 shadow-sm rounded-md">

    <div class=" py-5 flex items-center space-x-3">

        <div>
            <input type="date" wire:model.live="date1" class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">

        </div>

        <div>
            <input type="date" wire:model.live="date2" class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">

        </div>
    </div>
    <div class="py-4">
        <button wire:click="toggleAfficherSansKm"
                class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">
            {{ $afficherSansKm ? 'Cacher les véhicules sans km' : 'Afficher les véhicules sans km' }}
        </button>
    </div>
    <div class="mb-4">
        <a href="{{ route('nouveau-véhicule') }}"
           class="px-4 py-2  font-bold bg-lightprimary text-white rounded hover:bg-primary">
             Ajouter une voiture
        </a>
    </div>
    <div class="w-full overflow-x-auto">
    <table class="min-w-full text-sm text-center rounded-md divide-gray-200">
    <thead  class="min-w-full border-collapse text-sm">
    <tr>
        <th class="bg-primary px-4 py-2 rounded-tl-lg text-white text-center font-semibold text-sm cursor-pointer"> Véhicule </th>
        <th class="bg-primary px-4 py-2 text-white text-center font-semibold text-sm cursor-pointer"> Modèle </th>
        <th class="bg-primary px-4 py-2 text-white text-center font-semibold text-sm cursor-pointer"> Conducteur </th>
        <th class="bg-primary px-4 py-2 text-white text-center font-semibold text-sm cursor-pointer"> Date Attribution </th>
        <th class="bg-primary px-4 py-2 text-white text-center font-semibold text-sm cursor-pointer"> KM Total </th>
        <th class="bg-primary px-4 py-2 text-white text-center font-semibold text-sm cursor-pointer"> KM Période </th>
        <th class="bg-primary px-4 py-2 text-white text-center font-semibold text-sm cursor-pointer"> Dépassement <br> Frais Kilométriques </th>
        <th class="bg-primary px-4 py-2 rounded-tr-lg text-white text-center font-semibold text-sm cursor-pointer"> Actions </th>
    </tr>
    </thead>
    <tbody>
    @foreach ($vehicules as $vehicule)
    <tr class="odd:bg-gray-200 p-3">
        <td class="px-3 py-2 text-sm">{{ $vehicule->immatriculation }}</td>
        <td class="px-3 py-2 text-sm">{{ $vehicule->modele }}</td>
        <td class="px-3 py-2 text-sm">{{ strtoupper($vehicule->conducteur) }}</td>
        <td class="px-3 py-2 text-sm">{{ \Carbon\Carbon::parse($vehicule->date_attribution)->format('d/m/Y') }}</td>
        <td class="px-3 py-2 text-sm">{{ number_format($vehicule->kmTot, 0, '.', ' ') }} km</td>
        <td class="px-3 py-2 text-sm">{{ number_format(max(0, $vehicule->kmPeriode), 0, '.', ' ') }} km</td>
        <td class="px-3 py-2 text-sm">{{ number_format($vehicule->kmFrais, 0, '.', ' ') }} €</td>
        <td>
            <a href="{{ route('modifier-vehicule', ['id' => $vehicule->id]) }}" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-[#77BC23] rounded hover:bg-green-300 transition">
                <span class='fa fa-edit mr-1'></span>  Éditer
            </a>
            <a href="{{ route('supprimer-vehicule', ['id' => $vehicule->id]) }}" class="inline-flex items-center  px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded hover:bg-red-200 transition">
                <span class='fa fa-trash mr-1'></span> Supprimer
            </a>
        </td>
    </tr>
        @endforeach
    </tbody>
</table>
    </div>
</div>
