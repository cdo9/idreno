<?php

namespace App\Livewire\Challenge;

use Livewire\Component;
use App\Models\Challenge;

class DeleteChallenge extends Component
{
    public $idChallenge;
    public $challenge;

    public function mount($id)
    {
        $this->idChallenge = $id;
        $this->challenge = Challenge::findOrFail($id);
    }

    public function delete()
    {
        if ($this->idChallenge) {
            Challenge::where('id', $this->idChallenge)->delete();
            session()->flash('message', 'Challenge supprimé avec succès.');
            return redirect('/challenges');
        }
    }

    public function render()
    {
        return view('livewire.challenge.delete-challenge');
    }
}
