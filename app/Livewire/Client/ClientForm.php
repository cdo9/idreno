<?php

namespace App\Livewire\Client;

use App\Models\Client;
use App\Models\ClientHistory;
use App\Models\Source;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ClientForm extends Component
{
    // Client properties
    public $clientId = null;

    public $nom = '';

    public $prenom = '';

    public $adresse = '';

    public $code_postal = '';

    public $ville = '';

    public $telephone1 = '';

    public $telephone2 = '';

    public $mail = '';

    public $gps_lat = '';

    public $gps_lng = '';

    public $secteur = 1;

    // Form selection properties
    public $genre = 'Mr & Mme';

    public $id_source = '';

    public $type = 0;

    public $type_habitation = '';

    public $proprietaire = '';

    public $profession = '';

    public $profession2 = '';

    public $profil_clients = '';

    public $projet = '';

    public $signe = '';

    public $commentaire = '';

    public $date_relance = '';

    public $commercial_id = '';

    public $partage_avec = '';

    // Component state
    public $isEditMode = false;

    public $showForm = false;

    // Data collections
    public $sources = [];

    public $commercials = [];

    public $civilites = [];

    public $typeHabitations = [];

    public $secteurs = [];

    protected $listeners = [
        'openClientForm',
        'editClient',
        'closeClientForm',
        'setGpsCoordinates',
    ];

    protected $rules = [
        'nom' => 'nullable|string|max:255',
        'prenom' => 'string|max:255',
        'adresse' => 'required|string|max:255',
        'code_postal' => 'required|string|max:20',
        'ville' => 'required|string|max:255',
        'telephone1' => 'string|max:255',
        'telephone2' => 'nullable|string|max:255',
        'mail' => 'nullable|email|max:255',
        'genre' => 'string|max:255',
        'id_source' => 'required|integer|exists:sources,id',
        'type' => 'required|integer',
        'type_habitation' => 'string|max:255',
        'proprietaire' => 'string|max:255',
        'profil_clients' => 'string|max:255',
        'gps_lat' => 'nullable|string',
        'gps_lng' => 'nullable|string',
        'secteur' => 'nullable|integer',
        'profession' => 'nullable|string|max:255',
        'profession2' => 'nullable|string|max:255',
        'projet' => 'nullable|string|max:255',
        'signe' => 'nullable|string|max:255',
        'commentaire' => 'nullable|string',
        'date_relance' => 'nullable|date',
        'commercial_id' => 'nullable|integer|exists:users,id',
        'partage_avec' => 'nullable|integer|exists:users,id',
    ];

    protected $messages = [
        'prenom.required' => 'Le prénom est obligatoire.',
        'adresse.required' => 'L\'adresse est obligatoire.',
        'code_postal.required' => 'Le code postal est obligatoire.',
        'ville.required' => 'La ville est obligatoire.',
        'telephone1.required' => 'Le téléphone principal est obligatoire.',
        'genre.required' => 'La civilité est obligatoire.',
        'id_source.required' => 'La source est obligatoire.',
        'id_source.exists' => 'La source sélectionnée n\'existe pas.',
        'type.required' => 'Le type de client est obligatoire.',
        'type_habitation.required' => 'Le type d\'habitation est obligatoire.',
        'proprietaire.required' => 'Le statut de propriétaire est obligatoire.',
        'profil_clients.required' => 'Le profil client est obligatoire.',
        'mail.email' => 'L\'adresse email doit être valide.',
        'commercial_id.exists' => 'Le commercial sélectionné n\'existe pas.',
        'partage_avec.exists' => 'L\'utilisateur de partage sélectionné n\'existe pas.',
    ];

    public function mount()
    {
        $this->loadFormData();
        $this->commercial_id = Auth::id();
    }

    public function loadFormData()
    {
        // Load sources
        $this->sources = Source::all();

        // Load active commercials
        $this->commercials = User::whereNotIn('role', ['administratif'])->where('active', 1)->get();

        // Load civilités from Client model
        $this->civilites = Client::CIVILITES;

        // Load type habitations from Client model
        $this->typeHabitations = Client::TYPE_HABITATIONS;

        // Load secteurs
        $this->secteurs = DB::table('secteurs')->get();
    }

    public function openClientForm($gpsLat = null, $gpsLng = null)
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showForm = true;

        if ($gpsLat && $gpsLng) {
            $this->gps_lat = $gpsLat;
            $this->gps_lng = $gpsLng;
        }

        $this->dispatch('formOpened');
    }

    public function editClient($clientId)
    {
        $client = Client::find($clientId);

        if (! $client) {
            session()->flash('error', 'Client non trouvé.');

            return;
        }

        $this->isEditMode = true;
        $this->clientId = $client->id;
        $this->nom = $client->nom ?? '';
        $this->prenom = $client->prenom ?? '';
        $this->adresse = $client->adresse ?? '';
        $this->code_postal = $client->cp ?? '';
        $this->ville = $client->ville ?? '';
        $this->telephone1 = $client->telephone1 ?? '';
        $this->telephone2 = $client->telephone2 ?? '';
        $this->mail = $client->mail ?? '';
        $this->gps_lat = $client->gps_lat ?? '';
        $this->gps_lng = $client->gps_lng ?? '';
        $this->secteur = $client->secteur ?? session('selectedSecteur');
        $this->genre = $client->genre ?? 'Mr & Mme';
        $this->id_source = $client->id_source ?? '';
        $this->type = $client->type ?? 0;
        $this->type_habitation = $client->type_habitation ?? '';
        $this->proprietaire = $client->proprietaire ? 'oui' : 'non';
        $this->profession = $client->profession ?? '';
        $this->profession2 = $client->profession2 ?? '';
        $this->profil_clients = $client->profil_clients ?? '';
        $this->projet = $client->projet ?? '';
        $this->signe = $client->signe ?? '';
        $this->commentaire = $client->debrief ?? '';
        $this->date_relance = $client->date_relance ? $client->date_relance->format('Y-m-d') : '';
        $this->commercial_id = $client->rentre_par ?? Auth::id();
        $this->partage_avec = $client->partage_avec ?? '';

        $this->showForm = true;
        $this->dispatch('formOpened');
    }

    public function closeClientForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->dispatch('formClosed');
    }

    public function setGpsCoordinates($lat, $lng)
    {
        $this->gps_lat = $lat;
        $this->gps_lng = $lng;
    }

    public function resetForm()
    {
        $this->reset([
            'clientId', 'nom', 'prenom', 'adresse', 'code_postal', 'ville',
            'telephone1', 'telephone2', 'mail', 'gps_lat', 'gps_lng',
            'genre', 'id_source', 'type', 'type_habitation', 'proprietaire',
            'profession', 'profession2', 'profil_clients', 'projet', 'signe',
            'commentaire', 'date_relance', 'partage_avec',
        ]);

        $this->secteur = session('selectedSecteur');
        $this->commercial_id = Auth::id();
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->isEditMode && $this->clientId) {
                $client = Client::find($this->clientId);
                if (! $client) {
                    session()->flash('error', 'Client non trouvé.');

                    return;
                }
            } else {
                $client = new Client;
                $client->rentre_le = now();
                $client->id_doublon = 0;
                $client->bloque = 0;
                $client->relance = false;
            }

            // Set client properties
            $client->nom = $this->nom;
            $client->prenom = $this->prenom;
            $client->adresse = $this->adresse;
            $client->cp = $this->code_postal;
            $client->ville = $this->ville;
            $client->telephone1 = $this->telephone1;
            $client->telephone2 = $this->telephone2 ?? '';
            $client->mail = $this->mail ?? '';
            $client->gps_lat = $this->gps_lat;
            $client->gps_lng = $this->gps_lng;
            $client->secteur = $this->secteur ?? session('selectedSecteur');
            $client->genre = $this->genre ?? 'Mr & Mme';
            $client->id_source = $this->id_source;
            $client->type = $this->type;
            $client->type_habitation = $this->type_habitation;
            $client->proprietaire = $this->proprietaire === 'oui' ? 1 : 0;
            $client->profession = $this->profession ?? '';
            $client->profession2 = $this->profession2 ?? '';
            $client->profil_clients = $this->profil_clients;
            $client->projet = $this->projet ?? '';
            $client->signe = $this->signe ?? '';
            $client->debrief = $this->commentaire ?? '';
            $client->date_relance = $this->date_relance ? Carbon::parse($this->date_relance) : null;
            $client->rentre_par = $this->commercial_id ?? Auth::id();
            $client->partage_avec = $this->partage_avec ?? null;

            // Set default values for required fields
            $client->dernier_investissement = $client->dernier_investissement ?? '';
            $client->mode_conso = $client->mode_conso ?? '';
            $client->conso_chauffage = $client->conso_chauffage ?? '';
            $client->credit_impot = $client->credit_impot ?? '';
            $client->surface = $client->surface ?? null;
            $client->type_chauffage = $client->type_chauffage ?? null;
            $client->duree_proprietaire = $client->duree_proprietaire ?? '';

            $client->save();

            // Record status change in history if updating type
            if ($this->isEditMode) {
                ClientHistory::create([
                    'client_id' => $client->id,
                    'commercial_id' => Auth::id(),
                    'status' => $client->type,
                ]);
            }

            $message = $this->isEditMode ? 'Client mis à jour avec succès.' : 'Client créé avec succès.';
            session()->flash('success', $message);

            // Dispatch events to update the map
            $this->dispatch('clientSaved', [
                'client' => $client->toArray(),
                'isNew' => ! $this->isEditMode,
            ]);

            $this->closeClientForm();

        } catch (\Exception $e) {
            Log::error('Error saving client: '.$e->getMessage(), [
                'clientId' => $this->clientId,
                'isEditMode' => $this->isEditMode,
                'exception' => $e,
            ]);

            session()->flash('error', 'Une erreur est survenue lors de la sauvegarde.');
        }
    }

    public function deleteClient()
    {
        if (! $this->isEditMode || ! $this->clientId) {
            session()->flash('error', 'Aucun client à supprimer.');

            return;
        }

        try {
            $client = Client::find($this->clientId);

            if (! $client) {
                session()->flash('error', 'Client non trouvé.');

                return;
            }

            $client->delete();

            session()->flash('success', 'Client supprimé avec succès.');

            $this->dispatch('clientDeleted', $this->clientId);
            $this->closeClientForm();

        } catch (\Exception $e) {
            Log::error('Error deleting client: '.$e->getMessage(), [
                'clientId' => $this->clientId,
                'exception' => $e,
            ]);

            session()->flash('error', 'Une erreur est survenue lors de la suppression.');
        }
    }

    public function render()
    {
        return view('livewire.client.client-form');
    }
}
