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
 * 
 * @property Collection|Client[] $clients
 * @property Collection|Vente[] $ventes
 *
 * @package App\Models
 */
class Source extends Model
{
	protected $table = 'sources';
	public $timestamps = false;

	protected $fillable = [
		'nom'
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
