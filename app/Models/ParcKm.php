<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ParcKm
 *
 * @property int $id
 * @property int $auto_id
 * @property Carbon $date
 * @property int $km
 * @property ParcAuto $parc_auto
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcKm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcKm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcKm query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcKm whereAutoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcKm whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcKm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ParcKm whereKm($value)
 *
 * @mixin \Eloquent
 */
class ParcKm extends Model
{
    protected $table = 'parc_km';

    public $timestamps = false;

    protected $casts = [
        'auto_id' => 'int',
        'date' => 'datetime',
        'km' => 'int',
    ];

    protected $fillable = [
        'auto_id',
        'date',
        'km',
    ];

    public function parc_auto()
    {
        return $this->belongsTo(ParcAuto::class, 'auto_id');
    }
}
