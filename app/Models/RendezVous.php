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

/**
 * Class RendezVou
 *
 * @property int $id
 * @property int $id_commercial
 * @property int $id_client
 * @property string $resultat
 * @property string $commentaire
 * @property string $partage_avec
 * @property Carbon|null $date
 * @property Carbon|null $date_relance
 * @property int $relance_confirme
 * @property string $source
 * @property int $tarif_total_ht
 * @property int $devis_total_ht
 * @property string|null $produits
 * @property int $secteur
 * @property Client $client
 * @property User $user
 * @property Collection|Vente[] $ventes
 * @property-read int|null $ventes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereCommentaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereDateRelance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereDevisTotalHt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereIdClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereIdCommercial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous wherePartageAvec($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereProduits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereRelanceConfirme($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereResultat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereSecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RendezVous whereTarifTotalHt($value)
 *
 * @mixin \Eloquent
 */
class RendezVous extends Model
{
    protected $table = 'rendez_vous';

    public $timestamps = false;

    protected $casts = [
        'id_commercial' => 'int',
        'id_client' => 'int',
        'date' => 'datetime',
        'date_relance' => 'date',
        'relance_confirme' => 'int',
        'tarif_total_ht' => 'int',
        'devis_total_ht' => 'int',
        'secteur' => 'int',
    ];

    protected $fillable = [
        'id_commercial',
        'id_client',
        'resultat',
        'commentaire',
        'partage_avec',
        'date',
        'date_relance',
        'relance_confirme',
        'source',
        'tarif_total_ht',
        'devis_total_ht',
        'produits',
        'secteur',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_commercial');
    }

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(Secteur::class, 'secteur');
    }

    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class, 'id_rdv');
    }
}
