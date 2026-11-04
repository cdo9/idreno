<?php

/*
 * Règles IdReno : elles diffèrent de Homiso pour les mêmes noms de contrat.
 * Voir COMPARAISON-HOMISO-IDRENO.md § 3.1. À la fusion, ce fichier devient
 * le jeu de tests des règles de commission du site IdReno.
 */

use App\Services\CommissionService;

it('applies the rate grid of each contract', function (string $contrat, float $prixVente, float $prixTarif, array $expectedRates) {
    expect(CommissionService::calculTauxCommissionMensuel($contrat, $prixVente, $prixTarif))->toBe($expectedRates);
})->with([
    'directeur-v1 flat rate' => ['directeur-v1', 50000, 50000, [7.0, 7.0]],
    'directeur-v1 ignores the discount' => ['directeur-v1', 30000, 50000, [7.0, 7.0]],
    'directeur-v2 flat rate' => ['directeur-v2', 20000, 20000, [10.0, 10.0]],
    'directeur-v2 ignores the discount' => ['directeur-v2', 30000, 50000, [10.0, 10.0]],
    'commercial-v1 without discount' => ['commercial-v1', 20000, 20000, [11.0, 11.0]],
    'commercial-v1 with 15% discount' => ['commercial-v1', 17000, 20000, [11.0, 11.0]],
    'commercial-v1 with 20% discount' => ['commercial-v1', 32000, 40000, [8.5, 8.5]],
    'commercial-v1 with 20.5% discount' => ['commercial-v1', 31800, 40000, [8.5, 8.5]],
    'commercial-v1 with 35% discount' => ['commercial-v1', 26000, 40000, [1.0, 1.0]],
    'commercial-v1 with 40% discount' => ['commercial-v1', 30000, 50000, [0.0, 0.0]],
    'unknown contract' => ['inconnu', 20000, 20000, [0.0, 0.0]],
]);

it('has no rate for the Homiso-only contracts', function (string $contrat) {
    expect(CommissionService::calculTauxCommissionMensuel($contrat, 20000, 20000))->toBe([0.0, 0.0]);
})->with(['telepro-v1', 'telepro-v2', 'commercial-v2', 'commercial-v3', 'directeur-v3', 'resp-prosp']);

it('has no rate for idr-commercial, although 5 IdReno users have this contract', function () {
    // Point d'attention : voir COMPARAISON-HOMISO-IDRENO.md § 5.
    expect(CommissionService::calculTauxCommissionMensuel('idr-commercial', 20000, 20000))->toBe([0.0, 0.0]);
});

it('uses the same rate for RAI sales', function () {
    [$taux, $tauxRai] = CommissionService::calculTauxCommissionMensuel('commercial-v1', 32000, 40000);

    expect($tauxRai)->toBe($taux);
});

it('returns no rate when there is no tarif', function () {
    expect(CommissionService::calculTauxCommissionMensuel('commercial-v1', 20000, 0))->toBe([0.0, 0.0]);
});

it('identifies RAI sales from their products', function (string $produits, bool $isRai) {
    expect(CommissionService::isVenteRAI($produits))->toBe($isRai);
})->with([
    'ravalement avec isolation' => ['RAVALEMENT-AVEC-ISOLATION;PAC', true],
    'RPE' => ['RPE', true],
    'other products' => ['PAC;ITE_MUR', false],
]);
