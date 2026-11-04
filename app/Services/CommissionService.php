<?php

namespace App\Services;

use App\Models\User;

class CommissionService
{
    /**
     * Compute monthly commission rates (standard and RAI) for a user based on totals.
     * This is a simplified, configurable logic that can be adapted later.
     *
     * @return array [tauxStandard, tauxRAI]
     */
    public static function calculTauxCommissionMensuel(?string $contrat, float $prix_vente, float $prix_tarif): array
    {
        $taux = 0.0;  // Taux normal
        $tauxRAI = 0.0; // Taux ventes "ravalement avec isolation"

        // Aucune donnée tarif => taux 0
        if ($prix_tarif <= 0) return [0.0, 0.0];

        // Calcul de la remise en % selon legacy
        $remise = self::calculRemise($prix_vente, $prix_tarif);

        switch ($contrat) {
            
            case 'directeur-v1':
                $taux = 7.0;
                break;

            case 'directeur-v2':
                $taux = 10.0;
                break;

            case 'commercial-v1':
				// dd($remise);
                if ($remise > 35) {
					$taux = 0.0;
				}else if ($remise <= 15) {
					$taux = 11.0;
				}else{
					
					$pointsSupplementaires = floor($remise) - 15;
					$taux = 11.0 - ($pointsSupplementaires * 0.5);
				}
                break;

            default:
                // Unknown contract: keep 0
                break;
        }

        // Si aucun taux RAI particulier, tauxRAI = taux
        if ($tauxRAI == 0.0) $tauxRAI = $taux;
        return [round($taux, 2), round($tauxRAI, 2)];
    }

    /**
     * Compute commission amount for a given sale piece.
     */
    public static function calculMontantCommission(?string $contrat, float $caPart, float $caTotal, string $source, float $taux, float $marge): float
    {
        $contrat = (string)$contrat;
        $source = (string)$source;

        // Legacy homiso rules: passive sources => 70% of CA base
        if (self::isSourcePassive($source)) {
            $caPart *= 0.7;
        }

        $amount = 0.0;

        // Directors, telepros, resp-prosp: always percentage of the (possibly reduced) CA part
		if (in_array($contrat, ['directeur-v1', 'directeur-v2'])){
			$amount = ($taux / 100.0) * ($caPart - $marge);
		}
        if (in_array($contrat, ['commercial-v1'], true)) {
            $amount = ($taux / 100.0) * $caPart;
        }

        return round($amount, 2);
    }
	
	public static function calculPrimePerformanceCommercial(float $caTotalHt): float
	{
		if ($caTotalHt < 15000) {
			return 0.0;
		}

		$paliers = floor(($caTotalHt - 15000) / 5000);

		return round(150.0 + ($paliers * 50.0), 2);
	}
	
	public static function calculCommissionAgence(float $totalVendeursCaHt, float $totalResponsableCaHt): float
	{
		$base = $totalVendeursCaHt + $totalResponsableCaHt;
		$taux = $base < 100000 ? 1.0 : 1.5;

		return round(($taux / 100.0) * $base, 2);
	}

    /**
     * Optional monthly adjustment hook.
     */
    public static function updateMontantCommissionMensuel(?string $contrat, float $montant, float $taux, float $caTotalHt = 0.0): float
    {
        $c = (string)$contrat;
        switch ($c) {
            case 'commercial-v1':
                $montant += self::calculPrimePerformanceCommercial($caTotalHt);
                break;
            default:
                // no monthly adjustment
                break;
        }
        return round($montant, 2);
    }

    public static function calculMarge(float $caTotalHt, float $tarifTotalHt): float
    {
        // Legacy: marge = CA total - (Tarif total * 0.85)
        return round($caTotalHt - ($tarifTotalHt * 0.85), 2);
    }

    public static function calculRemise(float $ca, float $tarif): float
    {
        if ($tarif <= 0) return 0.0;
        $discount = 1.0 - ($ca / $tarif);
        return round(max(0.0, $discount) * 100.0, 2);
    }

    /**
     * Determine if a sale is considered RAI based on rendez-vous produits list.
     * RAI = Ravalement Avec Isolation (includes ITE and other isolation products)
     */
    public static function isVenteRAI(?string $produits): bool
    {
        if (!$produits) return false;

        $haystack = is_array($produits) ? implode(';', $produits) : (string)$produits;
        $haystack = strtoupper($haystack);

        // RAI product patterns based on legacy logic
        $raiPatterns = [
            /*'ITE_', // Isolation Thermique Extérieure
            'RAVALEMENT',
            'ISOLATION',*/
            'RAVALEMENT-AVEC-ISOLATION',
            'RPE'
        ];

        foreach ($raiPatterns as $pattern) {
            if (stripos($haystack, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine passive source (reduced base for commission) using legacy mapping.
     */
    public static function isSourcePassive(?string $source): bool
    {
        if (!$source) return false;
        $map = SourceService::createSourceLst();
        $idsPassif = array_keys($map['passif']);
        $idsTelepro = array_keys($map['télépro']); 
        $allIds = array_merge($idsPassif, $idsTelepro);

        return in_array((int)$source, $allIds, true);
    }

    /**
     * Flatten the ProjectService priorities list to key=>label mapping.
     */
    public static function listPrioritiesFlat(): array
    {
        $flat = [];
        $groups = ProjectService::listPriorities();
        foreach ($groups as $group => $items) {
            foreach ($items as $key => $label) {
                $flat[$key] = $label;
            }
        }
        return $flat;
    }

    /**
     * Should mask commission data for the current user.
     * If user is not admin/directeur and it is not their own line, hide.
     */
    public static function shouldMaskCommission(User $currentUser, ?int $commercialId, string $dimension = 'montant'): bool
    {
        if ($currentUser->hasRole('admin') || $currentUser->hasRole('directeur')) return false;
        if ($commercialId && $currentUser->id === $commercialId) return false;
        return true;
    }

    /**
     * Helper to compute per-vente share values if blank.
     */
    public static function computeShare(float $total, int $repartition): float
    {
        return round(($total * max(0, $repartition)) / 100.0, 2);
    }
}
