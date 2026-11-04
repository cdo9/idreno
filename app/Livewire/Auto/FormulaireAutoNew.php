<?php

namespace App\Livewire\Auto;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FormulaireAutoNew extends Component
{
    public $type = 'new';
    public $id;
    public $immat = '';
    public $modele = '';
    public $selectedConducteur = '';
    public $date_attribution;
    public $conducteurs = [];
    public $secteur = null;
    public $secteurs = [];

    public function mount($id = null, $action = null)
    {

        $commerciaux = DB::table('users')
            ->where('active', 1)
            ->get()
            ->map(fn($c) => $c->nom.' '.$c->prenom)
            ->toArray();

        $poseurs = DB::table('poseurs')
            ->pluck('poseur')
            ->toArray();
			
			
        $this->secteurs = DB::table('secteurs')->where('activated', 1)->get();

        $this->conducteurs = [
            'commerciaux' => $commerciaux,
            'poseurs' => $poseurs,
        ];

        $this->id = $id;
        $this->type = $action === 'delete' ? 'delete' : ($id ? 'modify' : 'new');

        $this->selectedConducteur = $commerciaux[0];

    }

    public function loadVehicule()
    {
        $vehicule = DB::table('parc_auto')->where('id', $this->id)->first();
        if ($vehicule) {
            $this->immat = $vehicule->immatriculation;
            $this->modele = $vehicule->modele;
            $this->selectedConducteur = $vehicule->conducteur;
            $this->date_attribution = $vehicule->date_attribution;
        }
    }

    public function save()
    {
        $this->validate([
            'immat' => 'required|string|max:255',
            'modele' => 'required|string|max:255',
            'selectedConducteur' => 'required|string',
            'date_attribution' => 'required|date',
            'secteur' => 'required',
        ]);
            $this->id = DB::table('parc_auto')->insertGetId([
                'immatriculation' => $this->immat,
                'modele' => $this->modele,
                'conducteur' => $this->selectedConducteur,
                'date_attribution' => $this->date_attribution,
                'secteur' => $this->secteur,
            ]);
        session()->flash('message', 'Véhicule enregistré.');
        return redirect()->to('/parc-auto');
    }
    public function cancel()
    {
        return redirect('/parc-auto');
    }

    public function delete()
    {
        DB::table('parc_auto')->where('id', $this->id)->delete();
        session()->flash('message', 'Véhicule supprimé.');
        return redirect('/parc-auto');
    }

    public function render()
    {
        return view('livewire.auto.formulaire-auto-new');
    }
}
