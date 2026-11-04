<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Comment
 *
 * @property int $id
 * @property int $client_id
 * @property int $user_id
 * @property Carbon $date
 * @property string $commentaire
 * @property Client $client
 * @property User $user
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereCommentaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereUserId($value)
 * @mixin \Eloquent
 */
class Comment extends Model
{
	protected $table = 'comments';
	public $timestamps = false;

	protected $casts = [
		'client_id' => 'int',
		'user_id' => 'int',
		'date' => 'datetime'
	];

	protected $fillable = [
		'client_id',
		'user_id',
		'date',
		'commentaire'
	];

	public function client()
	{
		return $this->belongsTo(Client::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
