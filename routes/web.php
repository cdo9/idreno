<?php

use App\Http\Controllers\ArgumentationController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PoseurController;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/ajax/commercials-by-secteur/{secteurId}', function ($secteurId) {
    // On filtre bien par secteur_id
    $users = User::join('secteur_commercials', 'users.id', '=', 'secteur_commercials.id_commercial')
        ->where('secteur_commercials.id_secteur', $secteurId)
        ->get(['users.id', 'users.prenom', 'users.nom', 'users.active']);

    return response()->json($users);
})->name('ajax.commercials.by.secteur');

Route::get('/ajax/setSelectedSecteur/{secteurId}', function ($secteurId) {
    if ($secteurId) {
        session(['selectedSecteur' => $secteurId]);
    }

    return response()->json('ok');
})->name('ajax.setSelectedSecteur');

Route::middleware('web')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/map-points', [MapController::class, 'getMapPoints'])
        ->name('map.points');

    Route::get('/getMarkerDetailContent', [MapController::class, 'getMarkerDetailContent'])
        ->name('map.detailContent');

    Route::post('/formulaire-contact', [ClientController::class, 'storeNouveauContact'])
        ->name('formulaire-contact');

    Route::get('/clients/autocomplete', [ClientController::class, 'autocomplete'])
        ->name('clients.autocomplete');

});

Route::middleware(['auth', 'blocages'])->group(function () {
    Route::get('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('welcome');
    })->name('logout');
    Route::get('/rattrapage', function () {
        return view('rattrapage.rattrapage');
    })->name('rattrapage');

    Route::get('/nouveau-contact', [ClientController::class, 'create'])
        ->name('nouveau-contact');

    Route::get('/confirmations-de-rendez-vous', function () {
        return view('rendez-vous.confirmations-de-rendez-vous');
    })->name('confirmations-de-rendez-vous');

    Route::get('/confirmations', function () {
        return view('rendez-vous.confirmations-de-rendez-vous');
    })->name('confirmations');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    // Route::post('logout', [LogoutController::class, 'destroy'])->name('logout');

    Route::get('/', function () {
        return view('list-cards.index');
    })->name('welcome');

    Route::get('/list-cards', function () {
        return view('list-cards.index');
    })->name('list-cards');
});

Route::middleware(['auth', 'blocages', 'role:directeur,admin,commercial,resp-prosp,telepro,administratif'])->group(function () {
    Route::get('/ventes', function () {
        return view('vente.index');
    })->name('ventes');

    Route::get('/argumentations', function () {
        return view('argumentation.argumentations');
    })->name('argumentations');

    Route::get('/poses', function () {
        return view('poses.poses');
    })->name('poses');

    Route::get('/rendez-vous', function () {
        return view('rendez-vous.rendez-vous');
    })->name('rendez-vous');
});

Route::middleware(['auth', 'blocages', 'role:directeur,admin,commercial,resp-prosp,telepro'])->group(function () {

    Route::get('/listing-clients', function () {
        return view('client.listing-client');
    })->name('listing-client');
    Route::get('/confirmations', function () {
        return view('rendez-vous.confirmations-de-rendez-vous');
    })->name('confirmations');

    Route::get('/relances', function () {
        $user = auth()->user();
        $isProspecteur = $user->hasRole('prospecteur');

        return view('relances.index', [
            'isProspecteur' => $isProspecteur,
        ]);
    })->name('relances');
    /*Route::get('/relances/contact/{clientId}/edit', function ($clientId) {
        return view('relances.edit-contact', compact('clientId'));
    })->name('relances.contact.edit');*/

    Route::get('/relances/argumentation/{rdvId}/edit', function ($rdvId) {
        return view('relances.edit-argumentation', compact('rdvId'));
    })->name('argumentation.edit');

    Route::get('tableau-chiffres', function () {
        return view('board.main-tableau-chiffres');
    })->name('tableau-chiffres');

    /*Route::get('/map', function() {
        return view('map.index');
    })->name('map');*/
    Route::get('/map', [MapController::class, 'index'])->name('map');

    Route::get('/parametre', function () {
        return view('parametres.parametres');
    })->name('parametres');

    Route::get('/stats-produit', function () {
        return view('produit.stats-produit');
    })->name('stats-produit');

    Route::get('/classement', function () {
        return view('classement.commerciaux');
    })->name('classement-commerciaux');

    Route::get('argumentation', [ArgumentationController::class, 'create'])
        ->name('argumentation');
    Route::post('argumentation', [ArgumentationController::class, 'store'])
        ->name('argumentation.store');

    Route::get('/nouveau-rendez-vous', function () {
        return view('rendez-vous.nouveau-rendez-vous');
    })->name('nouveau-rendez-vous');

    Route::get('/rendez-vous/{id}/edit', function ($id) {
        $rendezVous = Projet::findOrFail($id);

        return view('rendez-vous.edit-rendez-vous', compact('rendezVous'));
    })->name('edit-rendez-vous');

    Route::get('/clients/search', [ClientController::class, 'search'])
        ->name('clients.search');
    Route::get('/clients/{id}', [ClientController::class, 'show'])
        ->name('clients.show');

    // Route commented out - Map form functionality moved to Livewire MapContactForm component

    Route::get('/anniversaires', function () {
        return view('anniversaire.anniversaire');
    })->name('Anniversaires');

    Route::get('/challenges', function () {
        return view('challenge.challenges');
    })->name('challenges');

    Route::post('/comments', [CommentController::class, 'store'])
        ->name('comments.store');
});

Route::middleware(['auth', 'blocages', 'role:directeur,admin'])->group(function () {

    Route::get('/formulaire-challenge/', function () {
        $id = request('id');

        return view('challenge.formulaire-challenge', compact('id'));
    })->name('Formulaire-challenge');

    Route::get('/stats-contact', function () {
        return view('contact.stats-contact');
    })->name('stats-contact');

    Route::get('/create-challenge', function () {
        return view('challenge.create-challenge');
    })->name('Nouveau-Challenge');

    Route::get('/delete-challenge/{id}', function ($id) {
        return view('challenge.delete-challenge', compact('id'));
    })->name('Delete-challenge');

    Route::get('/commissions', function () {
        $commercial = request('commercial', '');
        $mois = request('mois', '');
        $annee = request('annee', '');

        return view('commission.index', compact('commercial', 'mois', 'annee'));
    })->name('commissions');

    Route::get('/poseurs', [PoseurController::class, 'index'])
        ->name('poseurs');
    Route::get('/poseurs/{poseur}/edit', [PoseurController::class, 'edit'])
        ->name('poseurs.edit');
    Route::post('/poseurs/{poseur}/update', [PoseurController::class, 'update'])
        ->name('poseurs.update');
    Route::get('/poseurs/create', [PoseurController::class, 'create'])
        ->name('poseurs.create');
    Route::post('/poseurs/post', [PoseurController::class, 'post'])
        ->name('poseurs.post');

    Route::put('/clients/{id}/update-type', [ClientController::class, 'updateType'])
        ->name('clients.update-type');

    Route::get('/nouvelle-pose/{id?}', function ($id = null) {
        return view('poses.nouvelle-pose', compact('id'));
    })->name('nouvelle-pose');

    Route::get('/blocages', function () {
        return view('blocage.blocages');
    })->name('blocages');

});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/gestion-commerciaux', function () {
        return view('gestion.gestion-commerciaux');
    })->name('gestion-commerciaux');

    Route::get('/parc-auto', function () {
        return view('parc.parc-auto');
    })->name('parc-auto');

    Route::get('/secteurs-list', function () {
        return view('secteurs.secteur-show-list');
    })->name('secteur-show-list');

    Route::get('/formulaire-auto-new', function () {
        return view('parc.formulaire-auto-new');
    })->name('nouveau-véhicule');

    Route::get('/modifier-vehicule/{id}', function ($id) {
        return view('parc.formulaire-auto-modify', compact('id'));
    })->name('modifier-vehicule');

    Route::get('/supprimer-vehicule/{id}', function ($id) {
        return view('parc.formulaire-auto-delete', compact('id'));
    })->name('supprimer-vehicule');

    Route::post('/supprimer-vehicule/{id}', function ($id) {
        DB::table('parc_km')->where('auto_id', $id)->delete();
        DB::table('parc_auto')->where('id', $id)->delete();

        return redirect('/parc-auto')->with('message', 'Véhicule supprimé.');
    });

    Route::middleware(['auth', 'blocages:ecarts,aconfirmer'])->group(function () {
        Route::get('/gestion.gestion-commerciaux', function () {
            return view('gestion.gestion-commerciaux');
        });
    });

    Route::get('alertes', function () {
        return view('alertes.alertes');
    })->name('alertes');

});
