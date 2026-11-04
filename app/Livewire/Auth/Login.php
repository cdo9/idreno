<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function authenticate()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
            'active' => 1,
        ];

        if (Auth::attempt($credentials, $this->remember)) {
            session()->regenerate();
            $user = Auth::user();
            session(['selectedSecteur' => $user->secteur_commercials[0]->id_secteur]);

            return redirect()->intended('/');
        }

        $this->addError('email', 'Identifiants incorrects.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
