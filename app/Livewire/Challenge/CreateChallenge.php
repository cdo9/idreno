<?php

namespace App\Livewire\Challenge;

use Livewire\Component;
use App\Models\Challenge;

class CreateChallenge extends Component
{
    public $challenge;
    public $date;

    protected $rules = [
        'challenge' => 'required|string|max:255',
        'date' => 'required|date',
    ];

    public function save()
    {
        $this->validate();

        Challenge::create([
            'challenge' => $this->challenge,
            'date' => $this->date,
            'secteur' => session('selectedSecteur')
        ]);

        session()->flash('message', 'Challenge créé avec succès !');
        $this->reset(['challenge', 'date']);
        return redirect()->to('/challenges');
    }
    public function cancel()
    {
        return redirect()->to('/challenges');
    }

    public function render()
    {
        return view('livewire.challenge.create-challenge');
    }
}
