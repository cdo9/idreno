@php
use Carbon\Carbon;
use App\Services\CommissionService;
Carbon::setLocale('fr');
@endphp

<div class="flex flex-col" style="font-size: 14px;">
    @isset ($error)
        <div>{{$error}}</div>
    @else

        <div class="flex flex-col gap-6 print:p-10 lg:p-10 bg-background rounded-xl justify-around">
            <div class="flex flex-row items-center justify-between mb-4">
                @if($isCommissionMode)
                    <div class="print:hidden flex justify-end mt-4 mb-4">
                         <button wire:click="redirectToVentes"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-full flex items-center gap-2 transition-colors duration-200">
                            <i class="fas fa-arrow-left"></i>
                            Retour aux ventes
                        </button>
                    </div>
                    <h2 class="text-2xl font-bold absolute left-1/2 transform -translate-x-1/2 text-primary">Commissions  {{Carbon::createFromFormat('m', $moisParam)->isoFormat('MMMM') . ' ' . $anneeParam }}</h2>
                @else
                    <h2 class="text-2xl font-bold text-primary">Tableau des Ventes et commissions</h2>
                @endif
            </div>

            <!-- Filter Section -->
            @if(!$isCommissionMode)
                <div class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Mois -->
                        <div class="flex flex-col">
                            <label class="text-sm font-medium text-gray-700 mb-1">Mois</label>
                            <select wire:model.live="moisParam" class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary">
                                <option value="">Tous les mois</option>
                                @foreach($mois_options as $num => $nom)
                                    <option value="{{ $num }}">{{ $nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Année -->
                        <div class="flex flex-col">
                            <label class="text-sm font-medium text-gray-700 mb-1">Année</label>
                            <select wire:model.live="anneeParam" class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary">
                                <option value="">Toutes les années</option>
                                @foreach($annees as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Commercial -->
                        <div class="flex flex-col">
                            <label class="text-sm font-medium text-gray-700 mb-1">Commercial</label>
                            @if($isDirecteur || $isAdministratif)
                                <select wire:model.live="commercial" class="w-full border border-gray-300 rounded-full px-4 py-2 bg-background-grey focus:ring-2 focus:ring-primary">
                                    <option value="">Tous les commerciaux</option>
                                    @foreach($commercials as $comm)
                                        <option value="{{ $comm['id'] }}">{{ $comm->display_name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" disabled value="Mes ventes" class="w-full border border-gray-300 rounded-full px-4 py-2 bg-gray-100 text-gray-500">
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto min-w-full">

                <table id="tableau_vente" class="sortable text-xs lg:text-sm gap-4 min-w-full">
                    <!-- Header -->
                    <thead>
                        <tr class="items-center min-w-full">
                            <th class="w-1/12 lg:p-4 bg-primary rounded-tl-xl text-white font-bold text-center">
                                <p>N° VENTE</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>DATE</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>CLIENT</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>VRP</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>PRIX TARIF</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>TAUX REMISE</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>CA HT</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>MARGE</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>COMM. TAUX</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center @if($this->isCommissionMode) rounded-tr-xl @endif">
                                <p>COMM. MONTANT</p>
                            </th>
                            @if(!$this->isCommissionMode)
                                <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                    <p>PRODUITS</p>
                                </th>
                                <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                    <p>COMMISSIONNÉ</p>
                                </th>
                                <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                    <p>SOURCE</p>
                                </th>
                                <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                    <p>PAIEMENT</p>
                                </th>
                                <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                    <p>ATTENTE</p>
                                </th>
                                <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                    <p>COMMANDE</p>
                                </th>
                                <th class="w-1/12 lg:p-4 bg-primary rounded-tr-xl text-white font-bold text-center">
                                    <p>ACTION</p>
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    <!-- Data Rows -->
                    @forelse($ventesData['ventes'] as $vente)
                        <tr class="items-center bg-background-grey rounded-xl p-5 hover:bg-gray-100 transition-colors duration-200 not-first:border-gray-100 not-first:border-t-1"
                            style="{{ $vente->backgroundColor }}"
                            wire:key="vente-main-{{ $vente->id_rdv }}">
                            <!-- Numéro de la vente -->
                            <td class="text-black font-bold text-center">
                                <p>{{ $loop->iteration }}</p>
                            </td>

                            <!-- Date -->
                            <td class="text-black text-center">
                                <p>{{ $vente->date ? \Carbon\Carbon::parse($vente->date)->format('d/m/Y') : 'N/A' }}</p>
                            </td>

                            <!-- Client -->
                            <td class="text-black font-bold text-center ">
                                <p>{{ $vente->client->nom ?? '' }}</p>
                            </td>

                            <!-- VRP -->
                            <td class="text-black text-center text-nowrap">
                                @if($vente->vrp1 != '' || $vente->vrp2 != '' || $vente->vrp3 != '')
                                    {!! $vente->vrp1 !!}
                                    @if($vente->vrp2)
                                        <br>{!! $vente->vrp2 !!}
                                    @endif
                                    @if($vente->vrp3)
                                        <br>{!! $vente->vrp3 !!}
                                    @endif
                                @else
                                    <p>N/A</p>
                                @endif
                            </td>

                            <!-- Prix tarif -->
                            <td class="text-black font-bold text-center">
                                <p>{{ number_format($vente->tarif_total_ht, 0, ',', ' ') }}€</p>
                            </td>

                            <!-- Taux remise -->
                            <td class="text-black text-center">
                                <p>{{ number_format($vente->remise , 0, ',', ' ') }}%</p>
                            </td>

                            <!-- CA HT -->
                            <td class="text-black text-center">
                                <p>{{ number_format($vente->ca_total_ht, 0, ',', ' ') }}€</p>
                            </td>

                            <!-- Marge -->
                            <td class="text-black text-center">
                                <p>{{ number_format((int)($vente->ca_total_ht - $vente->tarif_total_ht * 0.85) , 0, ',', ' ') }}€</p>
                            </td>

                            <!-- Commission Taux -->
                            <td class="text-black text-center">
                                @php
                                    // Commission rate calculation based on legacy logic
                                    if ($vente->statut != 3 && empty($vente->attente)) {
                                        
                                        echo $vente->taux;

                                        // Handle second commercial
                                        if ($vente->id_commercial_2) {
                                            echo "<br>";
                                            echo $vente->vente2_taux;
                                        }

                                        // Handle third commercial
                                        if ($vente->id_commercial_3) {
                                            echo "<br>";
                                            echo $vente->vente3_taux;
                                        }
                                    } else {
                                        echo '--';
                                    }
                                @endphp
                            </td>

                            <!-- Commission Montant -->
                            <td class="text-black text-center">
                                @php
                                    // Commission calculation based on legacy logic
                                    if ($vente->statut != 3 && empty($vente->attente)) {
                                        echo $vente->montant_commission;

                                        // Handle second commercial
                                        if ($vente->id_commercial_2) {
                                            echo "<br>";
                                            echo $vente->vente2_montant_commission;
                                        }

                                        // Handle third commercial
                                        if ($vente->id_commercial_3) {
                                            echo "<br>";
                                            echo $vente->vente3_montant_commission;
                                        }
                                    } else {
                                        echo '--';
                                    }
                                @endphp
                            </td>

                            @if(!$this->isCommissionMode)
                                <!-- Produits -->
                                <td class="text-black text-center">
                                    {!!$vente->final_products!!}
                                </td>

                                <!-- Commissionné -->
                                <td class="text-center">
                                    <span @if($isAdmin) wire:click="toggleCommission({{ $vente->id }})" @endif
                                        class="px-2 py-1 rounded-full text-xs {{ $vente->commissionneClass }} @if($isAdmin) cursor-pointer @endif hover:opacity-75 transition-opacity duration-200"
                                        title="Cliquer pour changer le statut de commission">
                                            {{ $vente->commissionneText }}
                                        </span>
                                </td>

                                <!-- Source -->
                                <td class="text-black text-center">
                                    <p>{{$vente->source_name }}</p>
                                </td>

                                <!-- Paiement -->
                                <td class="text-black text-center">
                                    <p>{{ $vente->paiement ?? 'N/A' }}</p>
                                </td>

                                <!-- Attente -->
                                <td class="text-black relative text-center">
                                    @if($vente->attente)
                                        @foreach(explode(';', $vente->attente) as $attente)
                                            @if($isAdmin)
                                                <a wire:click="handleAttente({{ $vente->id }}, '{{ $attente }}')"
                                                class="text-blue-600 hover:text-blue-800 cursor-pointer underline relative @if ($attente == "Autre") attente-autre @endif">
                                                    {{ $attente }}
                                                </a>
                                                @if($attente == "Autre")
                                                    <span class="tooltip absolute top-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded hidden min-w-[16rem] max-w-[24rem] break-words duration-200 z-10">
                                                        {{ $vente->attente_autre ?? '' }}
                                                    </span>
                                                @endif
                                            @else
                                                <div class="relative @if ($attente == "Autre") attente-autre @endif">
                                                    {{ $attente }}
                                                </div>
                                                @if($attente == "Autre")
                                                    <span class="tooltip absolute top-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded hidden min-w-[16rem] max-w-[24rem] break-words duration-200 z-10">
                                                        {{ $vente->attente_autre ?? '' }}
                                                    </span>
                                                @endif
                                            @endif
                                            <br>
                                        @endforeach
                                    @endif
                                </td>

                                <!-- Commande -->
                                <td class="text-center">
                                    @php
                                        $commandeClass = match($vente->commande) {
                                            1 => 'bg-green-100 text-green-800',
                                            0 => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        $commandeText = match($vente->commande) {
                                            1 => 'Oui',
                                            0 => 'Non',
                                            default => 'N/A'
                                        };
                                    @endphp
                                    <span @if($isAdmin) wire:click="toggleCommande({{ $vente->id }})" @endif
                                        class="px-2 py-1 rounded-full text-xs {{ $commandeClass }}  @if($isAdmin) cursor-pointer @endif hover:opacity-75 transition-opacity duration-200"
                                        title="Cliquer pour changer le statut">
                                            {{ $commandeText }}
                                        </span>
                                </td>

                                <!-- Action -->
                                <td class="text-center">
                                    <div class="flex justify-center gap-1">
                                        @if ($vente->statut == 1 || $isAdmin)
                                            <button wire:click="editVente({{ $vente->id_rdv }})" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        @php
                                            $statusButtonClass = "";
                                            if ($isAdmin || $isAdministratif){
                                                // Confirmed or other status - show cancel button
                                                $statusButtonClass = 'bg-red-500 hover:bg-red-600';
                                                $statusIcon = 'fas fa-times';
                                                $statusTitle = 'Annuler la vente';
                                            }
                                        @endphp
                                        @if($statusButtonClass != '')
                                            <button wire:click="toggleVenteStatus({{ $vente->id }}, false)"
                                                    class="{{ $statusButtonClass }} text-white px-2 py-1 rounded text-xs" title="{{ $statusTitle }}">
                                                <i class="{{ $statusIcon }}"></i>
                                            </button>
                                        @endif
                                        @php
                                            $statusButtonClass = "";
                                            if ($vente->statut != 2 && ($isAdmin)) {
                                                // Cancelled sale - show confirm button
                                                $statusButtonClass = 'bg-green-500 hover:bg-green-600';
                                                $statusIcon = 'fas fa-check';
                                                if($vente->statut == 1)
                                                    $statusTitle = 'Valider la vente';
                                                else 
                                                    $statusTitle = 'Revalider la vente';
                                            }
                                        
                                        @endphp
                                        @if($statusButtonClass != '')
                                            <button wire:click="toggleVenteStatus({{ $vente->id }}, true)"
                                                    class="{{ $statusButtonClass }} text-white px-2 py-1 rounded text-xs" title="{{ $statusTitle }}">
                                                <i class="{{ $statusIcon }}"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr class="justify-center items-center py-10 bg-background-grey rounded-xl">
                            @if($this->isCommissionMode)
                                <td colspan="10" class="text-center p-4">Aucune vente trouvée.</td>
                            @else
                                <td colspan="17" class="text-center p-4">Aucune vente trouvée.</td>
                            @endif
                        </tr>
                    @endforelse
                    </tbody>
                    <!-- Totals Row -->
                    <tfoot>
                    @if(count($ventesData['ventes']) > 0)
                        <tr class="items-center rounded-xl p-5 mt-2">
                            <!-- N° VENTE -->
                            <td class="bg-primary rounded-bl-lg text-white font-bold text-center">
                                <p>TOTAL</p>
                            </td>

                            <!-- DATE -->
                            <td class="bg-primary text-white font-bold text-center">
                                <p>-</p>
                            </td>

                            <!-- CLIENT -->
                            <td class="bg-primary text-white font-bold text-center">
                                <p>-</p>
                            </td>

                            <!-- VRP -->
                            <td class="bg-primary text-white font-bold text-center">
                                <p>-</p>
                            </td>

                            <!-- Prix tarif -->
                            <td class="bg-primary text-white font-bold text-center">
                                <p>{{ number_format($ventesData['totals']['prix_tarif'], 0, ',', ' ') }}€</p>
                            </td>

                            <!-- Taux remise -->
                            <td class="bg-primary text-white font-bold text-center">
                                <p>{{ number_format(floor($ventesData['totals']['taux_remise']), 0, ',', ' ') }}% ({{number_format(floor($ventesData['totals']['taux_remise_total']), 0, ',', ' ')}}%)</p>
                            </td>
                            
                            <!-- CA HT -->
                            <td class="bg-primary text-white font-bold text-center">
                                <p>{{ number_format($ventesData['totals']['ca_ht'], 0, ',', ' ') }}€</p>
                            </td>

                            <!-- Marge -->
                            <td class="bg-primary text-white font-bold text-center">
                                <p>{{ number_format($ventesData['totals']['marge'], 0, ',', ' ') }}€</p>
                            </td>

                            <!-- Commission Taux -->
                            <td class="bg-primary text-white font-bold text-center">
                                <p>-</p>
                            </td>

                            <!-- Commission Montant -->
                            <td class="bg-primary text-white font-bold text-center @if($this->isCommissionMode) rounded-br-xl @endif">
                                <p>{{ number_format($ventesData['totals']['commission_montant'], 0, ',', ' ') }} €</p>
                            </td>
                            @if(!$this->isCommissionMode)
                                <!-- PRODUITS -->
                                <td class="bg-primary text-white font-bold text-center">
                                    <p>-</p>
                                </td>

                                <!-- COMMISSIONNÉ -->
                                <td class="bg-primary text-white font-bold text-center">
                                    <p>-</p>
                                </td>

                                <!-- SOURCE -->
                                <td class="bg-primary text-white font-bold text-center">
                                    <p>-</p>
                                </td>

                                <!-- PAIEMENT -->
                                <td class="bg-primary text-white font-bold text-center">
                                    <p>-</p>
                                </td>

                                <!-- ATTENTE -->
                                <td class="bg-primary text-white font-bold text-center">
                                    <p>-</p>
                                </td>

                                <!-- COMMANDE -->
                                <td class="bg-primary text-white font-bold text-center">
                                    <p>-</p>
                                </td>

                                <!-- ACTION -->
                                <td class="bg-primary rounded-br-lg text-white font-bold text-center">
                                    <p>-</p>
                                </td>
                            @endif
                        </tr>
                    @endif
                    </tfoot>
                </table>
            </div>
           

            @if($isCommissionMode)
                <div class="flex flex-row items-center justify-center mb-4">
                    <h2 class="text-2xl font-bold text-primary">Régul commissions {{Carbon::createFromFormat('m', $moisParam)->isoFormat('MMMM') . ' ' . $anneeParam }}</h2>
                </div>

                <table class="block overflow-x-auto text-xs lg:text-sm gap-4 min-w-full">
                    <!-- Header -->
                    <thead>
                        <tr class="items-center min-w-full">
                            <th class="w-1/12 lg:p-4 bg-primary rounded-tl-xl text-white font-bold text-center">
                                <p>N° VENTE</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>DATE</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>CLIENT</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>VRP</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>PRIX TARIF</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>TAUX REMISE</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>CA HT</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>MARGE</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center">
                                <p>COMM. MONTANT</p>
                            </th>
                            <th class="w-1/12 lg:p-4 bg-primary text-white font-bold text-center rounded-tr-xl">
                                <p>COMMENTAIRE</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <!-- Data Rows -->
                    @php $sum = 0; @endphp
                    @forelse($regulComm['ventes'] as $vente)
                        @php
                            $sum += $vente->commission;
                        @endphp
                        <tr class="items-center bg-background-grey rounded-xl p-5 hover:bg-gray-100 transition-colors duration-200 not-first:border-gray-100 not-first:border-t-1"
                            style="{{ $vente->backgroundColor }}"
                            wire:key="vente-regul-{{ $vente->id_rdv }}">
                            <!-- Numéro de la vente -->
                            <td class="lg:p-3 text-black font-bold text-center">
                                <p>{{ $vente->id_rdv }}</p>
                            </td>

                            <!-- Date -->
                            <td class="lg:p-3 text-black text-center">
                                <p>{{ $vente->date ? \Carbon\Carbon::parse($vente->date)->format('d/m/Y') : 'N/A' }}</p>
                            </td>

                            <!-- Client -->
                            <td class="lg:p-3 text-black font-bold text-center ">
                                <p>{{ $vente->client->nom ?? '' }}</p>
                            </td>

                            <!-- VRP -->
                            <td class="lg:p-3 text-black text-center text-nowrap">
                                @if($vente->vrp1 != '')
                                    {!! $vente->vrp1 !!}
                                @else
                                    <p>N/A</p>
                                @endif
                            </td>

                            <!-- Prix tarif -->
                            <td class="lg:p-3 text-black font-bold text-center">
                                <p>{{ number_format($vente->tarif_total_ht, 0, ',', ' ') }}€</p>
                            </td>

                            <!-- Taux remise -->
                            <td class="lg:p-3 text-black text-center">
                                <p>{{ number_format($vente->remise , 0, ',', ' ') }}%</p>
                            </td>

                            <!-- CA HT -->
                            <td class="lg:p-3 text-black text-center">
                                <p>{{ number_format($vente->ca_total_ht, 0, ',', ' ') }}€</p>
                            </td>

                            <!-- Marge -->
                            <td class="lg:p-3 text-black text-center">
                                <p>{{ number_format((int)($vente->ca_total_ht - $vente->tarif_total_ht * 0.85) , 0, ',', ' ') }}€</p>
                            </td>

                            <!-- Commission Montant -->
                            <td class="lg:p-3 text-black text-center">
                                <input type="number" class="bg-white p-2" wire:change="updateCommission({{$vente->id}}, $event.target.value)" value='{{$vente->commission}}'>
                            </td>

                            <!-- COMMENTAIRE -->
                            <td class="lg:p-3 text-black text-center">
                                <input type="text" wire:change="updateCommentaire({{$vente->id}}, $event.target.value)" class='bg-white p-2' value='{{ $vente->commentaire}}'>
                            </td>
                        </tr>
                    @empty
                        <tr class="justify-center items-center py-10 bg-background-grey rounded-xl">
                            <td colspan="17">
                                <p class="text-black text-center">Aucune ancienne vente trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                    </body>
                    <!-- Totals Row -->
                    <tfoot>
                    @if(count($regulComm['ventes']) > 0)
                        <tr class="items-center rounded-xl p-5 mt-2">
                            <!-- N° VENTE -->
                            <td class="bg-primary rounded-bl-lg text-white font-bold text-center">
                                <p>TOTAL</p>
                            </td>

                            <!-- DATE -->
                            <td class="bg-primary text-white font-bold text-center"></td>

                            <!-- CLIENT -->
                            <td class="bg-primary text-white font-bold text-center"></td>

                            <!-- VRP -->
                            <td class="bg-primary text-white font-bold text-center"></td>

                            <!-- Prix tarif -->
                            <td class="bg-primary text-white font-bold text-center"></td>

                            <!-- Taux remise -->
                            <td class="bg-primary text-white font-bold text-center"></td>
                            
                            <!-- CA HT -->
                            <td class="bg-primary text-white font-bold text-center"></td>

                            <!-- Marge -->
                            <td class="bg-primary text-white font-bold text-center"></td>

                            <!-- Commission Montant -->
                            <td class="bg-primary text-white font-bold text-center ">
                                <p>{{ number_format($sum, 0, ',', ' ') }} €</p>
                            </td>
                           
                            <!-- COMMENTAIRE -->
                            <td class="bg-primary text-white font-bold text-center rounded-br-xl"></td>
                        </tr>
                    @endif
                    </tfoot>
                </table>
				
				@if($ventesData['totals']['prime'] > 0)
					<h3 class="text-center text-2xl">Total prime = {{ number_format($ventesData['totals']['prime'], 0, ',', ' ') }} €</h3>
				@endif

                <h2 class="text-center text-2xl">Total commissions = {{ number_format($sum + $regulComm['totals']['prime'] + $ventesData['totals']['commission_montant'], 0, ',', ' ') }} €</h2>

                <div style="margin-top: 20px; display: flex;flex-direction: row;flex-wrap: nowrap;justify-content: space-around; line-height: 3">
                    <div>
                        Vendeur : {{ $this->commName }}
                        <br>
                        Date :
                        <br>
                        Signature:
                    </div>
                    <div>
                        Damien Clouet
                        <br>
                        &nbsp;
                        <br>
                        Signature:
                    </div>
                </div>
                <a href="#" class="print:hidden" onclick="window.print()">Imprimer</a>
            @endif


            @if(count($ventesData['ventes']) > 0 && !$isCommissionMode && ($isAdmin))
                <!-- Export Button -->
                <div class="flex justify-between mt-4 mb-4">
                    <div class="flex-col">
                        <h4 class="pb-2"><b>Légende</b></h4>
                        <div class="flex gap-2">
                            <div style="background-color: rgba(0, 0, 255, 0.3);" class="text-black px-4 py-2 rounded-lg flex items-center gap-2">Validé</div>
                            <div style="background-color: rgba(0, 255, 0, 0.4);" class="text-black px-4 py-2 rounded-lg flex items-center gap-2">Attente validation</div>
                            <div style="background-color: rgba(255, 0, 0, 0.2);" class="text-black px-4 py-2 rounded-lg flex items-center gap-2">Annulé</div>
                            <div class="bg-background-grey text-black px-4 py-2 rounded-lg flex items-center gap-2">Non traité</div>
                            <div style="background-color: rgba(240, 198, 46, 0.4);" class=" text-black px-4 py-2 rounded-lg flex items-center gap-2">RPE / RAI</div>
                        </div>
                    </div>
                    <button onclick="exportTableToCSV('tableau_vente')"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors duration-200"
                            title="Exporter en CSV">
                        <i class="fas fa-download"></i>
                        Exporter CSV
                    </button>
                </div>

                <!-- Commission Button -->
                @if($commercial != '')
                    <div class="flex justify-end mt-4 mb-4">
                        <button wire:click="redirectToCommissions"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 transition-colors duration-200 font-semibold">
                            <i class="fas fa-chart-line"></i>
                            Commissions
                        </button>
                    </div>
                @endif
            @endif

            @if (($isAdmin || $isDirecteur) && $commercial == "" && $moisParam != "" && !empty($directorData['data']))
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-primary mb-4">Commission {{$directorData['director']['prenom']. ' ' . $directorData['director']['nom']}}</h3>
                    <div class="overflow-x-auto">
                        <div class="flex flex-col gap-2 min-w-[600px]">
                            <!-- Header -->
                            <div class="bg-primary rounded-xl flex flex-row p-4 items-center">
                                <div class="text-white font-bold w-[40%] text-center">
                                    <p>TAUX</p>
                                </div>
                                <div class="text-white font-bold w-[30%] text-center">
                                    <p>COMMISSION</p>
                                </div>
                            </div>

                            <!-- Data Rows -->
                            @foreach($directorData['data'] as $commercial)
                                <div class="flex flex-row items-center bg-background-grey rounded-xl p-4 hover:bg-gray-100 transition-colors duration-200">
                                    <!-- Commerciaux -->
                                    <div class="w-[40%] text-primary font-bold text-center">
                                        <p>{{ $commercial['prenom'] . ' ' . $commercial['nom'] }}</p>
                                    </div>

                                    <!-- CA -->
                                    <div class="w-[30%] text-black text-center">
                                        <p class="{{ $ventesData['commerciauxData'][$commercial['id']]["prix_vente"][$moisParam] < 18000 ? 'text-red-500' : ''}}">{{ $ventesData['commerciauxData'][$commercial['id']]["prix_vente"][$moisParam] }} €</p>
                                    </div>
                                </div>
                            @endforeach
                            <div class="bg-primary rounded-xl flex flex-row p-4 items-center">
                                <!-- Taux -->
                                <div class="w-[40%] text-white font-bold text-center">
                                    <p>{{ $directorData['taux'] }} %</p>
                                </div>

                                <!-- CA -->
                                <div class="w-[30%] text-white font-bold text-center">
                                    <p>{{ $directorData['com'] }} €</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Payment Summary Table -->
            @if(count($ventesData['paymentSummary']['summary']) > 0 && !$isCommissionMode)
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-primary mb-4">Paiements</h3>
                    <div class="overflow-x-auto">
                        <div class="flex flex-col gap-2 min-w-[600px]">
                            <!-- Header -->
                            <div class="bg-primary rounded-xl flex flex-row p-4 items-center">
                                <div class="text-white font-bold w-[40%] text-center">
                                    <p>PAIEMENTS</p>
                                </div>
                                <div class="text-white font-bold w-[30%] text-center">
                                    <p>CA</p>
                                </div>
                                <div class="text-white font-bold w-[30%] text-center">
                                    <p>RATIO</p>
                                </div>
                            </div>

                            <!-- Data Rows -->
                            @foreach($ventesData['paymentSummary']['summary'] as $paymentType => $data)
                                <div class="flex flex-row items-center bg-background-grey rounded-xl p-4 hover:bg-gray-100 transition-colors duration-200">
                                    <!-- Payment Type -->
                                    <div class="w-[40%] text-primary font-bold text-center">
                                        <p>{{ $paymentType }}</p>
                                    </div>

                                    <!-- CA -->
                                    <div class="w-[30%] text-black text-center">
                                        <p>{{ number_format($data['ca'], 0, ',', ' ') }} €</p>
                                    </div>

                                    <!-- Ratio -->
                                    <div class="w-[30%] text-black text-center">
                                        <p>{{ number_format($data['ratio'], 1, ',', ' ') }} %</p>
                                    </div>
                                </div>
                            @endforeach
                            <div class="bg-primary rounded-xl flex flex-row p-4 items-center">
                                <div class="text-white font-bold w-[40%] text-center">
                                    <p>TOTAL</p>
                                </div>
                                <div class="text-white font-bold w-[30%] text-center">
                                    <p>{{  number_format($ventesData['paymentSummary']['total'] , 0, ' ', ' ')}} €</p>
                                </div>
                                <div class="text-white font-bold w-[30%] text-center">
                                    <p>100 %</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Cancellations Summary Table -->
            @if(isset($ventesData['cancellationSummary']['summary']['Ventes']) && $ventesData['cancellationSummary']['summary']['Ventes']['count'] > 0 && !$isCommissionMode)
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-primary mb-4">Annulations</h3>
                    <div class="overflow-x-auto">
                        <div class="flex flex-col gap-2 min-w-[800px]">
                            <!-- Header -->
                            <div class="bg-primary rounded-xl flex flex-row p-4 items-center">
                                <div class="text-white font-bold w-[25%] text-center">
                                    <p>TYPE</p>
                                </div>
                                <div class="text-white font-bold w-[25%] text-center">
                                    <p>NOMBRE</p>
                                </div>
                                <div class="text-white font-bold w-[25%] text-center">
                                    <p>CA HT</p>
                                </div>
                                <div class="text-white font-bold w-[25%] text-center">
                                    <p>RATIO</p>
                                </div>
                            </div>

                            <!-- Data Rows -->
                            @foreach($ventesData['cancellationSummary']['summary'] as $type => $data)
                                <div class="flex flex-row items-center bg-background-grey rounded-xl p-4 hover:bg-gray-100 transition-colors duration-200">
                                    <!-- Type -->
                                    <div class="w-[25%] text-primary font-bold text-center">
                                        <p>{{ $type }}</p>
                                    </div>

                                    <!-- Count -->
                                    <div class="w-[25%] text-black text-center">
                                        <p>{{ number_format($data['count'], 1, ',', ' ') }}</p>
                                    </div>

                                    <!-- CA -->
                                    <div class="w-[25%] text-black text-center">
                                        <p>{{ number_format($data['ca'], 0, ',', ' ') }} €</p>
                                    </div>

                                    <!-- Ratio -->
                                    <div class="w-[25%] text-black text-center">
                                        <p>{{ number_format($data['ratio'], 1, ',', ' ') }} %</p>
                                    </div>
                                </div>
                            @endforeach
                            <div class="bg-primary rounded-xl flex flex-row p-4 items-center">
                                <div class="text-white font-bold w-[25%] text-center">
                                    <p>TOTAL</p>
                                </div>
                                <div class="text-white font-bold w-[25%] text-center">
                                    <p>{{$ventesData['cancellationSummary']['totalCount']}}</p>
                                </div>
                                <div class="text-white font-bold w-[25%] text-center">
                                    <p>{{number_format($ventesData['cancellationSummary']['total'], 0, ',', ' ')}} €</p>
                                </div>
                                <div class="text-white font-bold w-[25%] text-center">
                                    <p>100 %</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    
    @endisset

    
    <script defer src="{{ asset('js/jquery.js') }}"></script>
    <script defer src="{{ asset('js/jquery.tablesorter.min.js') }}"></script>
    <script defer src="{{ asset('js/tableSorter.js') }}"></script>

    <script defer>
        function exportTableToCSV(tableId, filename = 'export.csv') {
            const table = document.getElementById(tableId);
            const rows = Array.from(table.querySelectorAll('tr'));
            const csvContent = rows.map(row => {
                return Array.from(row.querySelectorAll('th,td'))
                            .map(cell => '"' + cell.innerText.replace(/"/g, '""') + '"')
                            .join(';');
            }).join('\n');

            
            const bom = new Uint8Array([0xEF, 0xBB, 0xBF]);
            const blob = new Blob([bom, csvContent], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();

            URL.revokeObjectURL(url);
        }

        window.addEventListener('triggerCsvExport', () => {
            exportTableToCSV('tableau_vente', 'mon_export.csv'); // Remplacez par l'ID du tableau
        });


    </script>
    <style>
        .attente-autre:hover + .tooltip {
            display: flex;
        }
    </style>
</div>
