<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SecteurCommercial
 *
 * @property int $id_secteur
 * @property int $id_commercial
 * @property User $user
 * @property Secteur $secteur
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecteurCommercial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecteurCommercial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecteurCommercial query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecteurCommercial whereIdCommercial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SecteurCommercial whereIdSecteur($value)
 * @mixin \Eloquent
 */
class SecteurCommercial extends Model
{
	protected $table = 'secteur_commercials';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id_secteur' => 'int',
		'id_commercial' => 'int'
	];

	protected $fillable = [
		'id_secteur',
		'id_commercial'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'id_commercial');
	}

	public function secteur()
	{
		return $this->belongsTo(Secteur::class, 'id_secteur');
	}
}
