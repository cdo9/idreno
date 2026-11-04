<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Database\Factories\SecteurFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Secteur
 *
 * @property int $id
 * @property string|null $nom
 * @property string $couleur
 * @property Collection|Client[] $clients
 * @property Collection|Evaluation[] $evaluations
 * @property Collection|Groupe[] $groupes
 * @property Collection|ObjectifAnnuelCommercial[] $objectif_annuel_commercials
 * @property Collection|Objectif[] $objectifs
 * @property Collection|ParcAuto[] $parc_autos
 * @property Collection|Pose[] $poses
 * @property Collection|Projet[] $projets
 * @property Collection|ProspectionCommune[] $prospection_communes
 * @property Collection|Prospection[] $prospections
 * @property Collection|RendezVous[] $rendez_vous
 * @property Collection|SecteurCommercial[] $secteur_commercials
 * @property Collection|Tracking[] $trackings
 * @property Collection|Vente[] $ventes
 * @property int $activated
 * @property string|null $coords
 * @property-read int|null $clients_count
 * @property-read int|null $evaluations_count
 * @property-read int|null $groupes_count
 * @property-read int|null $objectif_annuel_commercials_count
 * @property-read int|null $objectifs_count
 * @property-read int|null $parc_autos_count
 * @property-read int|null $poses_count
 * @property-read int|null $projets_count
 * @property-read int|null $prospection_communes_count
 * @property-read int|null $prospections_count
 * @property-read int|null $rendez_vous_count
 * @property-read int|null $secteur_commercials_count
 * @property-read int|null $trackings_count
 * @property-read int|null $ventes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secteur newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secteur newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secteur query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secteur whereActivated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secteur whereCoords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secteur whereCouleur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secteur whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Secteur whereNom($value)
 *
 * @mixin \Eloquent
 */
class Secteur extends Model
{
    /** @use HasFactory<SecteurFactory> */
    use HasFactory;

    protected $table = 'secteurs';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'couleur',
        'coords',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'secteur');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'secteur');
    }

    public function groupes(): HasMany
    {
        return $this->hasMany(Groupe::class, 'secteur');
    }

    public function objectif_annuel_commercials(): HasMany
    {
        return $this->hasMany(ObjectifAnnuelCommercial::class, 'secteur');
    }

    public function objectifs(): HasMany
    {
        return $this->hasMany(Objectif::class, 'secteur');
    }

    public function parc_autos(): HasMany
    {
        return $this->hasMany(ParcAuto::class, 'secteur');
    }

    public function poses(): HasMany
    {
        return $this->hasMany(Pose::class, 'secteur');
    }

    public function projets(): HasMany
    {
        return $this->hasMany(Projet::class, 'secteur');
    }

    public function prospection_communes(): HasMany
    {
        return $this->hasMany(ProspectionCommune::class, 'secteur');
    }

    public function prospections(): HasMany
    {
        return $this->hasMany(Prospection::class, 'secteur');
    }

    public function rendez_vous(): HasMany
    {
        return $this->hasMany(RendezVous::class, 'secteur');
    }

    public function secteur_commercials(): HasMany
    {
        return $this->hasMany(SecteurCommercial::class, 'id_secteur');
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(Tracking::class, 'secteur');
    }

    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class, 'secteur');
    }

    // Récupérer le polygone sous forme de tableau de points [lat, lng]
    public function getPolygonCoords()
    {
        if (empty($this->coords)) {
            return [];
        }

        $coords = $this->coords;

        // Cas : WKT "POLYGON((lng lat, lng lat,...))" ou "POLYGON((lat lng, lat lng,...))"
        // Ici on suppose que tu stockes : POLYGON((lng lat, lng lat))
        if (str_starts_with($coords, 'POLYGON') || str_starts_with($coords, 'POLYL')) {
            // On enlève POLYGON(( et ) et on découpe par virgules
            $points = trim($coords, 'POLYGON() ');
            $pairs = preg_split('/,\s*/', $points);

            return collect($pairs)->map(function ($pair) {
                $xy = preg_split('/\s+/', trim($pair));
                if (count($xy) >= 2) {
                    // $xy[0] = lng, $xy[1] = lat
                    return [
                        'lat' => (float) $xy[1],
                        'lng' => (float) $xy[0],
                    ];
                }

                return null;
            })->filter()->values()->all();
        }

        // Si tu stockes du JSON ou autre format, adapte ici
        return [];
    }

    // Vérifier si un point (lat, lng) est dans le polygone du secteur
    public function containsPoint($lat, $lng)
    {
        $polygon = $this->getPolygonCoords();
        if (empty($polygon)) {
            return false;
        }

        $point = [$lat, $lng];

        $inside = false;
        $j = count($polygon) - 1;

        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i]['lng'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];

            $intersect = (($yi > $lat) != ($yj > $lat)) &&
                ($lng < ($xj - $xi) * ($lat - $yi) / max($yj - $yi, 1e-9) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }
            $j = $i;
        }

        return $inside;
    }

    // Méthode statique : trouver le secteur contenant un point (lat, lng)
    public static function findByCoords($lat, $lng)
    {
        foreach (self::all() as $secteur) {
            if ($secteur->containsPoint($lat, $lng)) {
                return $secteur;
            }
        }

        return null;
    }
}
