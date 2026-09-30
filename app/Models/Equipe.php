<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipe extends Model
{
    use HasFactory;

    protected $table = 'equipes';
    public $timestamps = false;

    protected $fillable = [
        'nom_equipe',
        'chef_equipe', // Garde pour compatibilité (chef principal)
    ];

    /**
     * Chef principal de l'équipe (relation simple pour compatibilité)
     */
    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_equipe');
    }

    /**
     * NOUVELLE RELATION : Tous les membres (many-to-many via table pivot)
     */
    public function membres()
    {
        return $this->belongsToMany(User::class, 'equipe_user', 'equipe_id', 'user_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }
    
    /**
     * Tous les chefs de cette équipe (relation many-to-many)
     */
    public function chefs()
    {
        return $this->belongsToMany(User::class, 'equipe_user', 'equipe_id', 'user_id')
                    ->wherePivot('role', 'chef')
                    ->withPivot('role')
                    ->withTimestamps();
    }
    
    /**
     * Membres simples (sans les chefs)
     */
    public function membresSimples()
    {
        return $this->belongsToMany(User::class, 'equipe_user', 'equipe_id', 'user_id')
                    ->wherePivot('role', 'membre')
                    ->withPivot('role')
                    ->withTimestamps();
    }
    
    /**
     * Maintenances assignées à cette équipe (relation simple - ancienne)
     */
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'equipe_id');
    }

    /**
     * NOUVELLE: Toutes les maintenances où cette équipe est affectée (many-to-many)
     */
    public function maintenancesAffectees()
    {
        return $this->belongsToMany(Maintenance::class, 'equipe_maintenance')
                    ->withPivot('role', 'date_affectation')
                    ->withTimestamps()
                    ->orderBy('date_debut_prevue', 'desc');
    }

    /**
     * ANCIENNE RELATION (à supprimer après migration complète)
     * Gardée temporairement pour compatibilité
     */
    public function membresOld()
    {
        return $this->hasMany(User::class, 'equipe_id');
    }
}
