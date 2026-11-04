<div>
    <div class="w-full overflow-x-auto">
        <table class="min-w-full border-collapse text-xs">
            <thead class="bg-gray-100 text-white">
                <tr>
                    <th class="bg-primary lg:px-4 px-1 py-2 rounded-tl-lg"><a href="#" wire:click="sortBy('prenom')" class="flex items-center gap-2 h-8 overflow-hidden"> Commercial</a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('ca_objectif')" > CA<br>Objectif</a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('ca_realise')" > CA<br>Realise</a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('rdv_objectif')" > RDV<br>Objectif </a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('rdv_total')" > RDV<br>Total </a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('argus_total')"> Argus<br>Total </a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('S1_ca')"> S1 </a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('S2_ca')"> S2 </a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('S3_ca')"> S3 </a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('S4_ca')"> S4 </a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('S5_ca')"> S5 </a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center text-white font-semibold">Déperdition</th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center text-white font-semibold">RDV 48H</th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center text-white font-semibold">Transformation</th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center"><a href="#" wire:click="sortBy('panier_moyen')">Panier<br>Moyen</a></th>
                    <th class="bg-primary lg:px-4 px-1 py-2 text-center rounded-tr-lg">Marge</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupes as $groupe)
                    @foreach($groupe->users as $user )
                        @if(($user->nb_ventes > 0 || $user->active == 1) && !$user->hasRole('admin'))
                        <tr class="odd:bg-gray-200 even:bg-white border-b text-xs border-gray-300 {{ $user->active === 0 ? 'text-red-600' : '' }}">
                            <td class="lg:px-4 px-1 py-2 font-semibold text-left text-nowrap truncate"
                                @if($user->is_fin_periode_essai)
                                    style="background-color: rgba(255, 184, 106, 0.5)"
                                @endif
                                >
                                {{ $user->display_name }} <br><span style="font-size:9px" class='italic'>({{ \Carbon\Carbon::parse($user->date_entree)->format('d/m/Y') }})</span>
                            </td>
                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center"
                                @if($user->is_fin_periode_essai)
                                    style="background-color: rgba(255, 184, 106, 0.5)"
                                @endif
                                >
                                {{ number_format($user->objectif_ca, 0, ',', ' ') }} €
                            </td>
                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center"
                                @if($user->is_fin_periode_essai)
                                    style="background-color: rgba(255, 184, 106, 0.5)"
                                @endif
                                >
                                <a href="{{route('ventes', ['anneeParam' => $this->year, 'moisParam' => $this->month, 'commercial' => $user->id])}}">
                                    {{ number_format($user->ca_realise, 0, ',', ' ') }} €
                                </a>
                            </td>
                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center"
                                @if($user->is_fin_periode_essai)
                                    style="background-color: rgba(255, 184, 106, 0.5)"
                                @endif
                                >
                                {{ $user->objectif_rdv }}
                            </td>
                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center"
                                @if($user->is_fin_periode_essai)
                                    style="background-color: rgba(255, 184, 106, 0.5)"
                                @endif
                                >
                                <a href="{{route('rendez-vous', ['y' => $this->year, 'm' => $this->month, 'commercial' => $user->id])}}">
                                    {{ $user->rdv_total }}
                                </a>
                            </td>
                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center"
                                @if($user->is_fin_periode_essai)
                                    style="background-color: rgba(255, 184, 106, 0.5)"
                                @endif
                                >
                                <a href="{{route('argumentations', ['y' => $this->year, 'm' => $this->month, 'commercial' => $user->id])}}">
                                    {{ $user->argus_total }}
                                </a>
                            </td>

                            @for ($i = 0; $i < 5; $i++)
                                <td class="lg:px-4 px-1 py-2  text-nowrap text-center"
                                    @if($user->is_fin_periode_essai)
                                        style="background-color: rgba(255, 184, 106, 0.5)"
                                    @endif
                                    >
                                    <a href="{{route('ventes', ['anneeParam' => $this->year, 'moisParam' => $this->month, 'commercial' => $user->id])}}">
                                        {{ isset($user->ca_par_semaine[$i]) ? number_format($user->ca_par_semaine[$i], 0, ',', ' ') . ' ' : '-' }}
                                    </a>
                                </td>
                            @endfor

                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center"
                                @if($user->is_fin_periode_essai)
                                    style="background-color: rgba(255, 184, 106, 0.5)"
                                @endif 
                                @if(!$user->is_fin_periode_essai && (($user->deperdition ?? 0) > 20))
                                   style="background-color: rgba(255, 0, 0, 0.2)"
                                @endif 
                                >
                                {{ number_format($user->deperdition, 0) }} %
                            </td>

                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center"
                                style="
                                @if($user->is_fin_periode_essai)
                                    background-color: rgba(255, 184, 106, 0.5)
                                @elseif(!$user->is_fin_periode_essai && (($user->rdv48h ?? 0) < 66))
                                   background-color: rgba(255, 0, 0, 0.2)
                                @endif  "
                                >
                                {{ $user->rdv48h ?? 0 }} %
                            </td>

                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center" 
                                style="
                                @if($user->is_fin_periode_essai)
                                   background-color: rgba(255, 184, 106, 0.5)
                                @elseif(!$user->is_fin_periode_essai && (($user->transformation ?? 0) < 25))
                                   background-color: rgba(255, 0, 0, 0.2)
                                @endif "                                 
                                >
                                {{ $user->transformation ?? 0 }} %
                            </td>


                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center"
                                @if($user->is_fin_periode_essai)
                                    style="background-color: rgba(255, 184, 106, 0.5)"
                                @endif
                                >
                                {{ number_format($user->panier_moyen, 0, ',', ' ') }} €
                            </td>

                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center "
                                @if($user->is_fin_periode_essai)
                                    style="background-color: rgba(255, 184, 106, 0.5)"
                                @endif
                                >
                                <a href="{{route('ventes', ['anneeParam' => $this->year, 'moisParam' => $this->month, 'commercial' => $user->id])}}">
                                    {{ number_format($user->marge, 0, ',', ' ') }} €
                                </a>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                    @if($groupe->users && count($groupe->users) > 0)
                        <tr class="bg-black font-semibold  text-white text-xs border-t border-gray-300">
                            <td class="lg:px-4 px-1 py-2 text-nowrap text-left">{{ $groupe->nom_secteur . ' - ' . $groupe->nom }}</td>
                            <td class="lg:px-4 px-1 py-2  text-nowrap text-center">{{ number_format($groupe->somme_objectif_ca,0,',',' ') }} </td>
                            <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ number_format($groupe->somme_ca_realise,0,',',' ') }} </td>
                            <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $groupe->somme_objectif_rdv }}</td>
                            <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $groupe->somme_rdv_total}}</td>
                            <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $groupe->somme_argus_total}}</td>
                            @for ($i = 0; $i < 5; $i++)
                            <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ isset($groupe->somme_ca_par_semaine[$i]) ? number_format($groupe->somme_ca_par_semaine[$i], 0, ',', ' ') . ' ' : '-' }}</td>
                            @endfor
                            <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{$groupe->somme_deperdition ?? 0}} % </td>
                            <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $groupe->pourcentage_rdv48h }} %</td>
                            <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{$groupe->somme_transformation ?? 0}} % </td>
                            <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ number_format($groupe->somme_panier_moyen,0,',',' ') }} </td>
                            <td  class="lg:px-4 px-1 py-2 text-nowrap text-center ">{{ number_format($groupe->somme_marge,0,',',' ') }} </td>
                        </tr>
                    @endif
                @endforeach
                @if(isset($totauxSecteur) && (!$isTelepro))
                <tr class="bg-lightprimary font-semibold text-xs text-darkgrey border-t">
                    <td class="lg:px-4 px-1 py-2 text-nowrap">Total secteur</td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ number_format($totauxSecteur->somme_objectif_ca, 0, ',', ' ') }} </td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ number_format($totauxSecteur->somme_ca_realise, 0, ',', ' ') }} </td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_objectif_rdv }}</td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_rdv_total }}</td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $totauxSecteur->somme_argus_total }}</td>
                    @for ($i = 0; $i < 5; $i++)
                        <td class="lg:px-4 px-1 py-2 text-nowrap text-center">
                            {{ isset($totauxSecteur->somme_ca_par_semaine[$i])
                                ? number_format($totauxSecteur->somme_ca_par_semaine[$i], 0, ',', ' ') . ' '
                                : '-' }}
                        </td>
                    @endfor
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">
                        {{ $totauxSecteur->somme_deperdition !== null ? number_format($totauxSecteur->somme_deperdition, 0) . ' %' : '-' }}
                    </td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $totauxSecteur->pourcentage_rdv48h }} %</td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">
                        {{ $totauxSecteur->somme_transformation !== null ? number_format($totauxSecteur->somme_transformation, 0) . ' %' : '-' }}
                    </td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ number_format($totauxSecteur->somme_panier_moyen, 0, ',', ' ') }} </td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ number_format($totauxSecteur->somme_marge, 0, ',', ' ') }} </td>
                </tr>
                @endif
                @if(isset($totauxGlobaux) && ($isAdmin))
                <tr class="bg-lightprimary font-semibold text-xs text-darkgrey border-t">
                    <td class="lg:px-4 px-1 py-2 text-nowrap">Total global</td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ number_format($totauxGlobaux->somme_objectif_ca, 0, ',', ' ') }} </td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ number_format($totauxGlobaux->somme_ca_realise, 0, ',', ' ') }} </td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_objectif_rdv }}</td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_rdv_total }}</td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $totauxGlobaux->somme_argus_total }}</td>
                    @for ($i = 0; $i < 5; $i++)
                        <td class="lg:px-4 px-1 py-2 text-nowrap text-center">
                            {{ isset($totauxGlobaux->somme_ca_par_semaine[$i])
                                ? number_format($totauxGlobaux->somme_ca_par_semaine[$i], 0, ',', ' ') . ' '
                                : '-' }}
                        </td>
                    @endfor
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">
                        {{ $totauxGlobaux->somme_deperdition !== null ? number_format($totauxGlobaux->somme_deperdition, 0) . ' %' : '-' }}
                    </td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ $totauxGlobaux->pourcentage_rdv48h }} %</td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">
                        {{ $totauxGlobaux->somme_transformation !== null ? number_format($totauxGlobaux->somme_transformation, 0) . ' %' : '-' }}
                    </td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ number_format($totauxGlobaux->somme_panier_moyen, 0, ',', ' ') }} </td>
                    <td class="lg:px-4 px-1 py-2 text-nowrap text-center">{{ number_format($totauxGlobaux->somme_marge, 0, ',', ' ') }} </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class=" pt-6  text-sm mb-2">
    <span class="inline-block bg-orange-300 px-2 py-1 rounded text-xs text-white">Fin de période d'essai</span>
    </div>
    <div class="bg-white text-red-600 border-1 border-black rounded-md px-3 py-1.5 inline-block text-xs font-semibold">
    Désactivé
    </div>
</div>
