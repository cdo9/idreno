<?php

namespace App\Livewire\TableauChiffre;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\EmptyStringException;

class MainTableau extends Component
{
    public $month;
    public $months = [];
    public $year;
    public $years = [];
    public $activeTab = 'chiffres';

    public function mount()
    {
        $this->initializeDateData();
        $this->initializeMonthsAndYears();
    }


    private function initializeDateData()
    {
        $this->month = Carbon::now()->month;
        $this->year = Carbon::now()->year;
    }

    private function initializeMonthsAndYears()
    {
        $this->months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];
        $this->years = range(Carbon::now()->year, 2014);
    }

    public function updatedMonth() { 
        $this->dispatch('filters-updated', ['month' => $this->month, 'year' => $this->year]); 
    }

    public function updatedYear() { 
        $this->dispatch('filters-updated', ['month' => $this->month, 'year' => $this->year]); 
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->dispatch('filters-updated', ['month' => $this->month, 'year' => $this->year]);
    }
    
    public function render()
    {
        return view('livewire.tableau-chiffre.main-tableau');
    }
}
