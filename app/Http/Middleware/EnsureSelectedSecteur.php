<?php

namespace App\Http\Middleware;

use App\Models\SecteurCommercial;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSelectedSecteur
{
    /**
     * Restore the selected secteur when the session lost it, e.g. after a
     * "remember me" re-authentication that bypasses the login form.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $request->session()->has('selectedSecteur')) {
            $secteurId = SecteurCommercial::where('id_commercial', $user->id)->value('id_secteur');

            if ($secteurId) {
                $request->session()->put('selectedSecteur', $secteurId);
            }
        }

        return $next($request);
    }
}
