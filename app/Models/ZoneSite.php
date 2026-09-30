<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneSite extends Model
{
    use HasFactory;

    protected $table = 'zones_sites';

    protected $fillable = [
        'site_id',
        'base_id',
        'client_id',
        'nom_zone',
        'code_zone',
        'observations',
        'est_vip', // 🚨 Marque les zones de hauts cadres
    ];

    protected $casts = [
        'est_vip' => 'boolean',
    ];

    /**
     * Une zone appartient à un site
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Une zone peut appartenir à une base (optionnel)
     */
    public function baseSite()
    {
        return $this->belongsTo(BaseSite::class, 'base_id');
    }

    /**
     * Une zone peut appartenir à un client (optionnel)
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Équipements de cette zone
     */
    public function equipements()
    {
        return $this->hasMany(Equipement::class, 'zone_id');
    }

    /**
     * Demandeurs rattachés à cette zone
     */
    public function demandeurs()
    {
        return $this->hasMany(User::class, 'zone_id')->where('type_utilisateur', 'demandeur');
    }
}
