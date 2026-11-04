<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Traits\BlocagesTrait;

class Rattrapage extends Component
{
    use BlocagesTrait;

    public $ecarts = [];
    public $evaluations = [];
    public $aconfirmer = [];
    public $prospections = [];
    public $entretiens = [];
    public $indicateurs = [];
    public $indicateursTab = [];
    public $isTelepro;
    public $isProspecteur;
    public $isAdmin;
    public $isDirecteur;
    public $hasBlocage;

    public function mount()
    {
        $user = Auth::user();
        $this->isTelepro = $user->hasRole('telepro');
        $this->isProspecteur = $user->hasRole('prospecteur');
        $this->isAdmin = $user->hasRole('admin');
        $this->isDirecteur = $user->hasRole('directeur');

        $blocages = new Blocages();
        $blocages->mount();
        $this->hasBlocage = $blocages->hasBlocageQuelconque($user, $this->isTelepro, $this->isProspecteur, $this->isDirecteur, $this->isAdmin);

        if (!$this->hasBlocage || auth()->user()->isReleased()) {
            return redirect('/');
        }

        $userBlocage = $blocages->blocagesParUser[$user->id];
        
        $this->ecarts = $userBlocage['ecarts'];
        $this->evaluations = $userBlocage['evaluations'];
        $this->aconfirmer = $userBlocage['aconfirmer'];
        $this->prospections = $userBlocage['prospections'];
        $this->entretiens = $userBlocage['entretiens'];
        $this->indicateurs = $userBlocage['ind'];
    }

    public function render()
    {
        return view('livewire.rattrapage');
    }
}
