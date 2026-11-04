<?php

namespace App\Livewire\Challenge;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Challenges extends Component
{
    public $month;

    public $year;

    public $months = [];

    public $years = [];

    public function mount()
    {
        $this->month = Carbon::now()->month;
        $this->year = Carbon::now()->year;

        $this->months = [
            0 => "Toute l'année",
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        $this->years = range(2014, Carbon::now()->year);
    }

    public function getChallengesProperty()
    {
        if ($this->month > 0 && $this->month <= 12) {
            $startDate = Carbon::createFromDate($this->year, $this->month)
                ->firstOfMonth()
                ->toDateString();

            $endDate = Carbon::createFromDate($this->year, $this->month)
                ->lastOfMonth()
                ->toDateString();
        } else {
            $startDate = Carbon::createFromDate($this->year)
                ->firstOfYear()
                ->toDateString();

            $endDate = Carbon::createFromDate($this->year)
                ->lastOfYear()
                ->toDateString();
        }

        return DB::table('challenges')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('secteur', '=', session('selectedSecteur'))
            ->orderBy('date', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.challenge.challenges', [
            'challenges' => $this->challenges,
        ]);
    }
}
