<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relance extends Model
{
    protected $table = 'relances';
    protected $fillable = [
        'client_id',
        'commercial_id',
        'relance',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'commercial_id');
    }
} 