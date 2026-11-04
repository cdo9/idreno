<div>
    @if (session()->has('message'))
        <div class="mb-4 px-4 py-3 rounded text-green-800 bg-green-100 border border-green-200">
            {{ session('message') }}
        </div>
    @endif
    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap bg-white px-4 py-3 border-b border-gray-200 mb-4 shadow-sm rounded-md">
        <div class="flex items-center space-x-4">
            <h2 class="text-xl font-semibold text-primary">Challenges</h2>
            @if(auth()->user()?->hasRole('admin'))
                <a href="{{ url('/create-challenge') }}"
                   class="bg-primary text-white px-3 py-1 rounded-md text-sm hover:bg-primary transition-colors duration-200">
                    + Nouveau
                </a>
            @endif
        </div>

        <!-- Filtres -->
        <div class="flex items-end space-x-3">
            <div>
                <label class="block text-xs text-gray-600 mb-1">Mois</label>
                <select wire:model.live="month"
                        class="border border-gray-300 text-sm rounded-md p-1 focus:ring-primary focus:border-primary">
                    @foreach ($months as $key => $month)
                        <option value="{{ $key }}">{{ $month }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-600 mb-1">Année</label>
                <select wire:model.live="year"
                        class="border border-gray-300 text-sm rounded-md p-1 focus:ring-primary focus:border-primary">
                    @foreach ($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="w-full overflow-x-auto">
        <table class="sortable min-w-full text-sm text-center rounded-md divide-gray-200 border border-gray-200">
            <thead>
            <tr>
                <th class="bg-primary px-4 py-2 rounded-tl-lg text-white font-semibold">Date</th>
                <th class="bg-primary px-4 py-2 text-white font-semibold">Challenge</th>
                <th class="bg-primary px-4 py-2 rounded-tr-lg text-white font-semibold">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($challenges as $challenge)
                <tr class="odd:bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                    <td class="px-4 py-3 text-sm text-gray-900">
                        {{ \Carbon\Carbon::parse($challenge->date)->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 text-center">
                        {!! nl2br(e($challenge->challenge)) !!}
                    </td>
                    <td class="px-4 py-3 text-sm space-x-2">
                        <a class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-[#77BC23] rounded hover:bg-green-300 transition"
                           href="{{ url('formulaire-challenge?id=' . $challenge->id) }}">
                            <span class="fa fa-edit mr-1"></span> Éditer
                        </a>
                        <a class="inline-flex items-center mt-3 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded hover:bg-red-200 transition"
                         href="{{ route('Delete-challenge', ['id' => $challenge->id]) }}">
                            <span class="fa fa-trash mr-1"></span> Supprimer
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-6 text-gray-500 text-center">
                        Aucun challenge trouvé.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <script src="{{ asset('js/jquery.js') }}"></script>
        <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
        <script src="{{ asset('js/tableSorter.js') }}"></script>
    </div>
</div>
