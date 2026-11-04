<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

use App\Models\Secteur;
use App\Services\TableauChiffreService;
use App\Livewire\TableauChiffre\TableauChiffres;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class DispatchPlannedSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:dispatch-planned {--date=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date')
        ? Carbon::parse($this->option('date'))
        : now();

        $apiKey = "9258f3912d5893a0b52113f34b49533f";
        $baseUrl = "https://www.spot-hit.fr/api/envoyer/sms";
        $sender = config('services.spothit.from');
        $smsLong = 1;
        $smsLongNbr = 1;

        $result = "";				

        // if ($this->isFridayEvening($date)) {
            // $receivers = "0671323822,0656680420";
            // $result = $this->sendFridaySms($date);
        // }

		// $isLastBusinessDayOfMonth = false;
        // if ($this->isLastBusinessDayOfMonth($date)) {
            // $receivers = "0671323822,0617064331,0762222603";
			// $isLastBusinessDayOfMonth = true;
            // $result = $this->sendMonthEndSms($date);
        // }

        // if($this->is8AMBusinessDay($date)){
            // $receivers = "0671323822,0617064331";
            // $result = $this->send8AMSms($date);
        // }

        if($result !== ''){
            $payload = [
                'key' => $apiKey,
                'message' => $result,
                'destinataires' => $receivers,
                'smslong' => 1,
                'timezone' => 'Europe/Paris',
            ];

            $response = Http::asForm()
                ->timeout(20)
                ->post($baseUrl, $payload);

            dd($response->json());

            return $response->json() ?? ['raw' => $response->body()];
        }
        return self::SUCCESS;
    }

    protected function isFridayEvening(Carbon $now): bool
    {
        return $now->isFriday() && (int) $now->format('H') == 20;
    }

    protected function isLastBusinessDayOfMonth(Carbon $now): bool
    {
        if ($now->isWeekend()) {
            return false;
        }

        $cursor = $now->copy()->addDay();

        while ($cursor->month === $now->month) {
            if (! $cursor->isWeekend()) {
                return false;
            }

            $cursor->addDay();
        }

		if($now->format('H') == 20)
			return true;
		else
			return false;
    }

    protected function is8AMBusinessDay(Carbon $now) : bool
    {
        if($now->isWeekend() || $now->format('H') != 8)
			return false;
		else
			return true;
    }

    public function getCurrentFridayWeekIndex(?Carbon $date = null): int
    {
        $year = $date->year;
        $month = $date->month;

        $friday = $date->copy();
        if (! $friday->isFriday()) {
            $friday = $friday->next(Carbon::FRIDAY);
        }

        $weeks = TableauChiffreService::getSemainesDefinitions($year, $month);

        foreach ($weeks as $index => [$startDay, $endDay]) {
            $start = Carbon::create($year, $month, $startDay)->startOfDay();
            $end = Carbon::create($year, $month, $endDay)->endOfDay();

            if ($friday->between($start, $end)) {
                return $index;
            }
        }

        throw new \RuntimeException('Impossible de déterminer la semaine du vendredi.');
    }

    protected function sendFridaySms(Carbon $now): string
    {
        $year = $now->year;
        $month = $now->month;

        $weekIndex = $this->getCurrentFridayWeekIndex($now);
        $user = User::where('id', 1);

        $groupes = TableauChiffres::getGroupesForUser($user, 1, 0, 0);
        $global = TableauChiffreService::initializeGlobalTotals();
        $secteurs = Secteur::get();
		$globalSecteur = [];
        foreach($secteurs as $secteur){
            $globalSecteur[$secteur->id] = TableauChiffreService::initializeGlobalTotals();
        }

        $groupesAvecUsers = TableauChiffreService::processGroupes($groupes, $year, $month, $user, $global, $globalSecteur, 1, 0, 0);
		
        TableauChiffreService::finalizeGlobalTotals($global, $globalSecteur);

        $results = "Résultats hebdomadaire du " . $month . '/' . $year . " S". ($weekIndex + 1) . " :" . "\r\n";
		
		//dd($secteurs, $globalSecteur, $weekIndex);

        foreach ($secteurs as $secteur) {
            if($secteur->activated ==1){
                $results .= $secteur->nom . " : " . number_format($globalSecteur[$secteur->id]->somme_ca_par_semaine[$weekIndex], 0, ' ', ' ') . " €" . "\r\n";
            }
        }

        $results .= "Global : " . number_format($global->somme_ca_par_semaine[$weekIndex], 0, ' ', ' ') . " €";
        
        return $results;
    
    }

    protected function sendMonthEndSms(Carbon $now): string
    {
        $year = $now->year;
        $month = $now->month;

        $user = User::where('id', 1);

        $groupes = TableauChiffres::getGroupesForUser($user, 1, 0, 0);
        $global = TableauChiffreService::initializeGlobalTotals();
        $secteurs = Secteur::get();
        foreach($secteurs as $secteur){
            $globalSecteur[$secteur->id] = TableauChiffreService::initializeGlobalTotals();
        }

        $groupesAvecUsers = TableauChiffreService::processGroupes($groupes, $year, $month, $user, $global, $globalSecteur, 1, 0, 0);
        
        TableauChiffreService::finalizeGlobalTotals($global, $globalSecteur);

        $results = "Résultats mensuels du " . $month . '/' . $year . " :" . "\r\n";

        foreach ($secteurs as $secteur) {
            if($secteur->activated ==1){
                $results .= $secteur->nom . " : " . number_format($globalSecteur[$secteur->id]->somme_ca_realise,0, ' ', ' ') . " €" . "\r\n";
            }
        }

        $results .= "Global : " . number_format($global->somme_ca_realise, 0, ' ', ' ') . " €";
		return $results;
    }

    protected function send8AMSms(Carbon $now): string
    {
        $previousDay = Carbon::parse($now)->subDay();
        if ($previousDay->isWeekend()) { // isWeekend() true pour samedi/dimanche [web:3]
            // recule jusqu'à vendredi (weekday = 5)
            $previousDay->previous(Carbon::FRIDAY);
        }

        $previousDayStr = $previousDay->toDateString();

        $results = "Rapport de mapping quotidien du " . $previousDay->isoFormat('D MMMM YYYY') . " : " . "\r\n";

        $secteurs = Secteur::where('activated', 1)->get();
		
		$usersAlreadyManaged = [];

        foreach($secteurs as $secteur){

			if(!in_array($secteur->id, [10,8])){
				$query = User::leftJoin('clients', function ($join) use ($previousDayStr) {
					$join->on('users.id', '=', 'clients.rentre_par')
						->where('clients.rentre_le', $previousDayStr);
				})
				->where('active', 1)
				->whereExists(function ($q) use ($secteur) {
					$q->selectRaw('1')
					  ->from('secteur_commercials')
					  ->whereColumn('secteur_commercials.id_commercial', 'users.id')
					  ->where('secteur_commercials.id_secteur', $secteur->id);
				})
				->selectRaw("
					users.id,
					users.nom,
					users.role,
					COUNT(clients.id) as total_clients
				")
				->groupBy('users.id', 'users.nom', 'users.prenom')
				->orderBy('users.nom')
				->get();

				$results .= "\r\n" . ucfirst($secteur->nom) . "\r\n";

				foreach($query as $user){
					if(!$user->hasRole('admin') && !$user->hasRole('administratif')){
						if(!in_array($user->id, $usersAlreadyManaged)){
							$usersAlreadyManaged[] = $user->id;
							$results .= $user->nom . " : " . $user->total_clients . "\r\n";
						}
					}
				}
			}
        }

        return $results;
    }
}
