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
 *
 * @package App\Models
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
