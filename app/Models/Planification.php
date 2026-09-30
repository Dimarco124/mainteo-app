<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planification extends Model
{
    use HasFactory;

    protected $table = 'planifications';
    public $timestamps = false;

    protected $fillable = [
        'demande_id',
        'depannage_id',
        'client_id',
        'site_code',
        'date_debut',
        'date_fin',
        'type_intervention',
        'nom_equipe',
        'equipe_id',
        'technicien_id',
        'commentaire',
        'commentaire_superviseur',
        'valide_par',
        'date_validation_superviseur',
        'statut',
        'created_by',
        'created_at',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'demande_id');
    }

    public function depannage()
    {
        return $this->belongsTo(Depannage::class, 'depannage_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function equipe()
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    public function valideParUser()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
