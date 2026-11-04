<?php

namespace App\Livewire\VentesCommissions;

use App\Models\User;
use App\Models\Vente;
use App\Services\CommerciauxService;
use App\Services\CommissionService;
use App\Services\ProjectService;
use App\Services\SourceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Ventes extends Component
{
    use WithPagination;

    //    protected $paginationTheme = 'bootstrap';

    // Filter properties
    public $moisParam = null;

    public $anneeParam = null;

    public $commercial = '';

    // Data for dropdowns
    public $commercials = [];

    public $annees = [];

    public $mois_options = [];

    public $userId;

    public $isDirecteur = false;

    public $isAdmin = false;

    public $isAdministratif = false;

    public $commName = '';

    public $isCommissionMode = false;

    //    protected $paginationTheme = 'bootstrap';

    // Reset pagination when filters change
    public function updatingMoisParam($value)
    {
        $this->moisParam = $value;
        $this->resetPage();
        $this->redirectWithFilters();
    }

    public function updatingAnneeParam($value)
    {
        if ($value == '') {
            $this->anneeParam = null;
        } else {
            $this->anneeParam = $value;
        }
        $this->resetPage();
        $this->redirectWithFilters();
    }

    public function updatingCommercial($value)
    {
        $this->commercial = $value;
        $this->resetPage();
        $this->redirectWithFilters();
    }

    public function redirectWithFilters()
    {
        return redirect()->route('ventes', [
            'moisParam' => $this->moisParam,
            'anneeParam' => $this->anneeParam ?: null,
            'commercial' => $this->commercial ?: null,
        ]);
    }

    protected $queryString = ['anneeParam', 'moisParam', 'commercial'];

    public function mount()
    {
        $user = Auth::user();
        $this->userId = $user->id;
        $this->isDirecteur = $user->hasRole('directeur');
        $this->isAdmin = $user->hasRole('admin');
        $this->isAdministratif = $user->hasRole('administratif');

        // Set default filters to current year and month
        $this->anneeParam = $this->anneeParam ?? date('Y');
        $this->moisParam = $this->moisParam ?? intval(date('m'));

        // If user is not admin, set commercial filter to current user
        if (! $this->isAdmin && ! $this->isAdministratif && ! $this->isDirecteur) {
            $this->commercial = $this->commercial != '' ? $this->commercial : $this->userId;
        }

        if ($this->commercial != '') {
            $selectedUser = User::where('id', $this->commercial)->get()->first()->toArray();
            $this->commName = $this->getCommercialNom($selectedUser);
        }

        // Load active commercials
        $this->commercials = CommerciauxService::getCommerciauxListBySector('date_entree', 'asc');

        // Generate years from oldest vente to current year
        $oldestYear = Vente::selectRaw('MIN(YEAR(date)) as min_year')->value('min_year');
        $currentYear = date('Y');

        if ($oldestYear) {
            for ($year = $currentYear; $year >= $oldestYear; $year--) {
                $this->annees[] = $year;
            }
        }

        // Generate month options in French
        $this->mois_options = [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre',
        ];
    }

    public function getCommercialNom($user)
    {
        return $user['nom'].' '.$user['prenom'];
    }

    public function exportCsv()
    {
        $this->dispatchBrowserEvent('triggerCsvExport');
    }

    private function listPrioritesFlat()
    {
        $priorities = ProjectService::listPriorities();
        $flat = [];
        foreach ($priorities as $category => $products) {
            foreach ($products as $key => $name) {
                $flat[$key] = $name;
            }
        }

        return $flat;
    }

    public function editVente($venteId)
    {
        // Redirect to argumentation page as requested
        return redirect()->route('argumentation.edit', ['rdvId' => $venteId]);
    }

    public function toggleVenteStatus($venteId, $validate)
    {
        try {
            $vente = Vente::findOrFail($venteId);

            // Check if user has permission to modify this vente
            if (! $this->isDirecteur && $vente->id_commercial != $this->userId) {
                session()->flash('error', 'Vous n\'avez pas l\'autorisation de modifier cette vente.');

                return;
            }

            $ventes = Vente::where('id_rdv', $vente->id_rdv)->get();

            // Toggle between confirmed (2) and cancelled (3) status
            if ($validate) {
                $newStatus = 2;
                $status = 'confirmée';
            } else {
                $newStatus = 3;
                $status = 'annulée';
            }

            foreach ($ventes as $v) {
                $v->statut = $newStatus;
                $v->save();
            }

            // Clear cached summaries since vente status affects cancellation summary
            $this->clearCachedSummaries();

            session()->flash('success', "Vente {$status} avec succès.");

        } catch (\Exception $e) {
            Log::error('Error toggling vente status: '.$e->getMessage());
            session()->flash('error', 'Erreur lors de la modification du statut de la vente.');
        }
    }

    public function toggleCommande($venteId)
    {
        try {
            $vente = Vente::findOrFail($venteId);

            // Check if user has permission to modify this vente
            if (! $this->isDirecteur && $vente->id_commercial != $this->userId) {
                session()->flash('error', 'Vous n\'avez pas l\'autorisation de modifier cette vente.');

                return;
            }

            $ventes = Vente::where('id_rdv', $vente->id_rdv)->get(); // Get all ventes if shared
            $status = '';
            foreach ($ventes as $vente) {
                // Toggle the commande boolean value between 0 and 1
                $vente->commande = $vente->commande ? 0 : 1;
                $vente->save();
                $status = $vente->commande ? 'activé' : 'désactivé';
            }

            session()->flash('success', "Statut de commande {$status} avec succès.");

        } catch (\Exception $e) {
            Log::error('Error toggling commande status: '.$e->getMessage());
            session()->flash('error', 'Erreur lors de la modification du statut de commande.');
        }
    }

    public function toggleCommission($venteId)
    {
        try {
            $vente = Vente::findOrFail($venteId);

            // Check if user has permission to modify this vente
            if (! $this->isDirecteur && $vente->id_commercial != $this->userId) {
                session()->flash('error', 'Vous n\'avez pas l\'autorisation de modifier cette vente.');

                return;
            }

            $ventes = Vente::where('id_rdv', $vente->id_rdv)->get(); // Get all ventes if shared
            $sessionMsg = '';

            foreach ($ventes as $vente) {

                // Toggle commission.blade.php: if date_commission exists, remove it; otherwise set to today
                if ($vente->date_commission) {
                    $vente->date_commission = null;
                    $vente->commission = 0;
                    $sessionMsg = 'Commission désactivée avec succès.';
                } else {
                    $vente->date_commission = now();
                    $vente->commission = 1;
                    $sessionMsg = 'Commission activée avec succès.';
                }

                $vente->save();
            }
            session()->flash('success', $sessionMsg);

        } catch (\Exception $e) {
            Log::error('Error toggling commission.blade.php status: '.$e->getMessage());
            session()->flash('error', 'Erreur lors de la modification du statut de commission.blade.php.');
        }
    }

    public function handleAttente($venteId, $attente)
    {
        try {
            // Check if user has admin permission
            if (! $this->isAdmin) {
                session()->flash('error', 'Vous n\'avez pas l\'autorisation d\'effectuer cette action.');

                return;
            }

            $vente = Vente::findOrFail($venteId);

            $ventes = Vente::where('id_rdv', $vente->id_rdv)->get(); // Get all ventes if shared

            foreach ($ventes as $vente) {

                $attentes = explode(';', $vente->attente);
                $attentes = array_diff($attentes, [$attente]);
                $vente->attente = implode(';', $attentes);
                $vente->save();
            }

            // Log the attente action for admin users
            Log::info('Admin action on attente', [
                'user_id' => $this->userId,
                'vente_id' => $venteId,
                'attente' => $attente,
            ]);

            session()->flash('success', "Action effectuée sur l'attente: {$attente}");

        } catch (\Exception $e) {
            Log::error('Error handling attente action: '.$e->getMessage());
            session()->flash('error', 'Erreur lors de l\'action sur l\'attente.');
        }
    }

    public function redirectToCommissions()
    {
        $this->isCommissionMode = true;
    }

    public function redirectToVentes()
    {
        $this->isCommissionMode = false;
    }

    private function getCurrentFilterHash()
    {
        return md5($this->moisParam.'|'.$this->anneeParam.'|'.$this->commercial.'|'.$this->userId);
    }

    public function render()
    {
        try {
            Log::info('VenteComission - render() started');

            $allComm = User::all()->toArray();

            $query = Vente::with([
                'client',
                'user',
                'rendez_vous',
                'source',
                'secteur',
            ]);

            $mois = $this->moisParam;
            $annee = $this->anneeParam;

            if ($this->isCommissionMode) {
                // On prend les données du mois précédent
                $mois--;
                if ($mois == 0) {
                    $mois = 12;
                    $annee--;
                }
            }

            // Month filter
            if ($mois !== '') {
                $query->whereMonth('ventes.date', $mois);
            }

            // Year filter
            if ($annee !== '') {
                $query->whereYear('ventes.date', $annee);
            }

            // Commercial filter
            if ($this->commercial !== '') {
                if ($this->isDirecteur || $this->isAdministratif) {
                    // Directors can see all sales for selected commercial
                    $query->where('ventes.id_commercial', $this->commercial);
                } else {
                    // Non-directors can only see their own sales, even when filtering by commercial
                    $query->where('ventes.id_commercial', $this->commercial)
                        ->where('ventes.id_commercial', $this->userId);
                }
            } else {
                // If no commercial selected and user is not director, show only their sales
                if (! $this->isDirecteur && ! $this->isAdministratif) {
                    $query->where('ventes.id_commercial', $this->userId);
                } else {
                    $query->where('ventes.secteur', session('selectedSecteur'));

                }
            }

            // only sells linked directy to a rendez_vous
            /*$query->join('rendez_vous', function ($join) {
                $join->on('ventes.id_rdv', '=', 'rendez_vous.id')
                    ->on('ventes.id_commercial', '=', 'rendez_vous.id_commercial');
            });*/
            /*$query->join('rendez_vous', function ($join) {
                $join->on('ventes.id_rdv', '=', 'rendez_vous.id')
                    ->where(function ($query) {
                        $query->whereColumn('ventes.id_commercial', '=', 'rendez_vous.id_commercial')
                            ->orWhereColumn('ventes.id_commercial', '=', 'rendez_vous.partage_avec');
                    });
            });*/

            $query->join('clients', function ($join) {
                $join->on('ventes.id_client', '=', 'clients.id');
            });

            if ($this->isCommissionMode) {
                $query->whereNotNull('date_commission');
            }

            $venteData = $this->processVente($query, $allComm);

            if ($this->isCommissionMode) {

                $queryRegul = Vente::with([
                    'client',
                    'user',
                    'rendez_vous',
                    'source',
                    'secteur',
                ]);

                // Commercial filter
                if ($this->commercial !== '') {
                    if ($this->isDirecteur || $this->isAdministratif) {
                        // Directors can see all sales for selected commercial
                        $queryRegul->where('ventes.id_commercial', $this->commercial);
                    } else {
                        // Non-directors can only see their own sales, even when filtering by commercial
                        $queryRegul->where('ventes.id_commercial', $this->commercial)
                            ->where('ventes.id_commercial', $this->userId);
                    }
                } else {
                    // If no commercial selected and user is not director, show only their sales
                    if (! $this->isDirecteur || $this->isAdministratif) {
                        $queryRegul->where('ventes.id_commercial', $this->userId);
                    }
                }

                $queryRegul->join('clients', function ($join) {
                    $join->on('ventes.id_client', '=', 'clients.id');
                });

                $date = Carbon::create($this->anneeParam, $this->moisParam, 1)->startOfDay();
                $premierJour = $date->copy()->startOfDay();

                // Dernier jour du mois
                $dernierJour = $premierJour->copy()->endOfMonth()->endOfDay();

                $date = $date->subMonth()->startOfDay();

                $queryRegul->whereBetween('date_commission', [$premierJour, $dernierJour]);
                $queryRegul->where('ventes.date', '<', $date);

                $regulData = $this->processVente($queryRegul, $allComm);

            } else {
                $regulData = [];
            }

            $directorData['data'] = $this->directorData();
            $total_ca = 0;
            $total_tarif = 0;
            $total_ca_comdir = 0;
            $director = null;
            foreach ($directorData['data'] as $commercial) {
                if (str_contains($commercial['role'], 'directeur')) {
                    $director = $commercial;
                }
                if (isset($venteData['commerciauxData'][$commercial['id']])) {
                    if ($this->moisParam != '') {
                        $total_tarif += $venteData['commerciauxData'][$commercial['id']]['prix_tarif'][$this->moisParam];
                        $total_ca += $venteData['commerciauxData'][$commercial['id']]['prix_vente'][$this->moisParam];
                        if ($venteData['commerciauxData'][$commercial['id']]['prix_vente'][$this->moisParam] > 18000) {
                            $total_ca_comdir += $venteData['commerciauxData'][$commercial['id']]['prix_vente'][$this->moisParam];
                        }
                    } else {
                        foreach ($this->mois_options as $mois => $moisStr) {
                            $total_tarif += $venteData['commerciauxData'][$commercial['id']]['prix_tarif'][$mois];
                            $total_ca += $venteData['commerciauxData'][$commercial['id']]['prix_vente'][$mois];
                            if ($venteData['commerciauxData'][$commercial['id']]['prix_vente'][$mois] > 18000) {
                                $total_ca_comdir += $venteData['commerciauxData'][$commercial['id']]['prix_vente'][$mois];
                            }
                        }
                    }
                } else {
                    dd($commercial, $venteData['commerciauxData']);
                }
            }

            $taux = 1;
            $remise = ($total_tarif != 0 ? (int) (100 * (1 - $total_ca / $total_tarif)) : 0);

            /*if     ($remise >= 30) $taux -= 1.0;
            elseif ($remise >= 29) $taux -= 0.9;
            elseif ($remise >= 28) $taux -= 0.8;
            elseif ($remise >= 27) $taux -= 0.7;
            elseif ($remise >= 25) $taux -= 0.6;
            elseif ($remise >= 23) $taux -= 0.5;
            elseif ($remise >= 21) $taux -= 0.4;
            elseif ($remise >= 19) $taux -= 0.3;
            elseif ($remise >= 17) $taux -= 0.2;
            elseif ($remise >= 15) $taux -= 0.1;*/

            $directorData['director'] = $director;
            $directorData['taux'] = $taux;
            $directorData['com'] = $total_ca_comdir * $taux / 100;

            if ($this->commercial != '') {
                $selectedUser = User::where('id', $this->commercial)->get()->first()->toArray();
                $this->commName = $this->getCommercialNom($selectedUser);
            }

            return view('livewire.ventes-commissions.vente', [
                'ventesData' => $venteData,
                'regulComm' => $regulData,
                'annees' => $this->annees,
                'anneeParam' => $this->anneeParam,
                'moisParam' => $this->moisParam,
                'mois_options' => $this->mois_options,
                'isDirecteur' => $this->isDirecteur,
                'isAdmin' => $this->isAdmin,
                'commercial' => $this->commercial,
                'isCommissionMode' => $this->isCommissionMode,
                'commercials' => $this->commercials,
                'directorData' => $directorData,
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading ventes data: '.$e->getMessage());

            // Return empty paginated result in case of error
            $emptyVentes = Vente::whereRaw('1 = 0')->paginate(15);

            return view('livewire.ventes-commissions.vente', [
                'ventes' => $emptyVentes,
                'totals' => ['prix_tarif' => 0, 'taux_remise' => 0, 'ca_ht' => 0, 'marge' => 0, 'commission_montant' => 0],
                'paymentSummary' => [],
                'cancellationSummary' => [],
                'commercials' => $this->commercials,
                'annees' => $this->annees,
                'anneeParam' => $this->anneeParam,
                'moisParam' => $this->moisParam,
                'mois_options' => $this->mois_options,
                'isDirecteur' => $this->isDirecteur,
                'isAdmin' => $this->isAdmin,
                'commercial' => $this->commercial,
                'error' => $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine(),
            ]);
        }
    }

    public function updateCommentaire($venteId, $comment)
    {
        $vente = Vente::find($venteId);
        if ($vente) {
            $vente->commentaire = $comment;
            $vente->save();
        }
    }

    public function updateCommission($venteId, $commission)
    {
        $vente = Vente::find($venteId);
        if ($vente) {
            $vente->commission = $commission ? $commission : 0;
            $vente->save();
        }
    }

    private function initCommData($id, &$commerciauxData)
    {

        $commerciauxData[$id] = [];
        $commerciauxData[$id]['prix_vente'] = [];
        $commerciauxData[$id]['prix_tarif'] = [];
        $commerciauxData[$id]['taux_commission'] = [];
        $commerciauxData[$id]['taux_commission_rai'] = [];
        for ($i = 1; $i <= 12; $i++) {
            $commerciauxData[$id]['prix_vente'][$i] = 0;
            $commerciauxData[$id]['prix_tarif'][$i] = 0;
            $commerciauxData[$id]['taux_commission'][$i] = 0;
            $commerciauxData[$id]['taux_commission_rai'][$i] = 0;
        }
    }

    private function directorData()
    {
        $users = User::join('secteur_commercials', 'users.id', '=', 'secteur_commercials.id_commercial')
            ->where('secteur_commercials.id_secteur', session('selectedSecteur'))
            ->where('users.active', 1)
            ->where('users.role', '<>', 'admin')
            ->select('users.*')
            ->get()
            ->toArray();

        return $users;
    }

    private function processVente($query, $allComm)
    {

        $ventes = $query->leftJoin('rendez_vous', 'ventes.id_rdv', '=', 'rendez_vous.id')
            ->select('ventes.id', 'ventes.id_rdv', 'ventes.id_commercial', 'ventes.id_commercial_2', 'ventes.id_commercial_3',
                'ventes.ca_ht', 'ventes.ca_total_ht', 'ventes.date', 'ventes.tarif_total_ht', 'ventes.remise', 'ventes.statut',
                'ventes.date_commission', 'ventes.commission', 'ventes.commentaire', 'ventes.tarif_ht', 'ventes.montant', 'ventes.paiement', 'ventes.attente', 'ventes.attente_autre', 'ventes.commande',
                'ventes.id_client',
                'rendez_vous.produits as produits_str')
            ->orderBy('ventes.statut', 'asc')->orderBy('ventes.date', 'asc')->get();
        // dd($query->select('ventes.*', 'clients.id_source as source')->orderBy('ventes.date', 'desc')->orderBy('ventes.id', 'desc')->toSql());
        // $ventesPaginate = $query->select('ventes.*')->orderBy('ventes.date', 'desc')->orderBy('ventes.id', 'desc')->paginate(15);

        $commerciauxData = [];

        foreach ($allComm as $commercial) {
            $this->initCommData($commercial['id'], $commerciauxData);
        }

        $rdv = [];

        $usersData = [];

        foreach ($ventes as $vente) {
            if (isset($rdv[$vente->id_rdv])) {
                if ($vente->id_commercial == $rdv[$vente->id_rdv]->id_commercial_2) {
                    $rdv[$vente->id_rdv]->vente2 = $vente;
                }
                if ($vente->id_commercial == $rdv[$vente->id_rdv]->id_commercial_3) {
                    $rdv[$vente->id_rdv]->vente3 = $vente;
                }
            } else {
                $rdv[$vente->id_rdv] = $vente;
            }
        }

        $ventes = $rdv;

        foreach ($ventes as $vente) {
            /*if(isset($rdv[$vente->id_rdv]))
                continue;
            else{
                $rdv[$vente->id_rdv] = 1;
                $filteredVentes[] = $vente;
            }*/

            if ($vente->id_commercial_2) {
                $vente2 = Vente::where('id_rdv', $vente->id_rdv)
                    ->where('id_commercial', $vente->id_commercial_2)
                    ->first();

                $ventes[$vente->id_rdv]->vente2 = $vente2;
            }

            if ($vente->id_commercial_3) {
                $vente3 = Vente::where('id_rdv', $vente->id_rdv)
                    ->where('id_commercial', $vente->id_commercial_3)
                    ->first();
                $ventes[$vente->id_rdv]->vente3 = $vente3;
            }

            if ($vente->statut != 3 && empty($vente->attente)) {
                if (! isset($commerciauxData[$vente->id_commercial])) {
                    $this->initCommData($vente->id_commercial, $commerciauxData);
                }

                $mois = date('n', strtotime($vente->date));

                $commerciauxData[$vente->id_commercial]['prix_vente'][$mois] += $vente->ca_ht;
                $commerciauxData[$vente->id_commercial]['prix_tarif'][$mois] += $vente->tarif_ht;

                if ($vente->id_commercial_2) {
                    if (! isset($commerciauxData[$vente->id_commercial_2])) {
                        $this->initCommData($vente->id_commercial_2, $commerciauxData);
                    }

                    $vente2 = $ventes[$vente->id_rdv]->vente2;
                    $commerciauxData[$vente2->id_commercial]['prix_vente'][$mois] += $vente2->ca_ht;
                    $commerciauxData[$vente2->id_commercial]['prix_tarif'][$mois] += $vente2->tarif_ht;

                }

                if ($vente->id_commercial_3) {
                    if (! isset($commerciauxData[$vente->id_commercial_3])) {
                        $this->initCommData($vente->id_commercial_3, $commerciauxData);
                    }

                    $vente3 = $ventes[$vente->id_rdv]->vente3;
                    $commerciauxData[$vente3->id_commercial]['prix_vente'][$mois] += $vente3->ca_ht;
                    $commerciauxData[$vente3->id_commercial]['prix_tarif'][$mois] += $vente3->tarif_ht;

                }
            }
        }

        // $ventes = $filteredVentes;

        foreach ($allComm as $k => $c) {
            for ($i = 1; $i <= 12; $i++) {
                // Calcul taux commission mensuel de base

                [$taux, $tauxRAI] = CommissionService::calculTauxCommissionMensuel($c['contrat'], $commerciauxData[$c['id']]['prix_vente'][$i], $commerciauxData[$c['id']]['prix_tarif'][$i]);

                $commerciauxData[$c['id']]['taux_commission'][$i] = $taux;
                $commerciauxData[$c['id']]['taux_commission_rai'][$i] = $tauxRAI;
            }
        }

        $usersData = User::all()->keyBy('id');

        /**foreach ($ventes as $vente) {

            if($vente->id_commercial){
                if(!isset($usersData[$vente->id_commercial]))
                    $usersData[$vente->id_commercial] = \App\Models\User::find($vente->id_commercial);
            }

            if($vente->id_commercial_2){
                if(!isset($usersData[$vente->id_commercial_2]))
                    $usersData[$vente->id_commercial_2] = \App\Models\User::find($vente->id_commercial_2);
            }

            if($vente->id_commercial_3){
                if(!isset($usersData[$vente->id_commercial_3]))
                    $usersData[$vente->id_commercial_3] = \App\Models\User::find($vente->id_commercial_3);
            }
        }*/

        // Check if we need to recalculate summaries
        $ca_ht = 0;
        $prix_tarif = 0;
        $ca_ht_attente = 0;
        $prix_tarif_attente = 0;
        $marge = 0;

        foreach ($ventes as $vente) {
            if ($vente->statut != 3 && empty($vente->attente)) {
                if ($this->commercial != '') {
                    $prix_tarif += $vente->tarif_ht;
                    $ca_ht += $vente->ca_ht;
                    $marge += ($vente->montant);
                } else {
                    $prix_tarif += $vente->tarif_total_ht;
                    $ca_ht += $vente->ca_total_ht;
                    $marge += ($vente->ca_total_ht - ($vente->tarif_total_ht * 0.85));
                }
            } else {
                if (! empty($vente->attente) && $vente->statut != 3) {
                    $prix_tarif_attente += $vente->tarif_ht;
                    $ca_ht_attente += $vente->ca_ht;
                }
            }
        }

        $taux = CommissionService::calculRemise($ca_ht, $prix_tarif);
        $taux_total = CommissionService::calculRemise($ca_ht + $ca_ht_attente, $prix_tarif + $prix_tarif_attente);
        $prime = ($this->isCommissionMode ? $this->calculatePrime($usersData[$this->commercial], $ca_ht, $marge) : 0);
        $totals = [
            'prix_tarif' => $prix_tarif,
            'taux_remise' => count($ventes) > 0 ? $taux : 0,
            'taux_remise_total' => count($ventes) > 0 ? $taux_total : 0,
            'ca_ht' => $ca_ht,
            'marge' => $marge,
            'commission_montant' => $this->calculateTotalCommission($ventes, $commerciauxData, $usersData),
            'prime' => $prime,
        ];

        // Calculate payment summary for the filtered results
        $paymentSummary = $this->calculatePaymentSummary($ventes);

        // Calculate cancellation summary for the filtered results
        $cancellationSummary = $this->calculateCancellationSummary($ventes);

        // calculate ALL rest
        $flatPriorities = ProjectService::listProductsFlat();
        foreach ($ventes as $vente) {
            if ($vente->id_commercial) {
                $commercial = $usersData[$vente->id_commercial];

                $this->calculateFinalTauxAndComm($vente, $commercial, $commerciauxData, $usersData);
                $vente->vrp1 = $this->generateVRP($vente, $commercial);
            }
            if ($vente->id_commercial_2) {
                $commercial = $usersData[$vente->id_commercial_2];
                $this->calculateFinalTauxAndComm($vente->vente2, $commercial, $commerciauxData, $usersData);
                $vente->vrp2 = $this->generateVRP($vente->vente2, $commercial);
                $vente->vente2_montant_commission = $vente->vente2->montant_commission;
                $vente->vente2_taux = $vente->vente2->taux;
            }
            if ($vente->id_commercial_3) {
                $commercial = $usersData[$vente->id_commercial_3];
                $this->calculateFinalTauxAndComm($vente->vente3, $commercial, $commerciauxData, $usersData);
                $vente->vrp3 = $this->generateVRP($vente->vente3, $commercial);
                $vente->vente3_montant_commission = $vente->vente3->montant_commission;
                $vente->vente3_taux = $vente->vente3->taux;
            }

            // Get products from rendez_vous table
            $produitsStr = $vente->produits_str ?? '';

            $vente->isRAI = CommissionService::isVenteRAI($produitsStr);
            $vente->backgroundColor = '';
            if ($vente->statut == 2 && empty($vente->attente)) {
                // Validated sale
                $vente->backgroundColor = 'background-color: rgba(0, 0, 255, 0.3);';
                $produitsStr = $vente->produits_str ?? '';
                if ($vente->isRAI && str_contains($produitsStr, ';') > 0 /* Si plusieurs produits */) {
                    $vente->backgroundColor = 'background-color: rgba(240, 198, 46, 0.4);';
                }
            } elseif ($vente->statut == 2) {
                // Awaiting validation
                $vente->backgroundColor = 'background-color: rgba(0, 255, 0, 0.4);';

            } elseif ($vente->statut == 3) {
                // Cancelled sale
                $vente->backgroundColor = 'background-color: rgba(255, 0, 0, 0.2);';
            }

            if ($vente->date_commission) {
                $vente->commissionneClass = 'bg-green-100 text-green-800';
                $vente->commissionneText = \Carbon\Carbon::parse($vente->date_commission)->format('m/Y');
            } else {
                $vente->commissionneClass = 'bg-red-100 text-red-800';
                $vente->commissionneText = 'Non';
            }

            // Apply legacy logic similar to the original
            $productNames = [];

            if (! empty($produitsStr)) {
                $productKeys = explode(';', $produitsStr);
                foreach ($productKeys as $key) {
                    if (isset($flatPriorities[$key])) {
                        $productNames[] = $flatPriorities[$key];
                    }
                }
            }

            $finalProducts = array_filter($productNames);

            if (count($finalProducts) > 0) {
                $vente->final_products = implode('<br>', $finalProducts);
            } else {
                $vente->final_products = '<p>N/A</p>';
            }

            $vente->source_name = SourceService::getSourceName($vente?->client?->id_source) ?? 'N/A';
        }

        return [
            'ventes' => $ventes,
            'usersData' => $usersData,
            'totals' => $totals,
            'paymentSummary' => $paymentSummary,
            'cancellationSummary' => $cancellationSummary,
            'commerciauxData' => $commerciauxData,
        ];
    }

    private function generateVRP($vente, $commercial)
    {
        $result = '';
        $source = $vente->source ?? '';
        $montant = (CommissionService::isSourcePassive($source)) ? (0.7 * $vente->ca_ht) : $vente->ca_ht;
        $result = $commercial->display_name;
        $result .= ' (';
        $result .= number_format($montant, 0, ',', ' ');
        $result .= ' €)';

        return $result;
    }

    private function calculateFinalTauxAndComm(&$vente, $commercial, &$commerciauxData, $usersData)
    {
        $contrat = $commercial['contrat'];

        $taux = $this->calculateTaux($vente, $commerciauxData, $usersData);

        $montant = CommissionService::calculMontantCommission(
            $contrat,
            $vente->ca_ht, // Convert from cents
            $vente->ca_total_ht, // Convert from cents
            $vente->source ?? '',
            $taux,
            $vente->montant
        );

        if (CommissionService::shouldMaskCommission(auth()->user(), $vente->id_commercial, 'taux')) {
            $vente->montant_commission = '--';
            $vente->taux = '--';
        } else {
            $vente->montant_commission = number_format($montant, 0, ',', ' ').' €';
            $vente->taux = number_format($taux, 2, ',', ' ').'%';
        }
    }

    private function calculateTaux($vente, $commerciauxData, $usersData)
    {

        $mois = date('n', strtotime($vente->date));
        $produitsStr = $vente->produits_str ?? '';
        $tauxRAI = $commerciauxData[$vente->id_commercial]['taux_commission_rai'][$mois];

        $commercial = $usersData[$vente->id_commercial];

        if ($commercial) {
            $contrat = $commercial['contrat'];
        }

        [$tauxStandard,$t] = CommissionService::calculTauxCommissionMensuel($contrat, $vente->ca_ht, $vente->tarif_ht);  // $commerciauxData[$vente->id_commercial]["taux_commission"][$mois];

        $taux = CommissionService::isVenteRAI($produitsStr) ? $tauxRAI : $tauxStandard;

        return $taux;
    }

    private function calculateTotalCommission($ventes, $commerciauxData, $usersData)
    {
        $totalCommission = 0;
        $contrat = '';
        foreach ($ventes as $vente) {
            if ($vente->statut != 3 && empty($vente->attente) && $vente->id_commercial != '0') {
                // Calculate commission.blade.php for first commercial
                $commercial = $usersData[$vente->id_commercial];

                if ($commercial) {
                    $contrat = $commercial['contrat'];

                    $taux = $this->calculateTaux($vente, $commerciauxData, $usersData);

                    $montant = CommissionService::calculMontantCommission(
                        $contrat,
                        $vente->ca_ht,
                        $vente->ca_total_ht,
                        $vente->source ?? '',
                        $taux,
                        $vente->montant
                    );

                    if (! CommissionService::shouldMaskCommission(auth()->user(), $vente->id_commercial, 'montant')) {
                        $totalCommission += $montant;
                    }
                }
            }
        }

        return $totalCommission;
    }

    private function calculatePaymentSummary($ventes)
    {
        $paymentSummary = [];
        $totalCA = 0;

        // Group ventes by payment type and calculate totals
        foreach ($ventes as $vente) {
            Log::debug('VENTE', [
                'id' => $vente->id ?? null,
                'statut' => $vente->statut,
                'paiement' => $vente->paiement,
                'ca_total_ht' => $vente->ca_total_ht,
            ]);
            if ($vente->statut != 3 /* && empty($vente->attente) */) {
                $paymentType = $vente->paiement ?: 'Non défini';
                $caHt = $vente->ca_total_ht;

                if (! isset($paymentSummary[$paymentType])) {
                    $paymentSummary[$paymentType] = [
                        'ca' => 0,
                        'count' => 0,
                    ];
                }

                $paymentSummary[$paymentType]['ca'] += $caHt;
                $paymentSummary[$paymentType]['count']++;
                $totalCA += $caHt;
            }
        }

        // Calculate ratios
        foreach ($paymentSummary as $paymentType => &$data) {
            $data['ratio'] = $totalCA > 0 ? ($data['ca'] / $totalCA) * 100 : 0;
        }

        // Sort by CA descending
        uasort($paymentSummary, function ($a, $b) {
            return $b['ca'] <=> $a['ca'];
        });

        return ['total' => $totalCA, 'summary' => $paymentSummary];
    }

    private function calculateCancellationSummary($ventes)
    {
        $cancellationSummary = [
            'Ventes' => ['count' => 0, 'ca' => 0],
            'Annulations' => ['count' => 0, 'ca' => 0],
        ];

        $totalCA = 0;
        $totalCount = 0;

        // Count sales and cancellations
        foreach ($ventes as $vente) {
            $caHt = $vente->ca_total_ht;
            if ($vente->statut == 3) {
                // Cancelled sale
                if ($vente->type_vente == 'Commune') {
                    $cancellationSummary['Annulations']['ca'] += $caHt / 2;
                    $totalCA += $caHt / 2;
                } else {
                    $cancellationSummary['Annulations']['ca'] += $caHt;
                    $totalCA += $caHt;
                }
                $totalCount++;
                $cancellationSummary['Annulations']['count']++;
            } else {
                $cancellationSummary['Ventes']['count']++;
                $totalCount++;
                $cancellationSummary['Ventes']['ca'] += $caHt;
                $totalCA += $caHt;
            }
        }

        // Calculate ratios
        foreach ($cancellationSummary as $type => &$data) {
            $data['ratio'] = $totalCount > 0 ? ($data['count'] / $totalCount) * 100 : 0;
        }

        return ['total' => $totalCA, 'totalCount' => $totalCount, 'summary' => $cancellationSummary];
    }

    private function calculatePrime($commercial, $ca_ht, $marge)
    {
        if ($commercial['contrat'] == 'commercial-v1') {
            return CommissionService::calculPrimePerformanceCommercial($ca_ht);
        }

        if (str_contains($commercial->contrat, 'directeur')) {
            $caAgence = $this->calculateCAAgence();
            $commAgence = CommissionService::calculCommissionAgence($caAgence, $ca_ht - $marge);
            $commDir = (($ca_ht - $marge) > 20000 ? ($ca_ht - $marge) * 0.02 : 0);

            return $commAgence + $commDir;
        }

    }

    private function calculateCAAgence()
    {
        $query = Vente::with([
            'client',
            'user',
            'rendez_vous',
            'source',
            'secteur',
        ]);

        // Commercial filter
        $query->whereNot('ventes.id_commercial', $this->commercial);
        $query->where('ventes.secteur', session('selectedSecteur'));

        // Exclure les ventes annulées et en attente
        $query->where('ventes.statut', '<>', 3);
        $query->where(function ($q) {
            $q->whereNull('ventes.attente')
                ->orWhere('ventes.attente', '');
        });

        $date = Carbon::create($this->anneeParam, $this->moisParam, 1)->startOfDay();

        // Dernier jour du mois

        $date = $date->subMonth()->startOfDay();
        $endDate = $date->subMonth()->endOfMonth();

        // $query->whereBetween('ventes.date', '<', $date);
        $query->whereBetween('ventes.date', [$date, $endDate]);

        $ventes = $query->get();

        return (float) $query->sum('ventes.ca_ht');
    }
}
