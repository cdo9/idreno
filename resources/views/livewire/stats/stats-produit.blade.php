<div class="p-4 bg-white shadow rounded-lg">
    <h2 class="text-2xl font-semibold mb-6 text-primary"> Stats Produits</h2>

    <!-- Filtres -->
    <div class="flex flex-wrap items-center gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium mb-1">Date début</label>
            <input type="date" wire:model.live="dateDebut" class="border p-2 rounded w-44">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Date fin</label>
            <input type="date" wire:model.live="dateFin" class="border p-2 rounded w-44">
        </div>

        @isset($commerciaux)
            <div>
                <label class="block text-sm font-medium mb-1">Commercial</label>
                <select wire:model.live="commercial" class="border p-2 rounded w-56">
                    <option value="">Tous les commerciaux</option>
                    @foreach($commerciaux as $c)
                        <option value="{{ $c->id }}">
                            {{ $this->getCommercialNom($c) }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endisset
    </div>

    <!-- Tableau -->
    <div class="overflow-x-auto">
        <table class="sortable table-auto w-full border-collapse text-sm">
            <thead class="bg-[#77BC23]  text-white">
            <tr>
                <th class=" px-3 py-2 text-left rounded-tl-lg">Produit</th>
                <th class=" px-3 py-2 text-right">CA Vente</th>
                <th class=" px-3 py-2 text-right">CA Total</th>
                <th class=" px-3 py-2 text-center rounded-tr-lg">% CA Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($statsProduits as $famille => $produits)
                @php $t1 = $t2 = 0; @endphp
                @foreach($produits as $slug => $p)
                    @php
                        $caVendeur = ($commercial === "" ? $p['ca_total'] : $p['ca_vendeur']);
                        $t1 += $caVendeur;
                        $t2 += $p['ca_total'];
                    @endphp
                    <tr class="odd:bg-gray-200">
                        <td class="px-3 py-2">{{ $p['label'] }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($caVendeur, 0, ',', ' ') }} €</td>
                        <td class="px-3 py-2 text-right">{{ number_format($p['ca_total'], 0, ',', ' ') }} €</td>
                        <td class="px-3 py-2 text-center">
                            @if($caTotal)
                                {{ number_format($caVendeur / $caTotal * 100, 0, ',', ' ') }} %
                            @endif
                        </td>
                    </tr>
                @endforeach


                <!-- Total par famille -->
                <tr class="bg-[#77BC23] text-white font-semibold">
                    <td class=" px-3 py-2">{{ $famille }}</td>
                    <td class=" px-3 py-2 text-right">{{ number_format($t1, 0, ',', ' ') }} €</td>
                    <td class=" px-3 py-2 text-right">{{ number_format($t2, 0, ',', ' ') }} €</td>
                    <td class=" px-3 py-2 text-center">
                        {{ $caTotal > 0 ? number_format($t1 / $caTotal * 100, 0, ',', ' ') : 0 }} %
                    </td>
                </tr>
            @endforeach
            </tbody>

            <!-- Total général -->
            <tfoot class="bg-gray-200 font-bold">
            @php
                $t1 = collect($statsProduits)
                        ->flatten(1)
                        ->sum(function($p) use ($commercial) {
                            return $commercial === "" ? $p['ca_total'] : $p['ca_vendeur'];
                        });
            @endphp

            <tr>
                <td class=" px-3 py-2 text-right rounded-bl-lg">Total</td>
                <td class=" px-3 py-2 text-right">{{ number_format($t1, 0, ',', ' ') }} €</td>
                <td class=" px-3 py-2 text-right">{{ number_format($caTotal, 0, ',', ' ') }} €</td>
                <td class=" px-3 py-2 text-center rounded-br-lg">
                    {{ $caTotal > 0 ? number_format(($p['ca_vendeur'] / $caTotal * 100)+($t1 / $caTotal * 100), 0, ',', ' ') : 0 }} %
                </td>
            </tr>
            </tfoot>
        </table>
        <script src="{{ asset('js/jquery.js') }}"></script>
        <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
        <script src="{{ asset('js/tableSorter.js') }}"></script>
    </div>
</div>
