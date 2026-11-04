<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProspectionCommune
 * 
 * @property int $id
 * @property string $nom
 * @property string $zone
 * @property Carbon $date
 * @property string|null $commerciaux
 * @property int $secteur
 * 
 *
 * @package App\Models
 */
class ProspectionCommune extends Model
{
	protected $table = 'prospection_communes';
	public $timestamps = false;

	protected $casts = [
		'date' => 'datetime',
		'secteur' => 'int'
	];

	protected $fillable = [
		'nom',
		'zone',
		'date',
		'commerciaux',
		'secteur'
	];

	public function secteur()
	{
		return $this->belongsTo(Secteur::class, 'secteur');
	}
}
