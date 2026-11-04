<?php

namespace App\Livewire\Auto;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ParcAuto extends Component
{
    public $date1;

    public $date2;

    public $vehicules = [];

    public $afficherSansKm = false;

    public function mount()
    {
        $this->date1 = $this->date1 ?: Carbon::now()->startOfMonth()->toDateString();
        $this->date2 = $this->date2 ?: Carbon::now()->endOfMonth()->toDateString();
        $this->loadVehicules();
    }

    public function updatedDate1()
    {
        $this->loadVehicules();
    }

    public function updatedDate2()
    {
        $this->loadVehicules();
    }

    public function toggleAfficherSansKm()
    {
        $this->afficherSansKm = ! $this->afficherSansKm;
        $this->loadVehicules();
    }

    public function loadVehicules()
    {
        $moisPrec = Carbon::parse($this->date1)->subMonth()->startOfMonth()->toDateString();

        $vehicules = DB::table('parc_auto')
            ->where('date_attribution', '<=', $this->date2)
            // ->where('secteur', '=', session('selectedSecteur'))
            ->orderBy('date_attribution', 'asc')
            ->get();

        $vehicules = $vehicules->map(function ($vehicule) use ($moisPrec) {
            $kmTot = DB::table('parc_km')
                ->where('auto_id', $vehicule->id)
                ->where('date', '<=', $this->date2)
                ->max('km') ?? 0;

            $kms = DB::table('parc_km')
                ->where('auto_id', $vehicule->id)
                ->whereBetween('date', [$moisPrec, $this->date2])
                ->orderBy('date', 'asc')
                ->pluck('km');

            $kmPeriode = 0;
            $kmFrais = 0;

            if ($kms->count() > 1) {
                for ($i = 1; $i < $kms->count(); $i++) {
                    $kmPeriode += $kms[$i] - $kms[$i - 1];
                }
                $kmFrais = max(0, $kmPeriode - 4000) * 0.14;
            }

            $vehicule->kmTot = $kmTot;
            $vehicule->kmPeriode = $kmPeriode;
            $vehicule->kmFrais = $kmFrais;

            return $vehicule;
        });

        if (! $this->afficherSansKm) {
            $vehicules = $vehicules->filter(fn ($v) => $v->kmPeriode != 0)->values();
        }

        $this->vehicules = $vehicules;
    }

    public function render()
    {
        return view('livewire.auto.parc-auto');
    }
}
