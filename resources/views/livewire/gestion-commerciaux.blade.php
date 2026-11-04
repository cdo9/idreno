<div class="p-6 space-y-6">
    <div class="min-w-full flex justify-center">
        <h1 class="text-2xl font-bold text-primary">
            Gérer mes commerciaux
        </h1>
    </div>

    {{-- Tableau utilisateurs --}}
    <div class="space-y-6 max-w-9xl mx-auto mt-6">
        {{-- Création groupe --}}
        <div class="bg-white p-4 rounded shadow max-w-9xl mx-auto mt-6">
            <h2 class="text-lg text-primary font-bold mb-4">Créer un groupe</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <input type="text" wire:model="newGroupeName" placeholder="Nom du groupe"
                       class="border rounded-full border-gray-300 p-2  w-full text-sm focus:outline-none focus:ring-2 focus:ring-primary">

                <select wire:model="newGroupeResponsable"
                        class="border border-gray-300 rounded-full p-2  w-full text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">-- Responsable --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->nom }} {{ $u->prenom }}</option>
                    @endforeach
                </select>

                <select wire:model="newGroupeSecteur"
                        class="border border-gray-300  rounded-full p-2 rounded w-full text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">-- Secteur --</option>
                    @if($secteurs)
                        @foreach($secteurs as $s)
                            <option value="{{ $s->id }}">{{ $s->nom }}</option>
                        @endforeach
                    @endif
                </select>

                @if($groupeId == null)
                    <button wire:click="createGroupe"
                            class="bg-lightprimary hover:bg-primary  cursor-pointer font-bold text-white px-4 py-2 rounded text-sm w-full transition-colors duration-200">
                        Ajouter
                    </button>
                @else
                    <button wire:click="editGroupe"
                            class="bg-lightprimary hover:bg-primary  cursor-pointer font-bold text-white px-4 py-2 rounded text-sm w-full transition-colors duration-200">
                        Modifier
                    </button>
                @endif

                <button wire:click="toggleGroupesTable"
                        class="bg-lightprimary hover:bg-primary font-bold cursor-pointer text-white px-4 py-2 rounded text-sm w-full transition-colors duration-200">
                    {{ $showGroupesTable ? 'Masquer' : 'Gérer les groupes' }}
                </button>
            </div>
        </div>


        @if($showGroupesTable)
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-lg font-bold mb-4 text-primary">Groupes existants</h2>
                <table class="w-full rounded-md">
                    <thead class="bg-primary text-white rounded">
                    <tr>
                        <th class="p-2 rounded-tl-lg">Nom</th>
                        <th class="p-2 ">Responsable</th>
                        <th class="p-2 ">Secteur</th>
                        <th class="p-2 ">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($groupes as $groupe)
                        <tr class="odd:bg-gray-200">
                            <td class="p-2 ">{{ $groupe->nom }}</td>
                            <td class="p-2 ">{{ $groupe->responsable?->nom }} {{ $groupe->responsable?->prenom }}</td>
                            <td class="p-2 ">{{ $secteurs->firstWhere('id', $groupe->secteur)?->nom }}</td>
                            <td class="p-2  text-center">
                                <button wire:click="loadGroupe({{ $groupe->id }})"
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded cursor-pointer">
                                    Modifier
                                </button>
                                <button wire:click="deleteGroupe({{ $groupe->id }})"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded cursor-pointer"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?')">
                                    Supprimer
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Création utilisateur --}}
        <div class="bg-white shadow-sm rounded-md border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-primary mb-4">Ajouter un commercial</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <input type="text" wire:model="newUser.nom" placeholder="Nom"
                       class="w-full border border-gray-300 rounded-full mb-5 sm:mb-0 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-[#77BC23]">
                <input type="text" wire:model="newUser.prenom" placeholder="Prénom"
                       class="w-full border border-gray-300 rounded-full mb-5 sm:mb-0 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-[#77BC23]">
                <input type="email" wire:model="newUser.email" placeholder="Email"
                       class="w-full border border-gray-300 rounded-full mb-5 sm:mb-0 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-[#77BC23]">
                @error('newUser.email')
                <div class="col-span-2 text-red-700 bg-red-100 border border-red-200 rounded-md px-2 py-1 text-sm">
                    {{ $message }}
                </div>
                @enderror
                <select wire:model="newUser.groupe"
                        class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                    <option value="">Sélectionner un groupe</option>
                    @foreach($groupes as $g)
                        <option value="{{ $g->id }}">{{ $g->nom }}</option>
                    @endforeach
                </select>
				@error('newUser.groupe')
					<p class="text-red-500 text-xs mt-1">{{ $message }}</p>
				@enderror

                <select wire:model="newUser.contrat"
                        class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                    <option value="">Sélectionner un contrat</option>
                   @foreach($typesContrat as $value=>$type)
                        <option value="{{ $value }}" >
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
				@error('newUser.contrat')
					<p class="text-red-500 text-xs mt-1">{{ $message }}</p>
				@enderror

                <input type="password" wire:model="newUser.password" placeholder="Mot de passe"
                       class="w-full border border-gray-300 rounded-full px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-[#77BC23]">
                @error('newUser.password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                <input type="date" wire:model="newUser.date_entree"
                       class="w-full border border-gray-300 rounded-full px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-[#77BC23]">

                <button wire:click="createUser"
                        class="bg-lightprimary hover:bg-primary text-white font-semibold rounded-md px-4 py-2 transition-colors duration-200 cursor-pointer">
                    Créer
                </button>
            </div>
        </div>


        {{-- Tableau utilisateurs --}}
        <div class="bg-white shadow-sm rounded-md border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-primary mb-4">Liste des commerciaux</h2>
            <div class="overflow-auto">
                <table class="w-full text-sm border-collapse ">
                    <thead class="bg-primary text-center text-white ">
                    <tr>
                        <th class="p-2 rounded-tl-lg" colspan="2">Nom</th>
                        <th class="p-2 ">Objectif CA</th>
                        <th class="p-2 ">Objectif RDV</th>
                        <th class="p-2 ">Objectif Annuel</th>
                        <th class="p-2 ">Groupe</th>
                        <th class="p-2 ">Contrat</th>
                        <th class="p-2 ">Rôle</th>
                        <th class="p-2 ">Date entrée</th>
                        <th class="p-2 "></th>
                        <th class="p-2 rounded-tr-lg">Actif</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users->sortByDesc('active') as $u)
                        <tr class="odd:bg-gray-200 {{ !$u->active ? 'bg-red-50 text-red-700' : '' }}">
                            <td class="p-2">{{ $u->nom }}</td>
                            <td class="p-2">{{ $u->prenom }}</td>

                            <td class="p-2">
                                <input type="number" value="{{ $u->objectif_ca ?? 0 }}"
                                       wire:change="updateUserField({{ $u->id }}, 'objectif_ca', $event.target.value)"
                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm {{ !$u->active ? 'bg-red-100 text-red-700 cursor-not-allowed' : '' }}"
                                    {{ !$u->active ? 'disabled' : '' }}>
                            </td>

                            <td class="p-2">
                                <input type="number" value="{{ $u->objectif_rdv ?? 0 }}"
                                       wire:change="updateUserField({{ $u->id }}, 'objectif_rdv', $event.target.value)"
                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm {{ !$u->active ? 'bg-red-100 text-red-700 cursor-not-allowed' : '' }}"
                                    {{ !$u->active ? 'disabled' : '' }}>
                            </td>

                            <td class="p-2">
                                <input type="number" value="{{ $objectifsAnnuel[$u->id] ?? 0 }}"
                                       wire:change="updateObjectifAnnuel({{ $u->id }}, $event.target.value)"
                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm {{ !$u->active ? 'bg-red-100 text-red-700 cursor-not-allowed' : '' }}"
                                    {{ !$u->active ? 'disabled' : '' }}>
                            </td>

                            <td class="p-2">
                                <select wire:change="updateUserField({{ $u->id }}, 'groupe', $event.target.value)"
                                        class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm {{ !$u->active ? 'bg-red-100 text-red-700 cursor-not-allowed' : '' }}"
                                    {{ !$u->active ? 'disabled' : '' }}>
                                    <option value="">— Aucun —</option>
                                    @foreach($groupes as $g)
                                        <option value="{{ $g->id }}" {{ $u->groupe == $g->id ? 'selected' : '' }}>
                                            {{ $g->nom }} - {{ $g->secteurRel?->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td class="p-2">
                                <select wire:change="updateUserField({{ $u->id }}, 'contrat', $event.target.value)"
                                        class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm {{ !$u->active ? 'bg-red-100 text-red-700 cursor-not-allowed' : '' }}"
                                    {{ !$u->active ? 'disabled' : '' }}>
                                    @foreach($typesContrat as $value=>$type)
                                        <option value="{{ $value }}" {{ $u->contrat === $value ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                    @if(!array_key_exists($u->contrat, $typesContrat) && $u->contrat)
                                        <option value="{{ $u->contrat }}" selected>{{ $u->contrat }}</option>
                                    @endif
                                </select>
                            </td>

                            <td class="p-2">
                                <select wire:change="updateUserField({{ $u->id }}, 'role', $event.target.value)"
                                        class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm {{ !$u->active ? 'bg-red-100 text-red-700 cursor-not-allowed' : '' }}"
                                    {{ !$u->active ? 'disabled' : '' }}>
                                    <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="commercial" {{ $u->role === 'commercial' ? 'selected' : '' }}>Commercial</option>
                                    <option value="directeur" {{ $u->role === 'directeur' ? 'selected' : '' }}>Directeur</option>
                                    <option value="telepro" {{ $u->role === 'telepro' ? 'selected' : '' }}>Téléprospecteur</option>
                                    <option value="resp-prosp" {{ $u->role === 'resp-prosp' ? 'selected' : '' }}>Resp. Prospection</option>
                                    <option value="administratif" {{ $u->role === 'administratif' ? 'selected' : '' }}>Administratif</option>
                                </select>
                            </td>

                            <td class="p-2">
                                <input type="date" value="{{ $u->date_entree ? \Carbon\Carbon::parse($u->date_entree)->format('Y-m-d') : '' }}"
                                       wire:change="updateUserField({{ $u->id }}, 'date_entree', $event.target.value)"
                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm {{ !$u->active ? 'bg-red-100 text-red-700 cursor-not-allowed' : '' }}"
                                    {{ !$u->active ? 'disabled' : '' }}>
                            </td>
                            <td class="px-4 py-3 text-sm flex justify-center flex-col space-x-2">
                                <button wire:click="openModal('info', {{ $u->id }})"
                                        class=" w-30 cursor-pointer inline-flex items-center px-2 py-1 text-xs font-medium bg-lightprimary text-white rounded hover:bg-primary transition">
                                    <span class="fa fa-edit mr-1"></span> Informations
                                </button>

                                <button wire:click="openModal('password', {{ $u->id }})"
                                        class="w-30 cursor-pointer inline-flex items-center mt-3 px-2 py-1 text-xs font-medium bg-lightprimary text-white rounded hover:bg-primary transition">
                                    <span class="fa fa-key mr-1"></span> Mot de passe
                                </button>
                            </td>
                            <td class="p-2 text-center">
                                <button wire:click="toggleActive({{ $u->id }})"
                                        class="px-3 py-1 rounded-md text-sm font-semibold cursor-pointer
                           {{ $u->active ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}
                           hover:opacity-80 transition">
                                    {{ $u->active ? 'Actif' : 'Inactif' }}
                                </button>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @if($showModal)
        <div class="fixed inset-0 flex items-center justify-center z-50">

            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md z-10">
                <h3 class="text-lg font-semibold mb-4 text-primary">
                    @if($modalType === 'info')
                        Modifier les informations
                    @elseif($modalType === 'password')
                        Changer le mot de passe
                    @endif
                </h3>

                @if($modalType === 'info')
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-bold text-primary py-2">Nom</label>
                            <input type="text" wire:model="selectedUser.nom" class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-primary py-2">Prénom</label>
                            <input type="text" wire:model="selectedUser.prenom" class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-primary py-2">Email</label>
                            <input type="email" wire:model="selectedUser.email" class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
                        </div>

                        <div>
                            <label class="text-sm font-bold text-primary py-2">Secteurs</label>
                            <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-4 bg-background-grey">
                                @foreach($secteurs as $secteur)
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            value="{{ $secteur->id }}"
                                            wire:model="selectedUser.selectedSecteurs"
                                            class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                        >
                                        <span class="text-sm text-gray-700">
                                            {{ $secteur->nom }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    
                @endif

                @if($modalType === 'password')
                    <div class="mt-4">
                        <label class="text-sm font-bold text-primary py-2">Nouveau mot de passe</label>
                        <input type="password" wire:model="newPassword" class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
                    </div>
                @endif

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="closeModal" class=" cursor-pointer text-white px-4 py-2 bg-grey rounded hover:bg-darkgrey">Fermer</button>

                    @if($modalType === 'info')
                        <button wire:click="saveUser" class=" cursor-pointer px-4 py-2 bg-lightprimary text-white rounded hover:bg-primary">
                            Sauvegarder
                        </button>
                    @elseif($modalType === 'password')
                        <button wire:click="updatePassword" class=" cursor-pointer px-4 py-2 bg-lightprimary text-white rounded hover:bg-primary">
                            Changer
                        </button>
                    @endif
                </div>
            </div>
        </div>
@endif
