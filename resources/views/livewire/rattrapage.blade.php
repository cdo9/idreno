<div class="max-w-7xl mx-auto px-4 py-8 space-y-10">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-800">Activité manquante</h1>
    </div>

    {{-- Ecarts RDV / Argus --}}
    @if(count($ecarts) > 0)
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#77BC23] text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Date</th>
                    <th class="px-4 py-2 text-left">Rendez-vous enregistrés</th>
                    <th class="px-4 py-2 text-left">Argumentations enregistrées</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                @foreach($ecarts as $ecart)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ \Carbon\Carbon::parse($ecart['date'])->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">
                            @foreach($ecart['rdv'] as $rdv)
                                {{ $rdv->prenom }} {{ $rdv->nom }}<br>
                            @endforeach
                        </td>
                        <td class="px-4 py-2">
                            @foreach($ecart['argu'] as $argu)
                                {{ $argu->prenom }} {{ $argu->nom }}<br>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Evaluations --}}
    @if(!empty($evaluations))
        @php
            $filteredEvaluations = collect($evaluations)->filter(function($eval) {
                return !empty($eval->prenom) || !empty($eval->nom);
            });
        @endphp

        @if($filteredEvaluations->count() > 0)
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#77BC23] text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Argumentations sans évaluation</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                    @php
                        $groupedEvaluations = $filteredEvaluations->groupBy('date');
                    @endphp
                    @foreach($groupedEvaluations as $date => $evals)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">
                                @foreach($evals as $eval)
                                    {{ $eval->prenom }} {{ $eval->nom }}<br>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    {{-- Entretiens --}}
    @if(!empty($entretiens))
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#77BC23] text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Entretien annuel manquant</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                @foreach($entretiens as $u)
                    <tr>
                        <td class="px-4 py-2">{{ getCommercialNom($u) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- A confirmer --}}
    @if(!empty($aconfirmer))
        @php
            $filteredAConfirmer = collect($aconfirmer)->filter(function($rdv) {
                return !empty($rdv->prenom) || !empty($rdv->nom);
            });
        @endphp

        @if($filteredAConfirmer->count() > 0)
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#77BC23] text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Rendez-vous non confirmés</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                    @php
                        $groupedAConfirmer = $filteredAConfirmer->groupBy('date');
                    @endphp
                    @foreach($groupedAConfirmer as $date => $rdvs)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">
                                @foreach($rdvs as $rdv)
                                    {{ $rdv->prenom }} {{ $rdv->nom }}<br>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    {{-- Prospections --}}
    @if(!empty($prospections))
        @php
            $filteredProspections = collect($prospections)->filter(function($prospect) {
                return !empty($prospect->zone) || !empty($prospect->commune);
            });
        @endphp

        @if($filteredProspections->count() > 0)
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#77BC23] text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Prospection en cours</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                    @php
                        $groupedProspections = $filteredProspections->groupBy('date');
                    @endphp
                    @foreach($groupedProspections as $date => $prospects)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">
                                @foreach($prospects as $prospect)
                                    {{ $prospect->zone }} - {{ $prospect->commune }}<br>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    {{-- Indicateurs chantiers --}}

        @php
            $poses = $this->indicateurs['details'];
            $hasVisitesManquantes = $this->indicateurs['alerte_visite'];
            $hasAvisManquants = collect($poses)->where('avis_client', false)->count() > 0;
            $hasPanneauxManquants = collect($poses)->where('avis_facebook', false)->count() > 0;
        @endphp

        @if($this->indicateurs['alerte_visite'] || $this->indicateurs['alerte_avis'] || $this->indicateurs['alerte_facebook'])
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#77BC23] text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Visites chantiers manquantes {{$indicateurs["ratio_visites"]}} %</th>
                        <th class="px-4 py-2 text-left">Avis clients manquants {{$indicateurs["ratio_avis"]}} % </th>
                        <th class="px-4 py-2 text-left">Panneaux chantiers manquants {{$indicateurs["ratio_facebook"]}} %</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                    <tr>
                        <td class="px-4 py-2">
                            @foreach($poses as $pose)
                                @if(!$pose->visite_chantier)
                                    {{ $pose->prenom ?? '' }} {{ $pose->nom ?? '' }}<br>
                                @endif
                            @endforeach
                        </td>
                        <td class="px-4 py-2">
                            @foreach($poses as $pose)
                                @if(!$pose->avis_client)
                                    {{ $pose->prenom ?? '' }} {{ $pose->nom ?? '' }}<br>
                                @endif
                            @endforeach
                        </td>
                        <td class="px-4 py-2">
                            @foreach($poses as $pose)
                                @if(!$pose->avis_facebook)
                                    {{ $pose->prenom ?? '' }} {{ $pose->nom ?? '' }}<br>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

@endif
