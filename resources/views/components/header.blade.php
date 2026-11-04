<header id="main-header" class="print:hidden fixed top-0 z-9 left-0 right-0 border-b-2 border-gray-200">
    <div class="flex flex-wrap justify-center lg:justify-between items-center bg-[#F3F3F3] p-4 w-full">

        <div class="flex flex-row flex-wrap justify-center lg:justify-between items-center">
            <button id="open-mobile-menu" class="lg:hidden h-14 flex items-center p-2 rounded-lg text-primary hover:bg-opacity-90">
                <i class="fas fa-bars text-xl flex items-center hover:border-green-400">
                    <span class="text-[#7A7A7A] text-sm pl-1">Menu</span>
                </i>
            </button>
			<div class="flex flex-col md:flex-row items-center gap-4 lg:hidden">
                <div class="bg-white h-10 rounded-xl pr-10 w-full">
                    <i class="fa-solid fa-location-pin absolute mt-3 ml-3 text-primary"></i>
                    <select name="secteurs" id="secteurs"
                            class="appearance-none text-[#7A7A7A] text-start h-full bg-transparent px-3 py-1 text-2xl pl-8 outline-none focus:outline-none focus:ring-0 focus:border-none cursor-pointer"
                            onchange="changeBackgroundColor(this.value, this)">
                        @foreach($secteurs as $secteur)						
							@if($user->has_secteur($secteur->id))
								<option value="{{ $secteur->couleur }}" data-id="{{ $secteur->id }}" {{$secteur->id == session('selectedSecteur') ? 'selected':'' }}>
									{{ $secteur->nom }}
								</option>
							@endif
                        @endforeach
                    </select>
                </div>
            </div>
            <!--<div class="flex items-center">
                <a href="{{ route('welcome') }}">
                    <img src="{{ asset('logo-homiso.png') }}"
                         alt="Logo"
                         class="w-auto h-8 pl-10 mb-3 lg:mb-0 hover:opacity-80 transition-opacity">
                </a>
            </div>-->
            <!-- Bouton burger pour mobile/tablette -->
            
        </div>

        <div class="flex flex-col sm:flex-row items-center w-full gap-4 lg:w-[70%]">
            <div class="lg:flex hidden flex-col lg:flex-row items-center gap-4 w-[85%]">
                <div class="bg-secondary  h-10 rounded-xl pr-10 w-full border border-white">
                    <i class="fa-solid fa-location-pin absolute mt-3 ml-3 text-primary"></i>
                    <select name="secteurs" id="secteurs"
                            class="appearance-none text-white text-start h-full bg-transparent px-3 py-1 text-2xl pl-8 outline-none focus:outline-none focus:ring-0 focus:border-none cursor-pointer"
                            onchange="changeBackgroundColor(this.value, this)">
                        @foreach($secteurs as $secteur)
                            @if($user->has_secteur($secteur->id))
                                <option value="{{ $secteur->couleur }}" data-id="{{ $secteur->id }}" {{$secteur->id == session('selectedSecteur') ? 'selected':'' }}>
                                    {{ $secteur->nom }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="bg-primary text-white h-10 rounded-xl lg:p-3 lg:pr-20 flex items-center w-full">
                    <p class="text-white text-start w-full justify-center text-2xl truncate overflow-hidden whitespace-nowrap pl-2">
                        {{ ucfirst(str_replace(['-', '.'], ' ', request()->route()->getName())) }}
                    </p>
                </div>
            </div>

            <ul class="flex flex-row items-center space-x-4 hidden lg:flex">
                <div class="flex flex-row">
                    <li class="w-auto">
                        <a href="{{ route('welcome') }}"
                           class="text-white hover:text-homiso-lightgray flex items-center gap-2">
                            <i class="fa-solid fa-house-user text-homiso-green hover:text-homiso-lightgray"></i>
                            <span>Accueil</span>
                        </a>
                    </li>
                </div>
                <span class="text-gray-500">|</span>
                <div class="flex flex-row">
                    <li>
                        <a href="{{ route('parametres') }}"
                           class="text-white hover:text-homiso-lightgray flex items-center gap-2">
                            <i class="fa-solid fa-gear text-homiso-green hover:text-secondary"></i>
                            <span>Parametres</span>
                        </a>
                    </li>
                </div>
                <span class="text-gray-500">|</span>
                <div class="flex flex-row">
                    <li>
                        <a href="{{ route('logout') }}"
                           class="text-white hover:text-secondary flex items-center gap-2">
                            <i class="fa-solid fa-arrow-right-from-bracket text-white hover:text-secondary"></i>
                            <span>Déconnexion</span>
                        </a>
                    </li>


                </div>
            </ul>

            <div class="bg-secondary w-[85%] border border-white h-10 text-white rounded-xl lg:flex hidden flex-row justify-center p-3">
                <div class="flex flex-row items-center">
                    <span><i class="fa-solid fa-circle-user mr-3"></i></span>
                    <p>
                        @if(isset($user) && $user)
                            {{ $user->prenom }} {{ $user->nom }}
                        @else
                            User
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    function changeBackgroundColor(color, selectElement) {
        const headerDiv = document.querySelector('#main-header > div');
        if (headerDiv) headerDiv.style.backgroundColor = color;
        localStorage.setItem('selectedColor', color);

        // Stocke l'id du secteur sélectionné
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const secteurId = selectedOption.getAttribute('data-id');

        // Convert to integer before storing
        const secteurIdInt = parseInt(secteurId, 10);

        // Store the integer value

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

    document.addEventListener('DOMContentLoaded', function () {
        const savedColor = localStorage.getItem('selectedColor');
        if (savedColor) {
            console.log('Header - Found savedColor:', savedColor);
            const headerDiv = document.querySelector('#main-header > div');
            if (headerDiv) headerDiv.style.backgroundColor = savedColor;

            // Sélectionner l'option correspondante dans le select
            const select = document.getElementById('secteurs');
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === savedColor) {
                    select.selectedIndex = i;
                    break;
                }
            }

            const savedSecteur = {{session('selectedSecteur')}};

            if (savedSecteur) {
                // Convert to integer for comparison
                const secteurInt = parseInt(savedSecteur, 10);

                const select = document.getElementById('secteurs');
                for (let i = 0; i < select.options.length; i++) {
                    const optionDataId = select.options[i].getAttribute('data-id');

                    // Compare as integers
                    if (parseInt(optionDataId, 10) === secteurInt) {
                        console.log('Header - Match found, setting selectedIndex to', i);
                        select.selectedIndex = i;
                        break;
                    }
                }
            }
        }
    });
</script>
