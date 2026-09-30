<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FicheFroid extends Model
{
    use HasFactory;

    protected $table = 'fiche_entretien_froid';
    public $timestamps = false;

    protected $fillable = [
        'equipement_id',
        'technicien_id',
        'freon',
        'etat_filtres',
        'dismatic',
        'dpn',
        'support',
        'telecommande',
        'cuivre',
        'armaflex',
        'test',
        'etat_general',
        'observations',
        'date_saisie',
    ];

    public function equipement()
    {
        return $this->belongsTo(Equipement::class, 'equipement_id');
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }
}
