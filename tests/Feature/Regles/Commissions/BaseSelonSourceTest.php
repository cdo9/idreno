<?php

/*
 * Règles IdReno : la commission d'un directeur se calcule sur (CA − marge),
 * et il n'y a aucune règle spéciale par source (parrainage télépro, leads,
 * apporteur). Voir COMPARAISON-HOMISO-IDRENO.md § 3.1 et § 8.3.
 */

use App\Models\Source;
use App\Services\CommissionService;
use App\Services\SourceService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    (new ReflectionProperty(SourceService::class, 'sourceList'))->setValue(null, []);
});

it('computes a commercial commission on the CA share, whatever the margin', function () {
    $source = Source::factory()->create();

    expect(CommissionService::calculMontantCommission('commercial-v1', 10000, 20000, (string) $source->id, 10, 2000))->toBe(1000.0);
});

it('computes a directeur commission on the CA share minus the margin', function (string $contrat) {
    $source = Source::factory()->create();

    expect(CommissionService::calculMontantCommission($contrat, 10000, 20000, (string) $source->id, 7, 2000))->toBe(560.0);
})->with(['directeur-v1', 'directeur-v2']);

it('reduces the CA share to 70% for a passive or telepro source', function (string $contrat, string $groupe, float $marge, float $expected) {
    $source = Source::factory()->create(['groupe' => $groupe]);

    expect(CommissionService::calculMontantCommission($contrat, 10000, 20000, (string) $source->id, 10, $marge))->toBe($expected);
})->with([
    'commercial, passive source' => ['commercial-v1', 'passif', 0, 700.0],
    'commercial, telepro source' => ['commercial-v1', 'télépro', 0, 700.0],
    'directeur, passive source, margin not reduced' => ['directeur-v1', 'passif', 2000, 500.0],
]);

it('treats a source without group as active, like every source in the IdReno database', function () {
    // En prod, sources.groupe est vide pour toutes les sources IdReno :
    // la règle des 70 % ne s'applique donc jamais. Voir § 8.3.
    $source = Source::factory()->create(['groupe' => null]);

    expect(CommissionService::isSourcePassive((string) $source->id))->toBeFalse()
        ->and(CommissionService::calculMontantCommission('commercial-v1', 10000, 20000, (string) $source->id, 10, 0))->toBe(1000.0);
});

it('applies no special rule to the Homiso special source ids', function (int $sourceId) {
    $source = Source::factory()->create(['id' => $sourceId]);

    expect(CommissionService::calculMontantCommission('commercial-v1', 10000, 20000, (string) $source->id, 10, 0))->toBe(1000.0);
})->with([
    'parrainage telepro (17 at Homiso)' => [17],
    'leads (18 at Homiso)' => [18],
    'apporteur (23 at Homiso)' => [23],
    'apporteur (8 at IdReno)' => [8],
]);

it('gives no commission to contracts without an IdReno rule', function (string $contrat) {
    $source = Source::factory()->create();

    expect(CommissionService::calculMontantCommission($contrat, 10000, 20000, (string) $source->id, 10, 0))->toBe(0.0);
})->with(['telepro-v1', 'resp-prosp', 'idr-commercial']);
