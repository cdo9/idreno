<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property Client $client
 * @property User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereGpsLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereGpsLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereIdClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereIdCommercial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereSecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereUserLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tracking whereUserLng($value)
 *
 * @mixin \Eloquent
 */
class Tracking extends Model
{
    protected $table = 'tracking';

    public $timestamps = false;

    protected $casts = [
        'id_client' => 'int',
        'id_commercial' => 'int',
        'datetime' => 'datetime',
        'secteur' => 'int',
    ];

    protected $fillable = [
        'id_client',
        'id_commercial',
        'datetime',
        'gps_lat',
        'gps_lng',
        'user_lat',
        'user_lng',
        'secteur',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_commercial');
    }

    public function secteur(): BelongsTo
    {
        return $this->belongsTo(Secteur::class, 'secteur');
    }
}
