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
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCommune newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCommune newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCommune query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCommune whereCommerciaux($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCommune whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCommune whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCommune whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCommune whereSecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCommune whereZone($value)
 * @mixin \Eloquent
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
