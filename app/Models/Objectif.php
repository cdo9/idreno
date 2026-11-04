<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Objectif
 *
 * @property int $id
 * @property int $id_commercial
 * @property Carbon $date
 * @property int $ca
 * @property int $rdv
 * @property int $secteur
 * @property User $user
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Objectif newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Objectif newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Objectif query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Objectif whereCa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Objectif whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Objectif whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Objectif whereIdCommercial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Objectif whereRdv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Objectif whereSecteur($value)
 * @mixin \Eloquent
 */
class Objectif extends Model
{
	protected $table = 'objectifs';
	public $timestamps = false;

	protected $casts = [
		'id_commercial' => 'int',
		'date' => 'datetime',
		'ca' => 'int',
		'rdv' => 'int',
		'secteur' => 'int'
	];

	protected $fillable = [
		'id_commercial',
		'date',
		'ca',
		'rdv',
		'secteur'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'id_commercial');
	}

	public function secteur()
	{
		return $this->belongsTo(Secteur::class, 'secteur');
	}
}
