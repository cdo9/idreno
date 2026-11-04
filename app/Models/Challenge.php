<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Challenge
 *
 * @property int $id
 * @property string|null $challenge
 * @property Carbon $date
 *
 * @package App\Models
 */
class Challenge extends Model
{
	protected $table = 'challenges';
	public $timestamps = false;

	protected $casts = [
		'date' => 'datetime'
	];

	protected $fillable = [
		'challenge',
		'date',
		'secteur'
	];
}
