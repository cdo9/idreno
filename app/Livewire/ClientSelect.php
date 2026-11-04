<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Modelable;

class ClientSelect extends Component
{
    public $query = '';

    #[Modelable]
    public $selectedClientId = null;

    public $selectedClientText = '';
    public $showDropdown = false;
    public $clients = [];
    public $isReadonly = false;
	public $isAdmin = false;

    public $requiredRelation = null; // 'rendezVous', 'argu' ou null
    
    // Properties for parent component communication
    public $clientFieldName = 'selectedClientId';
    public $placeholder = 'Rechercher un client...';
    
    protected $listeners = ['setClient', 'clearClient'];
	

    public function mount($selectedClientId = null, $clientText = '', $readonly = false, $fieldName = 'selectedClientId', $requiredRelation = null)
    {
        $this->selectedClientId = $selectedClientId;
        $this->selectedClientText = $clientText;
        $this->query = $clientText;
        $this->isReadonly = $readonly;
        $this->clientFieldName = $fieldName;
        $this->requiredRelation = $requiredRelation;
        
        if ($selectedClientId) {
            $this->showDropdown = false;
        }
		$user = Auth::user();
		
		$this->isAdmin = $user->hasRole('admin') || $user->hasRole('administratif');
    }

    public function updatedQuery()
    {
        if ($this->isReadonly) {
            return;
        }

        $this->selectedClientId = null;
        $this->selectedClientText = '';
        
        if (strlen($this->query) >= 2) {
            $this->searchClients();
            $this->showDropdown = true;
        } else {
            $this->clients = [];
            $this->showDropdown = false;
        }
    }

    public function searchClients()
    {
        try {
            $query = $this->query;
            $queryTerms = explode(' ', $query);
            
            // Logique exacte du ClientController::autocomplete
            $clientsQuery = Client::query();
            
            $currentUserId = Auth::id();
            
            if (!$currentUserId) {
                Log::warning('Unauthorized client search attempt');
                $this->clients = [];
                return;
            }

            // Filtrer par commercial connecté comme dans le controller
            //$clientsQuery->where('rentre_par', $currentUserId);

			if(!$this->isAdmin)
				$clientsQuery->where('secteur', session('selectedSecteur'));
			
            $clientsQuery->whereIn('type', [2,1]);

            switch($this->requiredRelation){
                case "rdv":
                    $clientsQuery->whereExists(function ($subQuery) {
                        $subQuery->selectRaw('1')
                            ->from('projets')
                            ->whereColumn('projets.id_client', 'clients.id');
                    });
                    break;
                case "argu":
                    $clientsQuery->whereExists(function ($subQuery) {
                        $subQuery->selectRaw('1')
                            ->from('rendez_vous')
                            ->whereColumn('rendez_vous.id_client', 'clients.id');
                    });
                    break;
            }
            

            // Logique de recherche complexe du controller
            $clientsQuery->where(function($q) use ($queryTerms, $query) {
                if (count($queryTerms) === 1) {
                    $term = $queryTerms[0];
                    $q->where('nom', 'LIKE', "%{$term}%")
                      ->orWhere('prenom', 'LIKE', "%{$term}%")
                      ->orWhere('telephone1', 'LIKE', "%{$query}%")
                      ->orWhere('telephone2', 'LIKE', "%{$query}%")
                      ->orWhere('mail', 'LIKE', "%{$query}%");
                } else {
                    // Multiple terms - try different combinations
                    $q->where(function($subq) use ($queryTerms) {
                        $subq->where('nom', 'LIKE', "%{$queryTerms[0]}%")
                             ->where('prenom', 'LIKE', "%{$queryTerms[1]}%");
                    })
                    ->orWhere(function($subq) use ($queryTerms) {
                        $subq->where('prenom', 'LIKE', "%{$queryTerms[0]}%")
                             ->where('nom', 'LIKE', "%{$queryTerms[1]}%");
                    })
                    ->orWhere('nom', 'LIKE', "%{$query}%")
                    ->orWhere('prenom', 'LIKE', "%{$query}%")
                    ->orWhere('telephone1', 'LIKE', "%{$query}%")
                    ->orWhere('telephone2', 'LIKE', "%{$query}%")
                    ->orWhere('mail', 'LIKE', "%{$query}%");
                }
            });

            // Conditions supplémentaires du controller
            $this->clients = $clientsQuery
                ->whereNotNull('gps_lat')
                ->whereNotNull('gps_lng')
                ->orderBy('rentre_le', 'desc')
                ->limit(10) // Limiter pour les performances
                ->get()
                ->map(function($client) {
                    return [
                        'id' => $client->id,
                        'text' => $client->nom . ' ' . $client->prenom,
                        'detail' => $client->adresse . ' - ' . $client->ville,
                        'nom' => $client->nom,
                        'prenom' => $client->prenom,
                        'adresse' => $client->adresse,
                        'ville' => $client->ville,
                        'telephone1' => $client->telephone1,
                        'telephone2' => $client->telephone2,
                        'mail' => $client->mail,
                        'full_text' => $client->nom . ' ' . $client->prenom . ' - ' . $client->adresse . ' - ' . $client->ville,
                        'type_habitation' => $client->type_habitation
                    ];
                })
                ->toArray();

            Log::info('Client search results', [
                'query' => $query,
                'user_id' => $currentUserId,
                'results_count' => count($this->clients)
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching clients: ' . $e->getMessage());
            $this->clients = [];
        }
    }

    public function selectClient($clientId)
    {
        try {
            $client = collect($this->clients)->firstWhere('id', $clientId);
            
            if ($client) {
                $this->selectedClientId = $clientId;
                $this->selectedClientText = $client['text'];
                $this->query = $client['text'];
                $this->showDropdown = false;
                
                // Notify parent component
                $this->dispatch(
                    'client-selected',
                    clientId: $clientId,
                    clientData: $client,
                    fieldName: $this->clientFieldName
                );
                
                Log::info('Client selected', ['client_id' => $clientId, 'client_name' => $client['text']]);
            }
        } catch (\Exception $e) {
            Log::error('Error selecting client: ' . $e->getMessage());
        }
    }

    public function clearClient()
    {
        $this->selectedClientId = null;
        $this->selectedClientText = '';
        $this->query = '';
        $this->clients = [];
        $this->showDropdown = false;
        
        // Notify parent component
        $this->dispatch(
            'client-selected',
            clientId: null,
            clientData: null,
            fieldName: $this->clientFieldName
        );
    }

    public function setClient($clientId, $clientText = null)
    {
        $this->selectedClientId = $clientId;
        
        if ($clientText) {
            $this->selectedClientText = $clientText;
            $this->query = $clientText;
        } else {
            // Load client data if only ID provided
            $client = Client::find($clientId);
            if ($client) {
                $this->selectedClientText = $client->nom . ' ' . $client->prenom;
                $this->query = $this->selectedClientText;
            }
        }
        
        $this->showDropdown = false;
    }

    public function hideDropdown()
    {
        // Small delay to allow click events to register
        $this->showDropdown = false;
    }

    public function render()
    {
        return view('livewire.client-select');
    }
}
