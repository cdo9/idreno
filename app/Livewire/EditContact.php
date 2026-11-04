<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Source;
use App\Models\User;
use App\Services\ProjectService;
use App\Utils\MailVerificator;
use App\Utils\DataNormalizer;
use App\Utils\PhoneFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class EditContact extends Component
{
    public $clientId;
    public $client;
    public $nom;
    public $prenom;
    public $genre;
    public $date_anniversaire;
    public $profession;
    public $profession2;
    public $signe;
    public $telephone1;
    public $telephone2;
    public $email;
    public $searchAdress = [
        'address_string' => '',
        'place_id' => ''
    ];
    public $adressesAutoComplete = [];
    public $adresse;
    public $cp;
    public $ville;
    public $gps_lat;
    public $gps_lng;
    public $id_source;
    public $type_hab;
    public $surface;
    public $nb_foyer;
    public $proprietaire;
    public $duree_proprietaire;
    public $isolation;
    public $type_chauffage;
    public $mode_conso;
    public $conso_chauffage;
    public $conso_chauffage_qualite;
    public $eau_chaude;
    public $age_materiel;
    public $projet;
    public $dernier_investissement;
    public $credit_impot;
    public $date_relance;
    public $debrief;
    public $rentre_par;
    public $partage_avec;
    public $comments;
    
    public $commerciaux = [];

    protected $rules = [
        'nom' => 'required|string|max:255',
        'prenom' => 'nullable|string|max:255',
        'adresse' => 'required|string',
        'cp' => 'required|string|max:10',
        'ville' => 'required|string|max:255',
        'telephone1' => 'required|phone:FR',
        'telephone2' => 'nullable|phone:FR',
        'email' => 'nullable|email',
        'genre' => 'nullable|string',
        'id_source' => 'nullable|int',
        'rentre_par' => 'nullable|int',
        'partage_avec' => 'nullable|int',
        'type_hab' => 'nullable|string',
        'surface' => 'nullable|numeric',
        'proprietaire' => 'nullable|bool',
        'duree_proprietaire' => 'nullable|string',
        'date_anniversaire' => 'nullable|date',
        'profession' => 'nullable|string',
        'profession2' => 'nullable|string',
        'dernier_investissement' => 'nullable|string',
        'credit_impot' => 'nullable|string',
        'date_relance' => 'required|date',
        'debrief' => 'string',
        'signe' => 'nullable|string',
        'projet' => 'nullable|string',
        'isolation' => 'nullable|string',
        'nb_foyer' => 'nullable|string',
        'type_chauffage' => 'nullable|string',
        'mode_conso' => 'nullable|string',
        'conso_chauffage_qualite' => 'nullable|string',
        'conso_chauffage' => 'nullable|string',
        'eau_chaude' => 'nullable|string',
        'age_materiel' => 'nullable|string',
        'gps_lat' => 'nullable|numeric',
        'gps_lng' => 'nullable|numeric',
    ];

    protected $messages = [
        'telephone1.required' => 'Le numéro de téléphone est obligatoire.',
        'telephone1.phone' => 'Le numéro de téléphone n\'est pas valide. Utilisez un format français.',
        'telephone2.phone' => 'Le numéro de telephone n\'est pas valide. Utilisez un format français.',
        'email.email' => 'L\'adresse email n\'est pas valide.',
        'nom.required' => 'Le nom est obligatoire.',
        'adresse.required' => 'L\'adresse est obligatoire.',
        'cp.required' => 'Le code postal est obligatoire.',
        'ville.required' => 'La ville est obligatoire.',
    ];

    public function mount($clientId)
    {
        if (url()->previous() !== url()->current()) {
            session(['return_url' => url()->previous()]);
        }
        $this->clientId = $clientId;
        $this->client = Client::findOrFail($clientId);
        
        $this->checkPermissions();
        
        $this->loadClientData();
        
        // Charger les commerciaux pour les directeurs ou les admin
        if (Auth::user()->role === 'directeur' || Auth::user()->role === 'admin') {
            $this->commerciaux = User::where('active', 1)
                ->orWhere('id', $this->client->rentre_par)
                ->orWhere('id', $this->client->partage_avec)
                ->get();
        }
    }

    private function checkPermissions()
    {
        $userRole = Auth::user()->role;
        if (!in_array($userRole, ['directeur', 'admin'])) {
            $currentUserId = Auth::user()->id;
            if ($this->client->rentre_par !== $currentUserId && $this->client->partage_avec !== $currentUserId) {
                abort(403, 'Vous n\'avez pas les permissions pour éditer ce contact.');
            }
        }
    }

    private function loadClientData()
    {
        $this->nom = $this->client->nom;
        $this->prenom = $this->client->prenom;
        $this->genre = $this->client->genre;
        $this->date_anniversaire = $this->client->date_anniversaire ? $this->client->date_anniversaire->format('Y-m-d') : null;
        $this->profession = $this->client->profession;
        $this->profession2 = $this->client->profession2;
        $this->signe = $this->client->signe;
        $this->telephone1 = PhoneFormatter::formatForDisplay($this->client->telephone1);
        $this->telephone2 = PhoneFormatter::formatForDisplay($this->client->telephone2);
        $this->email = $this->client->mail;
        $this->adresse = $this->client->adresse;
        $this->searchAdress = [
            "address_string" => $this->client->adresse,
            "place_id" => ""
        ];
        $this->cp = $this->client->cp;
        $this->ville = $this->client->ville;
        $this->gps_lat = $this->client->gps_lat;
        $this->gps_lng = $this->client->gps_lng;
        $this->id_source = $this->client->id_source;
        $this->rentre_par = $this->client->rentre_par;
        $this->partage_avec = $this->client->partage_avec;
        $this->type_hab = $this->client->type_habitation;
        $this->surface = $this->client->surface;
        $this->nb_foyer = $this->client->nb_foyer;
        $this->proprietaire = $this->client->proprietaire;
        $this->duree_proprietaire = $this->client->duree_proprietaire;
        $this->isolation = $this->client->isolation;
        $this->type_chauffage = $this->client->type_chauffage;
        $this->mode_conso = $this->client->mode_conso;
        $this->conso_chauffage = $this->client->conso_chauffage;
        $this->conso_chauffage_qualite = $this->client->conso_chauffage_qualite;
        $this->eau_chaude = $this->client->eau_chaude;
        $this->age_materiel = $this->client->age_materiel;
        $this->projet = $this->client->projet;
        $this->dernier_investissement = $this->client->dernier_investissement;
        $this->credit_impot = $this->client->credit_impot;
        $this->date_relance = $this->client->date_relance ? $this->client->date_relance->format('Y-m-d') : null;
        $this->debrief = $this->client->debrief;

        $this->comments = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->where('client_id', $this->clientId)
            ->select('comments.*', 'users.nom as client_nom', 'users.prenom as client_prenom')
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        Log::info('Loaded client data for editing', [
            'client_id' => $this->clientId,
            'data' => $this->client->toArray()
        ]);
    }


    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);

        if ($propertyName === 'searchAdress.address_string') {
            if ($this->searchAdress && isset($this->searchAdress['address_string']) && !empty($this->searchAdress['address_string']) && empty($this->searchAdress['place_id'])) {
                
                $response = Http::withHeaders([
                'X-Goog-Api-Key' => config('services.google.maps_api_key'),
                'Content-Type' => 'application/json'
                ])->post("https://places.googleapis.com/v1/places:autocomplete", [
                    'input' => $this->searchAdress['address_string'],
                    'locationRestriction' => [ // Restriction à la France
                        'rectangle' => [
                            'low' => ['latitude' => 40.0, 'longitude' => -6.0],
                            'high' => ['latitude' => 52.0, 'longitude' => 10.0],
                        ],
                    ],
                    'includedPrimaryTypes' => ['street_address', 'geocode'],
                ]);
                
                $results = $response->json();
            
                if (isset($results['suggestions']) && count($results['suggestions']) > 0) {
                    $this->adressesAutoComplete = array_map(function ($result) {
                        return [
                            'address_string' => $result['placePrediction']['text']['text'],
                            'place_id' => $result['placePrediction']['placeId']
                        ];
                    }, $results['suggestions']);
                }
            } elseif ($this->searchAdress && isset($this->searchAdress['place_id']) && !empty($this->searchAdress['place_id'])) {
                $response = Http::get("https://places.googleapis.com/v1/places/" . $this->searchAdress['place_id'] . "?fields=id,displayName,formattedAddress,addressComponents,location&key=" . config('services.google.maps_api_key'));
                $result = $response->json();

                Log::info('Google Maps Place result API response: ', ['data' => $result]);
                if (isset($result) && count($result) > 0) {

                    $streetNumber = '';
                    $route = '';
                    
                    if (isset($result['addressComponents'])) {
                        foreach ($result['addressComponents'] as $component) {
                            if (in_array('street_number', $component['types'])) {
                                $streetNumber = $component['longText'];
                            }
                            if (in_array('route', $component['types'])) {
                                $route = $component['longText'];
                            }
                            if (in_array('postal_code', $component['types'])) {
                                $this->cp = $component['longText'];
                            }
                            if (in_array('locality', $component['types'])) {
                                $this->ville = $component['longText'];
                            }
                        }
                    }
                    
                    $this->adresse = trim($streetNumber . ' ' . $route);
                    
                    if (isset($result['location'])) {
                        $this->gps_lat = $result['location']['latitude'];
                        $this->gps_lng = $result['location']['longitude'];
                        $this->searchAdress['place_id'] = null;
                    }
                }

                $this->adressesAutoComplete = [];
                return;
            }
        }
    }

    public function selectAdress($index)
    {
        if (isset($this->adressesAutoComplete[$index])) {
            $selectedAddress = $this->adressesAutoComplete[$index];
            
            $this->searchAdress = [
                'address_string' => $selectedAddress['address_string'],
                'place_id' => $selectedAddress['place_id']
            ];
                        
            $this->updated('searchAdress.address_string');
        }
    }

    public function save()
    {
        Log::info('=== SAVING CONTACT ===');
        
        $this->validate();

        if (!empty($this->email) && !MailVerificator::isValidEmail($this->email)) {
            MailVerificator::logInvalidEmail($this->email);
            $this->addError('email', 'L\'adresse email n\'est pas valide.');
            return;
        }
        
        if (!empty($this->genre) && !Client::isValidCivilite($this->genre)) {
            Log::error('Invalid civilite selected: ' . $this->genre);
            $this->addError('genre', 'La civilité sélectionnée n\'est pas valide.');
            return;
        }
        
        // Nettoyer et formater les numéros de téléphone
        $telephone1 = PhoneFormatter::formatForStorage($this->telephone1);
        $telephone2 = PhoneFormatter::formatForStorage($this->telephone2);
        
        $updateData = [
            'nom' => $this->nom,
            'prenom' => $this->prenom ?? '',
            'adresse' => $this->adresse,
            'cp' => $this->cp,
            'ville' => $this->ville,
            'gps_lat' => $this->gps_lat,
            'gps_lng' => $this->gps_lng,
            'telephone1' => $telephone1,
            'telephone2' => $telephone2,
            'mail' => $this->email,
            'debrief' => $this->debrief,
            'genre' => $this->genre,
            'type_habitation' => $this->type_hab,
            'profession' => $this->profession,
            'profession2' => $this->profession2,
            'dernier_investissement' => $this->dernier_investissement,
            'credit_impot' => $this->credit_impot,
            'surface' => $this->surface,
            'proprietaire' => $this->proprietaire,
            'duree_proprietaire' => $this->duree_proprietaire,
            'date_relance' => $this->date_relance,
            'date_anniversaire' => $this->date_anniversaire,
            'id_source' => $this->id_source,
            'rentre_par' => $this->rentre_par,
            'partage_avec' => $this->partage_avec,
            'signe' => $this->signe,
            'projet' => $this->projet,
            'isolation' => $this->isolation,
            'nb_foyer' => $this->nb_foyer,
            'type_chauffage' => $this->type_chauffage,
            'mode_conso' => $this->mode_conso,
            'conso_chauffage_qualite' => $this->conso_chauffage_qualite,
            'conso_chauffage' => $this->conso_chauffage,
            'eau_chaude' => $this->eau_chaude,
            'age_materiel' => $this->age_materiel,
            'updated_at' => now()
        ];

        //dd($updateData);


        Log::info('Updating client with data: ', ['data' => $updateData]);
        
        // Mettre à null tous les champs vides
        //$updateData = DataNormalizer::normalizeEmptyValues($updateData);
        $updateData['debrief'] = $this->debrief ?? '';
        $updateData['mode_conso'] = $this->debrief ?? '';

        // Gestion des commerciaux (seulement pour les directeurs et les admin)
        if (Auth::user()->role === 'directeur' || Auth::user()->role === 'admin') {
            if (empty($this->rentre_par)) {
                $this->rentre_par = null;
            }

            if (empty($this->partage_avec)) {
                $this->partage_avec = null;
            }

            $updateData['rentre_par'] = $this->rentre_par;
            $updateData['partage_avec'] = $this->partage_avec;
            
        } else {
            // Pour les autres rôles, on garde les valeurs existantes
            $updateData['rentre_par'] = $this->client->rentre_par;
            $updateData['partage_avec'] = $this->client->partage_avec;
        }
        
        try {
            Log::info('Attempting to update client with ID: ' . $this->client->id);
            $this->client->fill($updateData);
            $result = $this->client->save();
            Log::info('Update result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            
            if ($result) {
                Log::info('Contact updated successfully');
                session()->flash('success', 'Contact mis à jour avec succès.');

                // refresh
                return redirect()->to(session('return_url', url()->previous()));
            } else {
                Log::error('Update returned false');
                $this->addError('general', 'La mise à jour a échoué.');
            }
            
        } catch (\Exception $e) {
            Log::error('Exception during update: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->addError('general', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    public function getFormattedTelephone1Property()
    {
        return PhoneFormatter::formatForDisplay($this->telephone1);
    }

    public function getFormattedTelephone2Property()
    {
        return PhoneFormatter::formatForDisplay($this->telephone2);
    }

    public function getCivilitesProperty()
    {
        return Client::getCivilites();
    }

    public function getSourcesProperty()
    {
        return Source::query()->get();
    }

    public function getProjectsProperty()
    {
        return ProjectService::listPriorities();
    }

    public function getTypeHabitationsProperty()
    {
        return Client::getTypeHabitations();
    }

    public function getNbFoyerProperty()
    {
        return Client::getNbPersonnesFoyer();
    }

    public function getConsoChauffageAnnuelleProperty()
    {
        return Client::getConsoChauffageAnnuelle();
    }

    public function getProductionEauChaudeProperty()
    {
        return Client::getProductionEauChaude();
    }

    public function getNiveauxIsolationProperty()
    {
        return Client::getIsolation();
    }

    public function getTypesChauffageProperty()
    {
        return Client::getChauffageType();
    }

    public function getListeAgeMaterielProperty()
    {
        return Client::getAgeMateriel();
    }

    // Ajoute une méthode calculant l'âge pour le front
    public function getAgeProperty()
    {
        if ($this->date_anniversaire) {
            $age = Client::getAgeFromDate($this->date_anniversaire);
            return $age;
        }
        return null;
    }

    public function render()
    {
        return view('livewire.edit-contact');
    }
}
