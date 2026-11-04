<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pose
 *
 * @property int $id
 * @property int $id_vente
 * @property int $id_client
 * @property string $produit
 * @property string $produit2
 * @property string $produit3
 * @property Carbon|null $date_debut
 * @property Carbon|null $date_fin
 * @property int|null $visite_chantier
 * @property int|null $avis_client
 * @property int $indicateur
 * @property int|null $id_commercial_visite
 * @property int|null $avis_facebook
 * @property int $secteur
 *
 * @property Client $client
 * @property User|null $user
 * @property Vente $vente
 * @property PoseDetail|null $pose_detail
 *
 * @package App\Models
 */
class Pose extends Model
{
	protected $table = 'poses';
	public $timestamps = false;

	protected $casts = [
		'id_vente' => 'int',
		'id_client' => 'int',
		'date_debut' => 'datetime',
		'date_fin' => 'datetime',
		'visite_chantier' => 'int',
		'avis_client' => 'int',
		'indicateur' => 'int',
		'id_commercial_visite' => 'int',
		'avis_facebook' => 'int',
		'secteur' => 'int'
	];

	protected $fillable = [
		'id_vente',
		'id_client',
		'produit',
		'produit2',
		'produit3',
		'date_debut',
		'date_fin',
		'visite_chantier',
		'avis_client',
		'indicateur',
		'id_commercial_visite',
		'avis_facebook',
		'secteur'
	];

	public function client()
	{
		return $this->belongsTo(Client::class, 'id_client');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'id_commercial_visite');
	}

	public function vente()
	{
		return $this->belongsTo(Vente::class, 'id_vente');
	}

	public function secteur()
	{
		return $this->belongsTo(Secteur::class, 'secteur');
	}

	public function pose_detail()
	{
		return $this->hasOne(PoseDetail::class, 'id_pose');
	}
    public function poseurs()
    {
        return $this->belongsToMany(
            Poseur::class,
            'pose_details',
            'id_pose',
            'id_poseur'
        );
    }
}
