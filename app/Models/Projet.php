<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Projet
 *
 * @property int $id
 * @property int $id_client
 * @property int $priorite
 * @property string $projet
 * @property string $projet2
 * @property string $projet3
 * @property string $source
 * @property int $estimation
 * @property Carbon $date_ajout
 * @property Carbon|null $date
 * @property int $rdv_confirme
 * @property Carbon|null $date_confirmation
 * @property int $rdv_annule
 * @property string $compte_rendu
 * @property Carbon $date_realisation
 * @property string $raison
 * @property string $heure
 * @property string $couple_present
 * @property string $temps_disponible
 * @property string $quantite1
 * @property string $quantite2
 * @property string $quantite3
 * @property int $secteur
 *
 * @property Client $client
 *
 * @package App\Models
 */
class Projet extends Model
{
    /**
     * @var mixed|string
     */
    protected $table = 'projets';
	public $timestamps = false;

	protected $casts = [
		'id_client' => 'int',
		'priorite' => 'int',
		'estimation' => 'int',
		'date_ajout' => 'datetime',
		'date' => 'datetime',
		'rdv_confirme' => 'int',
		'date_confirmation' => 'datetime',
		'rdv_annule' => 'int',
		'date_realisation' => 'datetime',
		'secteur' => 'int'
	];

	protected $fillable = [
		'id_client',
		'priorite',
		'projet',
		'projet2',
		'projet3',
		'source',
		'estimation',
		'date_ajout',
		'date',
		'rdv_confirme',
		'date_confirmation',
		'rdv_annule',
		'compte_rendu',
		'date_realisation',
		'date_realisation_du_projet',
		'raison',
		'heure',
		'couple_present',
		'temps_disponible',
		'quantite1',
		'quantite2',
		'quantite3',
		'secteur',
		'pourquoi',
		'partage_avec'
	];

	public function client()
	{
		return $this->belongsTo(Client::class, 'id_client');
	}

	public function secteur()
	{
		return $this->belongsTo(Secteur::class, 'secteur');
	}
}
