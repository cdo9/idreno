<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Source
 *
 * @property int $id
 * @property string $nom
 * @property Collection|Client[] $clients
 * @property Collection|Vente[] $ventes
 * @property string|null $groupe
 * @property-read int|null $clients_count
 * @property-read int|null $ventes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source whereGroupe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Source whereNom($value)
 *
 * @mixin \Eloquent
 */
class Source extends Model
{
    protected $table = 'sources';

    public $timestamps = false;

    protected $fillable = [
        'nom',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class, 'id_source');
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class, 'id_source');
    }
}
