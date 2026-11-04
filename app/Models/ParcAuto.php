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
 * 
 * @property Collection|ParcKm[] $parc_kms
 *
 * @package App\Models
 */
class ParcAuto extends Model
{
	protected $table = 'parc_auto';
	public $timestamps = false;

	protected $casts = [
		'date_attribution' => 'datetime',
		'secteur' => 'int'
	];

	protected $fillable = [
		'immatriculation',
		'modele',
		'conducteur',
		'date_attribution',
		'secteur'
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
