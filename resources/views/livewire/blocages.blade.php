<div>
    <div >
        <h1 class="text-3xl font-bold text-gray-800 text-center py-6">
            Blocages
        </h1>
    </div>
    <div class="w-full overflow-x-auto">
        <table class="sortable min-w-full text-xs text-center rounded-md divide-gray-200 border border-gray-200">
            <thead>
            <tr>
                <th class="bg-primary px-4 py-2 rounded-tl-lg text-white font-semibold">Vendeur</th>
                <th class="bg-primary px-4 py-2 text-white font-semibold">Argus <br> Manquantes</th>
                <!--<th class="bg-primary px-4 py-2 text-white font-semibold">Evaluations <br> Manquantes</th>-->
                <!--<th class="bg-primary px-4 py-2 text-white font-semibold">Entretiens Annuels <br> Manquants</th>-->
                <!--<th class="bg-primary px-4 py-2 text-white font-semibold">Rdv <br> À confirmer</th>-->
                <!--<th class="bg-primary px-4 py-2 text-white font-semibold">Prospection <br> En cours</th>-->
                <th class="bg-primary px-4 py-2 text-white font-semibold">Visites chantiers <br> Manquantes</th>
                <th class="bg-primary px-4 py-2 text-white font-semibold">Avis client <br> Manquants</th>
                <th class="bg-primary px-4 py-2 text-white font-semibold">Panneaux chantier <br> Manquants</th>
                <th class="bg-primary px-4 py-2 rounded-tr-lg text-white font-semibold">Actions</th>
            </tr>
            </thead>
            <tbody>
               
            @foreach ($users as $user)
                <tr class="odd:bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                        {{ $user->nom }} {{ $user->prenom }}
                    </td>

                    <td class="px-4 py-3 text-xs text-gray-700 text-center">
                        @if (count($blocagesParUser[$user->id]['ecarts']) == 0)
                            ✅
                        @else
                            <table>
                                <tr>
                                    <th>Date</th>
                                    <th>RDV</th>
                                </tr>
                                @foreach($blocagesParUser[$user->id]['ecarts'] as $ecart)
                                    <tr class="even:bg-gray-300">
                                        <td> {{ $ecart['date'] }} </td>
                                        <td>
                                            @foreach($ecart['rdv'] as $rdv)
                                                @capitalizeLower($rdv->prenom) @capitalizeLower($rdv->nom)
                                                <br>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        @if($blocagesParUser[$user->id]['ind']['alerte_visite'])
                            {{ floor($blocagesParUser[$user->id]['ind']['ratio_visites']) }} %
                            <table>
                                <tr>
                                    <td>
                                        @foreach($blocagesParUser[$user->id]['ind']['details'] as $detail)
                                            @if($detail->visite_chantier != 1)
                                                <a href="nouvelle-pose/{{$detail->id}}" >{{$detail->prenom . " " . $detail->nom}}</a>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        @else
                            ✅
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        @if($blocagesParUser[$user->id]['ind']['alerte_avis'])
                            {{ floor($blocagesParUser[$user->id]['ind']['ratio_avis']) }} %
                            <table>
                                <tr>
                                    <td>
                                        @foreach($blocagesParUser[$user->id]['ind']['details'] as $detail)
                                            @if($detail->avis_client != 1)
                                               <a href="nouvelle-pose/{{$detail->id}}" >{{$detail->prenom . " " . $detail->nom}}</a>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        @else
                            ✅
                        @endif
                    </td>

                    <td class="px-4 py-3">
                        @if($blocagesParUser[$user->id]['ind']['alerte_facebook'])
                            {{ floor($blocagesParUser[$user->id]['ind']['ratio_facebook']) }} %
                            <table>
                                <tr>
                                    <td>
                                        @foreach($blocagesParUser[$user->id]['ind']['details'] as $detail)
                                            @if($detail->avis_facebook != 1)
                                                <a href="nouvelle-pose/{{$detail->id}}" >{{$detail->prenom . " " . $detail->nom}}</a>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        @else
                            ✅
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ((!$user->released || !\Carbon\Carbon::parse($user->released)->isToday()))
                            @if(count($blocagesParUser[$user->id]['ecarts']) != 0
                                || $blocagesParUser[$user->id]['ind']['alerte_facebook']
                                || $blocagesParUser[$user->id]['ind']['alerte_avis']
                                || $blocagesParUser[$user->id]['ind']['alerte_visite'])

                                @if($isAdmin ||(
                                    $isDirecteur && 
                                    count($blocagesParUser[$user->id]['ecarts']) != 0
                                    && !$blocagesParUser[$user->id]['ind']['alerte_facebook']
                                    && !$blocagesParUser[$user->id]['ind']['alerte_avis']
                                    && !$blocagesParUser[$user->id]['ind']['alerte_visite']
                                    /*&& count($blocagesParUser[$user->id]['prospections']) == 0
                                    && count($blocagesParUser[$user->id]['aconfirmer']) == 0
                                    && count($blocagesParUser[$user->id]['entretiens']) == 0
                                    && count($blocagesParUser[$user->id]['evaluations']) == 0*/
                                ))
                                    <button wire:click="updateReleased({{$user->id}})"type="button" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow">Débloquer</button>
                                @endif
                            @endif
                        @else
                            Débloqué
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>
        <script src="{{ asset('js/jquery.js') }}"></script>
        <script src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
        <script src="{{ asset('js/tableSorter.js') }}"></script>
    </div>
</div>
