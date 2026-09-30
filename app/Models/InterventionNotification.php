<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterventionNotification extends Model
{
    use HasFactory;

    protected $table = 'intervention_notifications';

    protected $fillable = [
        'user_id',
        'intervention_id',
        'demande_id',
        'maintenance_id',
        'type',
        'message',
        'statut',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function intervention()
    {
        return $this->belongsTo(Depannage::class, 'intervention_id');
    }

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'demande_id');
    }

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_id');
    }
}
