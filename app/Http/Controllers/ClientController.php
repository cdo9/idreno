<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Client;
use App\Models\ClientHistory;
use App\Models\Comment;
use App\Models\User;
use App\Services\ProjectService;
use App\Services\SourceService;
use App\Enums\Type;
use App\Models\Tracking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Models\Secteur;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    public function index()
    {
        // This method is not implemented yet
    }

    /**
     * Get autocomplete suggestions for clients by name, phone number, or email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autocomplete(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $query = $validated['query'];
        $queryTerms = explode(' ', $query);

        // Start building the query
        $clientsQuery = Client::select('clients.*')
            ->leftJoin('users as u1', 'u1.id', '=', 'clients.rentre_par')
            ->leftJoin('users as u2', 'u2.id', '=', 'clients.partage_avec');

        $currentUserId = auth()->id();

        if (!$currentUserId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        /*$clientsQuery->where(function ($q) use ($currentUserId) {
            $q->where('rentre_par', $currentUserId)
            ->orWhere(function ($q2) {
                $q2->where(function ($x) {
                    $x->whereNull('u1.id')->orWhere('u1.active', 0);
                })
                ->where(function ($x) {
                    $x->whereNull('u2.id')->orWhere('u2.active', 0);
                });
            });
        });*/

        $clientsQuery->where(function($q) use ($queryTerms, $query) {
            if (count($queryTerms) === 1) {
            $term = $queryTerms[0];
            $q->where('clients.nom', 'LIKE', "%{$term}%")
              ->orWhere('clients.prenom', 'LIKE', "%{$term}%")
              ->orWhere('telephone1', 'LIKE', "%{$query}%")
              ->orWhere('telephone2', 'LIKE', "%{$query}%")
              ->orWhere('mail', 'LIKE', "%{$query}%");
            } else {
            // If there are multiple terms, try different combinations
            $q->where(function($subq) use ($queryTerms) {
            $subq->where('clients.nom', 'LIKE', "%{$queryTerms[0]}%")
                ->where('clients.prenom', 'LIKE', "%{$queryTerms[1]}%");
            })
            ->orWhere(function($subq) use ($queryTerms) {
            $subq->where('clients.prenom', 'LIKE', "%{$queryTerms[0]}%")
             ->where('clients.nom', 'LIKE', "%{$queryTerms[1]}%");
            })
            ->orWhere('clients.nom', 'LIKE', "%{$query}%")
            ->orWhere('clients.prenom', 'LIKE', "%{$query}%")
            ->orWhere('telephone1', 'LIKE', "%{$query}%")
            ->orWhere('telephone2', 'LIKE', "%{$query}%")
            ->orWhere('mail', 'LIKE', "%{$query}%");
            }
        });

        $threeMonthsAgo = now()->subMonths(3);

        // Get clients that haven't had a sale in the last 3 months
        $clientsWithoutRecentSalesQuery = clone $clientsQuery;
        $clientsWithoutRecentSalesQuery->whereNotIn('id', function($query) use ($threeMonthsAgo) {
            $query->select('id_client')
                ->from('ventes')
                ->where('date', '>=', $threeMonthsAgo);
        });


        // Get the final results
        $clients = $clientsQuery
            ->whereNotNull('gps_lat')
            ->whereNotNull('gps_lng')
            ->orderBy('rentre_le', 'desc')
            ->get();

        // Format client data for autocomplete suggestions
        $suggestions = [];
        foreach ($clients as $client) {
            $suggestions[] = [
                'id' => $client->id,
                'text' => $client->nom . ' ' . $client->prenom . ' - ' . $client->adresse . ' - ' . $client->ville,
                'nom' => $client->nom,
                'prenom' => $client->prenom,
                'adresse' => $client->adresse,
                'ville' => $client->ville,
                'telephone' => $client->telephone1,
                'portable' => $client->telephone2,
                'mail' => $client->mail
            ];
        }

        // Return the suggestions as JSON
        return response()->json($suggestions);
    }

    /**
     * Search for clients by name, phone number, or email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'query' => 'required|string|min:2',
            'id' => 'nullable|integer'
        ]);

        $query = $validated['query'];
        $id = $validated['id'] ?? null;

        // If ID is provided, search for that specific client
        if ($id) {
            $clients = Client::where('id', $id)
                ->whereNotNull('gps_lat')
                ->whereNotNull('gps_lng')
                ->get();
        } else {
            // Otherwise, search for clients matching the query
            $clients = Client::where('nom', 'LIKE', "%{$query}%")
                ->orWhere('prenom', 'LIKE', "%{$query}%")
                ->orWhere('telephone1', 'LIKE', "%{$query}%")
                ->orWhere('telephone2', 'LIKE', "%{$query}%")
                ->orWhere('mail', 'LIKE', "%{$query}%")
                ->whereNotNull('gps_lat')
                ->whereNotNull('gps_lng')
                ->get();
        }

        // Format client data for the map
        $points = [];
        foreach ($clients as $client) {
            // Get the commercial who added the client
            $commercial = User::find($client->rentre_par);
            $commercialName = $commercial ? $commercial->prenom . ' ' . $commercial->nom : 'Ancien commercial';

            // Format the date when the client was added
            $addedDate = $client->rentre_le ? $client->rentre_le->format('d/m/Y') : 'Inconnue';

            $points[] = [
                'lat' => $client->gps_lat,
                'lng' => $client->gps_lng,
                'title' => $client->nom . ' ' . $client->prenom,
                'content' => '<div style="padding: 5px;">' .
                            '<strong>' . $client->nom . '</strong><br>' .
                            $client->adresse . '<br>' .
                            'Par: ' . $commercialName . '<br>' .
                            'Ajouté: ' . $addedDate . '<br>' .
                            '<a href="/clients/' . $client->id . '" style="color: blue; text-decoration: underline;">Voir la fiche</a>' .
                            (in_array($client->type, [0, 6, 7, 8]) ?
                            '<form id="updateTypeForm-' . $client->id . '" class="update-type-form" style="margin-top: 10px;">' .
                            '<select name="type" class="type-select" data-client-id="' . $client->id . '">' .
                            '<option value="0"' . ($client->type == 0 ? ' selected' : '') . '>Précontact</option>' .
                            '<option value="6"' . ($client->type == 6 ? ' selected' : '') . '>Refus</option>' .
                            '<option value="7"' . ($client->type == 7 ? ' selected' : '') . '>Absent</option>' .
                            '<option value="8"' . ($client->type == 8 ? ' selected' : '') . '>Relance</option>' .
                            '</select>' .
                            '</form>' : '') .
                            '</div>',
                'type' => $client->type, // Use client's actual type for marker color
                'relance' => $client->relance,
            ];
        }

        // Return the points as JSON
        return response()->json($points);
    }

    /**
     * Display the specified client.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\View\View|object
     */
    public function show($id, Request $request)
    {
        // Find the client
        $client = Client::findOrFail($id);

        // Get the commercial who added the client
        $commercial = User::find($client->rentre_par);

        // Récupère le secteur depuis la requête (GET ou POST)
        $secteurId = $request->input('secteur_id') ?? $request->query('secteur_id');

        // For now, we'll just return a simple view with client details
        // In a real implementation, you would create a proper view for client details
        return view('client.show', [
            'client' => $client,
            'commercial' => $commercial,
            'secteur_id' => $secteurId,
        ]);
    }

    /**
     * Update the client type
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public static function updateType(Request $request, $id)
    {
        // Validate the request data
        $validated = $request->validate([
            'type' => 'nullable|integer',
        ]);

        // Find the client
        $client = Client::findOrFail($id);

        // Update the client type
        if($client->type != 2 || !in_array($validated['type'], [7,6,3])){ // Si c'est un client on ne change pas son statut par annulé, absent ou refus

            if(in_array($client->type, [0,7,6,3]) ){ // Si le client n'est que annulé, absent, refus ou precontact alors le commercial qui change le statut le recupere
                $client->rentre_par = Auth()->id();
                $client->rentre_le = Carbon::now();
            }

            $client->type = $validated['type'];
            $client->save();
        }


        $trackings = $client->trackings()->latest('datetime')->first();
        if($trackings)
            $trackings->update(['datetime'=>Carbon::now()]);
        else{
            Tracking::create([
                'id_client' => $client->id,
                'id_commercial' => $client->rentre_par,
                'datetime' => Carbon::now(),
                'gps_lat' => $client->gps_lat,
                'gps_lng' => $client->gps_lng,
                'secteur' => $client->secteur
            ]);
        }

        // Record the status change in the ClientHistory table
        ClientHistory::create([
            'client_id' => $client->id,
            'commercial_id' => auth()->id(),
            'status' => $validated['type']
        ]);

        $client_types = config('client_types');

        Comment::create([
            'client_id' => $client->id,
            'user_id'=> auth()->id(),
            'date' => Carbon::now(),
            'commentaire' => "Passage au statut " . $client_types[$validated['type']]
        ]);

        // Return a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Type mis à jour avec succès',
            'client' => $client
        ]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:20',
            'ville' => 'required|string|max:255',
            'telephone1' => 'required|string|max:255',
            'signe' => 'nullable|string|max:255',
            'source' => 'required|integer|exists:sources,id',
            'projet' => 'nullable|string|max:255',
            'type' => 'required|integer',
            'choix' => 'required|string|max:255',
            'profil_client' => 'required|string|max:255',
            'type_habitation' => 'required|string|max:255',
            'proprietaire' => 'required|string|max:255',
            'gps_lat' => 'nullable|string',
            'gps_lng' => 'nullable|string',
            'user_lat' => 'nullable|string',
            'user_lng' => 'nullable|string',
            'secteur' => 'nullable|integer',
        ]);

        // Create a new client
        $client = new Client();
        $client->nom = $validated['nom'];
        $client->prenom = $validated['prenom'];
        $client->adresse = $validated['adresse'];
        $client->cp = $validated['code_postal'];
        $client->ville = $validated['ville'];
        $client->telephone1 = $validated['telephone1'];
        $client->signe = $validated['signe'];
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
        $client->telephone2 = ''; // Empty string for telephone2
        $client->mail = '';
        $client->rentre_par = auth()->id(); // Current authenticated user
        $client->partage_avec = null; // Default value (null to avoid foreign key constraint)
        $client->id_doublon = 0; // Default value
        $client->rentre_le = now(); // Current date and time
        $client->debrief = '';
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
        $client->secteur = session('selectedSecteur');

        // Save the client
        $client->save();

        // Check if the request is an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Client créé avec succès',
                'client' => $client
            ]);
        }
        toastr()->success('Data has been saved successfully!');
        // For non-AJAX requests, redirect back to the map with a success message
        return redirect()->route('map')->with('success', 'Client créé avec succès');
    }

    /**
     * Store a newly created client from the nouveau-contact form or update an existing one.
     */
    public function storeNouveauContact(Request $request)
    {
        // Check if we're adding a new comment to an existing client
        if ($request->has('ajouter_commentaire') && $request->has('client_id') && $request->has('nouveau_commentaire')) {
            // Validate the comment
            $request->validate([
                'client_id' => 'required|integer|exists:clients,id',
                'nouveau_commentaire' => 'required|string',
            ]);

            // Create a new comment
            DB::table('comments')->insert([
                'client_id' => $request->client_id,
                'user_id' => auth()->id(),
                'commentaire' => $request->nouveau_commentaire,
                'date' => now(),
            ]);

            toastr()->success('Le commentaire a été ajouté avec succès.');

            // Redirect back to the client's page
            //return redirect()->route('relances.contact.edit', ['clientId' => $request->client_id]);
            return redirect()->back();
        }

        // Regular form validation for client creation/update
        $validated = $request->validate([
            'choix' => 'nullable|string|max:50', // genre
            'source' => 'required|integer|exists:sources,id',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:512',
            'appart' => 'nullable|string|max:50',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            'telephone1' => 'nullable|string|max:16|regex:/^[0-9]+$/',
            'telephone2' => 'nullable|string|max:16|regex:/^[0-9]+$/',
            'commentaire' => 'nullable|string',
            'signe' => 'nullable|string',
            'projet' => 'nullable|string|max:100',
            'type' => 'required|integer',
            'type_habitation' => 'nullable|string|max:100',
            'proprietaire' => 'nullable|string|max:20',
            'profession_monsieur' => 'nullable|string|max:255',
            'profession_madame' => 'nullable|string|max:255',
            'date_relance' => 'nullable|date',
            'commercial' => 'nullable|integer|exists:users,id',
            'partage_avec' => 'nullable|integer|exists:users,id',
            'profil_client' => 'nullable|string|max:255',
            'profil_client_autre' => 'nullable|string|max:255',
            'secteur_id' => 'nullable|integer|exists:secteurs,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'gps_lat' => 'nullable|string',
            'gps_lng' => 'nullable|string',
            'user_lat' => 'nullable|string',
            'user_lng' => 'nullable|string',
            'secteur' => 'nullable|integer',
            'date_anniversaire' => 'nullable|date',
            'accept_demarchage' => 'required_if:type_habitation,Appartement|boolean',
        ]);

        // Check if we're updating an existing client
        if ($request->has('client_id')) {
            $client = \App\Models\Client::find($request->client_id);
            if (!$client) {
                // If client not found, create a new one
                $client = new \App\Models\Client();
                $client->rentre_le = now();
                $client->id_doublon = 0;
                $client->bloque = 0;
                $client->relance = false;
            }
        } else {
            // Create a new client
            $client = new \App\Models\Client();
            $client->rentre_le = now();
            $client->id_doublon = 0;
            $client->bloque = 0;
            $client->relance = false;
        }

        $client->genre = $validated['choix'] ?? null;
        $client->id_source = $validated['source'] ?? null;
        $client->nom = $validated['nom'] ?? '';
        $client->prenom = $validated['prenom'] ?? '';
        $client->adresse = $validated['adresse'] ?? '';
        $client->appart = $validated['appart'] ?? '';
        $client->cp = $validated['code_postal'];
        $client->ville = $validated['ville'];
        $client->telephone1 = $validated['telephone1'] ?? '';
        $client->telephone2 = $validated['telephone2'] ?? '';
        $client->mail = $client->mail ?? ''; // Keep existing email if any
        $client->rentre_par = $validated['commercial'] ?? auth()->id();
        $client->partage_avec = $validated['partage_avec'] ?? null;
        $client->debrief = ($validated['commentaire'] ?? '') . ($validated['signe'] ?? '');
        $client->type = $validated['type']; // Use the validated type from form
        $client->type_habitation = $validated['type_habitation'] ?? '';
        $client->profession = $validated['profession_monsieur'] ?? '';
        $client->profession2 = $validated['profession_madame'] ?? '';
        $client->dernier_investissement = $client->dernier_investissement ?? '';
        $client->mode_conso = $client->mode_conso ?? '';
        $client->conso_chauffage = $client->conso_chauffage ?? '';
        $client->credit_impot = $client->credit_impot ?? '';
        $client->surface = $client->surface ?? null;
        $client->type_chauffage = $client->type_chauffage ?? null;
        $client->proprietaire = $validated['proprietaire'] ?? 0;
        $client->duree_proprietaire = $client->duree_proprietaire ?? '';
        $client->projet = $validated['projet'] ?? '';
        $client->date_anniversaire = $validated['date_anniversaire'] ?? null;
        $client->accept_demarchage = $validated['accept_demarchage'] ?? false;
		
		// Store GPS coordinates for the client location
        if (isset($validated['gps_lat']) && isset($validated['gps_lng'])) {
            $client->gps_lat = $validated['gps_lat'];
            $client->gps_lng = $validated['gps_lng'];
        }

        // Champs optionnels ou NULL par défaut - keep existing values if any
        $client->secteur = $validated['secteur_id'] ?? $client->secteur ?? session('selectedSecteur');
        if(!$client->secteur){
            
            $secteur = Secteur::findByCoords($client->gps_lat, $client->gps_lng);
            $client->secteur = $secteur?->id ?? session('selectedSecteur');
            /*if($client->secteur == 8)
                $client->secteur = 5;
            if($client->secteur == 9)
                $client->secteur = 1;*/
        }
        
        $client->date_relance = $validated['date_relance'] ?? $client->date_relance;

        // Stockage unique dans 'profil_clients'
        if (empty($validated['profil_client'])) {
            $client->profil_clients = null;
        }else{
            $client->profil_clients = $validated['profil_client'] === 'Autre'
                ? $validated['profil_client_autre']
                : $validated['profil_client'];
        }

        // Override secteur with the one from form if provided
        /*if (isset($validated['secteur'])) {
            $client->secteur = $validated['secteur'];
        }*/

        // Check if a client already exists at the same address location
        $exists = DB::table('clients')
        ->where("adresse", $client->adresse)
        ->where("cp", $client->cp)
        ->where("ville", $client->ville)
        ->where("appart", $client->appart)
        ->first();
        if($exists && !$client->id){
            // Early return => DOUBLON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $request->has('client_id') ? 'Le contact a été mis à jour avec succès.' : 'Le contact a été créé avec succès.',
                    'client' => $exists->id,
                    'doublon' => true
                ]);
            }else{
                return redirect()
                ->back()
                ->with('error', 'Un client existe déjà à cette adresse.')
                ->withInput();
            }
        }

        // Check distance between author position and inserted point for alert creation
        if (isset($validated['user_lat'], $validated['user_lng'], $validated['gps_lat'], $validated['gps_lng'])) {
            $distance = $this->calculateDistance(
                $validated['user_lat'],
                $validated['user_lng'],
                $validated['gps_lat'],
                $validated['gps_lng']
            );

            // If distance is greater than 15 meters, create an alert
            if ($distance > 50) {
                Alert::create([
                    'message' => 'Distance importante détectée lors de l\'insertion d\'un client',
                    'type' => Type::MAP,
                    'id_commercial' => auth()->id(),
                    'details' => [
                        'distance_meters' => round((float) $distance, 2),
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
                            'adresse' => $validated['adresse'] ?? null,
                            'ville' => $validated['ville']
                        ],
                        'timestamp' => now()->toISOString()
                    ]
                ]);
            }

            if (! $request->has('client_id') && $distance > 200) {
                throw ValidationException::withMessages([
                    'gps_lat' => "Vous n'êtes pas à l'endroit où vous mappez",
                ]);
            }
        }        

        $client->save();

        if (!$request->has('client_id')) {
            Tracking::create([
                'id_client' => $client->id,
                'id_commercial' => $client->rentre_par,
                'datetime' => Carbon::now(),
                'gps_lat' => $client->gps_lat,
                'gps_lng' => $client->gps_lng,
                'secteur' => $client->secteur
            ]);
        }

        if ($request->boolean('create_rdv_after')) {
            return redirect()->route('nouveau-rendez-vous', [
                'id_client' => $client->id,
                'success' => 'Contact créé ! Prêt pour le RDV.'
            ]);
        }

        // Check if the request is an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $request->has('client_id') ? 'Le contact a été mis à jour avec succès.' : 'Le contact a été créé avec succès.',
                'client' => $client
            ]);
        }

        if ($request->has('client_id')) {
            toastr()->success('Le contact a été mis à jour avec succès.');
        } else {
            toastr()->success('Le contact a été créé avec succès.');
        }

        return redirect()->to(session('return_url', url()->previous()));
    }

    /**
     * Calculate distance between two geographical points using Haversine formula.
     * Returns distance in meters.
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        // Convert coordinates to floats
        $lat1 = floatval($lat1);
        $lng1 = floatval($lng1);
        $lat2 = floatval($lat2);
        $lng2 = floatval($lng2);

        // Earth's radius in meters
        $earthRadius = 6371000;

        // Convert degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLatRad = deg2rad($lat2 - $lat1);
        $deltaLngRad = deg2rad($lng2 - $lng1);

        // Haversine formula
        $a = sin($deltaLatRad / 2) * sin($deltaLatRad / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLngRad / 2) * sin($deltaLngRad / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Distance in meters
        return $earthRadius * $c;
    }

    /**
     * Display the form for creating a new client.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $priorities = ProjectService::listPriorities();
        $sources = SourceService::createSourceLst();
        $secteurs = DB::table('secteurs')->select('id', 'nom')->get();
        $users = User::join('secteur_commercials', 'users.id', '=', 'secteur_commercials.id_commercial')
            ->where('secteur_commercials.id_secteur', session('selectedSecteur'))
            ->whereNotIn('role',["administratif"])
            ->where('active', 1)
            ->get(['users.id', 'users.prenom', 'users.nom', 'users.active']);
            
        // Get all users for the "Partagé avec" dropdown
        //$allUsers = DB::table('users')->select('id', 'nom', 'prenom')->where("active",1)->where("secteur", session('selectedSecteur'))->get();
        $groupes = DB::table('groupes')
            ->join('users', 'groupes.id_responsable', '=', 'users.id')
            ->select('groupes.id', 'groupes.id_responsable', 'users.nom', 'users.prenom')
            ->get();

        // Check if client_id is provided
        $client = null;
        $comments = [];

        if ($request->has('client_id')) {
            $client = Client::find($request->client_id);
            //dd($client);
            if ($client) {
                // Load comments for this client
                $comments = DB::table('comments')
                    ->join('users', 'comments.user_id', '=', 'users.id')
                    ->where('client_id', $client->id)
                    ->select('comments.*', 'users.nom as client_nom', 'users.prenom as client_prenom')
                    ->orderBy('date', 'desc')
                    ->get();
            }
        }
		
		if (url()->previous() !== url()->current()) {
            session(['return_url' => url()->previous()]);
        }

        return view('contact.nouveau-contact', [
            'priorities' => $priorities,
            'sources' => $sources,
            'secteurs' => $secteurs,
            'users' => $users,
            /*'allUsers' => $allUsers,*/
            'groupes' => $groupes,
            'comments' => $comments,
            'nextNov20' => $this->nextNov20(),
            'client' => $client,
        ]);
    }

    public static function nextNov20() {
        $date = new DateTime('November 20');
        if ($date < new DateTime()) {
            $date->modify('+1 year');
        }
        return $date->format('Y-m-d');
    }
}
