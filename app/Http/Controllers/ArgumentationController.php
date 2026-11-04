<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\RendezVous;
use App\Models\Vente;
use App\Services\ProjectService;
use App\Services\SourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArgumentationController extends Controller
{
    public function index()
    {

    }

    public function create()
    {
        $groupes = DB::table('groupes')
            ->join('users', 'groupes.id_responsable', '=', 'users.id')
            ->select('groupes.id', 'groupes.id_responsable', 'users.nom', 'users.prenom')
            ->get();

        // Get all active commerciaux for the "Partagé avec" field
        $commerciaux = DB::table('users')
            ->select('id', 'nom', 'prenom', 'role', 'active', 'directeur', 'telepro')
            ->where('active', 1)
            ->get();

        // Get all clients
        $clients = DB::table('clients')
            ->select('id', 'nom', 'prenom', 'adresse', 'ville', 'cp', 'telephone1', 'mail', 'date_anniversaire')
            ->get();

        $priorities = ProjectService::listPriorities();
        $sources = SourceService::createSourceLst();
        $poseurs = DB::table('poseurs')->select('id', 'poseur')->get();
        $users = DB::table('users')->select('id', 'nom', 'prenom')->where('active', 1)->get();

        return view('argumentation.nouveau', [
            'groupes' => $groupes,
            'commerciaux' => $commerciaux,
            'priorities' => $priorities,
            'sources' => $sources,
            'poseurs' => $poseurs,
            'users' => $users,
            'clients' => $clients,
        ]);
    }

    /**
     * Store a newly created argumentation in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->merge([
            'id_commercial_2' => $request->input('id_commercial_2') ?: null,
            'id_commercial_3' => $request->input('id_commercial_3') ?: null,
        ]);
        // Validation des données du formulaire
        $validated = $request->validate([
            'id_rdv' => 'nullable|integer',
            'id_client' => 'required|exists:clients,id',
            'email' => 'nullable|email',
            'date' => 'required|date',
            'id_commercial' => 'required|exists:users,id',
            'type-vente' => 'nullable|string',
            'id_commercial_2' => 'nullable|exists:users,id',
            'vente-ok' => 'required|in:ok,nok',
            'date-relance' => 'nullable|date',
            'complement' => 'nullable|string',
            'ca-total-ht' => 'required|numeric',
            'tarif-total-ht' => 'required|numeric',
            'ca-ht' => 'nullable|numeric',
            'tarif-ht' => 'nullable|numeric',
            'prix-vente' => 'nullable|numeric',
            'remise' => 'nullable|numeric',
            'repartition' => 'nullable|numeric',
            'repartition_2' => 'nullable|numeric',
            'repartition_3' => 'nullable|numeric',
            'id_commercial_3' => 'nullable|exists:users,id',
            'date-anniversaire' => 'nullable|date',
            'commande' => 'nullable|in:0,1',
            'attente' => 'nullable|array',
            'attente_autre' => 'nullable|string',
            'produits' => 'nullable|array',
            'paiement' => 'nullable|string',
            'raison' => 'nullable|string',
        ]);

        // Mise à jour de l'email du client si fourni
        if (!empty($validated['email'])) {
            $client = Client::find($validated['id_client']);
            if ($client) {
                $client->mail = $validated['email'];
                $client->save();
            }
        }

        // Création ou mise à jour du rendez-vous
        $mode = $request->input('id_rdv') ? 'modification' : 'creation';

        if ($mode == 'modification') {
            $rdv = RendezVous::find($request->input('id_rdv'));
        } else {
            $rdv = new RendezVous();
        }

        $rdv->id_commercial = $validated['id_commercial'];
        $rdv->id_client = $validated['id_client'];
        $rdv->resultat = $validated['vente-ok'] == 'ok' ? 'Vendu' : 'Non vendu';
        $rdv->commentaire = $validated['raison'] ?? '';

        // Déterminer le second commercial (priorité à id_commercial_2 s'il est fourni)
        $secondCommercialId = 0;
        if (isset($validated['id_commercial_2']) && $validated['id_commercial_2'] > 0) {
            $secondCommercialId = $validated['id_commercial_2'];
        } elseif (isset($validated['type-vente']) && $validated['type-vente'] > 0) {
            $secondCommercialId = $validated['type-vente'];
        }
        $rdv->type = $secondCommercialId;

        $rdv->date = $validated['date'];
        $rdv->date_relance = $validated['date-relance'] ?? null;
        $rdv->relance_confirme = 0;
        $rdv->source = '';
        $rdv->tarif_total_ht = $validated['tarif-total-ht'];
        $rdv->devis_total_ht = $validated['ca-total-ht'];
        $rdv->produits = implode(';', $validated['produits'] ?? []);
        $rdv->secteur = auth()->user()->secteur_id ?? 1;

        $rdv->save();

        // Si vente réalisée, création ou mise à jour des ventes
        if ($validated['vente-ok'] == 'ok') {
            // Récupération des ventes existantes
            $ventes = Vente::where('id_rdv', $rdv->id)->orderBy('id', 'asc')->get();

            // Première vente (toujours présente)
            if (count($ventes) > 0) {
                $vente = $ventes[0];
            } else {
                $vente = new Vente();
                $vente->id_rdv = $rdv->id;
            }

            $vente->id_client = $rdv->id_client;
            $vente->id_commercial = $rdv->id_commercial;
            $vente->tarif_total_ht = $rdv->tarif_total_ht;
            $vente->ca_total_ht = $rdv->devis_total_ht;
            $vente->repartition = $validated['repartition'] ?? 100;
            $vente->tarif_ht = $validated['tarif-ht'] ?? 0;
            $vente->ca_ht = $validated['ca-ht'] ?? 0;
            $vente->remise = $validated['remise'] ?? 0;
            $vente->montant = 0;
            $vente->plus_value = 0;
            $vente->prime_cee = 0;
            $vente->date = $rdv->date;
            $vente->paiement = $validated['paiement'] ?? 'Comptant';
            $vente->type_vente = '';
            $vente->statut = 1;
            $vente->complement = $validated['complement'] ?? '';

            // Gestion des attentes
            $attentes = [];
            if (isset($validated['attente'])) {
                foreach ($validated['attente'] as $key => $value) {
                    $attentes[] = $key;
                }
            }
            $vente->attente = implode(';', $attentes);
            $vente->attente_autre = $validated['attente_autre'] ?? '';

            $vente->commande = $validated['commande'] ?? 0;
            $vente->secteur = $rdv->secteur;

            $vente->save();

            // Deuxième vente (si commercial 2 et repartition_2 > 0)
            if ($rdv->type > 0 && isset($validated['repartition_2']) && $validated['repartition_2'] > 0) {
                if (count($ventes) > 1) {
                    $vente2 = $ventes[1];
                } else {
                    $vente2 = new Vente();
                    $vente2->id_rdv = $rdv->id;
                }

                $vente2->id_client = $rdv->id_client;
                $vente2->id_commercial = $rdv->type;
                $vente2->tarif_total_ht = $rdv->tarif_total_ht;
                $vente2->ca_total_ht = $rdv->devis_total_ht;
                $vente2->repartition = $validated['repartition_2'];
                $vente2->tarif_ht = 0;
                $vente2->ca_ht = 0;
                $vente2->remise = 0;
                $vente2->montant = 0;
                $vente2->plus_value = 0;
                $vente2->prime_cee = 0;
                $vente2->date = $rdv->date;
                $vente2->paiement = $vente->paiement;
                $vente2->type_vente = '';
                $vente2->statut = 1;
                $vente2->complement = $vente->complement;
                $vente2->attente = $vente->attente;
                $vente2->attente_autre = $vente->attente_autre;
                $vente2->commande = $vente->commande;
                $vente2->secteur = $rdv->secteur;

                $vente2->save();
            }

            // Troisième vente (si commercial 3 et repartition_3 > 0)
            if (isset($validated['id_commercial_3']) && $validated['id_commercial_3'] > 0 &&
                isset($validated['repartition_3']) && $validated['repartition_3'] > 0) {
                if (count($ventes) > 2) {
                    $vente3 = $ventes[2];
                } else {
                    $vente3 = new Vente();
                    $vente3->id_rdv = $rdv->id;
                }

                $vente3->id_client = $rdv->id_client;
                $vente3->id_commercial = $validated['id_commercial_3'];
                $vente3->tarif_total_ht = $rdv->tarif_total_ht;
                $vente3->ca_total_ht = $rdv->devis_total_ht;
                $vente3->repartition = $validated['repartition_3'];
                $vente3->tarif_ht = 0;
                $vente3->ca_ht = 0;
                $vente3->remise = 0;
                $vente3->montant = 0;
                $vente3->plus_value = 0;
                $vente3->prime_cee = 0;
                $vente3->date = $rdv->date;
                $vente3->paiement = $vente->paiement;
                $vente3->type_vente = '';
                $vente3->statut = 1;
                $vente3->complement = $vente->complement;
                $vente3->attente = $vente->attente;
                $vente3->attente_autre = $vente->attente_autre;
                $vente3->commande = $vente->commande;
                $vente3->secteur = $rdv->secteur;

                $vente3->save();
            }
        }

        // Redirection avec message de succès
        if ($mode == 'creation') {
            toastr()->success('Argumentation créée avec succès!');
        } else {
            toastr()->success('Argumentation modifiée avec succès!');
        }

        return redirect()->route('welcome');
    }
}
