<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    use HasFactory;

    protected $table = 'installations';

    protected $fillable = [
        'client_id',
        'client_nom',
        'base_code',
        'base_nom',
        'site_code',
        'site_nom',
        'type_equipement',
        'marque',
        'modele',
        'numero_serie',
        'code_identification',
        'date_prevue',
        'date_installation',
        'technicien_id',
        'remarque',
        'statut',
        'rapport',
        'approuve_client',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }
}
