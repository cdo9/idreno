<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class RelanceListing extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Propriétés principales
    public $type = 'argumentations';

    public $date_debut = '';

    public $date_fin = '';

    public $secteur_id = '';

    public $perPage = 15;

    public $user;

    // Tri
    public $sortColumn = 'date_relance';

    public $sortDirection = 'desc';

    public $selectedClientId = null;

    public $selectedRendezVousId = null;

    protected $queryString = [
        'type' => ['except' => 'argumentations'],
        'date_debut',
        'date_fin',
        'secteur_id' => ['except' => ''],
    ];

    public function updatingDateDebut()
    {
        $this->resetPage();
    }

    public function updatingDateFin()
    {
        $this->resetPage();
    }

    public function updatingSecteurId()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
        $this->sortColumn = 'date_relance';
        $this->sortDirection = 'desc';
    }

    public function mount($type = 'argumentations', $secteurId = null, $dateDebut = '', $dateFin = '')
    {
        $this->user = auth()->user();
        $this->type = $type;
        $this->secteur_id = $secteurId;
        $this->date_debut = $dateDebut;
        $this->date_fin = $dateFin;

        // Par défaut : si aucune date fournie ET aucun paramètre date dans l'URL, on prend le mois en cours
        if (
            empty($this->date_debut) && empty($this->date_fin) &&
            ! (request()->has('date_debut') && request()->has('date_fin'))
        ) {
            $this->date_debut = Carbon::now()->startOfMonth()->format('Y-m-d');
            $this->date_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
        }
    }

    protected function getClients()
    {
        if ($this->type === 'argumentations') {
            return $this->getArgumentationsData();
        }
        if ($this->type === 'financements') {
            return $this->getFinancementData();
        }

        return $this->getContactsData();
    }

    protected function getViewName($type)
    {
        if ($type === 'argumentations') {
            return 'livewire.relance-listing-argumentations';
        }
        if ($type === 'financements') {
            return 'livewire.relance-listing-financements';
        }

        return 'livewire.relance-listing';
    }

    private function getArgumentationsData()
    {
        $userRole = Auth::user()->role;

        $query = RendezVous::with(['client:id,rentre_par, nom,prenom, telephone1, telephone2, ville'])
            ->with('client.user:id,nom,prenom')
            ->addSelect([
                'rendez_vous.id as rdv_id',
                'rendez_vous.date as date_argu',
                'rendez_vous.devis_total_ht as montant_devis',
                'rendez_vous.tarif_total_ht as montant_tarif',
                'rendez_vous.resultat',
                'rendez_vous.produits',
                'rendez_vous.date_relance',
                'rendez_vous.relance_confirme as rdv_relance',
                'groupes.nom as groupe_nom',
                'rendez_vous.id_client'])
            ->join('clients', 'rendez_vous.id_client', '=', 'clients.id')
            ->leftJoin('users', 'clients.rentre_par', '=', 'users.id')
            ->leftJoin('groupes', 'users.groupe', '=', 'groupes.id')
            ->where('relance_confirme', 0);

        if (! in_array($userRole, ['directeur', 'admin'])) {
            $currentUserId = Auth::user()->id;
            $query->whereRaw('(clients.rentre_par = ? OR clients.partage_avec = ?)', [$currentUserId, $currentUserId]);
        }

        // $query->where('clients.nom', 'LIKE', 'Rhault');

        // Filtrer par date de relance pour lister les relances (par défaut : mois en cours)
        $this->applyDateFilters($query, 'rendez_vous.date_relance');
        $this->applySecteurFilter($query, 'clients.secteur');

        // Tri sécurisé
        $allowedColumns = ['nom', 'prenom', 'ville', 'date_argu', 'montant_devis', 'groupe_nom'];
        $sortColumn = in_array($this->sortColumn, $allowedColumns) ? $this->sortColumn : 'date_argu';

        if (in_array($sortColumn, ['nom', 'prenom'])) {
            $sortColumn = 'clients.'.$sortColumn;
        }

        $argus = $query->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // Ajouter les paramètres de pagination à l'URL
        $argus->withPath('/relances?type='.$this->type.'&date_debut='.$this->date_debut.'&date_fin='.$this->date_fin.'&secteur_id='.$this->secteur_id);

        return $argus;
    }

    private function getFinancementData()
    {
        $userRole = Auth::user()->role;

        $query = DB::table('rendez_vous')
            ->join('clients', 'rendez_vous.id_client', '=', 'clients.id')
            ->leftJoin('users', 'clients.rentre_par', '=', 'users.id')
            ->leftJoin('groupes', 'users.groupe', '=', 'groupes.id')
            ->join('ventes', 'ventes.id_rdv', '=', 'rendez_vous.id')
            ->join('poses', 'poses.id_vente', '=', 'ventes.id')
            ->select(
                'clients.*',
                'rendez_vous.id as rdv_id',
                'rendez_vous.date as date_argu',
                'rendez_vous.devis_total_ht as montant_devis',
                'rendez_vous.tarif_total_ht as montant_tarif',
                'rendez_vous.resultat',
                'rendez_vous.produits',
                'rendez_vous.date_relance',
                'rendez_vous.relance_confirme as rdv_relance',
                'users.prenom as user_prenom',
                'users.nom as user_nom',
                'groupes.nom as groupe_nom'
            )
            ->selectRaw('DATE_ADD(poses.date_fin, INTERVAL ventes.postpone MONTH) as date_relance_financement')
            ->where('relance_confirme', 1)
            ->where('paiement', 'like', 'financement');

        if (! in_array($userRole, ['directeur', 'admin'])) {
            $currentUserId = Auth::user()->id;
            $query->whereRaw('(clients.rentre_par = ? OR clients.partage_avec = ?)', [$currentUserId, $currentUserId]);
        }

        // $query->where('clients.nom', 'LIKE', 'Rhault');

        // Filtrer par date de relance pour lister les relances (par défaut : mois en cours)
        if ($this->date_debut && $this->date_fin) {
            $query->whereRaw('DATE_ADD(poses.date_fin, INTERVAL ventes.postpone MONTH) BETWEEN ? AND ?',
                [$this->date_debut, $this->date_fin]);
        } elseif ($this->date_debut) {
            $query->whereRaw('DATE_ADD(poses.date_fin, INTERVAL ventes.postpone MONTH) >= ?',
                [$this->date_debut]);
        } elseif ($this->date_fin) {
            $query->whereRaw('DATE_ADD(poses.date_fin, INTERVAL ventes.postpone MONTH) <= ?',
                [$this->date_fin]);

        }
        $this->applySecteurFilter($query, 'clients.secteur');

        // Tri sécurisé
        $allowedColumns = ['nom', 'prenom', 'ville', 'date_argu', 'montant_devis', 'groupe_nom'];
        $sortColumn = in_array($this->sortColumn, $allowedColumns) ? $this->sortColumn : 'date_argu';

        if (in_array($sortColumn, ['nom', 'prenom'])) {
            $sortColumn = 'clients.'.$sortColumn;
        }

        $argus = $query->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // dd($query->toSql());

        // Ajouter les paramètres de pagination à l'URL
        $argus->withPath('/relances?type='.$this->type.'&date_debut='.$this->date_debut.'&date_fin='.$this->date_fin.'&secteur_id='.$this->secteur_id);

        return $argus;
    }

    private function getContactsData()
    {
        $userRole = Auth::user()->role;

        $query = Client::query()
            ->leftJoin('users', 'clients.rentre_par', '=', 'users.id')
            ->select('clients.*', 'users.prenom as user_prenom', 'users.nom as user_nom')
            ->whereNotNull('clients.date_relance');

        if (! in_array($userRole, ['directeur', 'admin'])) {
            $currentUserId = Auth::user()->id;
            $query->whereRaw('(clients.rentre_par = ? OR clients.partage_avec = ?)', [$currentUserId, $currentUserId]);
        }

        $this->applyDateFilters($query, 'clients.date_relance');
        $this->applySecteurFilter($query, 'clients.secteur');

        $allowedColumns = ['nom', 'prenom', 'telephone', 'ville', 'rentre_le', 'date_relance'];
        $sortColumn = in_array($this->sortColumn, $allowedColumns) ? $this->sortColumn : 'date_relance';

        $contacts = $query->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        $contacts->withPath('/relances?type='.$this->type.'&date_debut='.$this->date_debut.'&date_fin='.$this->date_fin.'&secteur_id='.$this->secteur_id);

        return $contacts;
    }

    public function editRendezVous($rdvId)
    {
        return redirect()->route('argumentation.edit', $rdvId);
    }

    public function editClient($clientId)
    {
        return redirect()->route('nouveau-contact', ['client_id' => $clientId]);
    }

    public function render()
    {
        $secteurs = $this->user->secteurs();

        $clients = $this->getClients();

        return view($this->getViewName($this->type), [
            'clients' => $clients,
            'secteurs' => $secteurs,
        ]);
    }

    // Tri simplifié
    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function sortiIcon($column)
    {
        if ($this->sortColumn === $column) {
            return $this->sortDirection === 'asc'
             ? '<span class="fa fa-sort-up"></span>'
             : '<span class="fa fa-sort-down"></span>';
        }

        return '<span class="fa fa-sort text-muted"></span>';
    }

    public function openRelanceModal($clientId, $rendezVousId = null)
    {
        $this->selectedClientId = $clientId;
        $this->selectedRendezVousId = $rendezVousId;

        // Émettre un événement pour déclencher la modal SweetAlert avec le type
        $this->dispatch('open-relance-modal', [
            'clientId' => $clientId,
            'rendezVousId' => $rendezVousId,
            'type' => $this->type,
        ]);
    }

    public function closeRelanceModal()
    {
        $this->selectedClientId = null;
        $this->selectedRendezVousId = null;

        // Émettre un événement pour fermer SweetAlert2
        $this->dispatch('close-relance-modal');
    }

    public function marquerRelance($clientId = null, $rendezVousId = null, $data = [])
    {
        $clientId = $clientId ?: $this->selectedClientId;
        $rendezVousId = $rendezVousId ?: $this->selectedRendezVousId;

        try {
            $type = $data['type'] ?? $this->type;
            $compteRendu = $data['compte_rendu'] ?? '';

            if ($type === 'argumentations' && $rendezVousId) {
                $updateData = [
                    'relance_confirme' => 1,
                    'date_relance' => null,
                ];

                RendezVous::where('id', $rendezVousId)->update($updateData);
            } else {
                $updateData = [
                    'date_relance' => null,
                ];

                Client::where('id', $clientId)->update($updateData);
            }

            $this->closeRelanceModal();

        } catch (\Exception $e) {
            $this->dispatch('relance-error', [
                'message' => 'Erreur lors de la sauvegarde : '.$e->getMessage(),
            ]);
        }
    }

    // Utilitaires
    public function resetFilters()
    {
        $this->date_debut = '';
        $this->date_fin = '';
        $this->secteur_id = '';
        $this->resetPage();
    }

    protected function applyDateFilters($query, $dateColumn = 'date_relance')
    {
        if ($this->date_debut && $this->date_fin) {
            $query->whereBetween($dateColumn, [$this->date_debut, $this->date_fin]);
        } elseif ($this->date_debut) {
            $query->where($dateColumn, '>=', $this->date_debut);
        } elseif ($this->date_fin) {
            $query->where($dateColumn, '<=', $this->date_fin);
        }
    }

    protected function applySecteurFilter($query, $secteurColumn = 'secteur')
    {
        if ($this->secteur_id) {
            $query->where($secteurColumn, $this->secteur_id);
        } else {
            $query->whereIn($secteurColumn, $this->user->secteur_commercials->pluck('id_secteur'));
        }
    }
}
