<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Client;
use App\Models\User;
use App\Models\Comment;
use App\Models\Vente;
use App\Services\CommerciauxService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class ListingClient extends Component
{
    use WithPagination;

    public $type = '';
    public $vendeur = '';
    public $date_debut;
    public $date_fin;
    public $search = '';
    public $commune;
    public $showModal = false;
    public $commentaires = [];
    public $clientIdModal;
    public $sortColumn = 'nom';
    public $sortDirection = 'asc';
    public $newComment = '';

    public $showCommentModal = false;

    public $clientsTypes = [];

    public $clientsTypesPrecontact = [0, 6, 7, 8];

    public $vendeurs = [];
    public $userId;
    public $isDirecteur = false;
    public $isAdmin = false;
    public $isAdministratif = false;
    
    public $lastPage;
    public $goToPage = 1;
    public $pageErrorMessage = '';

    // Configuration de la pagination pour cette page uniquement
    public function paginationView()
    {
        return 'livewire.pagination-listing-client';
    }

    //Update la page apres les filtres pour la pagination
    public function updatingType() { $this->resetPage(); }
    public function updatingVendeur() { $this->resetPage(); }
    public function updatingDateDebut() { $this->resetPage(); }
    public function updatingDateFin() { $this->resetPage(); }
    public function updatingSearch() { $this->resetPage(); }
    public function updatingCommune() { $this->resetPage(); }
    public function updatedPage($page)
    {
        $this->goToPage = $page;  // Sync input avec page courante
    }

    public function mount()
    {
        $this->clientsTypes = config('client_types');
        $user = Auth::user();
        $this->userId = $user->id;
        $this->isDirecteur = $user->hasRole('directeur');
        $this->isAdmin = $user->hasRole('admin');
        $this->isAdministratif = $user->hasRole('administratif');

        $this->vendeurs = ($this->isDirecteur || $this->isAdmin) ? CommerciauxService::getEntireCommerciauxList('date_entree','asc') : User::where('id', $this->userId)->get();

        $params = request()->query();

        if (isset($params['vendeur']) && is_numeric($params['vendeur'])) {
            $this->vendeur = $params['vendeur'];
        } else {
			if($this->isAdmin){
				$this->vendeur = "";
			}
            else{
				$this->vendeur = $this->userId;
			}
        }

        if (isset($params['date_debut'])) {
            $this->date_debut = $params['date_debut'];
        }

        if (isset($params['date_fin'])) {
            $this->date_fin = $params['date_fin'];
        }

        if (array_key_exists('type', $params)) {

            $this->type = $params['type'] ?: '';
        }
    }

    //Formater le numéro de téléphone
    public function formatPhone($number)
    {
        return preg_replace("/(\d{2})(?=\d)/", "$1 ", $number);
    }

    public function voirCommentaires($clientId)
    {
        $this->clientIdModal = $clientId;
        $this->commentaires = Comment::where('client_id', $clientId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get(['date', 'commentaire'])
            ->toArray();

        $this->showModal = true;
    }
    public function addComment($clientId)
    {
        $this->clientIdModal = $clientId;
        $this->newComment = '';
        $this->showCommentModal = true;
    }

    public function saveComment()
    {
        $this->validate([
            'newComment' => 'required|string|max:1000',
        ]);

        Comment::create([
            'client_id' => $this->clientIdModal,
            'user_id' => $this->userId,
            'date' => now(),
            'commentaire' => $this->newComment,
        ]);

        $this->refreshContact($this->clientIdModal);

        $this->showCommentModal = false;
        
        $this->resetPage();
        //$this->voirCommentaires($this->clientIdModal);
        session()->flash('success', 'Commentaire ajouté !');
    }
    public function dernierCommentaire()
    {
        return $this->hasOne(Comment::class)->latest('id');
    }

    //Obtenir nom du commercial pr user
    public function getCommercialNom($user)
    {
        return $user['prenom'] . ' ' . $user['nom'];
    }

    //Obtenir nom du commercial par Id
    public function getCommercialNomById($id)
    {
        if (!$id) return ''; // pas d'id

        $user = collect($this->vendeurs)->firstWhere('id', $id);

        if ($user) {
            return $this->getCommercialNom($user);
        }

        $user = User::find($id);

        return $user ? $user->prenom . ' ' . $user->nom : '';
    }

    //Obtenir le type
    public function getTypeClass($types){
        return match((int) $types) {
            0 => 'type-precontract',
            1 => 'type-prospect',
            6 => 'type-refus',
            7 => 'type-absent',
            8 => 'type-relance',
            9 => 'type-orphelin',
            default => 'type-standard',
        };
    }

    public function ClientsTypePrecontact(){
        return [0,6,7,8];
    }

    // Sorting des colonnes
    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    //symbole de tri des colonnes
    public function sortiIcon($column)
    {
        if ($this->sortColumn === $column) {
            return $this->sortDirection === 'asc'
                ? '<span class="fa fa-sort-up"></span>'
                : '<span class="fa fa-sort-down"></span>';
        }
        return '<span class="fa fa-sort text-muted"></span>';
    }

    // Changer le type du client
    public function updateClientType($clientId, $type){
        Client::where('id', $clientId)->update(['type'=> $type]);
    }

    // Rafraichir le contact
    public function refreshContact($clientId){
        Client::where('id',$clientId)->update([
            'date_dernier_contact' => now(),
        ]);
    }

    //filtre actif
    public function filtrer()
    {
        // Reset pagination to first page when filters are applied via form submission
        $this->resetPage();
    }

    //Export CSV
    public function exportCsv()
    {
        
        $query = $this->getClientQuery();

        $clients = $query->orderBy($this->sortColumn, $this->sortDirection)
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="clients.csv"',
        ];

        return Response::stream(function () use ($clients) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Type', 'Nom', 'Prénom', 'Adresse', 'Code Postal', 'Ville', 'Téléphone 1', 'Téléphone 2','signe', 'Débrief', 'Projet','Paiement', 'Rentré par', 'Rentré le'
            ]);

            foreach ($clients as $client) {
                fputcsv($handle, [
                    $this->clientsTypes[$client->type] ?? '',
                    $client->nom,
                    $client->prenom,
                    $client->adresse,
                    $client->cp,
                    $client->ville,
                    $client->telephone1,
                    $client->telephone2,
                    $client->signe,
                    $client->debrief,
                    $client->projet,
                    $client->dernier_paiement,
                    $this->getCommercialNomById($client->rentre_par),
                    $client->rentre_le ? \Carbon\Carbon::parse($client->rentre_le)->format('d/m/Y') : '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function getClientQuery(){
        $query = Client::query();
        
        $typesFree = [0, 6, 7, 8]; 

        $vendeurId = null;
        if ($this->vendeur !== '') {
            $vendeurId = $this->vendeur;
        } 

        // Filtre par type
        if ($this->type !== '') {
            if($vendeurId !== null || $this->type == 2){
                if(!$this->isAdmin)
                    $vendeurId = $this->userId;
                $query->where('type', $this->type);
                
                if($vendeurId !== null){
					$query->where(function ($q) use($vendeurId) {
						$q->where('rentre_par', $vendeurId)
						->orWhere('partage_avec', $vendeurId);
					});
				}
            }else{
                if($this->type == 9){
                    $query->where('type', '2');
                    /*->where(function ($q) {
						$q->where(function ($subQ) {
							$subQ->whereNull('rentre_par')
								 ->whereNull('partage_avec');
						})
						->orWhereNotExists(function ($exQ) {
							$exQ->select(DB::raw(1))
								->from('ventes')
								->whereColumn('ventes.id_client', 'clients.id')
								->whereExists(function ($noActiveQ) {
									$noActiveQ->select(DB::raw(1))
										->from('users as u')
										->whereColumn('u.id', 'ventes.id_commercial')
										->where('u.active', 1);
								});
						});
					});*/
                }elseif($this->type == 1){
                    $query->where('type', '1')
                    ->where(function ($q) use($vendeurId) {
                        $q->where('rentre_par', $vendeurId)
                        ->orWhere('partage_avec', $vendeurId)
                        ->orWhereRaw('
                            (
                                SELECT MAX(rdv.date) 
                                FROM rendez_vous rdv 
                                WHERE rdv.id_client = clients.id 
                            ) <= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                        ');
                    });
                }else{
                    $query->where('type', $this->type);
                
                    if(!in_array($this->type, $typesFree)){
                        $query->where(function ($q) use($vendeurId) {
                            $q->where('rentre_par', $vendeurId)
                            ->orWhere('partage_avec', $vendeurId);
                        });
                    }
                }
            }
        }else{
            if($vendeurId !== null){
                $query->where(function ($q) use($vendeurId) {
                    $q->where('rentre_par', $vendeurId)
                    ->orWhere('partage_avec', $vendeurId);
                });
            }else{
                // Logique OR : typesFree (TOUS) OU (autres types ET filtre vendeur)
                $query->where(function ($q) use ($typesFree, $vendeurId) {
                    // TOUS les deux cas OR sont dans cette closure
                    $q->where(function ($subQ) use ($typesFree, $vendeurId) {
                        // Branche 2: NON-free ET filtre vendeur
                        $subQ->whereIn('type', $typesFree);
                    })
                    ->orWhere(function ($subQ) use ($typesFree, $vendeurId) {
                        // Branche 2: NON-free ET filtre vendeur
                        $subQ->whereNotIn('type', $typesFree)
                        ->where(function ($subSubQ) use ($vendeurId) {
                            $subSubQ->where('rentre_par', $vendeurId)
                            ->orWhere('partage_avec', $vendeurId);
                        });
                    })
                    ->orWhere(function ($subQ) {
                        // NOUVEAU : Branche 3: ORPHELINS (type=2, commerciaux désactivés)
                        $subQ->where('type', 2)
                        ->whereNotExists(function ($exQ) {
                            $exQ->select(DB::raw(1))
                            ->from('ventes')
                            ->whereColumn('ventes.id_client', 'clients.id')
                            ->whereExists(function ($noActiveQ) {
                                $noActiveQ->select(DB::raw(1))
                                ->from('users as u')
                                ->whereColumn('u.id', 'ventes.id_commercial')
                                ->where('u.active', 1);
                            });
                        })
                        ->whereNotExists(function ($exQ) {
                            $exQ->select(DB::raw(1))
                                ->from('users')
                                ->whereColumn('users.id', 'clients.rentre_par')
                                ->where('users.active', 1);
                        })
                        ->whereNotExists(function ($exQ) {
                            $exQ->select(DB::raw(1))
                                ->from('users')
                                ->whereColumn('users.id', 'clients.partage_avec')
                                ->where('users.active', 1);
                        });                   
                    });
                    if($vendeurId !== null){
                    
                        $q->orWhere(function($subQ) use ($vendeurId){
                            $subQ->where('type', '1')
                            ->where(function ($q) use($vendeurId) {
                                $q->where('rentre_par', $vendeurId)
                                ->orWhere('partage_avec', $vendeurId)
                                ->orWhereRaw('
                                    (
                                        SELECT MAX(rdv.date) 
                                        FROM rendez_vous rdv 
                                        WHERE rdv.id_client = clients.id 
                                    ) <= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                                ');
                            });
                        });
                    }else{
                        $q->orWhere(function ($subQ) {
                            $subQ->where('type', 1);
                        });
                    }
                });
            }
        }

        // Filtres de dates
        if ($this->date_debut) {
            $query->whereDate('rentre_le', '>=', $this->date_debut);
        }

        if ($this->date_fin) {
            $query->whereDate('rentre_le', '<=', $this->date_fin);
        }

        if ($this->date_debut && $this->date_fin && $this->date_debut > $this->date_fin) {
            session()->flash('error', 'La date de fin ne peut pas être antérieure à la date de début.');
            return;
        }

        // Filtre de recherche
        if ($this->search) {
            $query->where(function ($q) {
                
                $searchTerm = '%' . $this->search . '%';
                $q->where('nom', 'like', $searchTerm)
                    ->orWhere('prenom', 'like', $searchTerm)
                    ->orWhere('telephone1', 'like', $searchTerm)
                    ->orWhere('telephone2', 'like', $searchTerm)
                    ->orWhere('adresse', 'like', $searchTerm)
                    ->orWhere('mail', 'like', $searchTerm)
                    ->orWhere('cp', 'like', $searchTerm)
                    ->orWhere('ville','like', $searchTerm)
                    ->orWhereRaw("CONCAT(nom, ' ', prenom) like ?", [$searchTerm])
                    ->orWhereRaw("CONCAT(prenom, ' ', nom) like ?", [$searchTerm]);
            });
			
			if(!$this->isAdmin && !$this->isAdministratif){
			
				$query->where('clients.secteur', session('selectedSecteur'));
			}
			
        }else{
			
			$query->where('clients.secteur', session('selectedSecteur'));
		}

        // Filtre par commune
        if ($this->commune) {
            $query->where('commune_id', $this->commune['id']);
        }

        $query->selectRaw('
            *, COALESCE(
                (SELECT MAX(t2.datetime) 
                FROM tracking t2 
                WHERE t2.id_client = clients.id AND t2.secteur = clients.secteur)
            ) as rentre_le2,
            COALESCE(
                (SELECT MAX(t2.datetime) 
                FROM tracking t2 
                WHERE t2.id_client = clients.id AND t2.secteur = clients.secteur),
                rentre_le
            ) as rentre_lesort
        ');

        $query->addSelect([
            'dernier_paiement' => Vente::select('paiement')
                ->whereColumn('ventes.id_client', 'clients.id')
                ->latest('date')
                ->limit(1),
            'duree_restante' => Vente::selectRaw("
                CASE
                    WHEN fin_financement IS NULL THEN NULL
                    WHEN fin_financement <= CURDATE() THEN 0
                    ELSE TIMESTAMPDIFF(DAY, CURDATE(), fin_financement)
                END
            ")
            ->whereColumn('ventes.id_client', 'clients.id')
            ->latest('date')
            ->limit(1),
            'dernier_commentaire' => Comment::select( 'commentaire' )  // Assure-toi du nom du modèle
                ->whereColumn('comments.client_id', 'clients.id')  // Nom table = 'comments' ?
                ->orderByRaw('id DESC')  // date VARCHAR → conversion
                ->limit(1),
            'date_dernier_commentaire' => Comment::select( 'date' )  // Assure-toi du nom du modèle
                ->whereColumn('comments.client_id', 'clients.id')  // Nom table = 'comments' ?
                ->orderByRaw('id DESC')  // date VARCHAR → conversion
                ->limit(1)
        ]);
        $query->addSelect(DB::raw("
			CASE
				WHEN (
					clients.rentre_par IS NULL
					AND clients.partage_avec IS NULL
				)
				OR (NOT EXISTS (
					SELECT 1
					FROM ventes v
					JOIN users u ON u.id = v.id_commercial
					WHERE v.id_client = clients.id
					AND u.active = 1
				) AND clients.type = 2)
				THEN 1
				ELSE 0
			END AS is_orphelin
		"));

        $query->when($this->type == 2, function ($q) {    
            $q->havingRaw('is_orphelin = 0');
        });

         $query->when($this->type == 9, function ($q) {    
            $q->havingRaw('is_orphelin = 1');
        });
        


        //$query->ddSql();

        return $query;
    }

    public function render()
    {
        $query = $this->getClientQuery();
		
        $clients = $query
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(100);

        //$query->ddSql();
        //dd($clients->total());
        //dd($clients);

        foreach($clients as &$client){
            if($client->is_orphelin && $client->type == 2)
                $client->type = 9;
			
            $client->rentre_par_str = $client->rentre_par ? CommerciauxService::getCommercialById($client->rentre_par)->display_name : '';
            $client->partage_str = $client->partage_avec ? CommerciauxService::getCommercialById($client->partage_avec)->display_name : 'N/A';
        }


        $this->lastPage = $clients->lastPage();

        return view('livewire.listing-client', [
            'clients' => $clients,
            'nbclient' => $clients->total(),
            'clientsTypes' => $this->clientsTypes,
            'vendeurs' => $this->vendeurs,
            'userId' => $this->userId,
            'isDirecteur' => $this->isDirecteur,
            'isAdmin' => $this->isAdmin,
        ]);
    }

    public function jumpToPage()
    {
        $page = (int) $this->goToPage;
        if ($page >= 1 && $page <= $this->lastPage) {
            $this->pageErrorMessage = "";
            $this->setPage($page);  // Méthode native Livewire
        } else {
            $this->pageErrorMessage = "Page invalide (1-$this->lastPage)";
        }
    }
}
