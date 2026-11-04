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
 * 
 * @property Client $client
 * @property User $user
 *
 * @package App\Models
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
		'secteur' => 'int'
	];

	protected $fillable = [
		'id_argu',
		'id_vendeur',
		'id_formateur',
		'action',
		'fait_par',
		'acquis',
		'secteur'
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
