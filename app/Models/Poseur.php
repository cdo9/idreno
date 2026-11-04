<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Poseur
 * 
 * @property int $id
 * @property string $poseur
 * @property int|null $archive
 * 
 * @property PoseDetail|null $pose_detail
 *
 * @package App\Models
 */
class Poseur extends Model
{
	protected $table = 'poseurs';
	public $timestamps = false;

	protected $casts = [
		'archive' => 'int'
	];

	protected $fillable = [
		'poseur',
		'archive'
	];

	public function pose_detail()
	{
		return $this->hasOne(PoseDetail::class, 'id_poseur');
	}
}
