<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CacheLock
 *
 * @property string $key
 * @property string $owner
 * @property int $expiration
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheLock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheLock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheLock query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheLock whereExpiration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheLock whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CacheLock whereOwner($value)
 * @mixin \Eloquent
 */
class CacheLock extends Model
{
	protected $table = 'cache_locks';
	protected $primaryKey = 'key';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'expiration' => 'int'
	];

	protected $fillable = [
		'owner',
		'expiration'
	];
}
