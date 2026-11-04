<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

/**
 * Class Client
 *
 * @property int $id
 * @property string $nom
 * @property string $prenom
 * @property string $fullname
 * @property string $adresse
 * @property string $appart
 * @property string $cp
 * @property string $ville
 * @property string|null $gps_lat
 * @property string|null $gps_lng
 * @property string $telephone1
 * @property string $telephone2
 * @property string $mail
 * @property int $rentre_par
 * @property int $partage_avec
 * @property string|null $source
 * @property int $id_doublon
 * @property Carbon $rentre_le
 * @property string $debrief
 * @property int $type
 * @property string $genre
 * @property int|null $id_source
 * @property string $type_habitation
 * @property string $age
 * @property string $profession
 * @property string $profession2
 * @property string|null $nb_foyer
 * @property string $dernier_investissement
 * @property string $mode_conso
 * @property string $conso_chauffage
 * @property string $credit_impot
 * @property string $surface
 * @property string $type_chauffage
 * @property string $proprietaire
 * @property string $duree_proprietaire
 * @property Carbon|null $date_relance
 * @property Carbon|null $date_anniversaire
 * @property string|null $isolation
 * @property string|null $conso_chauffage_qualite
 * @property string|null $eau_chaude
 * @property string|null $age_materiel
 * @property int $bloque
 * @property string|null $signe
 * @property string|null $projet
 * @property Carbon|null $date_dernier_contact
 * @property string|null $dernier_contact
 * @property int $secteur
 * @property bool $accept_demarchage
 * @property Collection|Evaluation[] $evaluations
 * @property Collection|Pose[] $poses
 * @property Collection|Projet[] $projets
 * @property Collection|RendezVous[] $rendez_vous
 * @property Collection|Tracking[] $trackings
 * @property Collection|Vente[] $ventes
 * @property string|null $profil_clients
 * @property bool $relance
 * @property-read Collection<int, ClientHistory> $clientHistory
 * @property-read int|null $client_history_count
 * @property-read Collection<int, Comment> $comments
 * @property-read int|null $comments_count
 * @property-read Comment|null $dernierCommentaire
 * @property-read Projet|null $dernierRdv
 * @property-read int|null $evaluations_count
 * @property-read string $telephone1_formatted
 * @property-read string $telephone2_formatted
 * @property-read User|null $partageUser
 * @property-read int|null $poses_count
 * @property-read int|null $projets_count
 * @property-read int|null $rendez_vous_count
 * @property-read int|null $trackings_count
 * @property-read User|null $user
 * @property-read int|null $ventes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAcceptDemarchage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAdresse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAgeMateriel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAppart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereBloque($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereConsoChauffage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereConsoChauffageQualite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreditImpot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDateAnniversaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDateDernierContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDateRelance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDebrief($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDernierContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDernierInvestissement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereDureeProprietaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEauChaude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereGenre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereGpsLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereGpsLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereIdDoublon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereIdSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereIsolation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereModeConso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereNbFoyer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePartageAvec($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePrenom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereProfession($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereProfession2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereProfilClients($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereProjet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereProprietaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereRelance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereRentreLe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereRentrePar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereSecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereSigne($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereSurface($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTelephone1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTelephone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTypeChauffage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTypeHabitation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereVille($value)
 *
 * @mixin \Eloquent
 */
class Client extends Model
{
    use Searchable;

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        // Je veux chercher dans la vue view_clients_fullname et dans la table clients
        return [
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'telephone1' => $this->telephone1,
            'telephone2' => $this->telephone2,
            'adresse' => $this->adresse,
            'cp' => $this->cp,
            'ville' => $this->ville,
            'mail' => $this->mail,
        ];
    }

    protected $table = 'clients';

    public $timestamps = false;

    const CIVILITES = [
        'Monsieur',
        'Madame',
        'Mr & Mme',
        'SCI',
        'Société',
    ];

    const TYPE_HABITATIONS = [
        'Maison Individuelle',
        'Appartement',
    ];

    const NB_PERSONNES_FOYER = [
        '0',
        '1',
        '2',
        '3+',
    ];

    const CONSO_CHAUFFAGE_ANNUELLE = [
        'Basse',
        'Moyenne',
        'Élevée',
    ];

    const PRODUCTION_EAU_CHAUDE = [
        'Cumulus',
        'Chaudière',
        'Autre',
    ];

    const ISOLATION = [
        'Excellente',
        'Moyenne',
        'Mauvaise',
    ];

    const CHAUFFAGE_TYPE = [
        'Électrique',
        'Fioul',
        'Gaz',
        'Poêle',
        'Autre',
    ];

    const AGE_MATERIEL = [
        '0-5 ans',
        '5-10 ans',
        '10-15 ans',
        '15+',
    ];

    protected $casts = [
        'rentre_par' => 'int',
        'partage_avec' => 'int',
        'id_doublon' => 'int',
        'rentre_le' => 'datetime',
        'type' => 'int',
        'id_source' => 'int',
        'date_relance' => 'date',
        'date_anniversaire' => 'date',
        'bloque' => 'int',
        'date_dernier_contact' => 'datetime',
        'relance' => 'bool',
        'secteur' => 'int',
        'date_naissance' => 'date',
        'accept_demarchage' => 'boolean',
    ];

    protected $fillable = [
        'nom',
        'prenom',
        'adresse',
        'appart',
        'cp',
        'ville',
        'gps_lat',
        'gps_lng',
        'telephone1',
        'telephone2',
        'mail',
        'rentre_par',
        'partage_avec',
        'source',
        'id_doublon',
        'rentre_le',
        'debrief',
        'type',
        'genre',
        'id_source',
        'type_habitation',
        'age',
        'profession',
        'profession2',
        'nb_foyer',
        'dernier_investissement',
        'mode_conso',
        'conso_chauffage',
        'credit_impot',
        'surface',
        'type_chauffage',
        'proprietaire',
        'duree_proprietaire',
        'date_relance',
        'date_anniversaire',
        'isolation',
        'conso_chauffage_qualite',
        'eau_chaude',
        'age_materiel',
        'bloque',
        'signe',
        'projet',
        'date_dernier_contact',
        'dernier_contact',
        'secteur',
        'date_naissance',
        'relance',
        'accept_demarchage',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'id_source');
    }

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(Secteur::class, 'secteur');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'id_argu');
    }

    public function poses(): HasMany
    {
        return $this->hasMany(Pose::class, 'id_client');
    }

    public function projets(): HasMany
    {
        return $this->hasMany(Projet::class, 'id_client');
    }

    public function rendez_vous(): HasMany
    {
        return $this->hasMany(RendezVous::class, 'id_client');
    }

    public function dernierRdv(): HasOne
    {
        return $this->hasOne(Projet::class, 'id_client')->latest('date');
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(Tracking::class, 'id_client');
    }

    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class, 'id_client');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rentre_par');
    }

    public function partageUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partage_avec');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'client_id');
    }

    public function dernierCommentaire(): HasOne
    {
        return $this->hasOne(Comment::class)->latest('id');
    }

    public function clientHistory(): HasMany
    {
        return $this->hasMany(ClientHistory::class, 'client_id');
    }

    public static function getAgeFromDate($date_anniversaire)
    {
        if ($date_anniversaire) {
            try {
                $birthDate = Carbon::parse($date_anniversaire);
                $now = Carbon::now();

                return (int) $birthDate->diffInYears($now);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Obtenir toutes les civilités disponibles
     */
    public static function getCivilites()
    {
        return self::CIVILITES;
    }

    public static function getTypeHabitations()
    {
        return self::TYPE_HABITATIONS;
    }

    public static function getNbPersonnesFoyer()
    {
        return self::NB_PERSONNES_FOYER;
    }

    public static function getConsoChauffageAnnuelle()
    {
        return self::CONSO_CHAUFFAGE_ANNUELLE;
    }

    public static function getProductionEauChaude()
    {
        return self::PRODUCTION_EAU_CHAUDE;
    }

    public static function getIsolation()
    {
        return self::ISOLATION;
    }

    public static function getChauffageType()
    {
        return self::CHAUFFAGE_TYPE;
    }

    public static function getAgeMateriel()
    {
        return self::AGE_MATERIEL;
    }

    /**
     * Vérifier si la civilité est valide
     */
    public static function isValidCivilite($civilite)
    {
        return in_array($civilite, self::CIVILITES);
    }

    public function getNomAttribute($value): string
    {
        return ucfirst(strtolower($value ?? ''));
    }

    public function getPrenomAttribute($value): string
    {
        return ucfirst(strtolower($value ?? ''));
    }

    public function getVilleAttribute($value): string
    {
        return ucwords(strtolower($value ?? ''));
    }

    public function getTelephone1FormattedAttribute(): string
    {
        return $this->formatPhone($this->telephone1);
    }

    public function getTelephone2FormattedAttribute(): string
    {
        return $this->formatPhone($this->telephone2);
    }

    private function formatPhone(?string $phone): string
    {
        if (! $phone || strlen(preg_replace('/[^0-9]/', '', $phone)) < 8) {
            return $phone ?? '';
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);

        return preg_replace('/(.{2})/', '$1 ', $clean);  // ✅ TOUS chiffres
    }

    /**
     * Recherche dans la table clients (Scout) et dans la vue view_clients_fullname (DB)
     */
    public static function searchFull($term, $limit = 100)
    {

        $user = auth()->user();
        $foundIds = self::search($term)->take($limit)->get()->pluck('id');

        $scoutQuery = self::query()
            ->whereIn('id', $foundIds)
            ->addSelect([
                'dernier_rdv' => DB::table('projets')
                    ->select('date')
                    ->whereColumn('projets.id_client', 'clients.id')
                    ->orderByDesc('date')
                    ->limit(1),
            ]);

        if (! $user->hasRole('admin') && ! $user->hasRole('directeur')) {
            $scoutQuery->where(function ($query) use ($user) {
                $query->where('rentre_par', $user->id)
                    ->orWhere('partage_avec', $user->id)
                    ->orWhereExists(function ($sub) use ($user) {
                        $sub->select(DB::raw(1))
                            ->from('projets')
                            ->whereColumn('projets.id_client', 'clients.id')
                            ->where('projets.partage_avec', $user->id);
                    });
            });
        }

        $scoutResults = $scoutQuery->orderByDesc('dernier_rdv')->get();

        $query = DB::table('view_clients_fullname')
            ->where('fullname', 'like', '%'.$term.'%')
            ->addSelect([
                '*',
                'dernier_rdv' => DB::table('projets')
                    ->select('date')
                    ->whereColumn('projets.id_client', 'id')
                    ->orderByDesc('date')
                    ->limit(1),
            ]);

        if (! $user->hasRole('admin') && ! $user->hasRole('directeur')) {
            $query = $query->where(function ($q) use ($user) {
                $q->where('rentre_par', $user->id)
                    ->orWhere('partage_avec', $user->id)
                    ->orWhereExists(function ($sub) use ($user) {
                        $sub->select(DB::raw(1))
                            ->from('projets')
                            ->whereColumn('projets.id_client', 'id')
                            ->where('projets.partage_avec', $user->id);
                    });
            });
        }

        $viewResults = $query
            ->orderByDesc('dernier_rdv')
            ->limit($limit)
            ->get();

        return collect($scoutResults)->merge($viewResults)->unique('id')->take($limit);
    }
}
