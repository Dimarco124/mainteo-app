<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depannage extends Model
{
    use HasFactory;

    protected $table = 'depannages';
    public $timestamps = false;

    protected $fillable = [
        'demande_id',
        'type_intervention',
        'created_by_role',
        'client_id',
        'client_nom',
        'site_code',
        'base_code',
        'demandeur_id',
        'demandeur_nom',
        'superviseur_base',
        'equipement_id',
        'equipement_reference',
        'description_panne',
        'photo_panne',
        'fichier_joint',
        'urgence',
        'etat_demande',
        'date_demande',
        'statut',
        'confirmation_reception',
        'date_confirmation_reception',
        'statut_validation_superviseur',
        'message_superviseur',
        'date_reponse_superviseur',
        'statut_validation_admin',
        'message_admin',
        'date_reponse_admin',
        'technicien_id',
        'equipe_id',
        'date_prevue',
        'date_debut_prevue',
        'date_fin_prevue',
        'date_realisation',
        'rapport',
        'ri_soutarah',
        'approuve_client',
        'statut_validation',
        'date_creation',
        'photo_carnet_rapport',
        'photo_equipement_apres',
        'date_rapport_technicien',
        'rapport_soumis_par_user_id',
        'statut_rapport_technicien',
        'date_transmission_rapport_client',
        'rapport_transmis_par_user_id',
        'demande_id', // Lien vers la demande d'origine
        'statut_operation', // Nouveau workflow
        'rapport_technique',
        'photos_rapport',
        'date_soumission_rapport',
        // Validation finale par Superviseur Client
        'statut_validation_finale_client',
        'commentaire_validation_client',
        'date_validation_finale_client',
        'validated_by_client_user_id',
        // Rejet du rapport technicien par Soutarah/Admin
        'raison_rejet_rapport',
        'date_rejet_rapport',
        'rapport_rejete_par_user_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    public function equipe()
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class, 'equipement_id');
    }

    /**
     * Site via l'équipement
     */
    public function site()
    {
        return $this->hasOneThrough(
            Site::class,
            Equipement::class,
            'id', // Foreign key sur equipements table
            'id', // Foreign key sur sites table
            'equipement_id', // Local key sur depannages table
            'site_id' // Local key sur equipements table
        );
    }

    /**
     * Demande d'intervention liée (nouveau workflow)
     */
    public function demande()
    {
        return $this->belongsTo(Demande::class, 'demande_id');
    }

    protected $casts = [
        'photos_rapport' => 'array',
        'date_soumission_rapport' => 'datetime',
        'date_demande' => 'datetime',
        'date_prevue' => 'datetime',
        'date_realisation' => 'datetime',
        'date_creation' => 'datetime',
        'date_confirmation_reception' => 'datetime',
        'date_reponse_superviseur' => 'datetime',
        'date_reponse_admin' => 'datetime',
    ];

    /**
     * Utilisateur qui a soumis le rapport technicien
     */
    public function rapportSoumisPar()
    {
        return $this->belongsTo(User::class, 'rapport_soumis_par_user_id');
    }

    /**
     * Utilisateur qui a transmis le rapport au client
     */
    public function rapportTransmisPar()
    {
        return $this->belongsTo(User::class, 'rapport_transmis_par_user_id');
    }

    /**
     * Superviseur Client qui a validé et clôturé l'intervention
     */
    public function validatedByClient()
    {
        return $this->belongsTo(User::class, 'validated_by_client_user_id');
    }

    /**
     * Planifications liées à cette intervention
     */
    public function planifications()
    {
        return $this->hasMany(Planification::class, 'depannage_id');
    }
}
