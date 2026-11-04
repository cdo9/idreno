<?php

namespace App\Livewire\Stats;

use App\Services\CommerciauxService;
use App\Services\StatsContactsServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StatsContact extends Component
{
    public $dateDebut;

    public $dateFin;

    public $commerciaux = [];

    public $statsData = [];

    public function mount()
    {
        $this->dateDebut = $this->dateDebut ?: Carbon::now()->startOfMonth()->toDateString();
        $this->dateFin = $this->dateFin ?: Carbon::now()->endOfMonth()->toDateString();
        $this->loadCommerciaux();
        $this->loadStats();
    }

    public function updatedDateDebut()
    {
        $this->loadStats();
    }

    public function updatedDateFin()
    {
        $this->loadStats();
    }

    private function loadCommerciaux()
    {
        $this->commerciaux = CommerciauxService::getCommerciauxListBySector();
    }

    private function loadStats()
    {
        $this->statsData = [];

        foreach ($this->commerciaux as $commercial) {
            $stats = DB::table('clients')
                ->select('type', DB::raw('count(1) as cpt'))
                ->whereBetween('rentre_le', [$this->dateDebut, $this->dateFin])
                ->where(function ($query) use ($commercial) {
                    $query->where('rentre_par', $commercial->id)
                        ->orWhere('partage_avec', $commercial->id);
                })
                ->where('secteur', session('selectedSecteur'))
                ->groupBy('type')
                ->pluck('cpt', 'type')
                ->toArray();

            $total = array_sum($stats);

            $this->statsData[$commercial->id] = [
                'commercial' => $commercial,
                'stats' => $stats,
                'total' => $total,
            ];
        }
    }

    private function getCommercialNom($commercial)
    {
        return $commercial->prenom.' '.$commercial->nom;
    }

    public function render()
    {
        $types = StatsContactsServices::ClientTypes();
        unset($types[5]); // En pose
        unset($types[3]); // Annulé

        return view('livewire.stats.stats-contact', [
            'types' => $types,
        ]);
    }
}
