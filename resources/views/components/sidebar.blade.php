<!-- Hamburger Menu Button (visible only on desktop) -->
<button id="sidebar-toggle" class="fixed top-4 left-4 z-9 p-2 rounded-md bg-primary text-white hidden lg:block focus:outline-none 
    border-primary border-1 hover:cursor-pointer hover:scale-110 transition-all duration-300">
    <i class="fa-solid fa-bars text-xl "></i>
</button>

<sidebar id="main-sidebar" class="max-h-screen overflow-y-scroll fixed inset-y-0 left-0 z-8 w-64 hidden lg:relative">
    <div class="flex flex-col bg-[#F3F3F3] p-8 pb-3 border-r-2 border-gray-200 ">
        <!-- Close button (visible only on desktop) -->
        <button id="sidebar-close" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 hidden lg:block">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
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

<div class="flex flex-col flex-wrap space-y-1 text-center align-stretch gap-2">
    @foreach ($items as $item)
        @if(auth()->user()->canAccessPage($item['route']))
        <a href="{{ route($item['route']) }}"
           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl transition-colors duration-200 border-2 border-gray-200
               {{ request()->routeIs($item['route'])
                    ? 'bg-darkgrey '
                    : 'hover:bg-darkgrey ' }}"
           style="text-decoration: none"
        >
            <div class="absolute -right-9 top-0 h-full w-3 bg-primary transform scale-y-0 transition-transform duration-200
                {{ request()->routeIs($item['route'])
                    ? 'scale-y-100'
                    : 'group-hover:scale-y-100' }}"
            ></div>
            <i class="fa-solid {{ $item['icon'] }} text-lg w-5
               {{ request()->routeIs($item['route'])
                    ? 'text-white'
                    : 'text-[#7A7A7A] group-hover:text-white' }}"
            ></i>
            <span class="text-sm
               {{ request()->routeIs($item['route'])
                    ? 'text-white'
                    : 'text-[#7A7A7A] group-hover:text-white' }}"
            >{{ $item['label'] }}</span>
        </a>
        @endif
    @endforeach
</div>

    </div>
</sidebar>

<!-- JavaScript for sidebar toggle functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggleButton = document.getElementById('sidebar-toggle');
        const closeButton = document.getElementById('sidebar-close');

        // Add a custom style to handle desktop-only visibility
        const style = document.createElement('style');
        style.textContent = `
            @media (min-width: 1024px) {
                .desktop-visible {
                    display: block !important;
                }
            }
        `;
        document.head.appendChild(style);

        // Function to open sidebar on desktop
        function openSidebar() {
            sidebar.classList.add('desktop-visible');
            //overlay.classList.remove('hidden');
            //document.body.classList.add('overflow-hidden'); // Prevent scrolling when sidebar is open
        }

        // Function to close sidebar on desktop
        function closeSidebar() {
            sidebar.classList.remove('desktop-visible');
            //overlay.classList.add('hidden');
            //document.body.classList.remove('overflow-hidden');
        }

        // Toggle button click event (desktop only)
        toggleButton.addEventListener('click', function() {
            if (window.innerWidth >= 1024) { // Only toggle on desktop
                if (sidebar.classList.contains('desktop-visible')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }
        });

        // Close button click event
        closeButton.addEventListener('click', closeSidebar);

        // Overlay click event
        //overlay.addEventListener('click', closeSidebar);

        // Initialize sidebar state on page load
        if (window.innerWidth >= 1024) {
            // On desktop, sidebar is initially hidden
            closeSidebar();
        }

        // Handle window resize events
        window.addEventListener('resize', function() {
            if (window.innerWidth < 1024) {
                // Always ensure sidebar is hidden on mobile
                sidebar.classList.remove('desktop-visible');
                //overlay.classList.add('hidden');
            }
        });
    });
</script>
