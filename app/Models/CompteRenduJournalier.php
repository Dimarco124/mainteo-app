<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompteRenduJournalier extends Model
{
    use HasFactory;

    protected $table = 'comptes_rendus_journaliers';

    protected $fillable = [
        'maintenance_id',
        'site_id',
        'equipe_id',
        'responsable_id',
        'date_rapport',
        'noms_intervenants',
        'activites_realisees',
        'nombre_equipements_traites',
        'anomalies_constatees',
        'difficultes_rencontrees',
        'materiel_utilise',
        'travaux_non_termines',
        'heure_fin',
        'nom_responsable',
        'created_by_user_id',
    ];

    protected $casts = [
        'date_rapport' => 'date',
        'nombre_equipements_traites' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
