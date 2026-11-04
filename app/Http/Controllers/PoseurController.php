<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poseur;

class PoseurController extends Controller
{
    // Affiche la liste des poseurs
    public function index()
    {
        $poseurs = Poseur::select('id', 'poseur', 'archive')->get();
        return view('poseurs.index', compact('poseurs'));
    }

    // Affiche le formulaire d'édition d'un poseur
    public function edit(Poseur $poseur)
    {
        return view('poseurs.edit', compact('poseur'));
    }

    public function create()
    {
        return view('poseurs.edit');
    }

    // Met à jour l'archive d'un poseur
    public function update(Request $request, Poseur $poseur)
    {
        $poseur->poseur = $request->input('poseur', 0);
        $poseur->archive = $request->input('archive', 0);
        $poseur->save();
        toastr()->success('Données bien modifié !');
        return redirect()->route('poseurs');
    }

    // Met à jour l'archive d'un poseur
    public function post(Request $request)
    {
        $poseur = new Poseur();
        $poseur->poseur = $request->input('poseur', 0);
        $poseur->archive = $request->input('archive', 0);
        $poseur->save();
        toastr()->success('Données sauvegardée !');
        return redirect()->route('poseurs');
    }
} 