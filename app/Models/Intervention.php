<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasFactory;

    protected $table = 'interventions';
    public $timestamps = false;

    protected $fillable = [
        'type_intervention',
        'demandeur',
        'demandeur_id',
        'date_demande',
        'equipement_code',
        'base',
        'site',
        'emplacement',
        'commentaire',
        'date_prevue',
        'date_execution',
        'date_approbation',
        'date_rejet',
        'accord_intervention',
        'accord_auteur',
        'urgence',
        'statut_intervention',
        'superviseur_id',
        'date_validation_superviseur',
        'commentaire_superviseur',
        'admin_id',
        'date_validation_admin',
        'technicien_id',
        'date_assignation_technicien',
        'date_assignation_equipe',
        'commentaire_admin',
        'superviseur_base',
        'date_creation',
        'rapport_intervention',
        'rapport_technicien',
        'accord_rapport',
        'date_validation_rapport',
        'superviseur_notifie',
        'date_notif_superviseur',
        'accord_rapport_auteur',
        'equipe_id',
        'planification_id',
        'notif_admin',
        'date_maj_tech',
        'date_fin',
        'rapport',
        'date_modification',
    ];

    public function demandeurUser()
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    public function superviseur()
    {
        return $this->belongsTo(User::class, 'superviseur_id');
    }

    public function equipe()
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }
}
