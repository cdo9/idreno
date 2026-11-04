<?php

namespace App\Livewire\Auto;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FormulaireAutoDelete extends Component
{
    public $id;

    public function mount($id)
    {
        $this->id = $id;
    }

    public function delete()
    {
        DB::table('parc_km')->where('auto_id', $this->id)->delete();
        DB::table('parc_auto')->where('id', $this->id)->delete();

        session()->flash('message', 'Véhicule et ses données associées supprimés.');

        return redirect('/parc-auto');
    }

    public function render()
    {
        return view('livewire.auto.formulaire-auto-delete');
    }
}
