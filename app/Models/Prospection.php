<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Prospection extends Model
{
	protected $table = 'prospections';
	public $timestamps = false;

	protected $casts = [
		'id_commercial' => 'int',
		'date' => 'datetime',
		'termine' => 'bool',
		'secteur' => 'int'
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
