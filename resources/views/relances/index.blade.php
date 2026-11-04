@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-8 pb-10">
        <div class="flex flex-col gap-6 p-6 bg-background rounded-xl w-full max-w-9xl shadow-md overflow-x-auto">
            <h1 class="text-3xl font-bold text-primary mb-6">
                <i class="fa-solid fa-phone mr-3"></i>
                Gestion des Relances
            </h1>

            <!-- Sélecteur de type -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <div class="flex gap-3">
                    <a href="{{ route('relances') }}?type=contacts"
                        class="px-6 py-3 {{ request('type') === 'contacts' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} rounded-lg transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-users"></i>
                        Relances Contacts
                    </a>
					@if(!$isProspecteur)
						<a href="{{ route('relances') }}?type=argumentations"
							class="px-6 py-3 {{ request('type') === 'argumentations' || !request('type') ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} rounded-lg transition-colors flex items-center gap-2">
							<i class="fa-solid fa-calendar-check"></i>
							Relances Argumentations
						</a>
						<a href="{{ route('relances') }}?type=financements"
							class="px-6 py-3 {{ request('type') === 'financements' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} rounded-lg transition-colors flex items-center gap-2">
							<i class="fa-solid fa-money-check"></i>
							Relances Financements
						</a>
					@endif
                </div>
            </div>

            @if (request('type') === 'contacts' || $isProspecteur)
                <livewire:relance-listing 
                :type="'contacts'" 
                :date-debut="request('date_debut', '')" 
                :date-fin="request('date_fin', '')" 
                :secteur-id="request('secteur_id', '')" />
            @elseif (request('type') === 'financements')
                <livewire:relance-listing 
                :type="'financements'" 
                :date-debut="request('date_debut', '')" 
                :date-fin="request('date_fin', '')" 
                :secteur-id="request('secteur_id', '')" />
            @else
                {{-- J'ai pas tout compris, mais je suis obligé d'appeler <livewire:relance-listing /> pour les argumentations --}}
                {{-- Mais après on utilise <livewire:relance-listing-argumentations /> à partir du composant dans app\Livewire\RelanceListing.php --}}
                <livewire:relance-listing 
                :type="'argumentations'" 
                :date-debut="request('date_debut', '')" 
                :date-fin="request('date_fin', '')" 
                :secteur-id="request('secteur_id', '')" />
            @endif
        </div>
    </div>
@endsection
