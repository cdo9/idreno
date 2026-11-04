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
 * 
 * @property User $user
 *
 * @package App\Models
 */
class ObjectifAnnuelCommercial extends Model
{
	protected $table = 'objectif_annuel_commercial';
	public $timestamps = false;

	protected $casts = [
		'id_user' => 'int',
		'year' => 'int',
		'value' => 'int',
		'secteur' => 'int'
	];

	protected $fillable = [
		'id_user',
		'year',
		'value',
		'secteur'
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
