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
 * @property Client $client
 * @package App\Models
 * @property int|null $date_realisation_du_projet
 * @property string|null $pourquoi
 * @property int|null $partage_avec
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereCompteRendu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereCouplePresent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereDateAjout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereDateConfirmation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereDateRealisation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereDateRealisationDuProjet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereEstimation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereHeure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereIdClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet wherePartageAvec($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet wherePourquoi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet wherePriorite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereProjet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereProjet2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereProjet3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereQuantite1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereQuantite2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereQuantite3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereRaison($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereRdvAnnule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereRdvConfirme($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereSecteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereTempsDisponible($value)
 * @mixin \Eloquent
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
