   
<div class="w-full overflow-x-auto text-sm">
    <table class="min-w-full border-collapse text-sm">
        <thead>
        <tr>
            <th class="bg-primary lg:px-4 py-2 rounded-tl-lg text-white font-semibold text-sm text-left"><a href="#" wire:click="sortBy('prenom')" >Commercial</a></th>
            <th class="bg-primary lg:px-4 py-2 text-center text-white"><a href="#" wire:click="sortBy('rdv_objectif')" > RDV<br>Objectif </a></th>
            <th class="bg-primary lg:px-4 py-2 text-center text-white"><a href="#" wire:click="sortBy('rdv_total')" > RDV<br>Total </a></th>
            <th class="bg-primary lg:px-4 py-2 text-center text-white font-semibold text-sm">CC / Jour</th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J1_rdv')" >J1</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J2_rdv')" >J2</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J3_rdv')" >J3</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J4_rdv')" >J4</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J5_rdv')" >J5</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J6_rdv')" >J6</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('S1_rdv')" >S1</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('S2_rdv')" >S2</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('S3_rdv')" >S3</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('S4_rdv')" >S4</a></th>
            @if(count($totauxGlobaux->somme_rdv_par_semaine) == 5)
                <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm rounded-tr-lg"><a href="#" wire:click="sortBy('S5_rdv')" >S5</a></th>
            @else
                <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('S5_rdv')" >S5</a></th>
                <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm rounded-tr-lg"><a href="#" wire:click="sortBy('S6_rdv')" >S6</a></th>
            @endif
        </tr>
        </thead>

        <tbody>
        @foreach ($groupes as $groupe)
            @foreach ($groupe->users as $user)
				@if(!$user->hasRole('admin'))
                <tr @class([
                        'odd:bg-gray-200 even:bg-white border-b text-xs border-gray-300',
                        'bg-orange-300' => $user->is_fin_periode_essai,
                        'text-red-600' => $user->active === 0
                    ])>
                    <td class="lg:px-4 py-2 text-nowrap font-semibold text-left">{{ $user->display_name }}</td>
                    <td class="lg:px-4 py-2 text-nowrap text-center">{{ $user->objectif_rdv }}</td>
                    <td class="lg:px-4 py-2 text-nowrap text-center">
                        <a href="{{route('rendez-vous')}}/?y={{$year}}&m={{$month}}&commercial={{$user->id}}">{{ $user->rdv_total}}</a>
                    </td>

                    <td @class([
                            'lg:px-4 py-2 text-nowrap text-center',
                            //'bg-orange-300' => $user->is_fin_periode_essai,
                            //'bg-red-300 bg-opacity-25' => !$user->is_fin_periode_essai && (($user->cc_jour ?? 0) < 1),
                        ])
                        @if(!$user->is_fin_periode_essai && (($user->cc_jour ?? 0) < 1))
                            style="background-color: rgba(255, 0, 0, 0.2)"
                        @endif
                        @if($user->is_fin_periode_essai)
                            style="background-color: rgba(255, 184, 106, 0.5)"
                        @endif
                        >
                        {{ number_format($user->cc_jour, 1) }}
                    </td>

                    @for($i = 0; $i < 6; $i++)
                        <td @class([
                                'lg:px-4 py-2 text-nowrap text-center',
                                //'bg-orange-300' => $user->is_fin_periode_essai,
                                //'bg-red-300 bg-opacity-25' => !$user->is_fin_periode_essai && (($user->rdv_par_jours[$i] ?? 0) == 0),
                            ])
                            @if(!$user->is_fin_periode_essai && (($user->rdv_par_jours[$i]["count"] ?? 0) == 0))
                                style="background-color: rgba(255, 0, 0, 0.2)"
                            @endif
                            @if($user->is_fin_periode_essai)
                                style="background-color: rgba(255, 184, 106, 0.5)"
                            @endif
                            >
                            @if(isset($user->rdv_par_jours[$i]))
                                <a href="{{route('rendez-vous')}}/?y={{$year}}&m={{$month}}&commercial={{$user->id}}">{{ $user->rdv_par_jours[$i]}}</a>
                            @else
                                -
                            @endif
                        </td>
                    @endfor

                    @for($i = 0; $i < count($user->rdv_par_semaine); $i++)
                        <td @class([
                                'lg:px-4 py-2 text-nowrap text-center',
                                //'bg-orange-300' => $user->is_fin_periode_essai,
                                //'bg-red-300 bg-opacity-25' => !$user->is_fin_periode_essai && (($user->rdv_par_semaine[$i] ?? 0) < 5),
                            ])
                            @if(!$user->is_fin_periode_essai && (($user->rdv_par_semaine[$i]["count"] ?? 0) == 0))
                                style="background-color: rgba(255, 0, 0, 0.2)"
                            @endif
                            @if($user->is_fin_periode_essai)
                                style="background-color: rgba(255, 184, 106, 0.5)"
                            @endif
                            >
                            @if(isset($user->rdv_par_semaine[$i]))
                                <a href="{{route('rendez-vous')}}/?y={{$year}}&m={{$month}}&commercial={{$user->id}}">{{ $user->rdv_par_semaine[$i]}}</a>
                            @else
                                -
                            @endif
                        </td>
                    @endfor
                </tr>
				@endif

            @endforeach
            @if($groupe->users && count($groupe->users) > 0)
                <tr class="bg-black text-xs font-semibold text-white border-t border-gray-300">
                    <td class="lg:px-4 py-2 text-nowrap">{{ $groupe->nom }}</td>
                    <td class="lg:px-4 py-2 text-nowrap text-center">{{ $groupe->somme_objectif_rdv }}</td>
                    <td class="lg:px-4 py-2 text-nowrap text-center">{{ $groupe->somme_rdv_total }}</td>
                    <td class="lg:px-4 py-2 text-nowrap text-center">{{ number_format($groupe->total_cc_jour,1) }}</td>


                    @for($i = 0; $i < 6; $i++)
                        <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $groupe->somme_rdv_par_jours[$i] }} </td>
                    @endfor

                    @for($i = 0; $i < count($groupe->somme_rdv_par_semaine); $i++)
                        <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $groupe->somme_rdv_par_semaine[$i]}}</td>
                    @endfor
                </tr>
            @endif
        @endforeach
        @if(isset($totauxSecteur) && ($isAdmin))
            <tr class="bg-lightprimary font-semibold text-xs text-darkgrey border-t">
                <td class="lg:px-4 py-2 text-nowrap">Total secteur</td>
                <td class="lg:px-4 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_objectif_rdv}} </td>
                <td class="lg:px-4 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_rdv_total}} </td>
                <td class="lg:px-4 py-2 text-nowrap text-center">{{ number_format($totauxSecteur->total_cc_jour,1) }}</td>
                @for($i = 0; $i < 6; $i++)
                    <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_rdv_par_jours[$i] }} </td>
                @endfor
                @for($i = 0; $i < count($totauxSecteur->somme_rdv_par_semaine); $i++)
                    <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_rdv_par_semaine[$i]}}</td>
                @endfor
            </tr>
        @endif
        @if(isset($totauxGlobaux) && (!$isTelepro))
            <tr class="bg-lightprimary font-semibold text-xs text-darkgrey border-t">
                <td class="lg:px-4 py-2 text-nowrap">Total global</td>
                <td class="lg:px-4 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_objectif_rdv}} </td>
                <td class="lg:px-4 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_rdv_total}} </td>
                <td class="lg:px-4 py-2 text-nowrap text-center">{{ number_format($totauxGlobaux->total_cc_jour,1) }}</td>
                @for($i = 0; $i < 6; $i++)
                    <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_rdv_par_jours[$i] }} </td>
                @endfor
                @for($i = 0; $i < count($totauxGlobaux->somme_rdv_par_semaine); $i++)
                    <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_rdv_par_semaine[$i]}}</td>
                @endfor
            </tr>
        @endif

        </tbody>
    </table>
    <div class=" pt-6  text-sm mb-2">
        <span class="inline-block bg-orange-300 px-2 py-1 rounded text-xs text-white">Fin de période d'essai</span>
    </div>
    <div class="bg-white text-red-600 border-1 border-black rounded-md px-3 py-1.5 inline-block text-xs font-semibold">
        Désactivé
    </div>
</div>