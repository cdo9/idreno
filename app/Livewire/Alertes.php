<?php

namespace App\Livewire;

use App\Models\Alert;
use App\Services\CommerciauxService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Alertes extends Component
{
    public $alerts = [];

    public $vendeurs = [];

    public $date;

    public $vendeur;

    public function mount()
    {
        // Restrict access to user ID 1 only
        if (Auth::id() !== 1) {
            abort(403, 'Accès non autorisé.');
        }

        $this->date = '';
        $this->vendeurs = CommerciauxService::getCommerciauxListBySector();

        $this->loadAlerts();
    }

    public function updatedVendeur()
    {
        $this->loadAlerts();
    }

    public function updatedDate()
    {
        $this->loadAlerts();
    }

    public function filtrer()
    {
        $this->loadAlerts();
    }

    private function getAlertRequest($takeParamIntoAccount = true)
    {
        $query = Alert::with('idCommercial')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        if (! empty($this->vendeur) && $takeParamIntoAccount) {
            $query->where('id_commercial', $this->vendeur);
        }

        if (! empty($this->date) && $takeParamIntoAccount) {
            $query->whereDate('created_at', $this->date);
        }

        return $query;
    }

    public function loadAlerts()
    {
        // Fetch all non-soft-deleted alerts (since only user ID 1 can access this page)
        $query = $this->getAlertRequest();
        $this->alerts = $query->get();
    }

    public function markAsViewed($alertId)
    {
        $alert = Alert::find($alertId);

        if ($alert) {
            $alert->delete(); // This will soft delete the alert
            $this->loadAlerts(); // Reload the alerts list

            // Clear the cached alert data so the pin refreshes
            session()->forget('alertesParType');

            session()->flash('message', 'Alerte marquée comme vue.');
        }
    }

    private function genericDeleteFunction($deleteAll = false)
    {
        if ($deleteAll) {
            $query = $this->getAlertRequest(false);
        } else {
            $query = $this->getAlertRequest();
        }
        $query->delete();

        $this->loadAlerts();
    }

    public function deleteDisplayedAlerts()
    {
        $this->genericDeleteFunction();
    }

    public function deleteAllAlerts()
    {
        $this->genericDeleteFunction(true);
    }

    public function render()
    {
        return view('livewire.alertes');
    }
}
