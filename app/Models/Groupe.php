<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Groupe
 *
 * @property int $id
 * @property int $id_responsable
 * @property string $nom
 * @property int|null $tri
 * @property int $secteur
 * @property User $user
 * @property-read User $responsable
 * @property-read Secteur $secteurRel
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Groupe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Groupe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Groupe query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Groupe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Groupe whereIdResponsable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Groupe whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Groupe whereSecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Groupe whereTri($value)
 *
 * @mixin \Eloquent
 */
class Groupe extends Model
{
    protected $table = 'groupes';

    public $timestamps = false;

    protected $casts = [
        'id_responsable' => 'int',
        'tri' => 'int',
        'secteur' => 'int',
    ];

    protected $fillable = [
        'id_responsable',
        'nom',
        'tri',
        'secteur',
    ];

    /*public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_responsable');
    }*/

    public function secteurRel(): BelongsTo
    {
        return $this->belongsTo(Secteur::class, 'secteur');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_responsable');
    }
}
