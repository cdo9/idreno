<div class="w-full overflow-x-auto">
    <table class="min-w-full border-collapse text-sm">
        <thead>
        <tr>
            <th class="bg-primary lg:px-4 py-2 rounded-tl-lg text-white font-semibold text-sm text-left"><a href="#" wire:click="sortBy('prenom')" >Commercial</a></th>
            <th class="bg-primary lg:px-4 py-2 text-center text-white"><a href="#" wire:click="sortBy('rdv_objectif')" > RDV<br>Objectif </a></th>
            <th class="bg-primary lg:px-4 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('argus_total')" > Argus<br>Total</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J1_argus')" > J1</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J2_argus')" > J2</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J3_argus')" > J3</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J4_argus')" > J4</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J5_argus')" > J5</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('J6_argus')" > J6</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('S1_argus')" > S1</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('S2_argus')" > S2</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('S3_argus')" > S3</a></th>
            <th class="bg-primary lg:px-4 px-2 py-2 text-center text-white font-semibold text-sm"><a href="#" wire:click="sortBy('S4_argus')" > S4</a></th>
            @if(count($totauxGlobaux->somme_argus_par_semaine) == 5)
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
                ])
                >
                    <td class="font-semibold lg:px-4 py-2 text-nowrap text-left">{{ $user->display_name }}</td>
                    <td class="lg:px-4 py-2 text-nowrap text-center">{{ $user->objectif_rdv }}</td>
                    <td class="lg:px-4 py-2 text-nowrap text-center">
                        <a href="{{route('argumentations')}}/?y={{$year}}&m={{$month}}&commercial={{$user->id}}">{{ $user->argus_count}}</a>
                    </td>

                    @for($i = 0; $i < 6; $i++)
                        <td @class([
                            'lg:px-4 py-2 text-nowrap text-center'
                        ])
                        style="
                        @if($user->is_fin_periode_essai)
                            background-color: rgba(255, 184, 106, 0.5)
                        @elseif(!$user->is_fin_periode_essai && (($user->argus_par_jours[$i] ?? 0) == 0))
                            background-color: rgba(255, 0, 0, 0.2)
                        @endif
                        "
                        >
                        @if(isset($user->argus_par_jours[$i]))
                            <a href="{{route('rendez-vous')}}/?y={{$year}}&m={{$month}}&commercial={{$user->id}}">{{ $user->argus_par_jours[$i]}}</a>
                        @else
                            -
                        @endif
                        </td>
                    @endfor

                    @for($i = 0; $i < count($user->argus_par_semaine); $i++)
                        @php
                        $background = '';
                        if($user->is_fin_periode_essai)
                            $background = "rgba(255, 184, 106, 0.5)";
                        elseif(!$user->is_fin_periode_essai && ($user->argus_par_semaine[$i]->cpt < 5))
                            $background = "rgba(255, 0, 0, 0.2)";
                        @endphp
                        <td @class([
                            'lg:px-4 py-2 text-nowrap text-center'
                        ])
                        style="background-color:{{$background}}"
                        >
                        @if(isset($user->argus_par_semaine[$i]))
                            <a href="{{route('rendez-vous')}}/?y={{$year}}&m={{$month}}&commercial={{$user->id}}">{{ $user->argus_par_semaine[$i]->cpt}}</a>
                        @else
                            -
                        @endif
                        </td>
                    @endfor
                </tr>
				@endif
            @endforeach
            @if($groupe->users && count($groupe->users) > 0)
                <tr class="bg-black font-semibold text-xs text-white border-t border-gray-300">
                    <td class="lg:px-4 py-2 text-nowrap">{{ $groupe->nom }}</td>
                    <td class="lg:px-4 py-2 text-nowrap text-center">{{ $groupe->somme_objectif_rdv }}</td>
                    <td class="lg:px-4 py-2 text-nowrap text-center">{{ $groupe->somme_argus_total }}</td>

                    @for($i = 0; $i < 6; $i++)
                        <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $groupe->somme_argus_par_jours[$i]}}</td>
                    @endfor

                    @for($i = 0; $i < count($groupe->somme_argus_par_semaine); $i++)
                        <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $groupe->somme_argus_par_semaine[$i]}}</td>
                    @endfor
                </tr>
            @endif
        @endforeach
        @if(isset($totauxSecteur) && ($isAdmin))
            <tr class="bg-lightprimary font-semibold text-xs text-darkgrey border-t">
                <td class="lg:px-4 py-2 text-nowrap">Total secteur</td>
                <td class="lg:px-4 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_objectif_rdv}} </td>
                <td class="lg:px-4 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_argus_total}} </td>
                @for($i = 0; $i < 6; $i++)
                    <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_argus_par_jours[$i] }} </td>
                @endfor
                @for($i = 0; $i < count($totauxSecteur->somme_argus_par_semaine); $i++)
                    <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_argus_par_semaine[$i]}}</td>
                @endfor
            </tr>
        @endif
        @if(isset($totauxGlobaux) && (!$isTelepro))
            <tr class="bg-lightprimary font-semibold text-xs text-darkgrey border-t">
                <td class="lg:px-4 py-2 text-nowrap">Total global</td>
                <td class="lg:px-4 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_objectif_rdv}} </td>
                <td class="lg:px-4 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_argus_total}} </td>
                @for($i = 0; $i < 6; $i++)
                    <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_argus_par_jours[$i] }} </td>
                @endfor
                @for($i = 0; $i < count($totauxGlobaux->somme_argus_par_semaine); $i++)
                    <td class="lg:px-4 px-2 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_argus_par_semaine[$i]}}</td>
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
