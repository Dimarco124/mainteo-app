<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_maintenance',
        'type_maintenance',
        'client_id',
        'base_id',
        'site_id',
        'description',
        'taches_prevues',
        'pieces_prevues',
        'date_debut_prevue',
        'date_fin_prevue',
        'duree_estimee_heures',
        'equipe_id',
        'technicien_id',
        'statut',
        'date_confirmation_client',
        'confirme_par_user_id',
        'date_debut_reelle',
        'date_fin_reelle',
        'rapport_technicien',
        'pieces_utilisees',
        'created_by_user_id',
        'created_by_role',
        'nombre_equipements_prevus',
        'nombre_equipements_traites',
        'equipements_par_jour',
        'jours_non_ouvrables',
        'jours_ouvrables_count',
        // Workflow validation
        'statut_rapport_technicien',
        'date_transmission_rapport_client',
        'rapport_transmis_par_user_id',
        'raison_rejet_rapport',
        'date_rejet_rapport',
        'statut_validation_client',
        'commentaire_validation_client',
        'date_validation_client',
        'validated_by_client_user_id',
    ];

    protected $casts = [
        'date_debut_prevue' => 'datetime',
        'date_fin_prevue' => 'datetime',
        'date_confirmation_client' => 'datetime',
        'date_debut_reelle' => 'datetime',
        'date_fin_reelle' => 'datetime',
        'nombre_equipements_prevus' => 'integer',
        'nombre_equipements_traites' => 'integer',
        'equipements_par_jour' => 'integer',
        'jours_non_ouvrables' => 'array',
        'jours_ouvrables_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function base()
    {
        return $this->belongsTo(BaseSite::class, 'base_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    /**
     * Équipe principale (ancienne relation, gardée pour compatibilité)
     */
    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }

    /**
     * NOUVELLE: Toutes les équipes affectées à cette maintenance (many-to-many)
     */
    public function equipes()
    {
        return $this->belongsToMany(Equipe::class, 'equipe_maintenance')
                    ->withPivot('role', 'date_affectation')
                    ->withTimestamps()
                    ->orderByPivot('role', 'desc'); // 'principale' avant 'support'
    }

    /**
     * Équipe principale via la relation many-to-many
     */
    public function equipePrincipale()
    {
        return $this->belongsToMany(Equipe::class, 'equipe_maintenance')
                    ->wherePivot('role', 'principale')
                    ->withPivot('role', 'date_affectation')
                    ->withTimestamps()
                    ->limit(1);
    }

    /**
     * Équipes de support
     */
    public function equipesSupport()
    {
        return $this->belongsToMany(Equipe::class, 'equipe_maintenance')
                    ->wherePivot('role', 'support')
                    ->withPivot('role', 'date_affectation')
                    ->withTimestamps();
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function confirmePar()
    {
        return $this->belongsTo(User::class, 'confirme_par_user_id');
    }

    public function rapportTransmisPar()
    {
        return $this->belongsTo(User::class, 'rapport_transmis_par_user_id');
    }

    public function validatedByClient()
    {
        return $this->belongsTo(User::class, 'validated_by_client_user_id');
    }

    public function comptesRendusJournaliers()
    {
        return $this->hasMany(CompteRenduJournalier::class, 'maintenance_id')->orderBy('date_rapport', 'desc');
    }

    /**
     * Calculer le pourcentage d'avancement
     */
    public function getPourcentageAvancementAttribute()
    {
        if ($this->nombre_equipements_prevus <= 0) {
            return 0;
        }
        $pourcentage = ($this->nombre_equipements_traites / $this->nombre_equipements_prevus) * 100;
        return min(100, round($pourcentage, 1));
    }

    /**
     * Nombre d'équipements restants à traiter sur le site
     */
    public function getNombreEquipementsRestantsAttribute()
    {
        if ($this->nombre_equipements_prevus <= 0) {
            return 0;
        }
        return max(0, $this->nombre_equipements_prevus - $this->nombre_equipements_traites);
    }

    /**
     * Recalculer l'avancement global d'après tous les comptes rendus journaliers
     */
    public function recalculerAvancement()
    {
        $totalTraites = (int) $this->comptesRendusJournaliers()->sum('nombre_equipements_traites');
        $this->nombre_equipements_traites = $totalTraites;

        // Si le nombre d'équipements traités atteint ou dépasse le nombre prévu, marquer terminée
        if ($this->nombre_equipements_prevus > 0 && $totalTraites >= $this->nombre_equipements_prevus && $this->statut !== 'terminée') {
            $this->statut = 'terminée';
            if (!$this->date_fin_reelle) {
                $this->date_fin_reelle = now();
            }
        } elseif ($totalTraites > 0 && in_array($this->statut, ['planifiée', 'confirmée_client'])) {
            $this->statut = 'en_cours';
            if (!$this->date_debut_reelle) {
                $this->date_debut_reelle = now();
            }
        }

        $this->save();
        return $this->pourcentage_avancement;
    }

    // Helper pour générer le numéro automatique
    public static function genererNumero()
    {
        $derniere = self::orderBy('id', 'desc')->first();
        $prochain = $derniere ? $derniere->id + 1 : 1;
        return 'MAINT-' . date('Y') . '-' . str_pad($prochain, 5, '0', STR_PAD_LEFT);
    }
}

