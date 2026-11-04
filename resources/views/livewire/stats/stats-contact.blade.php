<div>
    <div class="flex items-center justify-between flex-wrap bg-white px-4 py-3 border-b border-gray-200 mb-4 shadow-sm rounded-md">
        <div class="flex items-center space-x-4">
            <h2 class="text-xl font-semibold text-primary">Stats Contacts</h2>
        </div>

        <div class="py-5 flex items-center space-x-3">
            <div>
                <label class="text-xl font-semibold text-primary mb-2">Date début</label>
                <input type="date" wire:model.live="dateDebut" value="{{ $dateDebut }}"
                       class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
            </div>
            <div>
                <label class="text-xl font-semibold text-primary mb-2">Date fin</label>
                <input type="date" wire:model.live="dateFin" value="{{ $dateFin }}"
                       class="border border-gray-300 rounded-full px-4 py-3 focus:ring-2 focus:ring-primary focus:outline-none bg-background-grey w-full">
            </div>
        </div>
    </div>

    <div class="w-full overflow-x-auto">
        <table class="sortable min-w-full text-xs lg:text-sm text-center rounded-md divide-gray-200">
            <thead class="min-w-full border-collapse ">
            <tr>
                <th class="bg-primary lg:px-4 py-2 rounded-tl-lg text-white text-center font-semibold cursor-pointer">Commercial</th>
                <th class="bg-primary lg:px-4 py-2 text-white text-center font-semibold cursor-pointer ">Total</th>
                @foreach($types as $type)
                    <th class="bg-primary lg:px-4 py-2 text-white text-center font-semibold cursor-pointer {{ $loop->last ? 'rounded-tr-lg' : '' }}" colspan="2">{{ $type }}</th>
                @endforeach
            </tr>
            <tr>
                <th class="bg-gray-100 lg:px-4 py-1 text-gray-600 text-xs"></th>
                <th class="bg-gray-100 lg:px-4 py-1 text-gray-600 text-xs"></th>
                @foreach($types as $type)
                    <th class="bg-gray-100 lg:px-2 py-1 text-gray-600 text-xs border-l border-gray-300">Nb</th>
                    <th class="bg-gray-100 lg:px-2 py-1 text-gray-600 text-xs ">%</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($statsData as $userId => $data)
                <tr class="odd:bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                    <td class="lg:px-4 py-3 font-medium text-gray-900">{{ $this->getCommercialNom($data['commercial']) }}</td>
                    <td class="lg:px-4 py-3 ">
                        <a href="{{ route('listing-client', [
                                'vendeur' => $data['commercial']->id,
                                'date_debut' => $dateDebut,
                                'date_fin' => $dateFin,
                                'type' => ""
                            ]) }}"
                           class="text-lightprimary hover:text-primary font-semibold hover:underline transition-colors duration-200">
                            {{ $data['total'] }}
                        </a>
                    </td>
                    @foreach($types as $k => $type)
                        @php
                            $stat1 = $data['stats'][$k] ?? 0;
                            $stat2 = $data['total'] ? round($stat1 / $data['total'] * 100, 1) : 0;
                        @endphp
                        <td class="lg:px-3 py-3 border-l border-gray-200">
                            @if($stat1 > 0)
                                <span class="inline-flex items-center px-2 py-1 font-medium bg-green-200 text-[#77BC23] rounded-full">
                                        {{ $stat1 }}
                                    </span>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="lg:px-3 py-3 rounded-tr-lg">
                            @if($stat2 > 0)
                                <span class="text-gray-600 font-medium">{{ $stat2 }}%</span>
                            @else
                                <span class="text-gray-400">0%</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        <script src="{{ asset('js/jquery.js') }}"></script>
        <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
        <script src="{{ asset('js/tableSorter.js') }}"></script>
    </div>
</div>
