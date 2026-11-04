<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Parametre extends Component
{
    public $user;
    public $password;
    public $password_confirmation;

    public function mount()
    {
        $this->user = User::find(Auth::id());
    }

    public function updatePassword()
    {
        $this->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $this->user->password = Hash::make($this->password);
        $this->user->save();

        session()->flash('success', 'Mot de passe mis à jour avec succès ✅');
        $this->reset(['password', 'password_confirmation']);
    }

    public function render()
    {
        return view('livewire.parametre');
    }
}
