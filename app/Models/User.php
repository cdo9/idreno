<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $nom
 * @property string $prenom
 * @property int $objectif_ca
 * @property int $objectif_rdv
 * @property int $type
 * @property int|null $directeur
 * @property int $groupe
 * @property int $telepro
 * @property int|null $prospecteur
 * @property int|null $junior
 * @property string|null $role
 * @property string|null $contrat
 * @property int $ordre_affichage
 * @property int $active
 * @property string|null $activation_hash
 * @property string|null $password_reset_hash
 * @property int|null $password_reset_timestamp
 * @property bool $failed_logins
 * @property int|null $last_failed_login
 * @property Carbon|null $registration_datetime
 * @property string $registration_ip
 * @property Carbon|null $delete
 * @property Carbon|null $popup_winner_loser
 * @property string|null $horaires
 * @property Carbon|null $date_entree
 * @property string|null $lat
 * @property string|null $lng
 * @property Carbon|null $gps
 * @property int|null $nouveau_vendeur
 * @property int|null $formateur
 * @property Carbon|null $date_entretien
 * @property Carbon|null $released
 * @property Collection|Evaluation[] $evaluations
 * @property Collection|Groupe[] $groupes
 * @property Collection|ObjectifAnnuelCommercial[] $objectif_annuel_commercials
 * @property Collection|Objectif[] $objectifs
 * @property Collection|Pose[] $poses
 * @property Collection|Prospection[] $prospections
 * @property Collection|RendezVous[] $rendez_vous
 * @property Collection|SecteurCommercial[] $secteur_commercials
 * @property Collection|Tracking[] $trackings
 * @property Collection|Vente[] $ventes
 * @package App\Models
 * @property-read int|null $evaluations_count
 * @property-read string $display_name
 * @property-read int|null $groupes_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read int|null $objectif_annuel_commercials_count
 * @property-read int|null $objectifs_count
 * @property-read int|null $poses_count
 * @property-read int|null $prospections_count
 * @property-read int|null $rendez_vous_count
 * @property-read int|null $secteur_commercials_count
 * @property-read int|null $trackings_count
 * @property-read int|null $ventes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActivationHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereContrat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDateEntree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDateEntretien($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDirecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFailedLogins($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFormateur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGroupe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereHoraires($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereJunior($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastFailedLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNouveauVendeur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereObjectifCa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereObjectifRdv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrdreAffichage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePasswordResetHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePasswordResetTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePopupWinnerLoser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePrenom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProspecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRegistrationDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRegistrationIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereReleased($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTelepro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use Notifiable;
    protected $has_secteurs = null;

    /**
     * Check if the user has a specific role
     *
     * @param string $role The role to check
     * @param User|null $currentUser The user to check the role for
     * @return bool
     */
    public function hasRole(string $role, $currentUser = null): bool
    {
        if ($currentUser === null) $currentUser = $this;

        switch ($role) {
            case 'admin':
                return in_array($currentUser->role, ['admin']);
            case 'directeur':
                return in_array($currentUser->role, ['admin', 'directeur']) || $currentUser->directeur == 1;
            case 'commercial':
            case 'vendeur':
                return in_array($currentUser->role, ['admin', 'directeur', 'commercial']);
            case 'telepro':
            case 'prospecteur':
                return in_array($currentUser->role, ['telepro', 'resp-prosp']) || $currentUser->telepro == 1;
            case '!telepro':
            case '!prospecteur':
                return $currentUser->role == 'telepro';
            case 'metreur':
                return $currentUser->role == 'metreur';
            case 'resp-prosp':
                return $currentUser->role == 'resp-prosp';
            case 'animateur':
                return $currentUser->is_animateur ?? false;
            case 'formateur':
                return $currentUser->formateur == 1;
            case 'responsable':
                return $currentUser->role === 'responsable';
            case 'administratif':
                return $currentUser->role === 'administratif';
            default:
                return false;
        }
    }

    public function getAuthorizedPages()
    {
        // Pages toujours accessibles (même en cas de blocage)
        $always_accessible = [
            'rattrapage', 'horaire', 'formulaire-contact', 'planning-telepro', 'confirmations'
        ];

        $page_enable = $always_accessible;

        // Pour les utilisateurs admin avec id = 1 : accès complet à toutes les routes et pages
        if ($this->id == 1) {
            $page_enable = array_merge($page_enable, [
                'listing-client', 'clients.update-type', 'Anniversaires', 'ventes', 'parametres',
                'classement-commerciaux', 'tableau-chiffres', 'confirmations-de-rendez-vous', 'relances',
                'produits', 'nouvelle-pose', 'nouveau-contact', 'nouveau-rendez-vous',
                'argumentations', 'argumentation', 'rendez-vous', 'formulaire-pose', 'poses',
                'challenges', 'Formulaire-challenge', 'planning-telepro', 'map', 'poseurs',
                'formulaire-poseur', 'stats-produit', 'blocages', 'gestion-utilisateurs',
                'gestion-commerciaux', 'parc-auto', 'formulaire-auto', 'stats-contact',
                'secteur-show-list', 'alertes', 'commissions'
            ]);
            return $page_enable;
        }

        // Admins : Accès complet à toutes les fonctionnalités + gestion utilisateurs
        if ($this->hasRole('admin')) {
            $page_enable = array_merge($page_enable, [
                // Toutes les fonctionnalités des directeurs
                'listing-client', 'clients.update-type', 'Anniversaires', 'ventes', 'commissions',
                'classement-commerciaux', 'tableau-chiffres', 'stats-produit',
                'nouveau-contact', 'nouveau-rendez-vous', 'argumentations', 'argumentation', 'rendez-vous',
                'confirmations-de-rendez-vous', 'relances',
                'map', 'planning-telepro',
                'produits', 'nouvelle-pose', 'formulaire-pose', 'poses', 'challenges', 'Formulaire-challenge',
                'poseurs', 'formulaire-poseur', 'parametres',
                'blocages',
                // Fonctionnalités admin uniquement
                'gestion-utilisateurs', 'gestion-commerciaux', 'parc-auto',
                'formulaire-auto', 'stats-contact', 'secteur-show-list',
                'alertes'
            ]);
        }
        // Directeurs : Accès complet à toutes les fonctionnalités (sauf admin uniquement)
        elseif ($this->hasRole('directeur') || $this->directeur == 1) {
            $page_enable = array_merge($page_enable, [
                // Gestion clients, ventes, commissions
                'listing-client', 'clients.update-type', 'Anniversaires', 'ventes', 'commissions',
                // Statistiques et analyses complètes
                'classement-commerciaux', 'tableau-chiffres', 'stats-produit',
                // Toutes les fonctionnalités de saisie
                'nouveau-contact', 'nouveau-rendez-vous', 'argumentations', 'argumentation', 'rendez-vous',
                'confirmations-de-rendez-vous', 'relances',
                // Cartes et plannings
                'map', 'planning-telepro',
                // Gestion des produits et du site web
                'produits', 'poses', 'challenges', 'Formulaire-challenge',
                 'formulaire-poseur', 'parametres','stats-contact',
                // Évaluations et blocages
                'blocages'
            ]);
        }
        // Commerciaux : Accès étendu aux fonctionnalités commerciales
        elseif ($this->hasRole('commercial')) {
            $page_enable = array_merge($page_enable, [
                // Gestion de leurs clients et ventes
                'listing-client', 'Anniversaires', 'ventes',
                // Saisie de contacts, RDV, argumentations
                'nouveau-contact', 'nouveau-rendez-vous', 'argumentations', 'argumentation', 'rendez-vous',
                // Statistiques personnelles et classements
                'classement-commerciaux', 'tableau-chiffres', 'stats-produit',
                // Prospections et cartes
                'map',
                // Confirmations et relances
                'confirmations-de-rendez-vous', 'relances',
                // Évaluations
                'poses', 'challenges',
                'parametres'
            ]);
        }
        // Prospecteurs/Téléprospecteurs : Accès limité aux fonctionnalités de prospection
        elseif ($this->hasRole('telepro') || $this->hasRole('prospecteur') || $this->hasRole('resp-prosp')) {
            $page_enable = array_merge($page_enable, [
                // Saisie de contacts et RDV
                'nouveau-contact', 'nouveau-rendez-vous',
                // Consultations clients limitées
                //'listing-client',
                // Confirmations et relances
                'confirmations-de-rendez-vous', 'relances',
                // Statistiques de base
                'tableau-chiffres', 'classement-commerciaux', 'stats-produit',
                // Fonctionnalités de base
                'argumentations', 'argumentation', 'rendez-vous', 'poses', 'map',
                'parametres','ventes'
            ]);
        }
        // Métreurs : Accès très restreint - Uniquement la page ventes
        elseif ($this->hasRole('metreur')) {
            $page_enable = array_merge($page_enable, [
                'ventes'
            ]);
        }
        elseif ($this->hasRole('administratif')) {
            $page_enable = [
                'ventes', 'argumentations', 'rendez-vous', 'poses'
            ];
        }

        return $page_enable;
    }

    /**
     * Vérifier si l'utilisateur peut accéder à une page spécifique
     */
    public function canAccessPage($page)
    {
        return in_array($page, $this->getAuthorizedPages());
    }

    /**
     * Get the full name of the user
     *
     * @return string
     */
    public function getFullName(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getDisplayNameAttribute(): string
    {        
        return ucfirst(strtolower($this->prenom)) . ' ' . ucfirst(strtolower(substr($this->nom, 0, 3)));
    }

	public function isFinPeriodeEssai(): bool
	{
		$dateEntree = Carbon::parse($this->date_entree)->startOfDay();
		$date1 = now()->startOfDay()->subMonths(3);
		$date2 = now()->startOfDay()->subMonths(3)->addDays(21);

		return $dateEntree->between($date1, $date2);
	}

	/*
	* Retourne si le compte de l'utilisateur a été débloqué
	*/
	public function isReleased(): bool {
		$released = false;
        if ($this->released != null && Carbon::parse($this->released)->isToday()) {
            $released = true;
        }
		return $released;
	}

	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime',
		'objectif_ca' => 'int',
		'objectif_rdv' => 'int',
		'type' => 'int',
		'directeur' => 'int',
		'groupe' => 'int',
		'telepro' => 'int',
		'prospecteur' => 'int',
		'junior' => 'int',
		'ordre_affichage' => 'int',
		'active' => 'int',
		'password_reset_timestamp' => 'int',
		'failed_logins' => 'bool',
		'last_failed_login' => 'int',
		'registration_datetime' => 'datetime',
		'delete' => 'datetime',
		'popup_winner_loser' => 'datetime',
		'date_entree' => 'datetime',
		'gps' => 'datetime',
		'nouveau_vendeur' => 'int',
		'formateur' => 'int',
		'date_entretien' => 'datetime',
		'released' => 'date'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'email_verified_at',
		'password',
		'remember_token',
		'nom',
		'prenom',
		'objectif_ca',
		'objectif_rdv',
		'type',
		'directeur',
		'groupe',
		'telepro',
		'prospecteur',
		'junior',
		'role',
		'contrat',
		'ordre_affichage',
		'active',
		'activation_hash',
		'password_reset_hash',
		'password_reset_timestamp',
		'failed_logins',
		'last_failed_login',
		'registration_datetime',
		'registration_ip',
		'delete',
		'popup_winner_loser',
		'horaires',
		'date_entree',
		'lat',
		'lng',
		'gps',
		'nouveau_vendeur',
		'formateur',
		'date_entretien'
	];

	public function evaluations()
	{
		return $this->hasMany(Evaluation::class, 'id_vendeur');
	}

	public function groupes()
	{
		return $this->hasMany(Groupe::class, 'id_responsable');
	}

	public function objectif_annuel_commercials()
	{
		return $this->hasMany(ObjectifAnnuelCommercial::class, 'id_user');
	}

	public function objectifs()
	{
		return $this->hasMany(Objectif::class, 'id_commercial');
	}

	public function poses()
	{
		return $this->hasMany(Pose::class, 'id_commercial_visite');
	}

	public function prospections()
	{
		return $this->hasMany(Prospection::class, 'id_commercial');
	}

	public function rendez_vous()
	{
		return $this->hasMany(RendezVous::class, 'id_commercial');
	}

	public function secteur_commercials()
	{
		return $this->hasMany(SecteurCommercial::class, 'id_commercial')
            ->whereHas('secteur', function ($query) {
                $query->where('activated', 1);
            });
	}

    public function secteurs()
    {
        return $this->secteur_commercials()->with('secteur')->get()->pluck('secteur');
    }

	public function has_secteur($secteur_id): bool
    {
        if ($this->has_secteurs === null) {
            $this->has_secteurs = $this->secteur_commercials->pluck('id_secteur')->unique()->toArray();
        }
        
        return in_array($secteur_id, $this->has_secteurs, true);
    }

	public function trackings()
	{
		return $this->hasMany(Tracking::class, 'id_commercial');
	}

	public function ventes()
	{
		return $this->hasMany(Vente::class, 'id_commercial');
	}
}
