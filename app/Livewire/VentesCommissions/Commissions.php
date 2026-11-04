<?php

namespace App\Livewire\VentesCommissions;

use App\Models\User;
use App\Models\Vente;
use App\Services\CommissionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Commissions extends Component
{
    // Filter properties inherited from previous page
    public $mois = '';

    public $annee = '';

    public $commercial = '';

    // Data for dropdowns
    public $commercials = [];

    public $annees = [];

    public $mois_options = [];

    public $userId;

    public $isDirecteur = false;

    public $isAdmin = false;

    // Commission data
    public $commissionData = [];

    public $totalCommission = 0;

    public $PrimeCommission = 0;

    public function mount($commercial = null, $mois = null, $annee = null)
    {
        $user = Auth::user();
        $this->userId = $user->id;
        $this->isDirecteur = $user->hasRole('directeur');
        $this->isAdmin = $user->hasRole('admin');

        // Retrieve info from URL parameters first, then use passed parameters as fallback
        $urlCommercial = request('commercial', '');
        $urlMois = request('mois', '');
        $urlAnnee = request('annee', '');

        \Log::info($urlCommercial);
        \Log::info($urlMois);
        \Log::info($urlAnnee);

        // Set filters from URL parameters, then passed parameters, then defaults
        $this->commercial = $urlCommercial ?: ($commercial ?? '');
        $this->mois = $urlMois ?: ($mois ?? '');
        $this->annee = $urlAnnee ?: ($annee ?? date('Y'));

        $this->commercial = User::find($this->commercial);
        Log::info($this->commercial);

        // $this->loadCommissionData();
    }

    public function getCommercialNom($user)
    {
        return $user['prenom'].' '.$user['nom'];
    }

    public function loadCommissionData()
    {
        try {
            $query = Vente::with(['rendez_vous', 'client'])
                ->where('statut', '!=', 3) // Exclude cancelled sales
                ->whereNull('attente'); // Exclude sales awaiting validation

            // Apply filters
            if ($this->commercial->id !== '') {
                $query->where(function ($q) {
                    $q->where('id_commercial', $this->commercial->id)
                        ->orWhere('id_commercial_2', $this->commercial->id)
                        ->orWhere('id_commercial_3', $this->commercial->id);
                });
            }
            $targetDate = Carbon::now()->year((int) $this->annee)->month((int) $this->mois);

            // Get sales data
            $ventes = Vente::with(['rendez_vous', 'client'])
                ->where('date', $targetDate)
                ->where('statut', '!=', 3)
                ->where('id_commercial', $this->commercial->id)
                ->orWhere('id_commercial_2', $this->commercial->id)
                ->orWhere('id_commercial_3', $this->commercial->id)
                ->orderBy('date', 'desc')->get();

            // Collect detailed commission.blade.php data for each sale
            $this->commissionData = [];
            $this->totalCommission = 0;
            $counter = 1;

            foreach ($ventes as $vente) {
                // Get commercial info - prioritize based on selection or use primary commercial
                $commercialId = null;
                $commercialName = '';

                if ($this->commercial) {
                    if ($vente->id_commercial == $this->commercial->id) {
                        $commercialId = $vente->id_commercial;
                    } elseif ($vente->id_commercial_2 == $this->commercial->id) {
                        $commercialId = $vente->id_commercial_2;
                    } elseif ($vente->id_commercial_3 == $this->commercial->id) {
                        $commercialId = $vente->id_commercial_3;
                    }
                } else {
                    $commercialId = $vente->id_commercial; // Use primary commercial if no filter
                }

                if ($commercialId) {
                    $commercial = User::find($commercialId);
                    if ($commercial) {
                        $commercialName = $commercial->prenom.' '.$commercial->nom;
                    }
                }

                // Calculate commission.blade.php details
                $commission = $commercialId ? $this->calculateCommissionForSale($vente, $commercialId) : 0;
                $commissionDetails = $this->getDetailedCommissionInfo($vente, $commercialId);

                // Calculate margin
                $marge = $vente->ca_ht - $vente->tarif_ht;

                $this->commissionData[] = [
                    'numero' => $counter++,
                    'date' => Carbon::parse($vente->date)->format('d/m/Y'),
                    'client' => $vente->client ? ($vente->client->prenom.' '.$vente->client->nom) : 'N/A',
                    'vrp' => $commercialName ?: 'N/A',
                    'prix_tarif' => $vente->tarif_ht,
                    'taux_remise' => $vente->tarif_ht > 0 ? (($vente->tarif_ht - $vente->ca_ht) / $vente->tarif_ht) * 100 : 0,
                    'ca_ht' => $vente->ca_ht,
                    'marge' => $marge,
                    'commission_taux' => $commissionDetails['taux'],
                    'commission_montant' => $commission,
                ];

                $this->totalCA += (str_contains('directeur', $commercial->contrat) ? $vente->ca_ht - $marge : $vente->ca_ht);
                $this->totalCommission += $commission;
            }

            dd($this->commissionData);
            if (str_contains('directeur', $commercial->contrat)) {
                $ventes = Vente::with(['rendez_vous', 'client'])
                    ->where('date', $targetDate)
                    ->where('date', $targetDate)
                    ->where('statut', '!=', 3)
                    ->where('id_commercial', $this->commercial->id)
                    ->orWhere('id_commercial_2', $this->commercial->id)
                    ->orWhere('id_commercial_3', $this->commercial->id)
                    ->orderBy('date', 'desc')->get();

                $this->PrimeCommission = ($this->totalCommission > 20000 ? $this->totalCA * 0.02 : 0);
            }

        } catch (\Exception $e) {
            \Log::error('Error loading commission.blade.php data: '.$e->getMessage());
            $this->commissionData = [];
            $this->totalCommission = 0;
        }
    }

    private function getDetailedCommissionInfo($vente, $commercialId)
    {
        try {
            if (! $commercialId) {
                return ['taux' => 0, 'montant' => 0];
            }

            $commercial = User::find($commercialId);
            if (! $commercial) {
                return ['taux' => 0, 'montant' => 0];
            }

            $contrat = $commercial->contrat;
            $produitsStr = $vente->rendez_vous->produits ?? '';

            // Calculate proper commission.blade.php rates based on contract and performance
            $prix_vente_mensuel = $vente->ca_ht;
            $prix_tarif_mensuel = $vente->tarif_ht;

            [$tauxStandard, $tauxRAI] = CommissionService::calculTauxCommissionMensuel(
                $contrat,
                $prix_vente_mensuel,
                $prix_tarif_mensuel
            );

            $taux = CommissionService::isVenteRAI($produitsStr) ? $tauxRAI : $tauxStandard;

            return ['taux' => $taux, 'montant' => 0]; // montant will be calculated separately

        } catch (\Exception $e) {
            \Log::error('Error getting commission.blade.php info: '.$e->getMessage());

            return ['taux' => 0, 'montant' => 0];
        }
    }

    private function calculateCommissionForSale($vente, $commercialId)
    {
        try {
            $commercial = User::find($commercialId);
            if (! $commercial) {
                return 0;
            }

            $contrat = $commercial->contrat;
            $produitsStr = $vente->rendez_vous->produits ?? '';

            // Calculate proper commission.blade.php rates based on contract and performance
            $prix_vente_mensuel = $vente->ca_ht;
            $prix_tarif_mensuel = $vente->tarif_ht;

            [$tauxStandard, $tauxRAI] = CommissionService::calculTauxCommissionMensuel(
                $contrat,
                $prix_vente_mensuel,
                $prix_tarif_mensuel
            );

            $taux = CommissionService::isVenteRAI($produitsStr) ? $tauxRAI : $tauxStandard;

            $montant = CommissionService::calculMontantCommission(
                $contrat,
                $vente->ca_ht,
                $vente->ca_total_ht,
                $vente->client->id_source ?? '',
                $taux,
                $vente->marge
            );

            // Check if commission.blade.php should be masked for this user
            if (CommissionService::shouldMaskCommission(auth()->user(), $commercialId, 'montant')) {
                return 0;
            }

            return $montant;

        } catch (\Exception $e) {
            \Log::error('Error calculating commission.blade.php: '.$e->getMessage());

            return 0;
        }
    }

    public function backToVentes()
    {
        return redirect()->route('ventes', [
            'commercial' => $this->commercial,
            'mois' => $this->mois,
            'annee' => $this->annee,
        ]);
    }

    public function render()
    {
        $data = Ventes::loadData($this->mois, $this->annee, $this->commercial, $this->userId, $this->isDirecteur);

        return view('livewire.ventes-commissions.commission', $data);
    }
}
