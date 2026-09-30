<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    use HasFactory;

    protected $table = 'demandes';

    protected $fillable = [
        'numero_demande',
        'numero_reference_externe', // Numéro de référence externe (demande manuelle hors app)
        'created_by_user_id',
        'created_by_role',
        'client_id',
        'base_id',
        'site_id',
        'zone_id',
        'equipement_id',
        'type_intervention',
        'description',
        'niveau_urgence',
        'date_debut_souhaitee',
        'photo_panne',
        'statut',
        'est_vip', // 🚨 Marque si la demande concerne un équipement VIP (haut cadre)
        'date_validation_client',
        'validated_by_client_user_id',
        'message_validation_client',
        'date_validation_soutarah',
        'validated_by_soutarah_user_id',
        'message_validation_soutarah',
        'appel_notes',
        'appel_effectue_par_user_id',
        'appel_date',
        'date_confirmee_avec_demandeur',
        'date_confirmation_demandeur',
        'commentaire_demandeur',
        'date_validation_finale',
        'validated_finale_by_user_id',
        'message_validation_finale',
        'technical_operation_id',
        'techniciens_sont_passes',
        'resultat_intervention',
        'commentaire_resultat',
        'date_confirmation_passage',
        'statut_validation_finale_client',
        'message_non_conformite',
        'date_cloture_finale',
        'cloture_par_user_id',
        // Champs temporaires pour équipements en attente de complétion
        'temp_equipement_nom',
        'temp_equipement_type',
        'temp_equipement_emplacement',
        'equipement_needs_completion',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date_debut_souhaitee' => 'date',
        'date_validation_client' => 'datetime',
        'date_validation_soutarah' => 'datetime',
        'appel_date' => 'datetime',
        'date_confirmee_avec_demandeur' => 'date',
        'date_confirmation_demandeur' => 'datetime',
        'date_validation_finale' => 'datetime',
        'est_vip' => 'boolean', // 🚨 Cast en boolean
    ];

    /**
     * Générer automatiquement le numéro de demande
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($demande) {
            if (!$demande->numero_demande) {
                $year = date('Y');
                $lastDemande = self::whereYear('created_at', $year)
                    ->orderBy('id', 'desc')
                    ->first();

                $number = $lastDemande ? intval(substr($lastDemande->numero_demande, -4)) + 1 : 1;
                $demande->numero_demande = 'DEM-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relations
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function base()
    {
        return $this->belongsTo(BaseSite::class, 'base_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function zone()
    {
        return $this->belongsTo(ZoneSite::class, 'zone_id');
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class, 'equipement_id');
    }

    public function validatedByClient()
    {
        return $this->belongsTo(User::class, 'validated_by_client_user_id');
    }

    public function validatedBySoutarah()
    {
        return $this->belongsTo(User::class, 'validated_by_soutarah_user_id');
    }

    public function validatedFinaleBy()
    {
        return $this->belongsTo(User::class, 'validated_finale_by_user_id');
    }

    public function appelEffectuePar()
    {
        return $this->belongsTo(User::class, 'appel_effectue_par_user_id');
    }

    public function technicalOperation()
    {
        return $this->belongsTo(Depannage::class, 'technical_operation_id');
    }

    /**
     * Planifications liées à cette demande
     */
    public function planifications()
    {
        return $this->hasMany(Planification::class, 'demande_id');
    }

    /**
     * Interventions (dépannages) liées à cette demande
     */
    public function interventions()
    {
        return $this->hasMany(Depannage::class, 'demande_id');
    }

    // Helper methods pour les statuts
    public function isPendingClientValidation()
    {
        return $this->statut === 'pending_client_validation';
    }

    public function isValidatedByClient()
    {
        return $this->statut === 'validated_by_client';
    }

    public function isRejectedByClient()
    {
        return $this->statut === 'rejected_by_client';
    }

    public function needsTechnicalOperation()
    {
        return $this->statut === 'needs_technical_operation';
    }

    public function isRejectedBySoutarah()
    {
        return $this->statut === 'rejected_by_soutarah';
    }

    public function isConfirmedByDemandeur()
    {
        return $this->statut === 'confirmed_by_demandeur';
    }

    public function isClosed()
    {
        return $this->statut === 'closed';
    }

    /**
     * Accesseur pour le nom du demandeur
     */
    public function getDemandeurNomAttribute()
    {
        return $this->createdBy ? $this->createdBy->nom_complet : 'N/A';
    }

    /**
     * Accesseur pour la référence de l'équipement
     */
    public function getEquipementReferenceAttribute()
    {
        if ($this->equipement) {
            return $this->equipement->equipement_code . ' - ' . $this->equipement->equipement_nom;
        }
        return 'N/A';
    }

    /**
     * Accesseur pour le téléphone du demandeur
     */
    public function getDemandeurTelephoneAttribute()
    {
        return $this->createdBy ? $this->createdBy->telephone : null;
    }

    /**
     * Accesseur pour la description du problème (alias pour compatibilité)
     */
    public function getDescriptionProblemeAttribute()
    {
        return $this->description;
    }

    /**
     * Utilisateur qui a clôturé la demande
     */
    public function cloturePar()
    {
        return $this->belongsTo(User::class, 'cloture_par_user_id');
    }
}
