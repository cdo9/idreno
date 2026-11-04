<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Anniversaire extends Component
{
    
    public $clients = [];

   
    public function mount()
    {
        $this->loadClients(); 
    }

    // Load clients whose birthday is today
   public function loadClients()
{
    $today = Carbon::now()->format('m-d');
    $userId = Auth::id();

    $this->clients = Client::whereRaw("DATE_FORMAT(date_anniversaire, '%m-%d') = ?", [$today])
        ->where(function ($query) use ($userId) {
            $query->where('rentre_par', $userId)
                  ->orWhere('partage_avec', $userId);
        })
        ->orderByRaw("DATE_FORMAT(date_anniversaire, '%m-%d')")
        ->get();
}


    public function render()
    {
        return view('livewire.anniversaire'); 
    }
}
