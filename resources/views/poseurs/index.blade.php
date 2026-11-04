@extends('layouts.app')
@section('content')
<div class="flex flex-col w-full mt-8 gap-6 text-start px-4 md:px-0">
    <div>
        <a href="{{ route('poseurs.create') }}"
            class="inline-flex items-center px-3 py-1 bg-lightprimary text-white rounded-full hover:bg-primary transition duration-200">
            <i class="fa-solid fa-plus mr-2"></i> Créer
        </a>
    </div>
    <div class="overflow-x-auto rounded-2xl md:border md:border-[#DCDADA] bg-[#F3F3F3]">
        <div class="hidden md:block">
            <table class="sortable min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-primary uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-primary uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($poseurs as $poseur)
                    <tr class="hover:bg-gray-50 transition duration-200">
                        <td class="px-6 py-4 font-semibold text-gray-700">{{ $poseur->poseur }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('poseurs.edit', $poseur->id) }}"
                               class="inline-flex items-center px-3 py-1 bg-lightprimary text-white rounded-full hover:bg-primary transition duration-200">
                                <i class="fa-solid fa-pen-to-square mr-2"></i> Éditer
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <script src="{{ asset('js/jquery.js') }}"></script>
            <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
            <script src="{{ asset('js/tableSorter.js') }}"></script>
        </div>

        <!-- Mobile view -->
        <div class="block md:hidden space-y-4 p-4">
            @foreach($poseurs as $poseur)
            <div class="bg-white rounded-xl shadow-md p-5 border border-[#DCDADA] flex flex-col gap-3 transition duration-200 hover:shadow-lg">
                <div>
                    <span class="text-xs text-gray-500 font-semibold uppercase">Nom</span><br>
                    <span class="font-bold text-primary text-lg">{{ $poseur->poseur }}</span>
                </div>
                <div>
                    <a href="{{ route('poseurs.edit', $poseur->id) }}"
                       class="inline-flex items-center px-3 py-1 bg-lightprimary text-white rounded-full hover:bg-primary transition duration-200">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Éditer
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection