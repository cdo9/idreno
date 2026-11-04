<?php

namespace App\Livewire;

use App\Livewire\Traits\BlocagesTrait;
use Livewire\Component;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;


class Blocages extends Component
{
    use BlocagesTrait;

    public $user;
    public $users = [];
    public $isTelepro;
    public $isProspecteur;
    public $isAdmin;
    public $isDirecteur;
    public $ecarts;
    public $ecartsRdv;
    public $ecartsArgus;
    public $ecartsEntretiens;
    public $evaluations;
    public $aConfirmer;
    public $prospections;
    public $indicateurs;
    public $blocagesParUser = [];

    public function mount()
    {
        $user = Auth::user();
        $this->isTelepro = $user->hasRole('telepro');
        $this->isProspecteur = $user->hasRole('prospecteur');
        $this->isAdmin = $user->hasRole('admin');
        $this->isDirecteur = $user->hasRole('directeur');
        $this->users = $this->loadUsers($this->isDirecteur);
        $this->loadBlocagesParUser();
    }

    public static function loadUsers($isDirecteur){
        if($isDirecteur){
            return User::query()
                ->join('secteur_commercials as sc', 'sc.id_commercial', 'users.id')
                ->where('sc.id_secteur', session('selectedSecteur'))
                ->where('active', 1)
                ->whereNotIn('role', ['admin','administratif'])
                ->get();
        }else{
            return User::query()
                ->where('active', 1)
                ->whereNotIn('role', ['admin','administratif'])
                ->get();
        }
    }

    public function loadBlocagesParUser()
    {
        $this->blocagesParUser = [];

        foreach ($this->users as $u) {

            $ecartCount       = $this->blocageGetEcarts($u, $u->hasRole('telepro'), $u->hasRole('prospecteur'));
            

            $ecarts = [];
            foreach($ecartCount as $ecartData){
                
                $ecart['date'] = $ecartData->date;

                $ecart['rdv'] = $this->blocageGetEcartsRdv($u, $ecartData->date);
                $ecart['argu'] = $this->blocageGetEcartsArgus($u, $ecartData->date);
				

                foreach($ecart['rdv'] as $index=>$rdv){
                    foreach($ecart['argu'] as $argus){
                        if($argus->id == $rdv->id)
                            unset($ecart['rdv'][$index]);
                    }
                }

                $ecarts[] = $ecart;
            }

            $evals        = []; // $this->blocageGetEvaluations($u, $this->isTelepro, $this->isProspecteur);
            $entretiens   = []; // $this->blocageGetEcartsEntretiens($u, $this->isDirecteur, $this->isAdmin);
            $aconfirmer   = []; // $this->blocageGetAConfirmer($u, $this->isTelepro, $this->isProspecteur);
            $prospections = []; // $this->blocageGetProspection($u, $this->isTelepro, $this->isProspecteur);
            $ind          = $this->getIndicateursChantiers($u, $u->hasRole('telepro'), $u->hasRole('prospecteur'));

            $this->blocagesParUser[$u->id] = [
                'ecarts'       => $ecarts,
                'evaluations'  => $evals,
                'entretiens'   => $entretiens,
                'aconfirmer'   => $aconfirmer,
                'prospections' => $prospections,
                'ind'          => $ind
            ];

        }
    }

    public function updateReleased($userId){
        $user = User::find($userId);
        if($user){
            $user->released = Carbon::today();
            $user->save();
            
            redirect(request()->header('Referer'));
        }
    }

    public function render()
    {
        return view('livewire.blocages');
    }
}
