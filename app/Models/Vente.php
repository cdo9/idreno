<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\VenteFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Vente
 *
 * @property int $id
 * @property int $id_rdv
 * @property int $id_client
 * @property int $id_commercial
 * @property int|null $id_commercial_2
 * @property int|null $id_commercial_3
 * @property int $tarif_total_ht
 * @property int $ca_total_ht
 * @property int $repartition
 * @property int $tarif_ht
 * @property int $ca_ht
 * @property int $remise
 * @property int $montant
 * @property int $plus_value
 * @property int $prime_cee
 * @property Carbon $date
 * @property int|null $id_source
 * @property string $paiement
 * @property string $type_vente
 * @property int $statut
 * @property Carbon|null $fin_financement
 * @property Carbon|null $dateAdd
 * @property string $complement
 * @property string $attente
 * @property string|null $attente_autre
 * @property int|null $commande
 * @property Carbon|null $date_commission
 * @property int|null $commission.blade.php
 * @property string|null $commentaire
 * @property int $secteur
 * @property Client $client
 * @property User $user
 * @property RendezVous $rendez_vou
 * @property Source|null $source
 * @property Collection|Pose[] $poses
 * @property int|null $commission
 * @property int|null $duree_pret
 * @property int|null $postpone
 * @property int|null $mensualite
 * @property-read mixed $attente_options
 * @property-read mixed $paiement_options
 * @property-read mixed $type_vente_options
 * @property-read int|null $poses_count
 * @property-read RendezVous $rendez_vous
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereAttente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereAttenteAutre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereCaHt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereCaTotalHt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereCommande($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereCommentaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereCommission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereComplement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereDateAdd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereDateCommission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereDureePret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereFinFinancement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereIdClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereIdCommercial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereIdCommercial2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereIdCommercial3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereIdRdv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereIdSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereMensualite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente wherePaiement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente wherePlusValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente wherePostpone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente wherePrimeCee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereRemise($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereRepartition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereSecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereTarifHt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereTarifTotalHt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vente whereTypeVente($value)
 *
 * @mixin \Eloquent
 */
class Vente extends Model
{
    /** @use HasFactory<VenteFactory> */
    use HasFactory;

    protected $table = 'ventes';

    public $timestamps = false;

    const TYPE_VENTE_OPTIONS = ['Commune', 'Normale'];

    const PAIEMENT_OPTIONS = ['Comptant', 'Financement', 'Comptant et financement'];

    const ATTENTE_OPTIONS = [
        'Financement',
        'Mairie',
        'AH',
        'ANAH',
        'Pose différée',
        'Syndic',
        'GRDF',
        'Acompte',
        'Maprimerenov',
        'DPE',
        'Metre décalé',
        'Autre',
    ];

    protected $casts = [
        'id_rdv' => 'int',
        'id_client' => 'int',
        'id_commercial' => 'int',
        'id_commercial_2' => 'int',
        'id_commercial_3' => 'int',
        'tarif_total_ht' => 'int',
        'ca_total_ht' => 'int',
        'repartition' => 'int',
        'tarif_ht' => 'int',
        'ca_ht' => 'int',
        'remise' => 'int',
        'montant' => 'int',
        'plus_value' => 'int',
        'prime_cee' => 'int',
        'date' => 'datetime',
        'id_source' => 'int',
        'statut' => 'int',
        'fin_financement' => 'datetime',
        'postpone' => 'int',
        'duree_pret' => 'int',
        'mensualite' => 'int',
        'dateAdd' => 'datetime',
        'commande' => 'int',
        'date_commission' => 'datetime',
        'commission' => 'int',
        'secteur' => 'int',
    ];

    protected $fillable = [
        'id_rdv',
        'id_client',
        'id_commercial',
        'id_commercial_2',
        'id_commercial_3',
        'tarif_total_ht',
        'ca_total_ht',
        'repartition',
        'tarif_ht',
        'ca_ht',
        'remise',
        'montant',
        'plus_value',
        'prime_cee',
        'date',
        'id_source',
        'paiement',
        'postpone',
        'duree_pret',
        'type_vente',
        'statut',
        'fin_financement',
        'dateAdd',
        'complement',
        'attente',
        'attente_autre',
        'commande',
        'date_commission',
        'commission',
        'commentaire',
        'secteur',
        'mensualite',
    ];

    public static function getPaiementOptionsAttribute()
    {
        return self::PAIEMENT_OPTIONS;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_commercial');
    }

    public function rendez_vous(): BelongsTo
    {
        return $this->belongsTo(RendezVous::class, 'id_rdv');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'id_source');
    }

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(Secteur::class, 'secteur');
    }

    public function poses(): HasMany
    {
        return $this->hasMany(Pose::class, 'id_vente');
    }

    // ! Pas use mais je le met au cas où
    public static function getTypeVenteOptionsAttribute()
    {
        return self::TYPE_VENTE_OPTIONS;
    }

    public static function getAttenteOptionsAttribute()
    {
        return self::ATTENTE_OPTIONS;
    }
}
