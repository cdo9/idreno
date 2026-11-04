<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PoseDetail
 *
 * @property int $id_pose
 * @property int $id_poseur
 * @property Pose $pose
 * @property Poseur $poseur
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoseDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoseDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoseDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoseDetail whereIdPose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PoseDetail whereIdPoseur($value)
 *
 * @mixin \Eloquent
 */
class PoseDetail extends Model
{
    protected $table = 'pose_details';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'id_pose' => 'int',
        'id_poseur' => 'int',
    ];

    protected $fillable = [
        'id_pose',
        'id_poseur',
    ];

    public function pose(): BelongsTo
    {
        return $this->belongsTo(Pose::class, 'id_pose');
    }

    public function poseur(): BelongsTo
    {
        return $this->belongsTo(Poseur::class, 'id_poseur');
    }
}
