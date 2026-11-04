<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PoseDetail
 * 
 * @property int $id_pose
 * @property int $id_poseur
 * 
 * @property Pose $pose
 * @property Poseur $poseur
 *
 * @package App\Models
 */
class PoseDetail extends Model
{
	protected $table = 'pose_details';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id_pose' => 'int',
		'id_poseur' => 'int'
	];

	protected $fillable = [
		'id_pose',
		'id_poseur'
	];

	public function pose()
	{
		return $this->belongsTo(Pose::class, 'id_pose');
	}

	public function poseur()
	{
		return $this->belongsTo(Poseur::class, 'id_poseur');
	}
}
