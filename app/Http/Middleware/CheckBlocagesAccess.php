<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckBlocagesAccess
{
    private $blocages = null;
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        if(!$this->blocages){
            $this->blocages = new \App\Livewire\Blocages();
            $this->blocages->mount();
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($this->blocages->hasBlocageQuelconque($user, $user->hasRole('telepro'),$user->hasRole('prospecteur'),$user->hasRole('admin'),$user->hasRole('directeur')) && !$user->isReleased()) {
            $routesAutorisees = [
                'rattrapage',
                'nouveau-contact',
                'confirmations-de-rendez-vous',
                'confirmations',
                'logout',
                'welcome'
            ];

            if (!in_array($request->route()->getName(), $routesAutorisees)) {
                return redirect()->route('rattrapage');
            }
        }

        return $next($request);
    }
   
}
