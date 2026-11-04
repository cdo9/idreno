<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use App\Models\Client;
use App\Models\User;
use App\Models\Poseur;
use App\Services\ProjectService;
use App\Services\SourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RendezVousController extends Controller
{
    /**
     * Display a listing of the rendez-vous.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */


     public function index(Request $request)
    {
        // Get filter parameters
        $commercial = $request->query('commercial');
        $client = $request->query('client');
        $dateStart = $request->query('date_start');
        $dateEnd = $request->query('date_end');

        // Start query
        $query = RendezVous::with(['client', 'user', 'secteur']);

        // Apply filters if provided
        if ($commercial) {
            $query->where('id_commercial', $commercial);
        }

        if ($client) {
            $query->where('id_client', $client);
        }

        if ($dateStart && $dateEnd) {
            $query->whereBetween('date', [$dateStart, $dateEnd]);
        }

        // Get results
        $rendezVous = $query->orderBy('date', 'desc')->paginate(15);

        // Get commercials for filter dropdown
        $commercials = User::where('active', 1)->get();

        // Check if it's an AJAX request
        if ($request->ajax()) {
            return response()->json($rendezVous);
        }

        // Return view with data
        return view('rendez_vous.index', [
            'rendezVous' => $rendezVous,
            'commercials' => $commercials,
            'filters' => [
                'commercial' => $commercial,
                'client' => $client,
                'date_start' => $dateStart,
                'date_end' => $dateEnd
            ]
        ]);
    }

    /**
     * Show the form for creating a new rendez-vous.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $groupes = \DB::table('groupes')
            ->join('users', 'groupes.id_responsable', '=', 'users.id')
            ->select('groupes.id', 'groupes.id_responsable', 'users.nom', 'users.prenom')
            ->get();

        // Get all active commerciaux for the "Partagé avec" field
        $commerciaux = \DB::table('users')
            ->select('id', 'nom', 'prenom', 'role', 'active')
            ->where('active', 1)
            ->get();

        $priorities = ProjectService::listPriorities();
        $sources = SourceService::listPriorities();
        $poseurs = \DB::table('poseurs')->select('id', 'poseur')->get();
        $users = \DB::table('users')->select('id', 'nom', 'prenom')->where('active', 1)->get();

        return view('rendez-vous.nouveau-rendez-vous', [
            'groupes' => $groupes,
            'commerciaux' => $commerciaux,
            'priorities' => $priorities,
            'sources' => $sources,
            'poseurs' => $poseurs,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created rendez-vous in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'id_client' => 'required|exists:clients,id',
            'id_commercial' => 'required|exists:users,id',
            'type' => 'required|string',
            'date' => 'required|date',
            'date_relance' => 'nullable|date',
            'commentaire' => 'nullable|string',
            'resultat' => 'nullable|string',
            'source' => 'nullable|string',
            'tarif_total_ht' => 'nullable|integer',
            'devis_total_ht' => 'nullable|integer',
            'produits' => 'nullable|string',
            'secteur' => 'nullable|exists:secteurs,id'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new rendez-vous
        $rendezVous = new RendezVous();
        $rendezVous->id_client = $request->id_client;
        $rendezVous->id_commercial = $request->id_commercial;
        $rendezVous->type = $request->type;
        $rendezVous->date = $request->date;
        $rendezVous->date_relance = $request->date_relance;
        $rendezVous->commentaire = $request->commentaire;
        $rendezVous->resultat = $request->resultat;
        $rendezVous->source = $request->source;
        $rendezVous->tarif_total_ht = $request->tarif_total_ht ?? 0;
        $rendezVous->devis_total_ht = $request->devis_total_ht ?? 0;
        $rendezVous->produits = $request->produits;
        $rendezVous->secteur = $request->secteur;
        $rendezVous->relance_confirme = 0;
        $rendezVous->save();

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Rendez-vous créé avec succès',
                'rendezVous' => $rendezVous
            ], 201);
        }

        return redirect()->route('rendez-vous.show', $rendezVous->id)
            ->with('success', 'Rendez-vous créé avec succès');
    }



    /**
     * Display the specified rendez-vous.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $rendezVous = RendezVous::with(['client', 'user', 'secteur', 'ventes'])
            ->findOrFail($id);

        if ($request->ajax()) {
            return response()->json($rendezVous);
        }

        return view('rendez_vous.show', [
            'rendezVous' => $rendezVous
        ]);
    }

    /**
     * Update the specified rendez-vous in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Find the rendez-vous
        $rendezVous = RendezVous::findOrFail($id);

        // Validate request data
        $validator = Validator::make($request->all(), [
            'id_client' => 'sometimes|required|exists:clients,id',
            'id_commercial' => 'sometimes|required|exists:users,id',
            'type' => 'sometimes|required|string',
            'date' => 'sometimes|required|date',
            'date_relance' => 'nullable|date',
            'commentaire' => 'nullable|string',
            'resultat' => 'nullable|string',
            'source' => 'nullable|string',
            'tarif_total_ht' => 'nullable|integer',
            'devis_total_ht' => 'nullable|integer',
            'produits' => 'nullable|string',
            'secteur' => 'nullable|exists:secteurs,id',
            'relance_confirme' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update rendez-vous with validated data
        $rendezVous->fill($request->only([
            'id_client', 'id_commercial', 'type', 'date', 'date_relance',
            'commentaire', 'resultat', 'source', 'tarif_total_ht',
            'devis_total_ht', 'produits', 'secteur', 'relance_confirme'
        ]));

        $rendezVous->save();

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Rendez-vous mis à jour avec succès',
                'rendezVous' => $rendezVous
            ]);
        }

        return redirect()->route('rendez-vous.show', $rendezVous->id)
            ->with('success', 'Rendez-vous mis à jour avec succès');
    }

    /**
     * Remove the specified rendez-vous from storage.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        $rendezVous = RendezVous::findOrFail($id);

        // Check if there are related sales
        if ($rendezVous->ventes->count() > 0) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Impossible de supprimer ce rendez-vous car il est lié à des ventes'
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Impossible de supprimer ce rendez-vous car il est lié à des ventes');
        }

        $rendezVous->delete();

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Rendez-vous supprimé avec succès'
            ]);
        }

        return redirect()->route('rendez-vous.index')
            ->with('success', 'Rendez-vous supprimé avec succès');
    }
}
