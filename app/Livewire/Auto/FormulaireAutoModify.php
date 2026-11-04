<?php

namespace App\Livewire\Auto;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FormulaireAutoModify extends Component
{
    public $id;

    public $immat = '';

    public $modele = '';

    public $selectedConducteur = '';

    public $date_attribution;

    public $conducteurs = [];

    public $secteur = null;

    public $kmMensuels = [];

    public $months = [];

    public $secteurs = [];

    public function mount($id = null)
    {
        $commerciaux = DB::table('users')
            ->where('active', 1)
            ->get()
            ->map(fn ($c) => $c->nom.' '.$c->prenom)
            ->toArray();

        $poseurs = DB::table('poseurs')
            ->pluck('poseur')
            ->toArray();

        $this->secteurs = DB::table('secteurs')->where('activated', 1)->get();

        $this->conducteurs = [
            'commerciaux' => $commerciaux,
            'poseurs' => $poseurs,
        ];
        $this->loadVehicule($id);

        $this->months = collect();
        for ($i = 9; $i >= 0; $i--) {
            $this->months->push(Carbon::now()->startOfMonth()->subMonths($i));
        }
        if ($this->id) {
            $this->loadKmMensuels();
        }

    }

    public function loadVehicule($id)
    {
        $this->id = $id;

        $vehicule = DB::table('parc_auto')->where('id', $this->id)->first();
        if ($vehicule) {
            $this->immat = $vehicule->immatriculation;
            $this->modele = $vehicule->modele;
            $this->selectedConducteur = $vehicule->conducteur;
            $this->date_attribution = $vehicule->date_attribution;
            $this->secteur = $vehicule->secteur;

            $this->loadKmMensuels();
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

        DB::table('parc_auto')->where('id', $this->id)->update([
            'immatriculation' => $this->immat,
            'modele' => $this->modele,
            'conducteur' => $this->selectedConducteur,
            'date_attribution' => $this->date_attribution,
            'secteur' => $this->secteur,
        ]);

        $this->saveKmMensuels();
        session()->flash('message', 'Véhicule enregistré.');

        return redirect('/parc-auto');
    }

    public function loadKmMensuels()
    {
        $this->kmMensuels = [];

        foreach ($this->months as $month) {
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();

            $kmRecord = DB::table('parc_km')
                ->where('auto_id', $this->id)
                ->whereBetween('date', [$start, $end])
                ->orderBy('date', 'desc')
                ->first();

            $km = $kmRecord ? $kmRecord->km : 0;

            $this->kmMensuels[] = [
                'month' => $month->format('Y-m-01'),
                'label' => $month->format('M Y'),
                'km' => $km,
            ];
        }
    }

    public function saveKmMensuels()
    {
        foreach ($this->kmMensuels as $kmData) {
            $existing = DB::table('parc_km')
                ->where('auto_id', $this->id)
                ->where('date', $kmData['month'])
                ->first();

            if ($existing) {
                DB::table('parc_km')
                    ->where('auto_id', $this->id)
                    ->where('date', $kmData['month'])
                    ->update(['km' => $kmData['km']]);
            } else {
                DB::table('parc_km')->insert([
                    'auto_id' => $this->id,
                    'date' => $kmData['month'],
                    'km' => $kmData['km'],
                ]);
            }
        }

        session()->flash('message', 'Km mensuels enregistrés.');
    }

    public function updatedKmMensuels($value, $key) {}

    public function cancel()
    {
        return redirect('/parc-auto');
    }

    public function render()
    {
        return view('livewire.auto.formulaire-auto-modify');
    }
}
