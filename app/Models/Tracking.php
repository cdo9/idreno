<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tracking
 * 
 * @property int $id
 * @property int $id_client
 * @property int $id_commercial
 * @property Carbon $datetime
 * @property string $gps_lat
 * @property string $gps_lng
 * @property string|null $user_lat
 * @property string|null $user_lng
 * @property int $secteur
 * 
 * @property Client $client
 * @property User $user
 *
 * @package App\Models
 */
class Tracking extends Model
{
	protected $table = 'tracking';
	public $timestamps = false;

	protected $casts = [
		'id_client' => 'int',
		'id_commercial' => 'int',
		'datetime' => 'datetime',
		'secteur' => 'int'
	];

	protected $fillable = [
		'id_client',
		'id_commercial',
		'datetime',
		'gps_lat',
		'gps_lng',
		'user_lat',
		'user_lng',
		'secteur'
	];

	public function client()
	{
		return $this->belongsTo(Client::class, 'id_client');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'id_commercial');
	}

	public function secteur()
	{
		return $this->belongsTo(Secteur::class, 'secteur');
	}
}
