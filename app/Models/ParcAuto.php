<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ParcAuto
 *
 * @property int $id
 * @property string $immatriculation
 * @property string $modele
 * @property string $conducteur
 * @property Carbon $date_attribution
 * @property int $secteur
 * @property Collection|ParcKm[] $parc_kms
 * @property-read int|null $parc_kms_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcAuto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcAuto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcAuto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcAuto whereConducteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcAuto whereDateAttribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcAuto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcAuto whereImmatriculation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcAuto whereModele($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcAuto whereSecteur($value)
 *
 * @mixin \Eloquent
 */
class ParcAuto extends Model
{
    protected $table = 'parc_auto';

    public $timestamps = false;

    protected $casts = [
        'date_attribution' => 'datetime',
        'secteur' => 'int',
    ];

    protected $fillable = [
        'immatriculation',
        'modele',
        'conducteur',
        'date_attribution',
        'secteur',
    ];

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteur');
    }

    public function parc_kms()
    {
        return $this->hasMany(ParcKm::class, 'auto_id');
    }
}
