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
 * @property int $secteur
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereChallenge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereSecteur($value)
 *
 * @mixin \Eloquent
 */
class Challenge extends Model
{
    protected $table = 'challenges';

    public $timestamps = false;

    protected $casts = [
        'date' => 'datetime',
    ];

    protected $fillable = [
        'challenge',
        'date',
        'secteur',
    ];
}
