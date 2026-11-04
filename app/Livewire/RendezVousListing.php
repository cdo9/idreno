<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use App\Services\CommerciauxService;

class RendezVousListing extends Component
{
    public $filterDay = 'today';
    public Collection $rendezVous;
    public $confirmationStats = [];
    public $targetDate;
    public $showRelanceModal = false;
    public $relanceId = null;
    public $compteRendu = '';

    public function mount($day = 'null')
    {
        $this->filterDay = $day ?? request('day', 'today') ?? 'today';
        $this->rendezVous = new Collection();
        $this->loadData();
    }

    public function loadData()
    {

        $user = auth()->user();

        $today = now()->startOfDay();

        $this->targetDate = match ($this->filterDay) {
            'today' => $today,
            'j1' => $today->copy()->addDay(),
            'j2' => $today->copy()->addDays(2),
            'j3' => $today->copy()->addDays(3),
            'j4' => $today->copy()->addDays(4),
            default => $today,
        };

        $commercialId = Auth::id();

        // Get confirmation stats for all days
        $this->confirmationStats = [];
        for ($i = 0; $i <= 4; $i++) {
            $date = $today->copy()->addDays($i);

            $rendezVousQuery  = Projet::with(['client', 'secteur'])
            ->whereDate('date', $date)
            ->where('rdv_annule', 0)
            ->where('secteur', '=', session('selectedSecteur'))
            /*->whereDate('date_ajout', '<=', now()->subDays(2)->startOfDay())*/;

            if(!$user->hasRole('directeur') && !$user->hasRole('admin')){
                $rendezVousQuery->where(function($q) use ($user) {
                    $q->whereHas('client', function($clientQuery) use ($user) {
                        $clientQuery->where('rentre_par', $user->id);
                    })
                    ->orWhere('partage_avec', $user->id);
                });
            }

            if($date == $this->targetDate){
                $this->rendezVous = $rendezVousQuery
                ->orderBy('date', 'asc')
                ->orderBy('heure', 'asc')
                ->get();

                // Add shared commercial name and main commercial name
                $this->rendezVous->each(function ($rdv) {
                    // Shared commercial
                    $sharedUser = CommerciauxService::getCommercialById($rdv->partage_avec);
                    $rdv->shared_commercial_name = $sharedUser ? $sharedUser->display_name : 'N/A';

                    // Main commercial (rentre_par field)
                    $mainUser = CommerciauxService::getCommercialById($rdv->client->rentre_par);
                    $rdv->main_commercial_name = $mainUser ? $mainUser->display_name : 'N/A';
                    $rdv->agendaLink = RendezVous::generateCalendarLink($rdv);
                });
            }

            $count = $rendezVousQuery->count();
            $key = $i === 0 ? 'j0' : "j$i";
            $this->confirmationStats[$key] = [
                'date' => $date->toDateString(),
                'count' => $count,
                'active' => $this->filterDay === ($i === 0 ? 'today' : "j$i")
            ];
        }
    }

    public function filterByDay($day)
    {
        $this->filterDay = $day;
        return redirect()->to(route('confirmations-de-rendez-vous') . '?day=' . $day);
    }

    public function updateConfirmation($id)
    {
        try {
            // Logique exacte du controller updateConfirmation
            $rendezVous = Projet::findOrFail($id);
            Log::info('Updating confirmation for rendez-vous ID: ' . $id);

            // Toggle comme dans le composant original, ou utiliser une valeur spécifique
            $rendezVous->rdv_confirme = !$rendezVous->rdv_confirme;
            $rendezVous->date_confirmation = $rendezVous->rdv_confirme ? Carbon::now() : null;
            $rendezVous->save();

            Log::info('Confirmation status updated:', ['status' => $rendezVous->rdv_confirme]);

            // Reload data to reflect changes
            $this->loadData();

            $this->dispatch('confirmation-updated', [
                'message' => 'Statut de confirmation mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating confirmation: ' . $e->getMessage());
            $this->dispatch('confirmation-error', [
                'message' => 'Erreur lors de la mise à jour du statut'
            ]);
        }
    }

    public function deleteRendezVous($id)
    {
        try {
            $rendezVous = Projet::with('client')->findOrFail($id);

            Log::info('Attempting to delete rendez-vous ID: ' . $id);

            // Vérification des ventes liées (commentée dans le controller mais disponible)
            // if ($rendezVous->ventes->count() > 0) {
            //     $this->dispatch('deletion-error', [
            //         'message' => 'Impossible de supprimer ce rendez-vous car il est lié à des ventes'
            //     ]);
            //     return;
            // }

            $rendezVous->rdv_annule = 1;
            $rendezVous->client->type = 0;
            $rendezVous->client->save();
            $rendezVous->save();


            $this->loadData();

            $this->dispatch('rendez-vous-deleted', [
                'message' => 'Rendez-vous annulé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting rendez-vous: ' . $e->getMessage());
            $this->dispatch('deletion-error', [
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()
            ]);
        }
    }

    public function editRendezVous($id)
    {
        return redirect()->route('edit-rendez-vous', $id);
    }

    public function openRelanceModal($id)
    {
        $this->relanceId = $id;
        $this->compteRendu = '';
        $this->showRelanceModal = true;
    }

    public function closeRelanceModal()
    {
        $this->showRelanceModal = false;
        $this->relanceId = null;
        $this->compteRendu = '';
    }

    public function relanceRendezVous()
    {
        try {
            if (!$this->relanceId) {
                $this->dispatch('relance-error', [
                    'message' => 'ID de rendez-vous non spécifié'
                ]);
                return;
            }


            $rendezVous = Projet::with('client')->findOrFail($this->relanceId);
            
            Log::info('Relance rendez-vous ID: ' . $this->relanceId);
            Log::info('Compte rendu: ' . $this->compteRendu);

            // Logique de relance
            $rendezVous->rdv_confirme = 2; // A REPO
            $rendezVous->compte_rendu = $this->compteRendu;
            $rendezVous->date_confirmation = null;
            $rendezVous->save();
            $rendezVous->client->type = 0;
            $rendezVous->client->save();

            Log::info('Rendez-vous relancé avec succès:', ['id' => $this->relanceId]);

            $this->loadData();
            $this->closeRelanceModal();

            $this->dispatch('relance-success', [
                'message' => 'Rendez-vous relancé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error relaunching rendez-vous: ' . $e->getMessage());
            $this->dispatch('relance-error', [
                'message' => 'Erreur lors de la relance du rendez-vous: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.rendez-vous-listing');
    }
}
