<div class="flex flex-col" style="font-size: 14px;">
    <div class="flex flex-col gap-6 p-4 md:p-10 bg-background rounded-xl justify-around">
        <!-- Back Button Section -->
        <div class="mb-6">
            <div class="flex justify-start">
                <button wire:click="backToVentes"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full flex items-center gap-2 transition-colors duration-200">
                    <i class="fas fa-arrow-left"></i>
                    Retour aux ventes
                </button>
            </div>
        </div>
        <div class="flex flex-row items-center justify-center mb-4">
            <h2 class="text-2xl font-bold text-primary">Résumé des Commissions de {{ $commercial->nom . ' ' . $commercial->prenom }}</h2>
        </div>


        <!-- Commission Detail Table -->
        <div class="overflow-x-auto flex flex-col gap-4">
            <!-- Header -->
            <div class="bg-primary rounded-xl flex flex-row p-5 items-center">
                <div class="text-white font-bold w-[5%] text-center">
                    <p>#</p>
                </div>
                <div class="text-white font-bold w-[10%] text-center">
                    <p>DATE</p>
                </div>
                <div class="text-white font-bold w-[15%] text-center">
                    <p>CLIENT</p>
                </div>
                <div class="text-white font-bold w-[12%] text-center">
                    <p>VRP</p>
                </div>
                <div class="text-white font-bold w-[10%] text-center">
                    <p>PRIX TARIF</p>
                </div>
                <div class="text-white font-bold w-[8%] text-center">
                    <p>TAUX REMISE</p>
                </div>
                <div class="text-white font-bold w-[10%] text-center">
                    <p>CA HT</p>
                </div>
                <div class="text-white font-bold w-[10%] text-center">
                    <p>MARGE</p>
                </div>
                <div class="text-white font-bold w-[10%] text-center">
                    <p>COMMISSION TAUX</p>
                </div>
                <div class="text-white font-bold w-[10%] text-center">
                    <p>COMMISSION MONTANT</p>
                </div>
            </div>

            <!-- Data Rows -->
            @forelse($ventesPaginate as $index => $vente)
                <div
                    class="flex flex-row items-center bg-background-grey rounded-xl p-5 hover:bg-gray-100 transition-colors duration-200"
                    wire:key="commission-{{ $index }}">

                    <!-- Numéro -->
                    <div class="w-[5%] text-primary font-bold text-center">
                        <p>{{ $vente->id_rdv }}</p>
                    </div>

                    <!-- Date -->
                    <div class="w-[10%] text-text-muted text-center">
                        <p>{{ $vente->date ? \Carbon\Carbon::parse($vente->date)->format('d/m/Y') : 'N/A' }}</p>
                    </div>

                    <!-- Client -->
                    <div class="w-[15%] text-text-muted text-center">
                         <p>{{ $vente->client->nom ?? '' }}</p>
                    </div>

                    <!-- VRP -->
                    <div class="w-[12%] text-text-muted text-center">
                        <p class="truncate" title="{{ $sale['vrp'] }}">{{ $sale['vrp'] }}</p>
                    </div>

                    <!-- Prix Tarif -->
                    <div class="w-[10%] text-text-muted text-center">
                        <p>{{ number_format($sale['prix_tarif'], 0, ',', ' ') }}€</p>
                    </div>

                    <!-- Taux Remise -->
                    <div class="w-[8%] text-text-muted text-center">
                        <p>{{ number_format($sale['taux_remise'], 1, ',', ' ') }}%</p>
                    </div>

                    <!-- CA HT -->
                    <div class="w-[10%] text-text-muted text-center">
                        <p>{{ number_format($sale['ca_ht'], 0, ',', ' ') }}€</p>
                    </div>

                    <!-- Marge -->
                    <div class="w-[10%] text-text-muted text-center">
                        <p>{{ number_format($sale['marge'], 0, ',', ' ') }}€</p>
                    </div>

                    <!-- Commission Taux -->
                    <div class="w-[10%] text-primary font-bold text-center">
                        <p>{{ number_format($sale['commission_taux'], 2, ',', ' ') }}%</p>
                    </div>

                    <!-- Commission Montant -->
                    <div class="w-[10%] text-primary font-bold text-center">
                        <p>{{ number_format($sale['commission_montant'], 0, ',', ' ') }}€</p>
                    </div>
                </div>
            @empty
                <div class="flex justify-center items-center py-10 bg-background-grey rounded-xl">
                    <p class="text-text-muted">Aucune commission trouvée pour les critères sélectionnés</p>
                </div>
            @endforelse

            <!-- Totals Row -->
            @if(count($commissionData) > 0)
                <div class="flex flex-row items-center bg-primary rounded-xl p-5 mt-2">
                    <!-- # -->
                    <div class="w-[5%] text-white font-bold text-center">
                        <p>TOTAL</p>
                    </div>

                    <!-- Date -->
                    <div class="w-[10%] text-white font-bold text-center">
                        <p>-</p>
                    </div>

                    <!-- Client -->
                    <div class="w-[15%] text-white font-bold text-center">
                        <p>{{ count($commissionData) }} vente{{ count($commissionData) > 1 ? 's' : '' }}</p>
                    </div>

                    <!-- VRP -->
                    <div class="w-[12%] text-white font-bold text-center">
                        <p>-</p>
                    </div>

                    <!-- Prix Tarif Total -->
                    <div class="w-[10%] text-white font-bold text-center">
                        @php
                            $totalPrixTarif = array_sum(array_column($commissionData, 'prix_tarif'));
                        @endphp
                        <p>{{ number_format($totalPrixTarif, 0, ',', ' ') }}€</p>
                    </div>

                    <!-- Taux Remise Moyenne -->
                    <div class="w-[8%] text-white font-bold text-center">
                        @php
                            $avgRemise = count($commissionData) > 0 ? array_sum(array_column($commissionData, 'taux_remise')) / count($commissionData) : 0;
                        @endphp
                        <p>{{ number_format($avgRemise, 1, ',', ' ') }}%</p>
                    </div>

                    <!-- CA HT Total -->
                    <div class="w-[10%] text-white font-bold text-center">
                        @php
                            $totalCA = array_sum(array_column($commissionData, 'ca_ht'));
                        @endphp
                        <p>{{ number_format($totalCA, 0, ',', ' ') }}€</p>
                    </div>

                    <!-- Marge Totale -->
                    <div class="w-[10%] text-white font-bold text-center">
                        @php
                            $totalMarge = array_sum(array_column($commissionData, 'marge'));
                        @endphp
                        <p>{{ number_format($totalMarge, 0, ',', ' ') }}€</p>
                    </div>

                    <!-- Commission Taux Moyenne -->
                    <div class="w-[10%] text-white font-bold text-center">
                        @php
                            $avgTaux = count($commissionData) > 0 ? array_sum(array_column($commissionData, 'commission_taux')) / count($commissionData) : 0;
                        @endphp
                        <p>{{ number_format($avgTaux, 2, ',', ' ') }}%</p>
                    </div>

                    <!-- Commission Totale -->
                    <div class="w-[10%] text-white font-bold text-center">
                        <p>{{ number_format($totalCommission, 0, ',', ' ') }}€</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Export and Actions -->
        <div class="flex justify-between items-center mt-4">
            <div class="text-sm text-gray-600">
                @if(count($commissionData) > 0)
                    Commission totale: <span class="font-bold text-primary">{{ number_format($totalCommission, 0, ',', ' ') }} €</span>
                @endif
            </div>
        </div>
    </div>
</div>
