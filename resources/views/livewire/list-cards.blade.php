<div>
    @php
        $items = [
            ['icon' => 'fa-location-dot', 'label' => 'Map', 'route' => 'map'],
            ['icon' => 'fa-id-card', 'label' => 'Nouveau contact', 'route' => 'nouveau-contact'],
            ['icon' => 'fa-clipboard-check', 'label' => 'Nouveau rendez-vous', 'route' => 'nouveau-rendez-vous'],
            ['icon' => 'fa-calendar-check', 'label' => 'Confirmations RDV', 'route' => 'confirmations-de-rendez-vous'],
            ['icon' => 'fa-handshake-simple', 'label' => 'Rendez-vous', 'route' => 'rendez-vous'],
            ['icon' => 'fa-clipboard-list', 'label' => 'Nouvelle argumentation', 'route' => 'argumentation'],
            ['icon' => 'fa-comment', 'label' => 'Argumentations', 'route' => 'argumentations'],
            ['icon' => 'fa-users', 'label' => 'Clients', 'route' => 'listing-client'],
            ['icon' => 'fa-calendar-plus', 'label' => 'Relances', 'route' => 'relances'],
            ['icon' => 'fa-truck', 'label' => 'Poses', 'route' => 'poses'],
            ['icon' => 'fa-euro-sign', 'label' => 'Ventes et Commissions', 'route' => 'ventes'],
            ['icon' => 'fa-table-list', 'label' => 'Tableau des chiffres', 'route' => 'tableau-chiffres'],
            ['icon' => 'fa-chart-line', 'label' => 'Classement', 'route' => 'classement-commerciaux'],
            ['icon' => 'fa-flag-checkered', 'label' => 'Challenges', 'route' => 'challenges'],
            ['icon' => 'fa-clipboard', 'label' => 'Nouvelle pose', 'route' => 'nouvelle-pose'],
            ['icon' => 'fa-user-gear', 'label' => 'Poseurs', 'route' => 'poseurs'],
            ['icon' => 'fa-users-gear', 'label' => 'Stats contacts', 'route' => 'stats-contact'],
            ['icon' => 'fa-user-plus', 'label' => 'Gestion commerciaux', 'route' => 'gestion-commerciaux'],
            ['icon' => 'fa-car-rear', 'label' => 'Parc auto & carburant', 'route' => 'parc-auto'],
            ['icon' => 'fa-triangle-exclamation', 'label' => 'Blocages', 'route' => 'blocages'],
            ['icon' => 'fa-bell', 'label' => 'Alertes', 'route' => 'alertes'],
            ['icon' => 'fa-signs-post', 'label' => 'Secteurs', 'route' => 'secteur-show-list']
            /*['icon' => 'fa-chart-pie', 'label' => 'Stats produits', 'route' => 'stats-produit']
            ['icon' => 'fa-cake-candles', 'label' => 'Anniversaires', 'route' => 'Anniversaires'],*/
        ];
    @endphp


    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-2 gap-6 p-2 sm:p-10 bg-white rounded-xl">
        @foreach ($items as $item)
            @if(auth()->user()->canAccessPage($item['route']))
                <a href="{{ route($item['route']) }}">
                    <div class="bg-white border border-primary shadow-xl relative rounded-xl p-5 sm:p-10 flex flex-col items-center w-[80%] h-[100%] justify-center cursor-pointer ml-5 hover:bg-darkgrey hover:text-white hover:color-white transition-all duration-300">
                        @if(in_array($item['route'], $alerteTypes) && $alertesParType[$item['route']]->cpt > 0)
                            <div
                                class="absolute -top-2 -left-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                {{$alertesParType[$item['route']]->cpt}}
                            </div>
                        @endif
                        @if(in_array($item['route'], $alerteTypes) && isset($alertesParType[$item['route']]->alerte) && $alertesParType[$item['route']]->alerte)
                            <div
                                class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center">
                                !
                            </div>
                        @endif
                        <div>
                            <i class="text-primary text-xl sm:text-5xl fa-solid {{ $item['icon'] }}"></i>
                        </div>
                        <div>
                            <p class="text-secondary font-semibold text-center sm:text-xl text-sm">
                                {{ $item['label'] }}
                            </p>
                        </div>
                    </div>
                </a>
            @endif
        @endforeach
    </div>
</div>
