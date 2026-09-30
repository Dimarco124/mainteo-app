<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'utilisateurs';
    public $timestamps = false;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'mot_de_passe',
        'type_utilisateur',
        'statut',
        'telephone',
        'specialite',
        'site',
        'client_id',
        'base',
        'superviseur_id',
        'created_by',
        'equipe_id',
        'base_id',
        'site_id', // Pour les Demandeurs
        'zone_id', // Pour les Demandeurs (zone spécifique du site)
        'dark_mode', // Préférence thème sombre
    ];

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    public function getAuthPasswordName()
    {
        return 'mot_de_passe';
    }

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }


    // Role helper methods
    public function isAdmin(): bool
    {
        return $this->type_utilisateur === 'admin';
    }

    public function isSuperviseur(): bool
    {
        return $this->type_utilisateur === 'superviseur' || $this->type_utilisateur === 'superviseur_client';
    }

    public function isSuperviseurClient(): bool
    {
        return $this->type_utilisateur === 'superviseur_client' || $this->type_utilisateur === 'superviseur';
    }

    public function isSuperviseurSoutarah(): bool
    {
        return $this->type_utilisateur === 'superviseur_soutarah';
    }

    public function isDemandeur(): bool
    {
        return $this->type_utilisateur === 'demandeur';
    }

    public function isTechnicien(): bool
    {
        return in_array($this->type_utilisateur, ['technicien', 'chef technicien']);
    }

    public function isChefTechnicien(): bool
    {
        if ($this->type_utilisateur === 'chef technicien') {
            return true;
        }
        // Vérifier si l'utilisateur est chef via la colonne chef_equipe (SOURCE DE VÉRITÉ)
        return \App\Models\Equipe::where('chef_equipe', $this->id)->exists();
    }

    public function isTechnicienSimple(): bool
    {
        return $this->type_utilisateur === 'technicien' && !$this->isChefTechnicien();
    }

    public function getRoleEquipeLabelAttribute(): string
    {
        $roles = [];
        
        // Vérifier si l'utilisateur est chef via la colonne chef_equipe (SOURCE DE VÉRITÉ)
        // On vérifie toutes les équipes où chef_equipe = cet utilisateur
        $equipesChef = \App\Models\Equipe::where('chef_equipe', $this->id)->get();
        foreach ($equipesChef as $eq) {
            $roles[] = "Chef de {$eq->nom_equipe}";
        }
        
        // Sinon, afficher simplement "Membre de..." pour les équipes où il est membre
        if (empty($roles)) {
            foreach ($this->equipes as $eq) {
                $roles[] = "Membre de {$eq->nom_equipe}";
            }
            
            // Ou via equipe_id (ancien système)
            if (empty($roles) && $this->equipe) {
                $roles[] = "Membre de {$this->equipe->nom_equipe}";
            }
        }
        
        return implode(', ', array_unique($roles));
    }

    public function getNomCompletAttribute(): string
    {
        return trim(($this->nom ?? '') . ' ' . ($this->prenom ?? ''));
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function baseSite()
    {
        return $this->belongsTo(BaseSite::class, 'base_id');
    }

    /**
     * ANCIENNE RELATION (équipe unique via equipe_id)
     * Gardée pour compatibilité temporaire
     */
    public function equipe()
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }
    
    /**
     * NOUVELLE RELATION : Toutes les équipes de cet utilisateur (many-to-many)
     */
    public function equipes()
    {
        return $this->belongsToMany(Equipe::class, 'equipe_user', 'user_id', 'equipe_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }
    
    /**
     * Équipes où cet utilisateur est chef
     */
    public function equipesEnTantQueChef()
    {
        return $this->belongsToMany(Equipe::class, 'equipe_user', 'user_id', 'equipe_id')
                    ->wherePivot('role', 'chef')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function depannages()
    {
        return $this->hasMany(Depannage::class, 'technicien_id');
    }

    /**
     * Site assigné (pour les Demandeurs) - ANCIENNE RELATION (site unique)
     * Gardée pour compatibilité temporaire
     */
    public function siteAssigne()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Sites assignés (pour les Demandeurs) - NOUVELLE RELATION (many-to-many)
     */
    public function sitesAssignes()
    {
        return $this->belongsToMany(Site::class, 'demandeur_site', 'demandeur_id', 'site_id')
                    ->withTimestamps();
    }

    /**
     * Assignment Superviseur Soutarah
     */
    public function assignment()
    {
        return $this->hasOne(Assignment::class, 'superviseur_soutarah_id');
    }

    /**
     * Demandes créées par cet utilisateur
     */
    public function demandesCreees()
    {
        return $this->hasMany(Demande::class, 'created_by_user_id');
    }

    /**
     * Obtenir le périmètre de responsabilité pour un superviseur client
     * Retourne ['type' => 'base'|'client', 'id' => X] ou null
     */
    public function getScopeAttribute()
    {
        if (!$this->isSuperviseurClient()) {
            return null;
        }

        if ($this->base_id) {
            return ['type' => 'base', 'id' => $this->base_id];
        } elseif ($this->client_id) {
            return ['type' => 'client', 'id' => $this->client_id];
        }

        return null;
    }

    /**
     * Zone / Sous-site rattaché au demandeur
     */
    public function zone()
    {
        return $this->belongsTo(ZoneSite::class, 'zone_id');
    }
}
