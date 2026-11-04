<?php

/*
 * Règles IdReno : pas de fixe déduit (contrairement à Homiso), mais une prime
 * de performance mensuelle pour commercial-v1 et une commission d'agence pour
 * les directeurs. Voir COMPARAISON-HOMISO-IDRENO.md § 3.1 et § 3.2.
 */

use App\Services\CommissionService;

it('adds the performance bonus to a commercial-v1 monthly commission', function (float $caTotalHt, float $expectedAmount) {
    expect(CommissionService::updateMontantCommissionMensuel('commercial-v1', 3000, 10, $caTotalHt))->toBe($expectedAmount);
})->with([
    'below 15k' => [14999, 3000.0],
    'at 15k' => [15000, 3150.0],
    'just below 20k' => [19999, 3150.0],
    'at 20k' => [20000, 3200.0],
    'at 42k' => [42000, 3400.0],
]);

it('applies no monthly adjustment to the other contracts', function (string $contrat) {
    expect(CommissionService::updateMontantCommissionMensuel($contrat, 3000, 10, 50000))->toBe(3000.0);
})->with(['directeur-v1', 'directeur-v2', 'commercial-v3', 'telepro-v5', 'idr-directeur']);

it('computes the performance bonus by steps of 5k above 15k', function (float $caTotalHt, float $expectedBonus) {
    expect(CommissionService::calculPrimePerformanceCommercial($caTotalHt))->toBe($expectedBonus);
})->with([
    'below 15k' => [10000, 0.0],
    'at 15k' => [15000, 150.0],
    'at 24 999' => [24999, 200.0],
    'at 25k' => [25000, 250.0],
]);

it('computes the agency commission at 1% below 100k and 1.5% from 100k', function (float $vendeurs, float $responsable, float $expected) {
    expect(CommissionService::calculCommissionAgence($vendeurs, $responsable))->toBe($expected);
})->with([
    'below 100k' => [60000, 30000, 900.0],
    'at 100k' => [80000, 20000, 1500.0],
]);
