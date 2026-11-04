<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property int $commercial_id
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Client $client
 * @property-read User $commercial
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientHistory whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientHistory whereCommercialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientHistory whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ClientHistory extends Model
{
    protected $table = 'clients_history';

    protected $fillable = [
        'client_id',
        'commercial_id',
        'status',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function commercial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }
}
