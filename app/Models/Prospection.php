<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Prospection
 *
 * @property int $id
 * @property int $id_commercial
 * @property string $commune
 * @property string $zone
 * @property Carbon $date
 * @property string $type
 * @property bool|null $termine
 * @property string $heure_debut
 * @property string $heure_fin
 * @property int $secteur
 * @property User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection whereCommune($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection whereHeureDebut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection whereHeureFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection whereIdCommercial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection whereSecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection whereTermine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prospection whereZone($value)
 *
 * @mixin \Eloquent
 */
class Prospection extends Model
{
    protected $table = 'prospections';

    public $timestamps = false;

    protected $casts = [
        'id_commercial' => 'int',
        'date' => 'datetime',
        'termine' => 'bool',
        'secteur' => 'int',
    ];

    protected $fillable = [
        'id_commercial',
        'commune',
        'zone',
        'date',
        'type',
        'termine',
        'heure_debut',
        'heure_fin',
        'secteur',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_commercial');
    }

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(Secteur::class, 'secteur');
    }
}
