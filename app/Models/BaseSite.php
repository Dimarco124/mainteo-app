<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseSite extends Model
{
    use HasFactory;

    protected $table = 'bases';
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'nom_base',
        'code_base',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function utilisateurs()
    {
        return $this->hasMany(User::class, 'base_id');
    }

    /**
     * Équipements de cette base (via les sites)
     * Hiérarchie: BASE → SITE → ÉQUIPEMENT
     */
    public function equipements()
    {
        return $this->hasManyThrough(
            Equipement::class,  // Modèle final
            Site::class,        // Modèle intermédiaire
            'base_id',          // Clé étrangère sur sites (sites.base_id)
            'site_id',          // Clé étrangère sur equipements (equipements.site_id)
            'id',               // Clé locale sur bases (bases.id)
            'id'                // Clé locale sur sites (sites.id)
        );
    }

    /**
     * Sites de cette base
     */
    public function sites()
    {
        return $this->hasMany(Site::class, 'base_id');
    }

    /**
     * Demandes de cette base
     */
    public function demandes()
    {
        return $this->hasMany(Demande::class, 'base_id');
    }

    /**
     * Assignment de superviseur Soutarah à cette base
     */
    public function assignment()
    {
        return $this->hasOne(Assignment::class, 'base_id');
    }
}
