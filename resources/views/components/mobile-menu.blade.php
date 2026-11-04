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

<div id="mobile-menu" class="fixed inset-0 bg-[#F3F3F3] z-40 hidden flex items-center justify-center">
    <div class="w-4/5 max-w-2xl bg-white rounded-xl shadow-lg transform transition-all duration-300 ease-in-out scale-95" id="mobile-menu-content">
        <div class="flex flex-col h-[80vh] p-6">
            <div class="flex justify-end items-center mb-6">
                <button id="close-mobile-menu" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-4 w-[85%] lg:hidden">
                <div class="bg-white h-10 rounded-xl pr-10 w-full">
                    <i class="fa-solid fa-location-pin absolute mt-3 ml-3 text-[#77BC23]"></i>
                    <select name="secteurs" id="secteurs"
                            class="appearance-none text-[#7A7A7A] text-start h-full bg-transparent px-3 py-1 text-2xl pl-8 outline-none focus:outline-none focus:ring-0 focus:border-none cursor-pointer"
                            onchange="changeBackgroundColor(this.value, this)">
                        @foreach($secteurs as $secteur)			
								<option value="{{ $secteur->couleur }}" data-id="{{ $secteur->id }}">
									{{ $secteur->nom }}
								</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto bg-white rounded-lg p-4">
                <!-- Sous-menus du header -->
                <div class="flex flex-col space-y-3 mb-6 pb-4">
                    <a href="{{ route('welcome') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors duration-200 hover:bg-[#77BC23] hover:text-white text-[#7A7A7A]">
                        <i class="fa-solid fa-house-user text-[#77BC23] hover:text-white"></i>
                        <span class="text-sm">Accueil</span>
                    </a>
                    <div class="border-b border-gray-200"></div>
                    <a href="{{ route('parametres') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors duration-200 hover:bg-[#77BC23] hover:text-white text-[#7A7A7A]">
                        <i class="fa-solid fa-gear text-[#77BC23] hover:text-white"></i>
                        <span class="text-sm">Paramètres</span>
                    </a>
                    <div class="border-b border-gray-200"></div>
                    <a href="{{ route('logout') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors duration-200 hover:bg-[#77BC23] hover:text-white text-[#7A7A7A]">
                        <i class="fa-solid fa-arrow-right-from-bracket text-[#77BC23] hover:text-white"></i>
                        <span class="text-sm">Déconnexion</span>
                    </a>
                </div>

                <!-- Menu principal -->
                <div class="flex flex-col space-y-2">
                    @if(auth()->check())
                    @foreach ($items as $item)
                        @if(auth()->user()->canAccessPage($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors duration-200 border-2 border-gray-200
                               {{ request()->routeIs($item['route'])
                                    ? 'bg-[#77BC23] text-white'
                                    : 'hover:bg-[#77BC23] text-[#7A7A7A] hover:text-white' }}"
                           style="text-decoration: none"
                        >
                            <i class="fa-solid {{ $item['icon'] }} text-lg w-5"></i>
                            <span class="text-sm">{{ $item['label'] }}</span>
                        </a>
                        @endif
                    @endforeach
                        @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuContent = document.getElementById('mobile-menu-content');
    const openButton = document.getElementById('open-mobile-menu');
    const closeButton = document.getElementById('close-mobile-menu');
    const header = document.querySelector('header');

    function changeBackgroundColor(color, selectElement) {
        const headerDiv = document.querySelector('#main-header > div');
        if (headerDiv) headerDiv.style.backgroundColor = color;
        localStorage.setItem('selectedColor', color);

        // Stocke l'id du secteur sélectionné
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const secteurId = selectedOption.getAttribute('data-id');

        // Convert to integer before storing
        const secteurIdInt = parseInt(secteurId, 10);

        if (secteurIdInt) {
            fetch('/ajax/setSelectedSecteur/' + encodeURIComponent(secteurIdInt), {
                method: 'get'
            })
            .then(response => response.json())
            .then(data =>{
                window.location.reload()
            })
            .catch(error => {
                console.error('Erreur lors de l’appel API', error);
            });
        }

        // Déclenche un event pour informer les autres pages
        window.dispatchEvent(new Event('secteurChanged'));
        console.log('Header - Dispatched secteurChanged event');
    }


    function openMenu() {
        mobileMenu.classList.remove('hidden');
        if (header) {
            header.style.display = 'none';
        }
        setTimeout(() => {
            mobileMenuContent.classList.remove('scale-95', 'opacity-0');
            mobileMenuContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeMenu() {
        mobileMenuContent.classList.remove('scale-100', 'opacity-100');
        mobileMenuContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            mobileMenu.classList.add('hidden');
            if (header) {
                header.style.display = '';
            }
        }, 300);
    }

    if (openButton) {
        openButton.addEventListener('click', openMenu);
    }
    closeButton.addEventListener('click', closeMenu);
    mobileMenu.addEventListener('click', function(e) {
        if (e.target === mobileMenu) {
            closeMenu();
        }
    });
});
</script>
