@extends('layouts.app')
@section('content')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initAutocomplete&loading=async" async defer></script>
<style>
    /* Style for the autocomplete dropdown */
    .pac-container {
        z-index: 10000;
        width: auto !important;
        position: absolute !important;
        font-family: inherit;
    }
    .pac-item {
        padding: 8px;
        font-size: 14px;
    }
    .pac-item:hover {
        background-color: #f0f0f0;
    }
</style>
<form action="{{ route('formulaire-contact') }}" method="POST" class="gap-6 p-4 sm:p-10 bg-white rounded-xl shadow-lg max-w-4xl mx-auto items-center">
    @csrf
    <input type="hidden" name="secteur_id" id="secteur_id_hidden" value="{{session('selectedSecteur')}}">
    @if(isset($client))
    <input type="hidden" name="client_id" value="{{ $client->id }}">
    @endif
    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border border-darkgrey text-primary px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    <div class="flex flex-col p-2 sm:p-6 gap-8">
        <!-- Personal Information Row -->
        <div class="flex flex-row flex-wrap sm:flex-nowrap gap-4">
            <div class="flex flex-col w-full sm:w-1/4">
                <label for="choix" class="text-primary font-bold">Civilité<span class="text-orange-500">*</span></label>
                @php
                    $currentValue = isset($client) ? $client->genre : old('choix');
                    if($currentValue == "")
                        $currentValue = "Mr & Mme";
                @endphp
                <select name="choix" id="choix" class=" text-start h-10 px-3 py-2 bg-[#F3F3F3] rounded-full border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition" required>
                    <option value="" disabled {{ $currentValue == '' ? 'selected' : '' }} hidden>Choisir....</option>
                    <option value="Monsieur" {{ $currentValue == 'Monsieur' ? 'selected' : '' }}>Monsieur</option>
                    <option value="Madame" {{ $currentValue == 'Madame' ? 'selected' : '' }}>Madame</option>
                    <option value="Mr & Mme" {{ $currentValue == 'Mr & Mme' ? 'selected' : '' }}>M. & Mme</option>
                </select>
                @error('choix')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full sm:w-1/5">
                <label for="source" class="text-primary font-bold">Source<span class="text-orange-500">*</span></label>
                <select name="source" id="source" class=" text-start h-10 px-3 py-2 bg-[#F3F3F3] rounded-full border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition" required>
                    <option value="" disabled selected hidden>Choisir....</option>
                    @php
                        $currentValue = isset($client) ? $client->id_source : old('source');
                    @endphp
                    @foreach($sources as $categorie => $liste)
                        <optgroup label="{{ $categorie }}">
                            @foreach($liste as $slug => $nom)
                                <option value="{{ $slug }}" {{ $currentValue == $slug ? 'selected' : '' }}>{{ $nom }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('source')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full sm:w-1/4">
                <label for="nom" class="text-primary font-bold">Nom<span class="text-orange-500">*</span></label>
                <input type="text" name="nom" id="nom" value="{{ old('nom', isset($client) ? $client->nom : '') }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition" required>
                @error('nom')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full sm:w-1/4">
                <label for="prenom" class="text-primary font-bold">Prénom</label>
                <input type="text" name="prenom" id="prenom" value="{{ old('prenom', isset($client) ? $client->prenom : '') }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
                @error('prenom')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <input type="text" name="type" id="type" value="{{ old('type', isset($client) ? $client->type : 0) }}" class="hidden" required>
        </div>
        <div class="flex flex-col w-full">
            <label for="adresse_search" class="text-primary font-bold">Rechercher une adresse</label>
            <input type="text" name="adresse_search" id="adresse_search" placeholder="Indiquez un lieu" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
            <input type="text" name="gps_lat" id="gps_lat" class="hidden">
            <input type="text" name="gps_lng" id="gps_lng" class="hidden">
        </div>
        <div class="flex flex-row flex-wrap sm:flex-nowrap gap-4">
            <div class="flex flex-col w-full sm:w-1/2">
                <label for="adresse" class="text-primary font-bold">Adresse</label>
                <input type="text" name="adresse" id="adresse" value="{{ old('adresse', isset($client) ? $client->adresse : '') }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
                @error('adresse')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full sm:w-1/4">
                <label for="code_postal" class="text-primary font-bold">Code Postal<span class="text-orange-500">*</span></label>
                <input type="text" name="code_postal" id="code_postal" value="{{ old('code_postal', isset($client) ? $client->cp : '') }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition" required pattern="[0-9]{5}" inputmode="numeric" maxlength="5" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                @error('code_postal')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full sm:w-1/4">
                <label for="ville" class="text-primary font-bold">Ville<span class="text-orange-500">*</span></label>
                <input type="text" name="ville" id="ville" value="{{ old('ville', isset($client) ? $client->ville : '') }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition" required>
                @error('ville')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="flex flex-row flex-wrap sm:flex-nowrap gap-4">
            <div class="flex flex-col w-full sm:w-1/3">
                <label for="telephone1" class="text-primary font-bold">Téléphone 1<span class="text-orange-500">*</span></label>
                <input type="tel" name="telephone1" id="telephone1"
                    value="{{ old('telephone1', isset($client) ? $client->telephone1 : '') }}"
                    class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition"
                    required inputmode="numeric" pattern="[0-9]*" maxlength="16"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                @error('telephone1')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full sm:w-1/3">
                <label for="telephone2" class="text-primary font-bold">Téléphone 2</label>
                <input type="tel" name="telephone2" id="telephone2" value="{{ old('telephone2', isset($client) ? $client->telephone2 : '') }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                @error('telephone2')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full sm:w-1/3">
                <label for="appart" class="text-primary font-bold">Appartement / Etage</label>
                <input type="text" name="appart" id="appart" value="{{ old('appart', isset($client) ? $client->appart : '') }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
                @error('appart')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="flex flex-col w-full">
            <label for="commentaire" class="text-primary font-bold">Commentaire</label>
            <textarea name="commentaire" id="commentaire" class="bg-[#F3F3F3] rounded-xl px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition h-24 resize-y">{{ old('commentaire', isset($client) ? $client->debrief : '') }}</textarea>
            @error('commentaire')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="flex flex-row flex-wrap sm:flex-nowrap gap-4">
            <div class="flex flex-col w-full sm:w-1/2">
                <label for="profil_client" class="text-primary font-bold">Habitude d'achat<span class="text-orange-500">*</span></label>
                <select name="profil_client" id="profil_client" required class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
                    <option value="" disabled selected hidden>Choisir l'habitude d'achat...</option>
                    <option value="Foire / salon" {{ old('profil_client', isset($client) && $client->profil_clients == 'Foire / salon' ? 'Foire / salon' : '') == 'Foire / salon' ? 'selected' : '' }}>Foire / salon</option>
                    <option value="Artisan" {{ old('profil_client', isset($client) && $client->profil_clients == 'Artisan' ? 'Artisan' : '') == 'Artisan' ? 'selected' : '' }}>Artisan</option>
                    <option value="Internet" {{ old('profil_client', isset($client) && $client->profil_clients == 'Internet' ? 'Internet' : '') == 'Internet' ? 'selected' : '' }}>Internet</option>
                    <option value="Démarchage à domicile" {{ old('profil_client', isset($client) && $client->profil_clients == 'Démarchage à domicile' ? 'Démarchage à domicile' : '') == 'Démarchage à domicile' ? 'selected' : '' }}>Démarchage à domicile</option>
                    <option value="Bouche à oreille" {{ old('profil_client', isset($client) && $client->profil_clients == 'Bouche à oreille' ? 'Bouche à oreille' : '') == 'Bouche à oreille' ? 'selected' : '' }}>Bouche à oreille</option>
                    <option value="Famille" {{ old('profil_client', isset($client) && $client->profil_clients == 'Famille' ? 'Famille' : '') == 'Famille' ? 'selected' : '' }}>Famille</option>
                    <option value="Magasin" {{ old('profil_client', isset($client) && $client->profil_clients == 'Magasin' ? 'Magasin' : '') == 'Magasin' ? 'selected' : '' }}>Magasin</option>
                    <option value="Autre" {{ old('profil_client', isset($client) && !in_array($client->profil_clients, ['Foire / salon', 'Artisan', 'Internet', 'Démarchage à domicile', 'Bouche à oreille', 'Famille', 'Magasin']) ? 'Autre' : '') == 'Autre' ? 'selected' : '' }}>Autre</option>
                </select>
                <input type="text" name="profil_client_autre" id="profil_client_autre" value="{{ old('profil_client_autre', isset($client) && !in_array($client->profil_clients, ['Foire / salon', 'Artisan', 'Internet', 'Démarchage à domicile', 'Bouche à oreille', 'Famille', 'Magasin']) ? $client->profil_clients : '') }}" placeholder="Précisez si autre..." class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition mt-2" style="{{ isset($client) && !in_array($client->profil_clients, ['Foire / salon', 'Artisan', 'Internet', 'Démarchage à domicile', 'Bouche à oreille', 'Famille', 'Magasin']) ? '' : 'display:none;' }}">
                @error('profil_client')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
                @error('profil_client_autre')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const select = document.getElementById('profil_client');
                const inputAutre = document.getElementById('profil_client_autre');
                if (select && inputAutre) {
                    // Initial check on page load
                    if (select.value === 'Autre') {
                        inputAutre.style.display = '';
                        inputAutre.required = true;
                    }

                    // Add change event listener
                    select.addEventListener('change', function () {
                        if (this.value === 'Autre') {
                            inputAutre.style.display = '';
                            inputAutre.required = true;
                        } else {
                            inputAutre.style.display = 'none';
                            inputAutre.required = false;
                            inputAutre.value = '';
                        }
                    });
                }
            });
            </script>
            <div class="flex flex-col w-full sm:w-1/2">
                <label for="accept_demarchage" class="text-primary font-bold">Accepte démarchage<span class="text-orange-500">*</span></label>
                <div class="flex flex-row w-full flex-wrap sm:flex-nowrap gap-4">
                    <label>
                        Oui
                        <input type="radio" name="accept_demarchage" value="1" {{ old('accept_demarchage', isset($client) && $client->accept_demarchage ? 'checked' : '') }}>
                    </label>
                    <label>
                        Non
                        <input type="radio" name="accept_demarchage" value="0" {{ old('accept_demarchage', isset($client) && !$client->accept_demarchage ? 'checked' : '') }}>
                    </label>
                </div>
            </div>
        </div>
        <div class="flex flex-row flex-wrap sm:flex-nowrap gap-4">
            <div class="flex flex-col w-full sm:w-1/2">
                <label for="projet" class="text-primary font-bold">Projet<span class="text-orange-500">*</span></label>
                @php
                    $currentValue = isset($client) ? $client->projet : old('projet');
                @endphp
                <select name="projet" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition" required>
                    <option value='' disabled {{ $currentValue == "" ? 'selected' : '' }} hidden>Choisir....</option>
                    @foreach ($priorities as $categorie => $priorites)
                    <optgroup label="{{ $categorie }}">
                        @foreach ($priorites as $slug => $priorite)
                        <option value='{{ $priorite }}' {{ $currentValue == $priorite ? 'selected' : '' }}>
                            {{ $priorite }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                @error('projet')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full sm:w-1/2">
                <label for="type_habitation" class="text-primary font-bold">Type d'habitation<span class="text-orange-500">*</span></label>
                @php
                    $currentValue = isset($client) ? $client->type_habitation : old('type_habitation');
                    if($currentValue == "")
                        $currentValue = 'Maison individuelle';
                @endphp
                <select name="type_habitation" id="type_habitation" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition" required>
                    <option value="" disabled {{ $currentValue == '' ? 'selected' : '' }} hidden>Choisir....</option>
                    <option value="Appartement" {{ $currentValue == 'Appartement' ? 'selected' : '' }}>Appartement</option>
                    <option value="Maison individuelle" {{ $currentValue == 'Maison individuelle' ? 'selected' : '' }}>Maison individuelle</option>
                </select>
                @error('type_habitation')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="flex flex-row flex-wrap sm:flex-nowrap gap-4">
            <div class="flex flex-col w-full sm:w-1/2">
                <label for="date_anniversaire" class="text-primary font-bold">Date d'anniversaire</label>
                <input type="date" name="date_anniversaire" id="date_anniversaire" value="{{ old('date_anniversaire', isset($client) && $client->date_anniversaire ? $client->date_anniversaire->format('Y-m-d') : '') }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
                @error('date_anniversaire')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full sm:w-1/2">
                <label for="proprietaire" class="text-primary font-bold">Propriétaire<span class="text-orange-500">*</span></label>
                @php
                    $currentValue = isset($client) ? $client->proprietaire : old('proprietaire');
                    if($currentValue == '')
                        $currentValue = '1';
                @endphp
                <select name="proprietaire" id="proprietaire" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition" required>
                    <option value="" disabled {{ $currentValue == '' ? 'selected' : '' }} hidden>Choisir....</option>
                    <option value="1" {{ $currentValue == '1' ? 'selected' : '' }}>Oui</option>
                    <option value="0" {{ $currentValue == '0' ? 'selected' : '' }}>Non</option>
                </select>
                @error('proprietaire')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="flex flex-row flex-wrap sm:flex-nowrap gap-4">
            <div class="flex flex-col w-full sm:w-1/2">
                <label for="profession_monsieur" class="text-primary font-bold">Profession (Monsieur)</label>
                <input type="text" name="profession_monsieur" id="profession_monsieur" value="{{ old('profession_monsieur', isset($client) ? $client->profession : '') }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
                @error('profession_monsieur')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full sm:w-1/2">
                <label for="profession_madame" class="text-primary font-bold">Profession (Madame)</label>
                <input type="text" name="profession_madame" id="profession_madame" value="{{ old('profession_madame', isset($client) ? $client->profession2 : '') }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
                @error('profession_madame')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="flex flex-row flex-wrap sm:flex-nowrap gap-4">
            <div class="flex flex-col w-full sm:w-1/3">
                <label for="date_relance" class="text-primary font-bold">Date de relance</label>
                <input type="date" name="date_relance" id="date_relance" value="{{ old('date_relance', isset($client) && $client->date_relance ? $client->date_relance->format('Y-m-d') : $nextNov20) }}" class="bg-[#F3F3F3] rounded-full px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
                @error('date_relance')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-col w-full @if(!auth()->user() || auth()->user()->role !== 'admin') sm:w-1/2 @else sm:w-1/3 @endif">
                <label for="commercial" class="text-primary font-bold">Commercial</label>
                <select name="commercial" id="commercial" class=" text-start h-10 px-3 py-2 bg-[#F3F3F3] rounded-full border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
                    <option value="" disabled hidden>Choisir....</option>
                    @foreach($users as $user)
                        @if(property_exists($user, 'active') ? $user->active == 1 : true)
                            <option value="{{ $user->id }}" {{ old('commercial', isset($client) ? $client->rentre_par : auth()->user()->id) == $user->id ? 'selected' : '' }}>
                                {{ $user->display_name }}
                            </option>
                        @endif
                    @endforeach
                </select>
                @error('commercial')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            @if(auth()->user() && auth()->user()->active == 1 && (auth()->user()->role == 'responsable' || in_array(auth()->user()->id, array_column($groupes->toArray(), 'id_responsable'))))
                <div class="flex flex-col w-full sm:w-1/3">
                    <label for="partage_avec" class="text-primary font-bold">Partagé avec...</label>
                    @if(isset($users) && count($users) > 0)
                        <select name="partage_avec" id="partage_avec" class=" text-start h-10 px-3 py-2 bg-[#F3F3F3] rounded-full border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition">
                            <option value="" disabled selected hidden>Choisir un commercial...</option>
                            @foreach($users as $user)
                                @if($user->id != auth()->id())
                                    <option value="{{ $user->id }}" {{ old('partage_avec', isset($client) ? $client->partage_avec : '') == $user->id ? 'selected' : '' }}>
                                        {{ $user->display_name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <p class="text-gray-500">Aucun commercial.</p>
                    @endif
                    @error('partage_avec')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            @endif
        </div>
        <div class="flex flex-row w-full justify-center">
            <button type="submit" class="cursor-pointer bg-primary text-white font-bold py-3 px-8 rounded-full hover:bg-[#e8911c] transition duration-300 shadow-md w-full">Suivant</button>
            @if(!$client)
                <button type="submit" name="create_rdv_after" value="1" class="cursor-pointer bg-primary text-white font-bold py-3 px-8 rounded-full hover:bg-[#e8911c] transition duration-300 shadow-md w-full">Nouveau RDV</button>
            @endif
        </div>
    </div>


    <div class="flex flex-col w-full mt-8 gap-4">
        <label class="text-primary font-bold mb-2">Tableau liste des commentaires</label>
        @if(isset($comments) && count($comments) > 0)
            <div class="flex flex-col gap-4">
                @foreach($comments as $comment)
                    <div class="bg-[#F3F3F3] rounded-xl shadow p-4 flex flex-col sm:flex-row sm:items-center gap-2 border border-[#DCDADA]">
                        <div class="flex flex-col sm:w-1/5">
                            <span class="text-xs text-gray-500 font-semibold">Date</span>
                            <span class="text-sm text-primary font-bold">{{ \Carbon\Carbon::parse($comment->date)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex flex-col sm:w-1/5">
                            <span class="text-xs text-gray-500 font-semibold">Utilisateur</span>
                            <span class="text-sm  font-bold">{{ $comment->client_prenom ?? '' }} {{ $comment->client_nom ?? '' }}</span>
                        </div>
                        <div class="flex-1 flex flex-col">
                            <span class="text-xs text-gray-500 font-semibold">Commentaire</span>
                            <span class="text-sm ">{{ $comment->commentaire }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Aucun commentaire.</p>
        @endif
    </div>
</form>

@if(isset($client))
<form action="{{ route('formulaire-contact') }}" method="POST" class="gap-6 p-4 sm:p-10 bg-lightgrey rounded-xl shadow-lg max-w-4xl mx-auto items-center mt-8 border-2 border-primary">
    @csrf
    <input type="hidden" name="client_id" value="{{ $client->id }}">
    <div class="flex flex-col w-full gap-4">
        <h2 class="text-xl font-bold text-primary">Ajouter un nouveau commentaire</h2>
        <div class="flex flex-col gap-4">
            <div class="flex flex-col w-full">
                <textarea name="nouveau_commentaire" id="nouveau_commentaire" class="bg-white rounded-xl px-4 py-2 border border-[#DCDADA] focus:outline-none focus:ring-2 focus:ring-darkgrey transition h-24 resize-y" placeholder="Écrivez votre commentaire ici..."></textarea>
                @error('nouveau_commentaire')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex flex-row w-full justify-end">
                <button type="submit" name="ajouter_commentaire" value="1" class="cursor-pointer bg-primary text-white font-bold py-2 px-6 rounded-full hover:bg-primary transition duration-300 shadow-md">Ajouter le commentaire</button>
            </div>
        </div>
    </div>
</form>
@endif
<script>
function initAutocomplete() {
    // Create the autocomplete object
    const input = document.getElementById('adresse_search');
    if (!input) return;

    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['address'],
        componentRestrictions: { country: 'fr' } // Restrict to France
    });

    // When the user selects an address from the dropdown, populate the address fields
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (!place.geometry) {
            // User entered the name of a place that was not suggested and
            // pressed the Enter key, or the Place Details request failed
            window.alert("Aucun détail disponible pour cette adresse: '" + place.name + "'");
            return;
        }

        // Get each component of the address from the place details
        let street_number = '';
        let route = '';
        let postal_code = '';
        let locality = '';
        const lat = place.geometry.location.lat();
        const lng = place.geometry.location.lng();

        for (const component of place.address_components) {
            const componentType = component.types[0];

            switch (componentType) {
                case 'street_number':
                    street_number = component.long_name;
                    break;
                case 'route':
                    route = component.long_name;
                    break;
                case 'postal_code':
                    postal_code = component.long_name;
                    break;
                case 'locality':
                    locality = component.long_name;
                    break;
            }
        }

        // Fill in the address fields
        document.getElementById('adresse').value = street_number ? street_number + ' ' + route : route;
        document.getElementById('code_postal').value = postal_code;
        document.getElementById('ville').value = locality;
        document.getElementById('gps_lat').value = lat;
        document.getElementById('gps_lng').value = lng;
    });
}
</script>
@endsection
