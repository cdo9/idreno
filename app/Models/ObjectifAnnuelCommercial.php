<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ObjectifAnnuelCommercial
 *
 * @property int $id
 * @property int $id_user
 * @property int $year
 * @property int $value
 * @property int $secteur
 * @property User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjectifAnnuelCommercial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjectifAnnuelCommercial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjectifAnnuelCommercial query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjectifAnnuelCommercial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjectifAnnuelCommercial whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjectifAnnuelCommercial whereSecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjectifAnnuelCommercial whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ObjectifAnnuelCommercial whereYear($value)
 *
 * @mixin \Eloquent
 */
class ObjectifAnnuelCommercial extends Model
{
    protected $table = 'objectif_annuel_commercial';

    public $timestamps = false;

    protected $casts = [
        'id_user' => 'int',
        'year' => 'int',
        'value' => 'int',
        'secteur' => 'int',
    ];

    protected $fillable = [
        'id_user',
        'year',
        'value',
        'secteur',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteur');
    }
}
