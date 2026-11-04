<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property int $commercial_id
 * @property int $relance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Client $client
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relance whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relance whereCommercialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relance whereRelance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relance whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Relance extends Model
{
    protected $table = 'relances';

    protected $fillable = [
        'client_id',
        'commercial_id',
        'relance',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }
}
