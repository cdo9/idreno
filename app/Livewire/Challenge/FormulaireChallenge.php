<?php

namespace App\Livewire\Challenge;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class FormulaireChallenge extends Component
{
    public $challenge;
    public $date;
    public $idChallenge;

    public function mount($id = null)
    {
        if ($id) {
            $challengeData = DB::table('challenges')->where('id', $id)->first();

            if ($challengeData) {
                $this->idChallenge = $challengeData->id;
                $this->challenge = $challengeData->challenge;
                $this->date = $challengeData->date;
            }
        }
    }
    public function cancel()
    {
        return redirect('/challenges');
    }
    public function save()
    {
        $this->validate([
            'challenge' => 'required|string',
            'date' => 'nullable|date',
        ]);

        if ($this->idChallenge) {
            DB::table('challenges')
                ->where('id', $this->idChallenge)
                ->update([
                    'challenge' => $this->challenge,
                    'date' => $this->date,
                ]);

            session()->flash('message', 'Challenge mis à jour');
        }
        return redirect('/challenges');
    }

    public function render()
    {
        return view('livewire.challenge.formulaire-challenge');
    }
}
