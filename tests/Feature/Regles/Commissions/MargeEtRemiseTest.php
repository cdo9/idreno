<?php

use App\Services\CommissionService;

it('computes the margin as CA minus 85% of the tarif', function () {
    expect(CommissionService::calculMarge(20000, 20000))->toBe(3000.0);
});

it('computes the discount as a percentage of the tarif', function () {
    expect(CommissionService::calculRemise(15000, 20000))->toBe(25.0);
});

it('never returns a negative discount when the CA exceeds the tarif', function () {
    expect(CommissionService::calculRemise(25000, 20000))->toBe(0.0);
});

it('returns no discount when there is no tarif', function () {
    expect(CommissionService::calculRemise(15000, 0))->toBe(0.0);
});
