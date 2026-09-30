<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $table = 'sites';
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'base_id',
        'nom_site',
        'code_site',
        'adresse',
        'ville',
        'telephone',
        'observations',
    ];

    /**
     * Une site appartient à une base
     * Note: Relation nommée 'baseSite' pour éviter conflit avec colonne 'base'
     */
    public function baseSite()
    {
        return $this->belongsTo(BaseSite::class, 'base_id');
    }

    /**
     * Un site peut appartenir directement à un client (sans base)
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Accessor pour compatibilité : $site->base renvoie la relation
     */
    public function getBaseAttribute($value)
    {
        // Si on accède à $site->base, retourner la relation au lieu de la colonne
        if (!$this->relationLoaded('baseSite')) {
            $this->load('baseSite');
        }
        return $this->getRelation('baseSite');
    }

    /**
     * Un site a plusieurs équipements
     */
    public function equipements()
    {
        return $this->hasMany(Equipement::class, 'site_id');
    }

    /**
     * Un site a plusieurs demandeurs
     */
    public function demandeurs()
    {
        return $this->hasMany(User::class, 'site_id')->where('type_utilisateur', 'demandeur');
    }

    /**
     * Un site a plusieurs demandes
     */
    public function demandes()
    {
        return $this->hasMany(Demande::class, 'site_id');
    }

    /**
     * Un site a plusieurs zones / sous-sites
     */
    public function zones()
    {
        return $this->hasMany(ZoneSite::class, 'site_id');
    }
}
