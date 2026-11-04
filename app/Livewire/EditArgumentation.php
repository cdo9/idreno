<?php

namespace App\Livewire;

use App\Http\Controllers\ClientController;
use App\Models\Client;
use App\Models\RendezVous;
use App\Models\User;
use App\Models\Vente;
use App\Services\ProjectService;
use App\Services\SourceService;
use App\Utils\DataNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class EditArgumentation extends Component
{
    public $rdvId;

    public $rendezVous;

    public $isCreate = false;

    public $secteur;

    public $last_client;

    public $client_id;

    public $id_source;

    public $client_id_save;

    public $client_search;

    public $email;

    public $vente1;

    public $vente2;

    public $vente3;

    public $id_commercial1;

    public $id_commercial3;

    public $date;

    public $id_commercial;

    public $type_vente;

    public $partage_avec;

    public $vente_ok;

    public $statut;

    public $date_relance;

    public $complement;

    public $paiement;

    public $postpone;

    public $duree_pret;

    public $mensualite;

    public $devis_total_ht;

    public $tarif_total_ht;

    public $devis_ht;

    public $tarif_ht;

    public $marge;

    public $remise;

    public $produits = [];

    public $commentaire;

    public $commentaire_save;

    public $repartition_1;

    public $repartition_2;

    public $repartition_3;

    public $commande;

    public $attente = [];

    public $attente_autre;

    public $date_anniversaire;

    public $clients = [];

    public $commerciaux = [];

    public $sources = [];

    public $priorites = [];

    public $client_fullname;

    public $isDirecteur = false;

    public $isAdmin = false;

    public $isTelepro = false;

    // Separate rule sets for client, rendez-vous and vente
    protected $rulesClient = [
        'client_id' => 'required|integer|exists:clients,id',
        'email' => 'email|nullable',
        'date_anniversaire' => 'nullable|required_if:vente_ok,ok|date_format:Y-m-d',
    ];

    protected $rulesRdv = [
        'email' => 'required|email',
        'date' => 'required|date',
        'id_commercial' => 'required|integer|exists:users,id',
        'partage_avec' => 'nullable|exists:users,id',
        'vente_ok' => 'required',
        'date_relance' => 'required|date',
        'secteur' => 'nullable|integer',
        'id_source' => 'required|integer|exists:sources,id',
        'complement' => 'required|nullable|string',
        'devis_total_ht' => 'required|numeric|min:0',
        'tarif_total_ht' => 'required|numeric|min:0',
        'commentaire' => 'required_if:vente_ok,nok|string|nullable',
    ];

    protected $rulesVente = [
        'paiement' => 'nullable|string',
        'repartition_1' => 'nullable|numeric|min:0|max:100',
        'repartition_2' => 'nullable|numeric|min:0|max:100',
        'repartition_3' => 'nullable|numeric|min:0|max:100',
        'commande' => 'nullable|integer|in:0,1',
        'postpone' => 'nullable|integer',
        'duree_pret' => 'nullable|integer',
        'mensualite' => 'nullable|integer',
    ];

    public function mount($rdvId = null)
    {

        if (url()->previous() !== url()->current()) {
            session(['return_url' => url()->previous()]);
        }
        $user = auth()->user();

        $this->isDirecteur = $user->hasRole('directeur');
        $this->isAdmin = $user->hasRole('admin');
        $this->isTelepro = $user->hasRole('telepro');

        $this->isCreate = false;

        $attenteOptions = implode(';', $this->getAttenteOptionsProperty());
        $this->rulesVente['attente'] = 'nullable|string|in:'.$attenteOptions;

        $produitsOptions = implode(';', $this->getProduitsOptionsProperty());
        $this->rulesRdv['produits'] = 'nullable|string|in:'.$produitsOptions;

        // Charge les listes pour les selects (communs à création et édition)
        $this->clients = [];
        $commQuery = User::query();
        $commQuery->select('users.*');
        if (! $this->isAdmin) {
            $commQuery->join('secteur_commercials', 'users.id', '=', 'secteur_commercials.id_commercial');
            $commQuery->where('secteur_commercials.id_secteur', session('selectedSecteur'));
        }

        $this->commerciaux = $commQuery->distinct()->whereNotIn('role', ['administratif'])->orderBy('active', 'desc')->orderBy('nom', 'asc')->get();

        $this->sources = SourceService::createSourceLst();
        $this->priorites = ProjectService::listPriorities();

        if ($rdvId) {
            // Mode édition
            $ventes = Vente::where('id_rdv', $rdvId)
                ->orderBy('id', 'asc')
                ->with('user')
                ->get();

            $orderedVentes = [];
            foreach ($ventes as $vente) {
                $orderedVentes[$vente->id_commercial] = $vente;
            }

            $vente1 = $ventes[0] ?? null;
            $this->vente1 = $vente1;
            // Ajout pré-remplissage postpone et duree_pret
            if ($this->vente1) {
                $this->id_commercial1 = $this->vente1->id_commercial;
                $this->repartition_1 = $this->vente1 ? ($this->vente1->repartition ?? 0) : 0;
                $this->postpone = $this->vente1->postpone ?? '';
                $this->duree_pret = $this->vente1->duree_pret ?? '';
                $this->mensualite = $this->vente1->mensualite ?? '';
            }

            if ($vente1 && isset($orderedVentes[$vente1->id_commercial_2])) {
                $this->vente2 = $orderedVentes[$vente1->id_commercial_2];
                $this->partage_avec = $this->vente2->id_commercial;
                $this->repartition_2 = $this->vente2 ? ($this->vente2->repartition ?? 0) : 0;
            }

            if ($vente1 && isset($orderedVentes[$vente1->id_commercial_3])) {
                $this->vente3 = $orderedVentes[$vente1->id_commercial_3];
                $this->id_commercial3 = $this->vente3->id_commercial;
                $this->repartition_3 = $this->vente3 ? ($this->vente3->repartition ?? 0) : 0;
            }

            $this->rdvId = $rdvId;
            $this->rendezVous = RendezVous::with('client')->findOrFail($rdvId);

            $this->checkPermissions();

            // Pré-remplit les champs du formulaire depuis le RDV existant
            $rdv = $this->rendezVous;
            $this->client_id = $rdv->id_client;
            $this->last_client = Client::find($this->client_id);

            $this->id_source = $this->vente1?->id_source ?? $this->last_client->id_source;
            $this->secteur = $rdv->secteur ?? null;

            $this->client_id_save = $rdv->id_client;
            $this->client_search = '';
            $this->client_fullname = $rdv->client ? ($rdv->client->prenom.' '.$rdv->client->nom) : '';
            $this->email = $this->last_client->mail ?? '';
            $this->date_anniversaire = $this->last_client->date_anniversaire ? date('Y-m-d', strtotime($this->last_client->date_anniversaire)) : '';
            $this->date = $rdv->date ? $rdv->date->format('Y-m-d') : '';
            $this->id_commercial = $rdv->id_commercial ?? null;
            $this->type_vente = $rdv->type_vente ?? null;
            $this->partage_avec = $rdv->partage_avec ?? null;
            $this->vente_ok = ($rdv->resultat ?? '') == 'Vendu' ? 'ok' : 'nok';
            $this->statut = $this->vente1?->statut ?? 3;
            $this->date_relance = $rdv->date_relance ? $rdv->date_relance->format('Y-m-d') : ClientController::nextNov20();
            $this->complement = $this->vente1?->complement ?? 'none';
            $this->paiement = $this->vente1 ? $this->vente1->paiement : null;
            $this->devis_total_ht = $rdv->devis_total_ht ?? 0;
            $this->tarif_total_ht = $rdv->tarif_total_ht ?? 0;
            $this->devis_ht = $rdv->devis_ht ?? 0;
            $this->tarif_ht = $rdv->tarif_ht ?? 0;
            $this->remise = $rdv->remise;
            $this->marge = $rdv->montant;
            $this->produits = $rdv->produits ? explode(';', $rdv->produits) : [];
            $this->attente = $this->vente1 ? ($this->vente1->attente ? explode(';', $this->vente1->attente) : []) : [];
            $this->attente_autre = $this->vente1 ? ($this->vente1->attente_autre ? explode(';', $this->vente1->attente_autre) : []) : [];
            $this->commentaire = $rdv->commentaire;
            $this->commentaire_save = $rdv->commentaire;
            $this->commande = $this->vente1 ? ($this->vente1->commande ?? 0) : 0;
            $this->update_tarifs_taux();
        } else {
            // Mode création
            $this->isCreate = true;
            $this->rdvId = null;
            $this->rendezVous = new RendezVous;

            // Valeurs par défaut pour le formulaire de création
            $this->client_id = null;
            $this->last_client = null;
            $this->client_id_save = null;
            $this->client_search = '';
            $this->client_fullname = null;
            $this->email = '';
            $this->date_anniversaire = null;
            $this->date = now()->format('Y-m-d');
            $this->id_commercial = $user->id;
            $this->type_vente = null;
            $this->partage_avec = null;
            $this->vente_ok = 'nok';
            $this->statut = 1;
            $this->date_relance = ClientController::nextNov20();
            $this->complement = '';
            $this->paiement = 'Financement';
            $this->postpone = '';
            $this->duree_pret = '';
            $this->mensualite = 0;
            $this->devis_total_ht = 0;
            $this->tarif_total_ht = 0;
            $this->devis_ht = 0;
            $this->tarif_ht = 0;
            $this->marge = 0;
            $this->remise = 0;
            $this->produits = [];
            $this->attente = [];
            $this->attente_autre = '';
            $this->commentaire = '';
            $this->commentaire_save = '';
            $this->commande = 0;
            $this->repartition_1 = 100;
            $this->repartition_2 = 0;
            $this->repartition_3 = 0;
            $this->id_source = null;
            $this->secteur = session('selectedSecteur');
        }
    }

    private function checkPermissions()
    {
        $userRole = Auth::user()->role;
        if (! in_array($userRole, ['directeur', 'admin'])) {
            $currentUserId = Auth::user()->id;
            $client = $this->rendezVous->client;
            if ($client->rentre_par !== $currentUserId && $client->partage_avec !== $currentUserId) {
                abort(403, 'Vous n\'avez pas les permissions pour éditer cette argumentation.');
            }
        }
    }

    public function updated($propertyName)
    {
        // Validate only against the relevant rule set
        //  try {
        //         $this->validate($this->rulesClient);
        //     } catch (\Illuminate\Validation\ValidationException $e) {
        //         $this->handleValidationException($e);
        //         return;
        //     }

        //     try {
        //         $this->validate(array_merge($this->rulesRdv, [$this->rulesRdv['produits'] ?? 'produits' => $this->rulesRdv['produits'] ?? 'nullable|string']));
        //     } catch (\Illuminate\Validation\ValidationException $e) {
        //         $this->handleValidationException($e);
        //         return;
        //     }

        //     try {
        //         $this->validate($this->rulesVente);
        //     } catch (\Illuminate\Validation\ValidationException $e) {
        //         $this->handleValidationException($e);
        //         return;
        //     }

        if ($propertyName === 'client_search') {
            $searchTerm = trim($this->client_search);
            if (strlen($searchTerm) < 2 && empty($searchTerm)) {
                $this->clients = [];
                $this->client_fullname = $this->last_client ? $this->last_client->prenom.' '.$this->last_client->nom : null;

                return;
            }

            $searchClients = Client::searchFull($searchTerm);
            $this->clients = $searchClients;
        }

        if ($propertyName === 'client_id') {
            if ($this->client_id) {
                $selectedClient = Client::find($this->client_id);
                if ($selectedClient) {
                    $this->client_fullname = $selectedClient->prenom.' '.$selectedClient->nom;
                    $this->email = $selectedClient->mail;
                    $this->date_anniversaire = ! empty($selectedClient->date_anniversaire)
                        ? date('Y-m-d', strtotime($selectedClient->date_anniversaire))
                        : null;
                    // $this->client_search = $this->client_fullname;
                    // Vide la liste des clients pour masquer le dropdown
                    $this->clients = [];
                } else {
                    $this->client_fullname = null;
                    $this->email = '';
                    $this->date_anniversaire = null;
                }
            } else {
                $this->client_fullname = null;
                $this->email = '';
                $this->date_anniversaire = null;
                $this->client_search = '';
                $this->clients = [];
            }
        }

        if ($propertyName === 'vente_ok') {
            $this->commentaire = $this->commentaire_save;
            if ($this->vente_ok == 'ok') {
                $this->commentaire = "La vente a été validée avec succès.\n"."Ancien commentaire : $this->commentaire";
            }
            $this->refreshRepartition();
            $this->dispatch('vente-ok', $this->commentaire_save);
        }

        if ($propertyName === 'partage_avec') {
            if ($this->partage_avec === '') {
                $this->partage_avec = null;
            }
            $this->refreshRepartition();
        }

        if ($propertyName === 'id_commercial3') {
            if ($this->id_commercial3 === '') {
                $this->id_commercial3 = null;
            }
            $this->refreshRepartition();
        }

        if ($propertyName === 'devis_total_ht') {
            $this->update_tarifs_taux();
        }

        if ($propertyName === 'tarif_total_ht') {
            $this->update_tarifs_taux();
        }

        if ($propertyName === 'repartition_1' || $propertyName === 'repartition_2' || $propertyName === 'repartition_3') {
            $this->update_tarifs_taux();
        }
    }

    public function update_tarifs_taux()
    {
        $prix_tarif_total = (float) $this->tarif_total_ht;
        $prix_vente_total = (float) $this->devis_total_ht;
        $taux = (float) $this->repartition_1;

        // $this->refreshRepartition();

        $prix_tarif = $prix_tarif_total * $taux / 100;
        $prix_vente = $prix_vente_total * $taux / 100;

        $this->devis_ht = $prix_vente;
        $this->tarif_ht = $prix_tarif;

        // calcul marges
        $prix_tarif = $this->tarif_ht;
        $prix_vente = $this->devis_ht;
        $prix_marge = 0;
        $taux_remise = 0;

        if ($prix_tarif > 0 && $prix_vente > 0) {
            // marge
            // = prix tarif * 15% - (prix tarif - prix vente)
            // = prix vente - prix tarif * 85%
            $prix_marge = $prix_vente - ($prix_tarif * 0.85);
        }

        if ($prix_tarif_total > 0 && $prix_vente_total > 0) {
            // taux de remise
            // = 1 - prix vente / prix tarif
            $taux_remise = 1 - ($prix_vente_total / $prix_tarif_total);
        }

        $this->marge = (int) round($prix_marge, 0);
        $this->remise = (int) round($taux_remise * 100, 0);
    }

    public function refreshRepartition()
    {

        if ($this->partage_avec === null && $this->id_commercial3 === null) {
            $this->repartition_1 = 100;
            $this->repartition_2 = 0;
            $this->repartition_3 = 0;

            return;
        } elseif ($this->partage_avec !== null && $this->id_commercial3 === null) {
            $this->repartition_1 = 50;
            $this->repartition_2 = 50;
            $this->repartition_3 = 0;

            return;
        } elseif ($this->partage_avec === null && $this->id_commercial3 !== null) {
            $this->repartition_1 = 50;
            $this->repartition_2 = 0;
            $this->repartition_3 = 50;

            return;
        }

        $comm2_isTelepro = $this->partage_avec ? $this->commerciaux->firstWhere('id', $this->partage_avec)->hasRole('telepro') : false;
        $comm3_isTelepro = $this->id_commercial3 ? $this->commerciaux->firstWhere('id', $this->id_commercial3)->hasRole('telepro') : false;

        if (
            ($this->isTelepro && $comm2_isTelepro && $comm3_isTelepro)
            || (! $this->isTelepro && ! $comm2_isTelepro && ! $comm3_isTelepro)
            || (! $this->isTelepro && $comm2_isTelepro && $comm3_isTelepro)
            || ($this->isTelepro && ! $comm2_isTelepro && ! $comm3_isTelepro)
        ) {
            $this->repartition_1 = 50;
            $this->repartition_2 = 25;
            $this->repartition_3 = 25;

            return;
        } elseif (
            ($this->isTelepro && $comm2_isTelepro && ! $comm3_isTelepro)
            || (! $this->isTelepro && ! $comm2_isTelepro && $comm3_isTelepro)
        ) {
            $this->repartition_1 = 25;
            $this->repartition_2 = 25;
            $this->repartition_3 = 50;

            return;
        } elseif (
            ($this->isTelepro && ! $comm2_isTelepro && $comm3_isTelepro)
            || (! $this->isTelepro && $comm2_isTelepro && ! $comm3_isTelepro)
        ) {
            $this->repartition_1 = 25;
            $this->repartition_2 = 50;
            $this->repartition_3 = 25;

            return;
        } else {
            $this->repartition_1 = 100;
            $this->repartition_2 = 0;
            $this->repartition_3 = 0;

            return;
        }
    }

    public function selectClient($clientId)
    {
        $this->client_id = $clientId;
        $this->client_search = '';
        $this->updated('client_id');
    }

    protected function handleValidationException(ValidationException $e)
    {
        Log::error('Validation errors:', $e->errors());
        foreach ($e->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $this->addError($field, $message);
            }
        }
    }

    public function clearClient()
    {
        /*$this->client_id = $this->client_id_save;
        $this->client_fullname = $this->last_client ? $this->last_client->prenom . ' ' . $this->last_client->nom : null;
        $this->email = $this->last_client ? $this->last_client->mail : '';
        $this->client_search = '';
        $this->clients = [];*/
        $this->client_id_save = $this->client_id;
        $this->client_fullname = null;
        $this->email = '';
        $this->date_anniversaire = null;
        $this->client_search = '';
        $this->clients = [];
    }

    public function save()
    {

        Log::info('Aucune vente existante, création des ventes', [
            'vente_ok' => $this->vente_ok,
            'rendezVousId' => $this->rendezVous->id,
            'clientId' => $this->client_id,

        ]);
        // Validate client, rdv and vente separately so we can show precise errors
        try {
            $this->validate($this->rulesClient);
        } catch (ValidationException $e) {
            $this->handleValidationException($e);

            return;
        }

        try {
            $this->validate($this->rulesRdv);
        } catch (ValidationException $e) {
            $this->handleValidationException($e);

            return;
        }

        try {
            $this->validate($this->rulesVente);
        } catch (ValidationException $e) {
            $this->handleValidationException($e);

            return;
        }

        // Gestion du commentaire et du résultat
        $resultat = $this->vente_ok == 'ok' ? 'Vendu' : 'Non vendu';
        $commentaire = $this->commentaire;

        // Contrôle doublon si création
        // Contrôle doublon uniquement en création (pas en update)
        if ($this->isCreate && ! $this->rendezVous->exists) {
            $doublon = RendezVous::where('id_client', $this->client_id)
                ->whereDate('date', $this->date)
                ->count();
            if ($doublon > 0) {
                session()->flash('error', 'Il existe déjà une argumentation pour ce client à cette date !');

                return;
            }
        }

        if ($this->devis_total_ht > $this->tarif_total_ht) {
            session()->flash('error', 'Le prix devis ne peut pas être inférieur au prix tarif !');

            return;
        }

        Log::info('Aucune vente existante, création des ventes', [
            'vente_ok' => $this->vente_ok,
            'rendezVousId' => $this->rendezVous->id,
            'clientId' => $this->client_id,

        ]);

        $finFinancement = null;
        Log::info('Updated property: vente_ok  '.$this->vente_ok);
        // Vérification de la somme des répartitions
        if ($this->vente_ok == 'ok') {
            Log::info('Updated property: paiement  '.$this->paiement);
            if (in_array($this->paiement, ['Financement', 'Comptant et financement']) && $this->postpone && $this->duree_pret) {
                try {
                    $dateBase = Carbon::parse($this->vente1->date ? $this->vente1->date : now());
                    $finFinancement = $dateBase->copy()->addMonths((int) $this->postpone + (int) $this->duree_pret)->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::error('Erreur calcul fin_financement', ['error' => $e->getMessage()]);
                }
            }
            $repartitions = [
                floatval($this->repartition_1 ?? 0),
                floatval($this->repartition_2 ?? 0),
                floatval($this->repartition_3 ?? 0),
            ];

            $totalRepartition = array_sum($repartitions);
            if ($totalRepartition !== 100.0) {
                $this->addError('repartition_1', 'La somme des répartitions doit être exactement 100%. (Actuellement : '.$totalRepartition.'%)');
                $this->addError('repartition_2', 'La somme des répartitions doit être exactement 100%. (Actuellement : '.$totalRepartition.'%)');
                $this->addError('repartition_3', 'La somme des répartitions doit être exactement 100%. (Actuellement : '.$totalRepartition.'%)');

                return;
            }
        }

        $updateData = [
            'id_client' => $this->client_id,
            'id_commercial' => $this->id_commercial,
            'source' => $this->id_source,
            'secteur' => $this->secteur ?? 1,
            'resultat' => $resultat,
            'commentaire' => $commentaire,
            'date' => $this->date,
            'date_relance' => $this->date_relance,
            'tarif_total_ht' => $this->tarif_total_ht,
            'devis_total_ht' => $this->devis_total_ht,
            'produits' => is_array($this->produits) ? implode(';', $this->produits) : $this->produits,
            'partage_avec' => $this->partage_avec,
        ];

        $updateData = DataNormalizer::normalizeEmptyValues($updateData);
        foreach ($updateData as $key => $value) {
            $this->rendezVous->$key = $value;
        }
        $this->rendezVous->save();

        // Si on était en création, s'assurer que le RDV fraîchement créé est bien chargé
        if ($this->isCreate) {
            $this->rdvId = $this->rendezVous->id;
            $this->isCreate = false;
            $this->rendezVous = $this->rendezVous->fresh();
        }

        // Suppression vente si non vendue
        if ($this->vente_ok != 'ok') {
            Vente::where('id_rdv', $this->rendezVous->id)->delete();
        }

        // MAJ vente si vendue
        if ($this->vente_ok == 'ok') {
            // Liste des commerciaux et répartitions
            $commerciaux = [
                ['id' => $this->id_commercial, 'repartition' => $this->repartition_1],
                ['id' => $this->partage_avec, 'repartition' => $this->repartition_2],
                ['id' => $this->id_commercial3, 'repartition' => $this->repartition_3],
            ];

            Log::info('Commerciaux à traiter', ['commerciaux' => $commerciaux]);

            // Récupère les ventes existantes pour ce RDV
            $ventesExistantes = Vente::where('id_rdv', $this->rendezVous->id)->orderBy('id', 'asc')->get();

            Log::info('Ventes existantes count', ['count' => $ventesExistantes->count()]);

            // Si aucune vente n'existe, on crée une entrée par commercial renseigné
            if ($ventesExistantes->count() === 0) {
                foreach ($commerciaux as $i => $data) {
                    if (empty($data['id'])) {
                        continue;
                    }
                    $ids_commerciaux = array_filter(array_column($commerciaux, 'id'));
                    Log::info('Ici', [
                        'ids_commerciaux' => $ids_commerciaux,
                        'rendezVousId' => $this->rendezVous->id,
                        'clientId' => $this->client_id,

                    ]);
                    $vente = new Vente;
                    $vente->id_rdv = $this->rendezVous->id;
                    $vente->id_client = $this->client_id;
                    $tarif_ht = $this->tarif_total_ht * ($data['repartition'] ?? 100) / 100;
                    $ca_ht = $this->devis_total_ht * ($data['repartition'] ?? 100) / 100;
                    $montant = $ca_ht - $tarif_ht * 0.85;
                    $venteData = [
                        'id_commercial' => $data['id'],
                        'id_commercial_2' => $ids_commerciaux[($i + 1) % 3] ?? null,
                        'id_commercial_3' => $ids_commerciaux[($i + 2) % 3] ?? null,
                        'tarif_total_ht' => $this->tarif_total_ht,
                        'ca_total_ht' => $this->devis_total_ht,
                        'tarif_ht' => $tarif_ht,
                        'ca_ht' => $ca_ht,
                        'montant' => $montant,
                        'plus_value' => 0, // ! ça m'a l'air useless mais je le laisse au cas où il est à 0 partout dans la DB
                        'prime_cee' => 0, // ! je sais pas où c'est mis à jour dans l'app
                        'statut' => 1,
                        'remise' => $this->devis_total_ht > 0 ? (1 - ($this->devis_total_ht / $this->tarif_total_ht)) * 100 : 0,
                        'repartition' => $data['repartition'] ?? 100,
                        'attente' => is_array($this->attente) ? implode(';', $this->attente) : $this->attente,
                        'attente_autre' => $this->attente_autre,
                        'commentaire' => $commentaire,
                        'complement' => $this->complement,
                        'date' => $this->date,
                        'id_source' => $this->id_source,
                        'secteur' => $this->secteur,
                        'paiement' => $this->paiement,
                        'type_vente' => ($this->partage_avec !== null || $this->id_commercial3 !== null) ? 'Commune' : 'Normale',
                        'commande' => $this->commande,
                        'postpone' => $this->postpone,
                        'duree_pret' => $this->duree_pret,
                        'mensualite' => $this->mensualite,
                        'fin_financement' => $finFinancement,
                    ];
                    $venteData = DataNormalizer::normalizeEmptyValues($venteData);
                    foreach ($venteData as $key => $value) {
                        $vente->$key = $value;
                    }
                    $vente->save();
                    Log::info('Vente après save', ['id' => $vente->id, 'attributes' => $vente->getAttributes()]);
                }
            } else {
                $ids_commerciaux = array_values(array_filter(array_column($commerciaux, 'id')));

                // Delete ventes pour commerciaux plus présent dans la vente
                Vente::where('id_rdv', $this->rendezVous->id)
                    ->whereNotIn('id_commercial', $ids_commerciaux)
                    ->delete();

                $ventesExistantes = Vente::where('id_rdv', $this->rendezVous->id)
                    ->orderBy('id', 'asc')->get();

                // Met à jour les ventes existantes et crée les manquantes si besoin
                $max = max($ventesExistantes->count(), count($commerciaux));
                for ($i = 0; $i < $max; $i++) {
                    $data = $commerciaux[$i] ?? null;
                    if (! $data || empty($data['id'])) {
                        continue;
                    }
                    $vente = $ventesExistantes->get($i) ?? new Vente;
                    $vente->id_rdv = $this->rendezVous->id;
                    $vente->id_client = $this->client_id;
                    Log::info('Vente mise à jour', ['devis_total_ht;' => $this->devis_total_ht]);

                    $tarif_ht = $this->tarif_total_ht * ($data['repartition'] ?? 100) / 100;
                    $ca_ht = $this->devis_total_ht * ($data['repartition'] ?? 100) / 100;
                    $montant = $ca_ht - $tarif_ht * 0.85;

                    $attenteAutre = $this->attente_autre;

                    if (is_array($attenteAutre)) {
                        $attenteAutre = reset($attenteAutre);
                    }

                    if (is_string($attenteAutre)) {
                        $decoded = json_decode($attenteAutre, true);

                        if (json_last_error() === JSON_ERROR_NONE) {
                            if (is_array($decoded)) {
                                $attenteAutre = implode('; ', $decoded);
                            } else {
                                $attenteAutre = $decoded;
                            }
                        }
                    }

                    $venteData = [
                        'id_commercial' => $data['id'],
                        'id_commercial_2' => $ids_commerciaux[($i + 1) % 3] ?? null,
                        'id_commercial_3' => $ids_commerciaux[($i + 2) % 3] ?? null,
                        'tarif_total_ht' => $this->tarif_total_ht,
                        'ca_total_ht' => $this->devis_total_ht,
                        'tarif_ht' => $tarif_ht,
                        'ca_ht' => $ca_ht,
                        'montant' => $montant,
                        'plus_value' => 0, // ! ça m'a l'air useless mais je le laisse au cas où il est à 0 partout dans la DB
                        'prime_cee' => 0, // ! je sais pas où c'est mis à jour dans l'app
                        'statut' => $this->statut,
                        'remise' => $this->devis_total_ht > 0 ? (1 - ($this->devis_total_ht / $this->tarif_total_ht)) * 100 : 0,
                        'repartition' => $data['repartition'] ?? 100,
                        'attente' => is_array($this->attente) ? implode(';', $this->attente) : $this->attente,
                        'attente_autre' => $attenteAutre,
                        'commentaire' => $commentaire,
                        'complement' => $this->complement,
                        'date' => $this->date,
                        'id_source' => $this->id_source,
                        'secteur' => $this->secteur,
                        'paiement' => $this->paiement,
                        'type_vente' => ($this->partage_avec !== null || $this->id_commercial3 !== null) ? 'Commune' : 'Normale',
                        'commande' => $this->commande,
                        'postpone' => $this->postpone,
                        'duree_pret' => $this->duree_pret,
                        'mensualite' => $this->mensualite,
                        'fin_financement' => $finFinancement,
                    ];

                    $venteData = DataNormalizer::normalizeEmptyValues($venteData);
                    foreach ($venteData as $key => $value) {
                        $vente->$key = $value;
                    }
                    Log::info('Vente mise à jour', ['paiement;' => $this->paiement]);
                    $vente->save();
                    Log::info('Vente après save', ['id' => $vente->id, 'attributes' => $vente->getAttributes()]);
                }
            }
        }

        // MAJ client
        $client = Client::find($this->client_id);
        if ($client) {
            if ($this->vente_ok == 'ok') {
                $client->type = 2;
            }
            if ($this->email) {
                $client->mail = $this->email;
            }
            if ($this->date_anniversaire) {
                $client->date_anniversaire = $this->date_anniversaire;
            }
            $client->save();
        }

        if ($this->isCreate) {
            session()->flash('success', 'Argumentation créée avec succès.');
        } else {
            session()->flash('success', 'Relance d\'argumentation mise à jour avec succès.');
        }

        return redirect()->to(session('return_url', url()->previous()));
    }

    public function getPaiementOptionsProperty()
    {
        return Vente::getPaiementOptionsAttribute();
    }

    public function getAttenteOptionsProperty()
    {
        return Vente::getAttenteOptionsAttribute();
    }

    public function getProduitsOptionsProperty()
    {
        return ProjectService::listProductsFlat();
    }

    public function getProduitsGroupedOptionsProperty()
    {
        return ProjectService::listPriorities();
    }

    public function render()
    {
        return view('livewire.edit-argumentation')->with(['isCreate' => $this->isCreate]);
    }
}
