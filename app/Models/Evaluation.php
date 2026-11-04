<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Evaluation
 *
 * @property int $id
 * @property int $id_argu
 * @property int $id_vendeur
 * @property int $id_formateur
 * @property string $action
 * @property string $fait_par
 * @property int $acquis
 * @property int $secteur
 * @property Client $client
 * @property User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereAcquis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereFaitPar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereIdArgu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereIdFormateur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereIdVendeur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereSecteur($value)
 *
 * @mixin \Eloquent
 */
class Evaluation extends Model
{
    protected $table = 'evaluations';

    public $timestamps = false;

    protected $casts = [
        'id_argu' => 'int',
        'id_vendeur' => 'int',
        'id_formateur' => 'int',
        'acquis' => 'int',
        'secteur' => 'int',
    ];

    protected $fillable = [
        'id_argu',
        'id_vendeur',
        'id_formateur',
        'action',
        'fait_par',
        'acquis',
        'secteur',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_argu');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_vendeur');
    }

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteur');
    }
}
