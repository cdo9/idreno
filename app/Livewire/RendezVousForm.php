<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Projet;
use App\Services\CommerciauxService;
use App\Services\ProjectService;
use App\Services\SourceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class RendezVousForm extends Component
{
    // Form data
    public $rendezVousId = null;

    public $isEdit = false;

    // Client data - logique intégrée du ClientController
    public $selectedClientId = null;

    public $clientSearch = '';

    public $clients = [];

    public $showClientDropdown = false;

    // Form fields
    public $date_prise_rdv;

    public $date;

    public $date_confirmation;

    public $projet = '';

    public $projet2 = '';

    public $projet3 = '';

    public $source = '';

    public $priorite = 1;

    public $estimation = 0;

    public $rdv_confirme = 0;

    public $rdv_annule = 0;

    public $compte_rendu = '';

    public $raison = '';

    public $heure_rdv = '';

    public $couple_present = 'oui';

    public $date_realisation_du_projet = '';

    public $temps_disponible = 'oui';

    public $quantite1 = '0';

    public $quantite2 = '0';

    public $quantite3 = '';

    public $secteur = 1;

    public $pourquoi = '';

    public $partage_avec = '';

    // Options for dropdowns
    public $users = [];

    public $priorities = [];

    public $sources = [];

    public $projects = [];

    public $googleCalendarUrl = '';

    protected $rules = [
        'selectedClientId' => 'required',
        'date_prise_rdv' => 'required|date',
        'date' => 'required|date',
        'heure_rdv' => 'required',
        'projet' => 'required|string|max:255',
        'couple_present' => 'required',
        'temps_disponible' => 'required',
        'secteur' => 'required|integer',
        'quantite1' => 'nullable|integer|min:0',
        'quantite2' => 'nullable|integer|min:0',
        'date_realisation_du_projet' => 'required|integer',
        'pourquoi' => 'required|string|max:255',
        'partage_avec' => 'nullable|exists:users,id',
    ];

    protected $messages = [
        'selectedClientId.required' => 'Veuillez sélectionner un client.',
        'date_prise_rdv.required' => 'La date de prise de rendez-vous est obligatoire.',
        'date.required' => 'La date du rendez-vous est obligatoire.',
        'heure_rdv.required' => 'L\'heure du rendez-vous est obligatoire.',
        'projet.required' => 'La prioritée travaux est obligatoire.',
        'couple_present.required' => 'Veuillez indiquer si le couple sera présent.',
        'temps_disponible.required' => 'Veuillez indiquer si le client a 1h30 à consacrer.',
        'quantite1.required' => 'La quantité/surface est obligatoire.',
        'quantite1.integer' => 'La quantité doit être un nombre entier.',
        'quantite1.min' => 'La quantité doit être positive ou nulle.',
        'quantite2.integer' => 'La quantité doit être un nombre entier.',
        'quantite2.min' => 'La quantité doit être positive ou nulle.',
        'date_realisation_du_projet.required' => 'La date de réalisation du projet est obligatoire.',
        'pourquoi.required' => 'Veuillez indiquer pourquoi ce projet.',
        'partage_avec.exists' => 'L\'utilisateur sélectionné n\'existe pas.',
    ];

    public function mount($rendezVousId = null)
    {
        if (url()->previous() !== url()->current()) {
            session(['return_url' => url()->previous()]);
        }
        $this->rendezVousId = $rendezVousId;
        $this->isEdit = ! is_null($rendezVousId);

        $this->secteur = session('selectedSecteur');

        $selectedClientId = request()->query('id_client');

        if ($selectedClientId) {
            $this->selectClient($selectedClientId);
        }

        $this->loadOptions();

        // Always set date_prise_rdv to today's date
        $this->date_prise_rdv = now()->toDateString();

        if ($this->isEdit) {
            $this->loadRendezVous();
            // Override date_prise_rdv with today's date even for edited appointments
            $this->date_prise_rdv = now()->toDateString();
        } else {
            // Log the initial sector_id for debugging
            Log::info('Initial secteur in new appointment', ['secteur' => $this->secteur]);
        }
    }

    public function loadOptions()
    {
        // Logique du controller create() - charger les utilisateurs actifs
        $this->users = CommerciauxService::getCommerciauxListBySector();

        // Charger les groupes (commenté dans le controller mais disponible)
        // $groupes = \DB::table('groupes')
        //     ->join('users', 'groupes.id_responsable', '=', 'users.id')
        //     ->select('groupes.id', 'groupes.id_responsable', 'users.nom', 'users.prenom')
        //     ->get();

        // Charger les commerciaux comme dans le controller
        $commerciaux = DB::table('users')
            ->select('id', 'nom', 'prenom', 'role', 'active')
            ->where('active', 1)
            ->get();

        // Charger les priorités et sources comme dans le controller
        $projectsData = ProjectService::listPriorities();
        $sourcesData = SourceService::createSourceLst();

        // Charger les poseurs comme dans le controller
        $poseurs = DB::table('poseurs')->select('id', 'poseur')->get();

        // Stocker les projets organisés par catégorie
        $this->projects = $projectsData;

        // Transformer les priorités en simple liste numérique pour les rendez-vous
        $this->priorities = [
            1 => 'Basse',
            2 => 'Normale',
            3 => 'Haute',
            4 => 'Urgente',
        ];

        // Stocker les sources organisées par catégorie (actif, passif, télépro)
        $this->sources = $sourcesData;
    }

    public function loadRendezVous()
    {
        $rendezVous = Projet::with('client')->findOrFail($this->rendezVousId);

        $this->selectedClientId = $rendezVous->id_client;
        $this->clientSearch = ($rendezVous->client->nom ?? '').' '.($rendezVous->client->prenom ?? '');
        $this->date_prise_rdv = $rendezVous->date_ajout;
        // Ensure date is properly formatted for the date input (YYYY-MM-DD)
        $this->date = $rendezVous->date ? date('Y-m-d', strtotime($rendezVous->date)) : null;
        $this->date_confirmation = $rendezVous->date_confirmation;
        $this->projet = $rendezVous->projet ?? '';
        $this->projet2 = $rendezVous->projet2 ?? '';
        $this->projet3 = $rendezVous->projet3 ?? '';
        $this->source = $rendezVous->source ?? '';
        $this->priorite = $rendezVous->priorite ?? 1;
        $this->estimation = $rendezVous->estimation ?? 0;
        $this->rdv_confirme = $rendezVous->rdv_confirme ?? 0;
        $this->rdv_annule = $rendezVous->rdv_annule ?? 0;
        $this->compte_rendu = $rendezVous->compte_rendu ?? '';
        $this->raison = $rendezVous->raison ?? '';
        $this->heure_rdv = $rendezVous->heure ?? '';
        $this->couple_present = $rendezVous->couple_present ?? '';
        $this->date_realisation_du_projet = $rendezVous->date_realisation_du_projet ?? '';
        $this->temps_disponible = $rendezVous->temps_disponible ?? '';
        $this->quantite1 = $rendezVous->quantite1 ?? '0';
        $this->quantite2 = $rendezVous->quantite2 ?? '0';
        $this->quantite3 = $rendezVous->quantite3 ?? '';
        $this->secteur = $rendezVous->secteur ?? 1;
        $this->pourquoi = $rendezVous->pourquoi ?? '';
        $this->partage_avec = $rendezVous->partage_avec ?? '';

        if ($this->couple_present != 'oui' && $this->couple_present != 'non' && $this->couple_present != 'seul') {
            $this->couple_present = 'non';
        }
    }

    // ique de recherche de clients basée sur ClientController::autocomplete
    public function updatedClientSearch()
    {
        $this->selectedClientId = null;

        if (strlen($this->clientSearch) >= 2) {
            $this->searchClients();
            $this->showClientDropdown = true;
        } else {
            $this->clients = [];
            $this->showClientDropdown = false;
        }
    }

    public function searchClients()
    {
        /*try {
            $query = $this->clientSearch;
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
            $clientsQuery->where('rentre_par', $currentUserId);

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
                ->get();

            Log::info('Client search results', [
                'query' => $query,
                'user_id' => $currentUserId,
                'results_count' => count($this->clients)
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching clients: ' . $e->getMessage());
            $this->clients = [];
        }*/

        $query = $this->clientSearch;
        $this->clients = Client::searchFull($query);
    }

    public function selectClient($clientId)
    {
        try {
            $client = Client::find($clientId);

            if ($client) {
                $this->selectedClientId = $clientId;
                $this->clientSearch = $client->nom.' '.$client->prenom;
                $this->showClientDropdown = false;

                Log::info('Client selected', ['client_id' => $clientId, 'client_name' => $this->clientSearch]);
            }
        } catch (\Exception $e) {
            Log::error('Error selecting client: '.$e->getMessage());
        }
    }

    public function save()
    {
        try {
            // Log the sector_id value before saving
            Log::info('Sector ID before saving', ['secteur' => $this->secteur]);

            DB::beginTransaction();

            // Validation complète basée sur la logique du controller
            $this->validate([
                'selectedClientId' => 'required|exists:clients,id',
                'priorite' => 'nullable|integer',
                'projet' => 'required|string|max:510',
                'projet2' => 'nullable|string|max:255',
                'projet3' => 'nullable|string|max:255',
                'source' => 'nullable|string|max:100',
                'estimation' => 'nullable|integer',
                'date_prise_rdv' => 'required|date',
                'date' => 'required|date',
                'rdv_confirme' => 'nullable|integer',
                'date_confirmation' => 'nullable|date',
                'rdv_annule' => 'nullable|integer',
                'compte_rendu' => 'nullable|string|max:100',
                'raison' => 'nullable|string|max:255',
                'heure_rdv' => 'required|string|max:255',
                'couple_present' => 'required|string|max:255',
                'date_realisation_du_projet' => 'required|integer',
                'temps_disponible' => 'required|string|max:255',
                'quantite1' => 'nullable|numeric|min:0',
                'quantite2' => 'nullable|numeric|min:0',
                'quantite3' => 'nullable|string|max:10',
                'secteur' => 'required|integer',
                'pourquoi' => 'required|string|max:255',
                'partage_avec' => 'nullable',
            ]);

            // Préparer les données exactement comme dans le controller
            $data = [
                'id_client' => $this->selectedClientId ?? 1,
                'priorite' => $this->priorite ?? 1,
                'projet' => $this->projet,
                'projet2' => $this->projet2 ?? '',
                'projet3' => $this->projet3 ?? '',
                'source' => $this->source ?? '',
                'estimation' => $this->estimation ?? 0,
                'date_ajout' => now()->toDateString(), // Always use today's date
                'date' => $this->date,
                'rdv_confirme' => $this->rdv_confirme ? 1 : 0,
                'date_confirmation' => null,
                'rdv_annule' => $this->rdv_annule ? 1 : 0,
                'compte_rendu' => $this->compte_rendu ?? '',
                'raison' => $this->raison ?? '',
                'heure' => $this->heure_rdv ?? '',
                'couple_present' => $this->couple_present ?? '',
                'date_realisation_du_projet' => $this->date_realisation_du_projet ?? '',
                'date_realisation' => now()->toDateString(),
                'temps_disponible' => $this->temps_disponible ?? '',
                'quantite1' => $this->quantite1 ?? '0',
                'quantite2' => $this->quantite2 ?? '0',
                'quantite3' => $this->quantite3 ?? '',
                'secteur' => (int) ($this->secteur),
                'pourquoi' => $this->pourquoi ?? '',
                'partage_avec' => (! empty($this->partage_avec)) ? $this->partage_avec : null,
            ];

            if ($this->isEdit) {
                // Logique d'update du controller
                $rendezVous = Projet::findOrFail($this->rendezVousId);

                // Filtrer les valeurs nulles comme dans le controller
                $updateData = array_filter($data, function ($value) {
                    return $value !== null;
                });

                if ($updateData['date'] != $rendezVous->date) { // Date de RDV modifiée
                    $updateData['date_confirmation'] = null;
                    $updateData['rdv_confirme'] = 0;

                    $dateRendezVous = Carbon::parse($data['date']);
                    [$updateData['rdv_confirme'], $updateData['date_confirmation']] =
                        $this->setIs48hConfirmationDate($dateRendezVous);
                }

                $this->checkClientState($rendezVous);

                $rendezVous->fill($updateData);
                $rendezVous->save();

                $this->googleCalendarUrl = RendezVous::generateCalendarLink($rendezVous);

                $message = 'Rendez-vous mis à jour avec succès!';

                Log::info('Rendez-vous updated via Livewire', ['id' => $this->rendezVousId]);
            } else {
                // Logique de création du controller - utilisation directe des propriétés
                $projet = new Projet;
                $projet->id_client = $data['id_client'];
                $projet->priorite = $data['priorite'];
                $projet->projet = $data['projet'];
                $projet->projet2 = $data['projet2'];
                $projet->projet3 = $data['projet3'];
                $projet->source = $data['source'];
                $projet->estimation = $data['estimation'];
                $projet->date_ajout = $data['date_ajout'];
                $projet->date = $data['date'];
                $projet->rdv_confirme = $data['rdv_confirme'];
                $projet->date_confirmation = $data['date_confirmation'];
                $projet->rdv_annule = $data['rdv_annule'];
                $projet->compte_rendu = $data['compte_rendu'];
                $projet->raison = $data['raison'];
                $projet->heure = $data['heure'];
                $projet->couple_present = $data['couple_present'];
                $projet->date_realisation_du_projet = $data['date_realisation_du_projet'];
                $projet->date_realisation = $data['date_realisation'];
                $projet->temps_disponible = $data['temps_disponible'];
                $projet->quantite1 = $data['quantite1'];
                $projet->quantite2 = $data['quantite2'];
                $projet->quantite3 = $data['quantite3'];
                // Log the sector value before assigning it
                Log::info('Sector value before assignment', [
                    'data_secteur' => $data['secteur'],
                    'secteur' => $this->secteur,
                    'secteur_type' => gettype($this->secteur),
                ]);

                // Explicitly cast to integer to ensure correct data type
                $projet->secteur = (int) $data['secteur'];
                $projet->pourquoi = $data['pourquoi'];
                $projet->partage_avec = (! empty($data['partage_avec'])) ? $data['partage_avec'] : null;

                // Log the sector value after assignment
                Log::info('Sector value after assignment', [
                    'projet_secteur' => $projet->secteur,
                    'projet_secteur_type' => gettype($projet->secteur),
                ]);

                // ========== VÉRIFICATION DU DOUBLON ==========
                $dateRendezVous = Carbon::parse($data['date']);

                // Vérifier s'il existe déjà un rendez-vous pour ce client à cette date
                $rendezVousExistant = Projet::where('id_client', $data['id_client'])
                    ->whereDate('date', $dateRendezVous->format('Y-m-d'))
                    ->first();

                if ($rendezVousExistant) {
                    DB::rollBack();

                    Log::warning('Duplicate rendez-vous attempt', [
                        'id_client' => $data['id_client'],
                        'date' => $dateRendezVous->format('Y-m-d'),
                        'existing_id' => $rendezVousExistant->id,
                    ]);

                    session()->flash('error', 'Un rendez-vous existe déjà pour ce client à cette date ('.$dateRendezVous->format('d/m/Y').')');

                    // Si vous utilisez Livewire
                    $this->dispatch('rendez-vous-error', [
                        'message' => 'Un rendez-vous existe déjà pour ce client à cette date ('.$dateRendezVous->format('d/m/Y').')',
                    ]);

                    return;
                }
                // ========== FIN VÉRIFICATION DU DOUBLON ==========
                [$projet->rdv_confirme, $projet->date_confirmation] =
                    $this->setIs48hConfirmationDate($dateRendezVous);

                $this->checkClientState($projet);

                $projet->save();

                $message = 'Rendez-vous créé avec succès!';

                $this->googleCalendarUrl = RendezVous::generateCalendarLink($projet);

                Log::info('Rendez-vous created via Livewire', [
                    'id' => $projet->id,
                    'secteur' => $projet->secteur,
                    'secteur_type' => gettype($projet->secteur),
                    'secteur_from_form' => $this->secteur,
                    'secteur_from_form_type' => gettype($this->secteur),
                ]);
            }

            DB::commit();

            session()->flash('success', $message);

            $this->dispatch('rendez-vous-saved', [
                'message' => $message,
            ]);

            return;
            /*dd(session('return_url', url()->current()));
            return redirect()->to(session('return_url', url()->current()))->with('success', $message);*/

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error saving rendez-vous via Livewire: '.$e->getMessage());
            // Let Laravel handle the validation errors automatically
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving rendez-vous via Livewire: '.$e->getMessage());

            // Add error message to specific fields or use a general error
            session()->flash('error', 'Erreur lors de la sauvegarde: '.$e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->to(session('return_url', url()->previous()));
    }

    public function render()
    {
        return view('livewire.rendez-vous-form');
    }

    private function setIs48hConfirmationDate($dateRendezVous)
    {

        $maintenant = Carbon::today();

        // Calculer la date limite (48h à partir de maintenant)
        $dateLimite = $maintenant->copy()->addDays(3)->subSecond();

        // Si la date limite tombe sur un week-end, passer au lundi suivant
        if ($dateLimite->isWeekend()) {
            $dateLimite->next(Carbon::MONDAY);
        }

        // Vérifier si le RDV est dans le futur et avant la date limite
        $rdvConfirme = $dateRendezVous->betweenIncluded($maintenant, $dateLimite);
        $dateConfirmation = $rdvConfirme ? $maintenant : null;

        return [$rdvConfirme, $dateConfirmation];
    }

    private function checkClientState($projet)
    {
        $client = Client::findOrFail($projet->id_client);
        if (in_array($client->type, [6, 7, 8, 0, 3])) {
            $client->type = 1;
        }
        if ($projet->partage_avec != null) {
            $client->partage_avec = $projet->partage_avec;
        }

        $client->save();
    }
}
