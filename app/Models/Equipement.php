<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipement extends Model
{
    use HasFactory;

    protected $table = 'equipements';
    public $timestamps = false;

    protected $fillable = [
        'equipement_code',
        'equipement_numero',
        'equipement_nom',
        'marque',
        'type',
        'emplacement',
        'reference',
        'puissance',
        'refrigerant',
        'mois_installation',
        'annee_installation',
        'type_unite',
        'num_sur_site',
        'fiche_technique',
        'photo',
        'client_id',
        'base_id',
        'site_id',
        'zone_id',
        'date_acquisition',
        'etat',
        'observations',
    ];

    public function zone()
    {
        return $this->belongsTo(ZoneSite::class, 'zone_id');
    }

    public function fichesFroid()
    {
        return $this->hasMany(FicheFroid::class, 'equipement_id');
    }

    public function depannages()
    {
        return $this->hasMany(Depannage::class, 'equipement_id');
    }

    public function baseSite()
    {
        return $this->belongsTo(BaseSite::class, 'base_id');
    }

    /**
     * Site (vraie relation vers la table sites)
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
