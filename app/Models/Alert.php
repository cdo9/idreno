<?php

namespace App\Models;

use App\Enums\Type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
