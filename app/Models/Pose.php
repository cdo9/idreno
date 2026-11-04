<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Pose
 *
 * @property int $id
 * @property int $id_vente
 * @property int $id_client
 * @property string $produit
 * @property string $produit2
 * @property string $produit3
 * @property Carbon|null $date_debut
 * @property Carbon|null $date_fin
 * @property int|null $visite_chantier
 * @property int|null $avis_client
 * @property int $indicateur
 * @property int|null $id_commercial_visite
 * @property int|null $avis_facebook
 * @property int $secteur
 * @property Client $client
 * @property User|null $user
 * @property Vente $vente
 * @property PoseDetail|null $pose_detail
 * @property-read Collection<int, Poseur> $poseurs
 * @property-read int|null $poseurs_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereAvisClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereAvisFacebook($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereDateDebut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereDateFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereIdClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereIdCommercialVisite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereIdVente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereIndicateur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereProduit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereProduit2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereProduit3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereSecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pose whereVisiteChantier($value)
 *
 * @mixin \Eloquent
 */
class Pose extends Model
{
    protected $table = 'poses';

    public $timestamps = false;

    protected $casts = [
        'id_vente' => 'int',
        'id_client' => 'int',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'visite_chantier' => 'int',
        'avis_client' => 'int',
        'indicateur' => 'int',
        'id_commercial_visite' => 'int',
        'avis_facebook' => 'int',
        'secteur' => 'int',
    ];

    protected $fillable = [
        'id_vente',
        'id_client',
        'produit',
        'produit2',
        'produit3',
        'date_debut',
        'date_fin',
        'visite_chantier',
        'avis_client',
        'indicateur',
        'id_commercial_visite',
        'avis_facebook',
        'secteur',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_commercial_visite');
    }

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class, 'id_vente');
    }

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(Secteur::class, 'secteur');
    }

    public function pose_detail(): HasOne
    {
        return $this->hasOne(PoseDetail::class, 'id_pose');
    }

    public function poseurs(): BelongsToMany
    {
        return $this->belongsToMany(
            Poseur::class,
            'pose_details',
            'id_pose',
            'id_poseur'
        );
    }
}
