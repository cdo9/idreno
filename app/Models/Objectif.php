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
 * 
 * @property User $user
 *
 * @package App\Models
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
