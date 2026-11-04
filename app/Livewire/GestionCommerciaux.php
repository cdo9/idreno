<?php

namespace App\Livewire;

use App\Models\Groupe;
use App\Models\ObjectifAnnuelCommercial;
use App\Models\Secteur;
use App\Models\SecteurCommercial;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class GestionCommerciaux extends Component
{
    public $users;

    public $groupes;

    public $secteurs;

    public $currentUser;

    public $totalCa = 0;

    public $totalRdv = 0;

    public $totalCaAnnuel = 0;

    public $newGroupeName;

    public $newGroupeResponsable = null;

    public $newGroupeSecteur = null;

    public $groupeId = null;

    public $objectifsAnnuel = [];

    public $showGroupesTable = false;

    public $typesContrat = [];

    public $showModal = false;

    public $modalType = '';

    public $newPassword = '';

    public $selectedUser = [
        'id' => null,
        'nom' => '',
        'prenom' => '',
        'email' => '',
    ];

    public $newUser = [
        'nom' => '',
        'prenom' => '',
        'email' => '',
        'password' => '',
        'role' => 'vendeur',
        'contrat' => null,
        'groupe' => null,
        'ordre_affichage' => 0,
        'date_entree' => null,
    ];

    protected $queryString = ['newGroupeName', 'newGroupeResponsable', 'newGroupeSecteur'];

    public function mount()
    {
        $this->newGroupeName = $this->newGroupeName ?? '';
        $this->newGroupeResponsable = $this->newGroupeResponsable ?? null;
        $this->newGroupeSecteur = $this->newGroupeSecteur ?? null;
        $this->currentUser = Auth::user();
        $this->loadData();

    }

    public function loadData()
    {
        $this->groupes = Groupe::with(['responsable', 'secteurRel'])->get();
        $this->secteurs = Secteur::where('activated', 1)->get() ?? collect();
        $this->typesContrat = [
            'commercial-v1' => 'Commercial v1 (10%)',
            /*'commercial-v2' => 'commercial-v2',
            'commercial-v3' => 'Commercial v3 (Fixe 1800€)',
            'commercial-v4' => 'Commercial v4 (Fixe 2400€)',
            'commercial-v5' => 'Commercial v5 (Fixe 2000€)',
            'commercial-v6' => 'Commercial v6 (Fixe 2600€)',
            'commercial-v7' => 'Commercial v7 (Fixe 2100€)',
            'telepro-v2' => 'Prospecteur v2 (10%)',
            'telepro-v5' => 'Prospecteur v5 (Fixe 1700€)',
            'telepro-v6' => 'Prospecteur v6 (Fixe 2000€)',*/
            'directeur-v1' => 'directeur-v1',
            'directeur-v2' => 'Directeur v2',
            // 'directeur-v3' => 'directeur-v3',
            // 'resp-prosp' => 'Responsable prospecteurs',
        ];
        /*User::select('contrat')
            ->distinct()
            ->whereNotNull('contrat')
            ->pluck('contrat')
            ->toArray();*/

        $annee = Carbon::now()->year;

        if ($this->currentUser->hasRole('admin')) {
            $groupesResponsable = Groupe::pluck('id');
        } else {
            $groupesResponsable = Groupe::where('id_responsable', $this->currentUser->id)->pluck('id');
        }

        $this->users = User::orderBy('nom', 'asc')
            ->join('secteur_commercials as secteur', 'secteur.id_commercial', 'users.id')
            ->where('secteur.id_secteur', session('selectedSecteur'))
            ->orderBy('prenom', 'asc')
            ->get()
            ->keyBy('id');

        $this->objectifsAnnuel = ObjectifAnnuelCommercial::where('year', $annee)
            ->pluck('value', 'id_user')
            ->toArray();

        $this->totalCa = $this->users->sum('objectif_ca');
        $this->totalRdv = $this->users->sum('objectif_rdv');
        $this->totalCaAnnuel = array_sum($this->objectifsAnnuel);
    }

    public function updateObjectifAnnuel($userId, $value)
    {
        $annee = Carbon::now()->year;

        $user = User::find($userId);

        $secteur = null;
        if ($user && $user->groupe) {
            $secteur = DB::table('groupes')
                ->where('id', $user->groupe)
                ->value('secteur');
        }

        ObjectifAnnuelCommercial::updateOrCreate(
            ['id_user' => $userId, 'year' => $annee],
            [
                'value' => $value,
                'secteur' => $secteur,
            ]
        );

        $this->objectifsAnnuel[$userId] = $value;
        $this->totalCaAnnuel = array_sum($this->objectifsAnnuel);
    }

    public function updateUserField($userId, $field, $value)
    {
        if (in_array($field, ['date_entree']) && $value === '') {
            $value = null;
        }

        if (in_array($field, ['groupe'])) {
            $groupe = Groupe::find($value);

            if ($groupe) {
                SecteurCommercial::updateOrCreate([
                    'id_commercial' => $userId,
                    'id_secteur' => $groupe->secteur,
                ]);
            }

        }

        User::where('id', $userId)->update([$field => $value]);
        $this->loadData();
    }

    public function toggleActive($userId)
    {
        $user = User::find($userId);
        $user->update(['active' => ! $user->active]);
        $this->loadData();
    }

    public function createUser()
    {
        $data = $this->validate([
            'newUser.nom' => 'required|string',
            'newUser.prenom' => 'required|string',
            'newUser.email' => 'required|email|unique:users,email',
            'newUser.password' => 'required|min:6',
            'newUser.date_entree' => 'nullable|date',
            'newUser.groupe' => 'required|exists:groupes,id',
            'newUser.contrat' => 'required|string',
        ])['newUser'];

        $data['objectif_ca'] = $data['objectif_ca'] ?? 0;
        $data['objectif_rdv'] = $data['objectif_ca'] ?? 0;
        $data['type'] = $data['type'] ?? 0;

        $data['password'] = bcrypt($data['password']);
        $data['name'] = $data['email'];
        $data['active'] = 1;
        $user = User::create($data);
        $secteurId = Groupe::find($data['groupe'])->secteur;

        SecteurCommercial::create([
            'id_secteur' => $secteurId,
            'id_commercial' => $user->id,
        ]);

        $this->reset('newUser');
        $this->loadData();
    }

    public function createGroupe()
    {
        if (! $this->newGroupeName || ! $this->newGroupeResponsable || ! $this->newGroupeSecteur) {
            return;
        }

        Groupe::create([
            'nom' => $this->newGroupeName,
            'id_responsable' => $this->newGroupeResponsable,
            'secteur' => $this->newGroupeSecteur,
        ]);

        $this->newGroupeName = '';
        $this->newGroupeResponsable = null;
        $this->newGroupeSecteur = null;

        $this->loadData();
    }

    public function toggleGroupesTable()
    {
        $this->showGroupesTable = ! $this->showGroupesTable;
    }

    public function loadGroupe($groupeId)
    {
        $this->groupeId = $groupeId;

        foreach ($this->groupes as $groupe) {
            if ($groupe->id === $this->groupeId) {
                $this->newGroupeName = $groupe->nom ?? '';
                $this->newGroupeResponsable = $groupe->responsable->id ?? null;
                $this->newGroupeSecteur = $groupe->secteur ?? null;
            }
        }
    }

    public function editGroupe()
    {

        $groupe = Groupe::where('id', $this->groupeId);

        $groupe->nom =
        $groupe->id_responsable = $this->newGroupeResponsable;
        $groupe->secteur = $this->newGroupeSecteur;

        $groupe->update([
            'nom' => $this->newGroupeName,
            'id_responsable' => $this->newGroupeResponsable,
            'secteur' => $this->newGroupeSecteur,
        ]);

        $this->groupeId = null;
        $this->newGroupeName = '';
        $this->newGroupeResponsable = null;
        $this->newGroupeSecteur = null;
        $this->loadData();
    }

    public function deleteGroupe($groupeId)
    {
        Groupe::where('id', $groupeId)->delete();
        $this->loadData();
    }

    public function openModal($type, $userId)
    {
        $this->modalType = $type;
        $this->newPassword = '';

        $user = DB::table('users')->find($userId);
        if ($user) {
            $this->selectedUser = [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
            ];

            $this->selectedUser['selectedSecteurs'] = DB::table('secteur_commercials')
                ->where('id_commercial', $userId)
                ->pluck('id_secteur')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function saveUser()
    {
        DB::table('users')
            ->where('id', $this->selectedUser['id'])
            ->update([
                'nom' => $this->selectedUser['nom'],
                'prenom' => $this->selectedUser['prenom'],
                'email' => $this->selectedUser['email'],
                'updated_at' => now(),
            ]);

        DB::table('secteur_commercials')
            ->where('id_commercial', $this->selectedUser['id'])
            ->delete();

        foreach ($this->selectedUser['selectedSecteurs'] as $idSecteur) {
            DB::table('secteur_commercials')->insert([
                'id_commercial' => $this->selectedUser['id'],
                'id_secteur' => $idSecteur,
            ]);
        }

        $this->loadData();
        $this->showModal = false;
    }

    public function updatePassword()
    {
        if (! empty($this->newPassword)) {
            DB::table('users')
                ->where('id', $this->selectedUser['id'])
                ->update([
                    'password' => Hash::make($this->newPassword),
                    'updated_at' => now(),
                ]);
        }

        $this->newPassword = '';
        $this->loadData();
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.gestion-commerciaux');
    }
}
