<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';
    public $timestamps = false;

    protected $fillable = [
        'code',
        'nom',
        'telephone',
        'mail',
        'adresse',
        'ville',
        'pays',
        'latitude',
        'longitude',
        'date_creation',
        'email',
        'mot_de_passe',
    ];

    /**
     * Accessor pour compatibilité avec client_nom
     * Permet d'utiliser $client->client_nom au lieu de $client->nom
     */
    public function getClientNomAttribute()
    {
        return $this->attributes['nom'] ?? null;
    }

    public function bases()
    {
        return $this->hasMany(BaseSite::class, 'client_id');
    }

    public function utilisateurs()
    {
        return $this->hasMany(User::class, 'client_id');
    }

    public function installations()
    {
        return $this->hasMany(Installation::class, 'client_id');
    }

    public function depannages()
    {
        return $this->hasMany(Depannage::class, 'client_id');
    }

    public function equipements()
    {
        return $this->hasMany(Equipement::class, 'client_id');
    }

    /**
     * Assignment de superviseur Soutarah à ce client (petite entreprise sans bases)
     */
    public function assignment()
    {
        return $this->hasOne(Assignment::class, 'client_id');
    }
}
