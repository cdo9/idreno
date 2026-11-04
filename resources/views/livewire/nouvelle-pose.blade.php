<div class="flex flex-col items-center justify-center min-h-screen bg-gray-50 gap-8 pb-10">
    <div class="flex flex-col gap-6 p-10 bg-white rounded-xl shadow-md w-full max-w-5xl">

        <h1 class="text-2xl font-bold text-primary mb-4">
            {{ $mode === 'creation' ? 'Nouvelle pose' : 'Modification de pose' }}
        </h1>

        @if (session()->has('message'))
            <div class="p-3 mb-4 bg-green-100 text-primary rounded">
                {{ session('message') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3 mb-4 bg-red-100 text-red-800 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit.prevent="save" class="flex flex-col gap-8">

            <!-- Vente & Commercial -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block text-primary font-semibold mb-2">Vente<span class="text-orange-500">*</span></label>
                    <input type="text" wire:model.live.300ms="searchVente" class="w-full p-3 rounded-full border border-gray-300 focus:ring-2 focus:ring-[#77BC23] bg-gray-50" placeholder="Recherchez une vente...">

                    @if(!empty($ventes) && strlen($searchVente) > 1)
                        <ul class="border border-gray-300 bg-white mt-1 rounded max-h-60 overflow-auto">
                            @foreach ($ventes as $vente)
                                <li wire:click="selectVente({{ $vente->id_vente }})" class="p-2 cursor-pointer hover:bg-primary hover:text-white">
                                    {{ $vente->prenom }} {{ $vente->nom }} |
                                    {{ $vente->adresse }} {{ $vente->ville }} {{ $vente->cp }} |
                                    {{ $vente->date }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                

                <div>
                    <label class="block text-primary font-semibold mb-2">Visite chantier par</label>
                    <select wire:model="pose.id_commercial_visite" class="w-full p-3 rounded-full border border-gray-300 focus:ring-2 focus:ring-[#77BC23] bg-gray-50">
                        <option value="0"></option>
                        @foreach ($commerciaux as $commercial)
                            <option value="{{ $commercial->id }}">
                                {{ $commercial->prenom }} {{ $commercial->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-primary font-semibold mb-2">Date de début<span class="text-orange-500">*</span></label>
                    <input type="date" wire:model="pose.date_debut" class="w-full p-3 rounded-full border border-gray-300 focus:ring-2 focus:ring-[#77BC23] bg-gray-50">
                </div>

                <div>
                    <label class="block text-primary font-semibold mb-2">Date de fin<span class="text-orange-500">*</span></label>
                    <input type="date" wire:model="pose.date_fin" class="w-full p-3 rounded-full border border-gray-300 focus:ring-2 focus:ring-[#77BC23] bg-gray-50">
                </div>
            </div>

            <!-- Produits -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @for ($i = 1; $i <= 3; $i++)
                    <div>
                        <label class="block text-primary font-semibold mb-2">Produit {{ $i }} @if($i == 1)<span class="text-orange-500">*</span>@endif</label>
                        <select wire:model="pose.produit{{ $i === 1 ? '' : $i }}" class="w-full p-3 rounded-full border border-gray-300 focus:ring-2 focus:ring-[#77BC23] bg-gray-50">
                            <option value="">Sélectionnez un produit</option>
                            @foreach($lstPriorities as $categorie => $priorites)
                                <optgroup label="{{ $categorie }}">
                                    @foreach($priorites as $slug => $priorite)
                                        <option value="{{ $priorite }}">{{ $priorite }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                @endfor
            </div>

            <!-- Suivi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 {{ $poseId ? "" : "hidden"}}">
                <div>
                    <label class="block text-primary font-semibold mb-2">Visite chantier<span class="text-orange-500">*</span></label>
                    <select wire:model="pose.visite_chantier" class="w-full p-3 rounded-full border border-gray-300 focus:ring-2 focus:ring-[#77BC23] bg-gray-50">
                        <option value="1">Oui</option>
                        <option value="0" selected>Non</option>
                    </select>
                </div>
                <div>
                    <label class="block text-primary font-semibold mb-2">Avis client<span class="text-orange-500">*</span></label>
                    <select wire:model="pose.avis_client" class="w-full p-3 rounded-full border border-gray-300 focus:ring-2 focus:ring-[#77BC23] bg-gray-50">
                        <option value="1">Oui</option>
                        <option value="0" selected>Non</option>
                    </select>
                </div>
            </div>

            <!-- Poseurs -->
            <div>
                <label class="block text-primary font-semibold mb-2">Poseurs</label>
                <div class="flex flex-wrap gap-4">
                    @foreach ($poseurs as $poseur)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" wire:model="pose.poseurs" value="{{ $poseur->id }}" class="accent-[#77BC23]">
                            <span>{{ $poseur->poseur }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex gap-4 justify-end mt-6">
                <button type="submit" class="bg-lightprimary text-white px-6 py-3 rounded-full hover:bg-primary transition cursor:pointer">
                    {{ $mode === 'creation' ? 'Enregistrer' : 'Mettre à jour' }}
                </button>
				@if($url)
                    <a href="{{ $url }}" target="_blank" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-full hover:bg-gray-300 transition cursor:pointer">
                        Ajouter à l'agenda
                    </a>
                @endif
                <a href="{{ session('return_url', url()->previous()) }}" class="bg-grey text-gray-700 px-6 py-3 rounded-full hover:bg-darkgrey transition cursor:pointer">
                    Annuler
                </a>
            </div>

        </form>
    </div>
</div>
