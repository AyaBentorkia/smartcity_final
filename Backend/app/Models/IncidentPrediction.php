<?php
// app/Models/IncidentPrediction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentPrediction extends Model
{
    protected $fillable = [
    'zone_id',
    'triggered_by',
    'category',
    'period',
    'semaine',
    'probabilite',
    'risque',
    'meteo',
    'est_ferie',      // ← était nb_feries
    'explication',
    'analyzed_at',
];

protected function casts(): array
{
    return [
        'probabilite' => 'float',
        'semaine'     => 'integer',
        'est_ferie'   => 'boolean',   // ← était nb_feries integer
        'meteo'       => 'array',
        'period'      => 'date',
        'analyzed_at' => 'datetime',
    ];
}
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}