<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProspectionCase
 *
 * @property string $nom
 * @property bool $actif
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCase whereActif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProspectionCase whereNom($value)
 * @mixin \Eloquent
 */
class ProspectionCase extends Model
{
	protected $table = 'prospection_cases';
	protected $primaryKey = 'nom';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'actif' => 'bool'
	];

	protected $fillable = [
		'actif'
	];
}
