<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Cache
 *
 * @property string $key
 * @property string $value
 * @property int $expiration
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache whereExpiration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cache whereValue($value)
 * @mixin \Eloquent
 */
class Cache extends Model
{
	protected $table = 'cache';
	protected $primaryKey = 'key';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'expiration' => 'int'
	];

	protected $fillable = [
		'value',
		'expiration'
	];
}
