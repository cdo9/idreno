<div class="flex items-center justify-between flex-wrap bg-white lg:px-4 py-3 border-b border-gray-200 mb-4 shadow-sm rounded-md">
    @if($filtreVue === 'commerciaux')
    <h1 class="text-2xl font-bold text-center text-primary mb-6">
        Classement des commerciaux
    </h1>

        <div
            wire:loading
            class="fixed inset-0 z-50 flex self-center "
            aria-live="polite"
            aria-label="Chargement en cours"
        >
            <div class="flex flex-col items-center space-y-2">
                <svg class="w-8 h-8 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="green" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                    </path>
                </svg>
            </div>
        </div>

    <div class=" py-5 flex items-center space-x-3">
        <!--<div>
            <select wire:model.live="filtreVue" class="border border-gray-300 text-sm rounded-md p-1 focus:ring-green-500 focus:border-green-500">
                <option value="commerciaux">Commerciaux</option>
                <option value="agence">Agence</option>
            </select>
        </div>-->
        <div>
            <select {{ $isAdmin ? '' : 'disabled' }} wire:model.live="year" id="year" class="border border-gray-300 text-sm rounded-md p-1 focus:ring-primary focus:border-primary">
                @foreach ($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <select wire:model.live="month"  class="border border-gray-300 text-sm rounded-md p-1 focus:ring-primary focus:border-primary">
                <option value="0">Aucun</option>
                @foreach ($months as $num => $name)
                    <option value="{{ $num }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="w-full overflow-x-auto scrollbar-board">
        <table @class(['w-[175rem]' => $month === '0',
                       'min-w-full' => $month !== '0',
                       'text-xs', 'lg:text-sm', 'text-center', 'rounded-md', 'table-fixed', 'divide-gray-200', 'rounded-lg','sortable'
        ])>
        <thead class="min-w-full border-collapse lg:text-sm text-xs">
            <tr>
                <th @class([
                        'w-40' => $month === '0',
                        'bg-primary px-4 py-2 text-white text-center font-semibold cursor-pointer rounded-tl-lg'
                    ])
                    wire:click="sortBy('ca_annuel')" >COMMERCIAUX {!! $this->sortiIcon('ca_annuel') !!}</th>
                <th @class([
                        'w-35' => $month === '0',
                        'bg-primary px-4 py-2 text-white text-center  font-semibold cursor-pointer'
                    ]) wire:click="sortBy('ratio')" >VENTES<br>NOUVEAU CLIENT {!! $this->sortiIcon('ratio') !!}</th>
                <!--<th @class([
                        'w-45' => $month === '0',
                        'bg-primary px-4 py-2 text-white text-center  font-semibold cursor-pointer'
                    ])wire:click="sortBy('objectif_ca')">OBJECTIF MENSUEL {!! $this->sortiIcon('objectif_ca') !!}</th>-->
                @if ($month === '0')
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer " wire:click="sortBy('M1')" colspan="2">JAN {!! $this->sortiIcon('M1') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M2')" colspan="2">FEV {!! $this->sortiIcon('M2') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M3')" colspan="2">MAR {!! $this->sortiIcon('M3') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M4')" colspan="2">AVR {!! $this->sortiIcon('M4') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M5')" colspan="2">MAI {!! $this->sortiIcon('M5') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M6')" colspan="2">JUIN {!! $this->sortiIcon('M6') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M7')" colspan="2">JUIL {!! $this->sortiIcon('M7') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M8')" colspan="2">AOÛT {!! $this->sortiIcon('M8') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M9')" colspan="2">SEP {!! $this->sortiIcon('M9') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M10')" colspan="2">OCT {!! $this->sortiIcon('M10') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M11')" colspan="2">NOV {!! $this->sortiIcon('M11') !!}</th>
                    <th class="bg-primary px-4 py-2  text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M12')" colspan="2">DEC {!! $this->sortiIcon('M12') !!}</th>
                @endif
                @if ($month != '0')
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center cursor-pointer" wire:click="sortBy('S1_ca')">S1 {!! $this->sortiIcon('S1_ca') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center cursor-pointer" wire:click="sortBy('S2_ca')" >S2 {!! $this->sortiIcon('S2_ca') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center cursor-pointer" wire:click="sortBy('S3_ca')" >S3 {!! $this->sortiIcon('S3_ca') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center cursor-pointer" wire:click="sortBy('S4_ca')" >S4 {!! $this->sortiIcon('S4_ca') !!}</th>
                    <th class="bg-primary px-4 py-2 text-white font-semibold text-center cursor-pointer" wire:click="sortBy('S5_ca')" >S5 {!! $this->sortiIcon('S5_ca') !!}</th>
                @endif
                <th @class([
                        'w-40' => $month === '0',
                        'bg-primary px-4 py-2 text-white text-center  font-semibold cursor-pointer'
                    ]) wire:click="sortBy('objectif_annuel')">Objectif <br> ANNUEL {!! $this->sortiIcon('objectif_annuel') !!}</th>
                <th @class([
                        'w-40' => $month === '0',
                        'bg-primary px-4 py-2 text-white text-center  font-semibold rounded-tr-lg cursor-pointer'
                    ]) wire:click="sortBy('ca_annuel')" >CA TOTAL {!! $this->sortiIcon('ca_annuel') !!}</th>
            </tr>

            </thead>
            <tbody class="min-w-full border-collapse ">
            @foreach ($users as $user)
                <tr class="odd:bg-gray-300 p-3">
                    <td @class([
                    'px-3 py-2 font-semibold',
                    'text-red-600' => $user->active == 0,
                    'text-gray-800' => $user->active != 0,
                ])>
                    @if($isAdmin || $isDirecteur)
                        {{ $user->display_name }}
                    @else
                        {{ $user->prenom }}
                    @endif
                            <br>
                            <span class="ml-2 text-gray-500">#{{ $user->rang_ca_annuel }}</span>
                    </td>
                    <td class="px-3 py-2 w-10">{{ number_format($user->ratio * 100, 0) }}%</td>
                    <!--<td class="px-3 py-2 w-10">{{ number_format($user->objectif_ca, 0, ',', ' ') }} €</td>-->
                    @if ($month === '0')
                        @for ($i = 1; $i <= 12; $i++)
                            <td class="px-4 py-2 w-1/5 text-black font-semibold"> {{ number_format($user->ca_month[$i] ?? 0 , 0, ',', ' ') }} €</td>
                            <td class=" py-2 text-center">
                                @if($user->objectif_ca != 0)
                                    {{ round(($user->ca_month[$i] / $user->objectif_ca) * 100) ?? 0}}%
                                @endif
                                @if ($user->objectif_ca === 0)
                                    0%
                                @endif
                            </td>
                        @endfor
                    @endif
                    @if ($month != '0')
                        @for ($i = 0; $i <= 4; $i++)
                            <td class="px-4 py-2 text-primary">
                                {{ number_format($user->caParSemaine[$i] ?? 0, 0, ',', ' ') }} €
                            </td>
                        @endfor
                    @endif
                    <td class="px-3 py-2 w-10">{{ $user->objectif_annuel }} </td>
                    <td class="px-3 py-2 w-20 font-semibold">

                        {{ number_format($user->ca_annuel, 0, ',', ' ') }} €
                        <div class="text-gray-500">Rang: {{ $user->rang_ca_annuel }}</div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tbody class="disable-sort">
            <tr class="bg-gray-200 text-gray-800">
                <td class="px-3 py-2">TOTAL</td>
                <td></td>
                <!--<td class="px-3 py-2 w-10">{{ number_format($this->totalRow['objectif_ca'], 0, ',', ' ') }} €</td>-->
                @if ($month === '0')
                    @for ($i = 1; $i <= 12; $i++)
                        @php
                            $caMois = $this->totalRow['ca_month'][$i] ?? 0;
                            $pourcentage = $this->totalRow['objectif_ca'] != 0 ? round(($caMois / $this->totalRow['objectif_ca']) * 100) : 0;
                        @endphp
                        <td class="px-4 py-2" colspan="2">
                            <div class="text-primary font-semibold leading-tight" >
                                {{ number_format($caMois, 0, ',', ' ') }} €
                            </div>
                            <div class="text-gray-600 mt-0.5" >
                                {{ $pourcentage }}%
                            </div>
                        </td>
                    @endfor
                @endif
                @if ($month != '0')
                    @for ($i = 0; $i <= 4; $i++)
                        <td class="px-4 py-2 text-primary">
                            {{ number_format($this->totalRow['caParSemaine'][$i], 0, ',', ' ') }} €
                        </td>
                    @endfor
                @endif
                <td class="px-3 py-2 w-10">{{ number_format($this->totalRow['objectif_annuel'], 0, ',', ' ') }} €</td>
                <td class="px-3 py-2 w-30">{{ number_format($this->totalRow['ca_annuel'], 0, ',', ' ') }} €</td>
            </tr>
            @endif



            @if($filtreVue === 'agence')
                <h1 class="text-2xl font-bold text-center text-primary mb-6">
                    Classement des agences
                </h1>

                <div class=" py-5 flex items-center space-x-3">
                    <div>
                        <select wire:model.live="filtreVue" class="border border-gray-300 text-sm rounded-md p-1 focus:ring-green-500 focus:border-green-500">
                            <option value="commerciaux">Commerciaux</option>
                            <option value="agence">Agence</option>
                        </select>
                    </div>
                    <div>
                        <select wire:model.live="year" id="year" class="border border-gray-300 text-sm rounded-md p-1 focus:ring-green-500 focus:border-green-500">
                            @foreach ($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select wire:model.live="month"  class="border border-gray-300 text-sm rounded-md p-1 focus:ring-green-500 focus:border-green-500">
                            <option value="0">Aucun</option>
                            @foreach ($months as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="w-full scrollbar-board overflow-x-auto ">
                <table @class(['w-[175rem]' => $month === '0',
                       'min-w-full' => $month !== '0',
                       'text-xs','lg:text-sm', 'text-center', 'rounded-md', 'table-fixed', 'divide-gray-200'
        ])>
                    <thead class="bg-primary text-white">
                    <tr>


                        <th @class([
                        'w-40' => $month === '0',
                        'bg-primary px-4 py-2  text-white text-center font-semibold cursor-pointer'
                    ]) wire:click="sortBy('nom')">AGENCE {!! $this->sortiIcon('ca_annuel') !!}</th>
                        <th @class([
                        'w-40' => $month === '0',
                        'bg-primary px-4 py-2 text-white text-center  font-semibold cursor-pointer'
                    ]) wire:click="sortBy('objectif_ca')">OBJECTIF MENSUEL {!! $this->sortiIcon('ca_objectif') !!}</th>
                        @if ($month === '0')
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer " wire:click="sortBy('M1')" colspan="2">JAN {!! $this->sortiIcon('M1') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M2')" colspan="2">FEV {!! $this->sortiIcon('M2') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M3')" colspan="2">MAR {!! $this->sortiIcon('M3') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M4')" colspan="2">AVR {!! $this->sortiIcon('M4') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M5')" colspan="2">MAI {!! $this->sortiIcon('M5') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M6')" colspan="2">JUIN {!! $this->sortiIcon('M6') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M7')" colspan="2">JUIL {!! $this->sortiIcon('M7') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M8')" colspan="2">AOÛT {!! $this->sortiIcon('M8') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M9')" colspan="2">SEP {!! $this->sortiIcon('M9') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M10')" colspan="2">OCT {!! $this->sortiIcon('M10') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M11')" colspan="2">NOV {!! $this->sortiIcon('M11') !!}</th>
                            <th class="bg-primary px-4 py-2  text-white font-semibold text-center w-auto cursor-pointer" wire:click="sortBy('M12')" colspan="2">DEC {!! $this->sortiIcon('M12') !!}</th>
                        @endif
                        @if ($month != '0')
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center cursor-pointer" wire:click="sortBy('S1_ca')">S1 {!! $this->sortiIcon('S1_ca') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center cursor-pointer" wire:click="sortBy('S2_ca')" >S2 {!! $this->sortiIcon('S2_ca') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center cursor-pointer" wire:click="sortBy('S3_ca')" >S3 {!! $this->sortiIcon('S3_ca') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center cursor-pointer" wire:click="sortBy('S4_ca')" >S4 {!! $this->sortiIcon('S4_ca') !!}</th>
                            <th class="bg-primary px-4 py-2 text-white font-semibold text-center cursor-pointer" wire:click="sortBy('S5_ca')" >S5 {!! $this->sortiIcon('S5_ca') !!}</th>
                        @endif
                        <th class="px-4 py-2 cursor-pointer"  wire:click="sortBy('objectif_annuel') ">OBJECTIF ANNUEL {!! $this->sortiIcon('objectif_annuel') !!}</th>
                        <th class="px-4 py-2 rounded-tr-lg cursor-pointer" wire:click="sortBy('ca_annuel')">CA TOTAL {!! $this->sortiIcon('ca_annuel') !!}</th>
                    </tr>

                    </thead>
                    <tbody>
                    @foreach($groupes as $groupe)
                        <tr class="odd:bg-gray-100">

                            <td class="px-4 py-2 font-semibold text-gray-800">
                                {{ $groupe->nom }}
                                <br>
                                    <span class="ml-2 text-xs text-gray-500">#{{ $groupe->rang_ca_annuel }}</span>

                            </td>
                            <td class="px-4 py-2 text-xs">{{ number_format($groupe->objectif_ca, 0, ',', ' ') }} €</td>

                            @if ($month === '0')
                                @for ($i = 1; $i <= 12; $i++)
                                    <td class="px-4 py-2 w-1/5 text-primary">
                                        {{ number_format($groupe->ca_month[$i] ?? 0, 0, ',', ' ') }} €
                                    </td>
                                    <td class="py-2 text-center ">
                                        @php
                                            $pourcentage = $groupe->objectif_ca != 0
                                                ? round(($groupe->ca_month[$i] / $groupe->objectif_ca) * 100)
                                                : 0;
                                        @endphp
                                        {{ $pourcentage }}%
                                    </td>
                                @endfor
                            @else
                                @for ($i = 1; $i <= 5; $i++)
                                    <td class="px-4 py-2 text-primary ">
                                        {{ number_format($groupe->caParSemaine[$i] ?? 0, 0, ',', ' ') }} €
                                    </td>
                                @endfor
                            @endif

                            <td class="px-4 py-2">{{ number_format($groupe->objectif_annuel, 0, ',', ' ') }} €</td>
                            <td class="px-4 py-2 font-semibold">
                                {{ number_format($groupe->ca_annuel, 0, ',', ' ') }} €
                                <div class="text-xs text-gray-500">Rang: {{ $groupe->rang_ca_annuel }}</div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            @endif
            </tbody>
        </table>
        <script src="{{ asset('js/jquery.js') }}"></script>
        <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
        <script src="{{ asset('js/tableSorter.js') }}"></script>
    </div>
</div>
