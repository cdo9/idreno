<?php

namespace App\Livewire;

use App\Models\Secteur;
use Livewire\Component;

class SecteurShowList extends Component
{
    public $secteurs = [];

    public function mount()
    {
        $this->secteurs = Secteur::all();
    }

    public function updateCouleur($secteurId, $couleur)
    {
        $secteur = Secteur::find($secteurId);
        if ($secteur) {
            $secteur->couleur = $couleur;
            $secteur->save();
            $this->secteurs = Secteur::all();
        }
    }

    public function render()
    {
        return view('livewire.secteur-show-list');
    }
}
