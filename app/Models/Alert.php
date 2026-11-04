<?php

namespace App\Models;

use App\Enums\Type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $message
 * @property Type $type
 * @property int $id_commercial
 * @property array<array-key, mixed>|null $details
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $idCommercial
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereIdCommercial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Alert extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'message',
        'type',
        'id_commercial',
        'details',
    ];

    public function idCommercial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_commercial');
    }

    protected function casts(): array
    {
        return [
            'type' => Type::class,
            'details' => 'array',
        ];
    }

    public function setTypeAttribute($value)
    {
        // Convert empty strings to null for proper enum handling
        $this->attributes['type'] = empty($value) ? null : $value;
    }

    public function getTypeAttribute($value)
    {
        // Convert empty strings to null when reading from database
        if (empty($value)) {
            return null;
        }

        // Let Laravel handle the enum casting for valid values
        return Type::tryFrom($value);
    }
}
