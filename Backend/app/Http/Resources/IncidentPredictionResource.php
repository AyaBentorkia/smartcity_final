<?php
// app/Http/Resources/IncidentPredictionResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentPredictionResource extends JsonResource
{
    public function toArray(Request $request): array
    {

    return [
        'id'           => $this->id,
        'zone_id'      => $this->zone_id,
        'zone_name'    => $this->zone?->name,
        'triggered_by' => $this->triggeredBy?->name,
        'category'     => $this->category,
        'period'       => $this->period?->format('Y-m-d'),
        'semaine'      => $this->semaine,
        'probabilite'  => $this->probabilite,
        'risque'       => $this->risque,
        'meteo'        => $this->meteo ?? [],
        'est_ferie'    => $this->est_ferie,  // ← était nb_feries
        'explication'  => $this->explication,
        'analyzed_at'  => $this->analyzed_at?->toISOString(),
        'created_at'   => $this->created_at?->toISOString(),
    ];

    }
}