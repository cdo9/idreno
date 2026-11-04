<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Groupe
 *
 * @property int $id
 * @property int $id_responsable
 * @property string $nom
 * @property int|null $tri
 * @property int $secteur
 *
 * @property User $user
 *
 * @package App\Models
 */
class Groupe extends Model
{
	protected $table = 'groupes';
	public $timestamps = false;

	protected $casts = [
		'id_responsable' => 'int',
		'tri' => 'int',
		'secteur' => 'int'
	];

	protected $fillable = [
		'id_responsable',
		'nom',
		'tri',
		'secteur'
	];

	/*public function user()
	{
		return $this->belongsTo(User::class, 'id_responsable');
	}*/

	public function secteurRel()
	{
		return $this->belongsTo(Secteur::class, 'secteur');
	}
	   public function responsable()
    {
        return $this->belongsTo(User::class, 'id_responsable');
    }
}
