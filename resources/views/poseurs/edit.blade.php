@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-2xl shadow-lg">
    <h2 class="text-2xl font-bold text-primary mb-8 text-center">Éditer l’archive du poseur</h2>
    <form action="{{ isset($poseur) ? route('poseurs.update', $poseur->id) : route('poseurs.post') }}" method="POST" class="space-y-6">
        @csrf
        <div>
            <label for="poseur" class="block text-gray-700 font-semibold mb-2">Nom du poseur</label>
            <input name="poseur" class="bg-gray-100 rounded-lg px-4 py-3 text-gray-700 font-medium border border-[#DCDADA]" value="{{ isset($poseur) ? $poseur->poseur : '' }}">
        </div>
        <div>
            <label for="archive" class="block text-gray-700 font-semibold mb-2">Supprimer ?</label>
            <select name="archive" id="archive" class="w-full border border-[#DCDADA] rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#77BC23] transition duration-200">
                <option value="0" @if(isset($poseur) && !$poseur->archive) selected @endif>Non</option>
                <option value="1" @if(isset($poseur) && $poseur->archive) selected @endif>Oui</option>
            </select>
        </div>
        <button type="submit" class="cursor-pointer w-full bg-lightprimary text-white font-bold py-3 rounded-lg hover:bg-primary transition duration-200 flex items-center justify-center gap-2">
            <i class="fa-solid fa-save"></i> Enregistrer
        </button>
        <a href="{{ route('poseurs') }}" class="block w-full text-center mt-4 py-3 rounded-lg bg-grey text-white font-bold hover:bg-darkgrey transition duration-200 flex items-center justify-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Annuler
        </a>
    </form>
</div>
@endsection
