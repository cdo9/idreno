<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Alert;
use App\Enums\Type;
use App\Services\ProjectService;
use Illuminate\Support\Facades\Auth;

class MapContactForm extends Component
{
    // Form properties
    public $nom = '';
    public $prenom = '';
    public $adresse = '';
    public $code_postal = '';
    public $ville = '';
    public $telephone1 = '';
    public $telephone2 = '';
    public $signe = '';
    public $source = '';
    public $projet = '';
    public $type = '';
    public $choix = '';
    public $profil_client = '';
    public $type_habitation = '';
    public $proprietaire = '';
    public $gps_lat = '';
    public $gps_lng = '';
    public $user_lat = '';
    public $user_lng = '';
    public $secteur = '';

    // Component state
    public $showForm = false;
    public $isSubmitting = false;

    protected $rules = [
        'nom' => 'nullable|string|max:255',
        'prenom' => 'string|max:255',
        'adresse' => 'required|string|max:255',
        'code_postal' => 'required|string|max:20',
        'ville' => 'required|string|max:255',
        'telephone1' => 'string|max:255',
        'signe' => 'nullable|string|max:255',
        'source' => 'required|integer|exists:sources,id',
        'projet' => 'string|max:255',
        'type' => 'required|integer',
        'choix' => 'string|max:255',
        'profil_client' => 'string|max:255',
        'type_habitation' => 'string|max:255',
        'proprietaire' => 'string|max:255',
        'gps_lat' => 'nullable|string',
        'gps_lng' => 'nullable|string',
        'user_lat' => 'nullable|string',
        'user_lng' => 'nullable|string',
        'secteur' => 'nullable|integer',
    ];

    protected $messages = [
        'prenom.required' => 'Le prénom est obligatoire.',
        'adresse.required' => 'L\'adresse est obligatoire.',
        'code_postal.required' => 'Le code postal est obligatoire.',
        'ville.required' => 'La ville est obligatoire.',
        'telephone1.required' => 'Le téléphone principal est obligatoire.',
        'source.required' => 'La source est obligatoire.',
        'type.required' => 'Le type est obligatoire.',
        'choix.required' => 'Le genre est obligatoire.',
        'profil_client.required' => 'Le profil client est obligatoire.',
        'type_habitation.required' => 'Le type d\'habitation est obligatoire.',
        'proprietaire.required' => 'Le statut propriétaire est obligatoire.',
    ];

    protected $listeners = ['openContactForm'];

    public function mount()
    {
        // Initialize with default values if needed
    }

    public function openContactForm($data = [])
    {
        $this->resetForm();

        // Set GPS coordinates
        $this->gps_lat = $data['gps_lat'] ?? '';
        $this->gps_lng = $data['gps_lng'] ?? '';
        $this->user_lat = $data['user_lat'] ?? '';
        $this->user_lng = $data['user_lng'] ?? '';
        $this->secteur = $data['secteur'] ?? '';

        // Pre-fill address information if available
        $this->adresse = $data['adresse'] ?? '';
        $this->code_postal = $data['code_postal'] ?? '';
        $this->ville = $data['ville'] ?? '';

        $this->showForm = true;
    }

    public function openForm($gpsLat = '', $gpsLng = '')
    {
        $this->resetForm();
        $this->gps_lat = $gpsLat;
        $this->gps_lng = $gpsLng;
        $this->showForm = true;
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();

        // Emit event to notify JavaScript that form is closed
        $this->dispatch('formClosed');
    }

    public function resetForm()
    {
        $this->reset([
            'nom', 'prenom', 'adresse', 'code_postal', 'ville',
            'telephone1', 'telephone2', 'signe', 'source', 'projet',
            'type', 'choix', 'profil_client', 'type_habitation',
            'proprietaire', 'gps_lat', 'gps_lng', 'user_lat', 'user_lng', 'secteur'
        ]);
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->isSubmitting = true;

        try {
            // Validate the form data
            $validated = $this->validate();

            // Create a new client
            $client = new Client();
            $client->nom = $validated['nom'];
            $client->prenom = $validated['prenom'];
            $client->adresse = $validated['adresse'];
            $client->cp = $validated['code_postal'];
            $client->ville = $validated['ville'];
            $client->telephone1 = $validated['telephone1'];
            $client->telephone2 = $validated['telephone2'] ?? '';
            $client->debrief = $validated['signe'];
            $client->id_source = $validated['source'];
            $client->projet = $validated['projet'];
            $client->type = $validated['type'];
            $client->genre = $validated['choix'];
            $client->profil_clients = $validated['profil_client'];
            $client->type_habitation = $validated['type_habitation'];
            $client->proprietaire = $validated['proprietaire'] === 'oui' ? 1 : 0;
            $client->gps_lat = $validated['gps_lat'];
            $client->gps_lng = $validated['gps_lng'];
            $client->relance = false;

            // Set default values for required fields that aren't in the form
            $client->mail = '';
            $client->rentre_par = Auth::id();
            $client->partage_avec = null;
            $client->id_doublon = 0;
            $client->rentre_le = now();
            $client->profession = '';
            $client->profession2 = '';
            $client->dernier_investissement = '';
            $client->mode_conso = '';
            $client->conso_chauffage = '';
            $client->credit_impot = '';
            $client->surface = null;
            $client->type_chauffage = null;
            $client->duree_proprietaire = '';
            $client->bloque = 0;
            $client->secteur = $validated['secteur'] ?? 0;

            // Check distance between author position and inserted point for alert creation
            if (!empty($validated['user_lat']) && !empty($validated['user_lng']) &&
                !empty($validated['gps_lat']) && !empty($validated['gps_lng'])) {
                $distance = $this->calculateDistance(
                    $validated['user_lat'],
                    $validated['user_lng'],
                    $validated['gps_lat'],
                    $validated['gps_lng']
                );

                // If distance is greater than 15 meters, create an alert
                if ($distance > 15) {
                    Alert::create([
                        'message' => 'Distance importante détectée lors de l\'insertion d\'un client',
                        'type' => Type::MAP,
                        'id_commercial' => Auth::id(),
                        'details' => [
                            'distance_meters' => (float) round($distance, 2),
                            'author_position' => [
                                'latitude' => floatval($validated['user_lat']),
                                'longitude' => floatval($validated['user_lng'])
                            ],
                            'inserted_point' => [
                                'latitude' => floatval($validated['gps_lat']),
                                'longitude' => floatval($validated['gps_lng'])
                            ],
                            'client_info' => [
                                'nom' => $validated['nom'],
                                'prenom' => $validated['prenom'],
                                'adresse' => $validated['adresse'],
                                'ville' => $validated['ville']
                            ],
                            'timestamp' => now()->toISOString()
                        ]
                    ]);
                }
            }

            // Save the client
            $client->save();

            // Close the form and reset
            $this->closeForm();

            // Emit event to refresh map markers
            $this->dispatch('clientCreated', [
                'message' => 'Client créé avec succès',
                'client' => $client->toArray()
            ]);

            session()->flash('success', 'Client créé avec succès');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors are automatically handled by Livewire
            $this->isSubmitting = false;
        } catch (\Exception $e) {
            $this->isSubmitting = false;
            $this->addError('general', 'Une erreur est survenue lors de la création du client.');
        }

        $this->isSubmitting = false;
    }

    /**
     * Calculate distance between two geographical points using Haversine formula.
     * Returns distance in meters.
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;

        $a = sin($deltaLat / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function getSourcesProperty()
    {
        return \App\Models\Source::orderBy('nom')->get();
    }

    public function getPrioritiesProperty()
    {
        return ProjectService::listPriorities();
    }

    public function render()
    {
        return view('livewire.map-contact-form');
    }
}
