<div class="flex flex-col gap-6 p-6 bg-background rounded-xl w-full max-w-9xl shadow-md"">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Alertes</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div id="filters">
        <form method="POST" wire:submit.prevent="filtrer"
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <select wire:model.live="vendeur" class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary">
                <option value="">Tous les vendeurs</option>
                @foreach ($vendeurs as $v)
                    <option value="{{ $v['id'] }}">{{ $v->display_name }}</option>
                @endforeach
            </select>
            <input class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary" type="date" wire:model.live="date">
            <span></span>
            <button
                type="button"
                wire:click="deleteDisplayedAlerts"
                wire:confirm="Supprimer toutes les alertes affichées ?"
                class="px-4 py-2 rounded-full bg-red-600 text-white hover:bg-red-700"
            >
                Supprimer les alertes affichées
            </button>
            <button
                type="button"
                wire:click="deleteAllAlerts"
                wire:confirm="Supprimer la totalité des alertes ?"
                class="px-4 py-2 rounded-full bg-red-600 text-white hover:bg-red-700"
            >
                Supprimer toutes les alertes
            </button>
        </form>

       
    </div>

    @if(count($alerts) > 0)
        <div class="space-y-4">
            @foreach($alerts as $alert)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <!-- Alert Type Badge -->
                            @if($alert->type)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-2">
                                    {{ ucfirst($alert->type->value) }}
                                </span>
                            @endif

                            <!-- Alert Owner -->
                            @if($alert->idCommercial)
                                <div class="bg-blue-50 rounded p-3 mb-3">
                                    <h4 class="text-sm font-medium text-blue-700 mb-1">Appartient à:</h4>
                                    <div class="text-sm text-blue-600">
                                        {{ $alert->idCommercial->display_name ?? 'Utilisateur #' . $alert->id_commercial }}
                                    </div>
                                </div>
                            @endif

                            <!-- Alert Message -->
                            <div class="text-gray-900 mb-3">
                                {!! nl2br(e($alert->message)) !!}
                            </div>

                            <!-- Alert Details (distance_meters and client_info only) -->
                            @if($alert->details && is_array($alert->details) && count($alert->details) > 0)
                                <div class="bg-gray-50 rounded p-3 mb-3">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Informations renseignées:</h4>
                                    <div class="text-sm text-gray-600">

                                        <!-- Display distance_meters -->
                                        @if(isset($alert->details['distance_meters']) && !empty($alert->details['distance_meters']))
                                            <div class="mb-2">
                                                <strong>Distance:</strong> 
                                                {{ 
                                                    $alert->details['distance_meters'] >= 1000 
                                                        ? round($alert->details['distance_meters'] / 1000, 2) . ' km' 
                                                        : $alert->details['distance_meters'] . ' mètres' 
                                                }}
                                            </div>
                                        @endif

                                        <!-- Display client_info -->
                                        @if(isset($alert->details['client_info']) && is_array($alert->details['client_info']))
                                            <div class="mb-2">
                                                <strong>Informations client:</strong>
                                                <div class="ml-4 mt-1">
                                                    @if(isset($alert->details['client_info']['nom']) && !empty($alert->details['client_info']['nom']))
                                                        <div><strong>Nom:</strong> {{ $alert->details['client_info']['nom'] }}</div>
                                                    @endif
                                                    @if(isset($alert->details['client_info']['prenom']) && !empty($alert->details['client_info']['prenom']))
                                                        <div><strong>Prénom:</strong> {{ $alert->details['client_info']['prenom'] }}</div>
                                                    @endif
                                                    @if(isset($alert->details['client_info']['ville']) && !empty($alert->details['client_info']['ville']))
                                                        <div><strong>Ville:</strong> {{ $alert->details['client_info']['ville'] }}</div>
                                                    @endif
                                                    @if(isset($alert->details['client_info']['adresse']) && !empty($alert->details['client_info']['adresse']))
                                                        <div><strong>Adresse:</strong> {{ $alert->details['client_info']['adresse'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            @endif

                            <!-- Timestamp -->
                            <div class="text-sm text-gray-500">
                                {{ $alert->created_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>

                        <!-- Mark as Viewed Button -->
                        <div class="ml-4">
                            <button
                                wire:click="markAsViewed({{ $alert->id }})"
                                onclick="return confirm('Marquer cette alerte comme vue?')"
                                class="bg-lightprimary hover:bg-primary text-white px-4 py-2 rounded text-sm font-medium transition-colors duration-200"
                            >
                                Marquer comme vue
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">
                <i class="fas fa-bell-slash"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-700 mb-2">Aucune alerte</h3>
            <p class="text-gray-500">Vous n'avez actuellement aucune alerte à afficher.</p>
        </div>
    @endif
</div>
