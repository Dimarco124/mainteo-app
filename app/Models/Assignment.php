<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'assignments';

    protected $fillable = [
        'superviseur_soutarah_id',
        'base_id',
        'client_id',
    ];

    /**
     * Le superviseur Soutarah assigné
     */
    public function superviseurSoutarah()
    {
        return $this->belongsTo(User::class, 'superviseur_soutarah_id');
    }

    /**
     * La base assignée (si applicable)
     */
    public function base()
    {
        return $this->belongsTo(BaseSite::class, 'base_id');
    }

    /**
     * Le client assigné (si petite company sans bases)
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Validation : un superviseur ne peut être assigné qu'à UNE base OU UNE company
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($assignment) {
            // Vérifier qu'il y a soit base_id soit client_id, mais pas les deux
            if (($assignment->base_id && $assignment->client_id) || 
                (!$assignment->base_id && !$assignment->client_id)) {
                throw new \Exception('Un superviseur Soutarah doit être assigné à UNE base OU UNE company, pas les deux.');
            }
        });
    }
}
