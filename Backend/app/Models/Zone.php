<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Incident;
use App\Models\Municipality;

class Zone extends Model
{
    protected $fillable = [
        'municipality_id',
        'name',
        'latitude_center',
        'longitude_center',
        'rayon_km',
        'description'
    ];

    public function incidents()
    {
        return $this->hasMany(Incident::class , 'zone_id', 'id');
    }
    public function municipalities()
    {
        return $this->belongsTo(Municipality::class , 'municipality_id', 'id');
    }
    // Dans app/Models/Zone.php — ajouter cette relation

public function riskAssessments()
{
    return $this->hasMany(RiskAssessment::class, 'zone_id', 'id');
}

public function latestRiskAssessment()
{
    return $this->hasOne(RiskAssessment::class, 'zone_id', 'id')->latestOfMany();
}
    /**
     * Trouve la zone correspondante à des coordonnées GPS
     * en utilisant la formule de Haversine
     */
    public static function findByCoordinates(float $lat, float $lng): ?self
    {
        // $zones = self::all();
        // $zoneFound = null;
        // $minDistance = PHP_FLOAT_MAX;

        // foreach ($zones as $zone) {
        //     $distance = self::haversineDistance(
        //         $lat, $lng,
        //         $zone->latitude_center,
        //         $zone->longitude_center
        //     );

        //     // Vérifier si le point est dans le rayon de la zone
        //     if ($distance <= $zone->rayon_km && $distance < $minDistance) {
        //         $minDistance = $distance;
        //         $zoneFound = $zone;
        //     }
        // }
        // // Si aucune zone ne correspond, retourner la plus proche
        // if (!$zoneFound) {
        //     foreach ($zones as $zone) {
        //         $distance = self::haversineDistance(
        //             $lat, $lng,
        //             $zone->latitude_center,
        //             $zone->longitude_center
        //         );
        //         if ($distance < $minDistance) {
        //             $minDistance = $distance;
        //             $zoneFound = $zone;
        //         }
        //     }
        // }

        // return $zoneFound;
         return self::selectRaw("
        *,
        (6371 * ACOS(
            COS(RADIANS(?)) * COS(RADIANS(latitude_center)) *
            COS(RADIANS(longitude_center) - RADIANS(?)) +
            SIN(RADIANS(?)) * SIN(RADIANS(latitude_center))
        )) AS distance_km
    ", [$lat, $lng, $lat])
    ->whereRaw("
        (6371 * ACOS(
            COS(RADIANS(?)) * COS(RADIANS(latitude_center)) *
            COS(RADIANS(longitude_center) - RADIANS(?)) +
            SIN(RADIANS(?)) * SIN(RADIANS(latitude_center))
        )) <= rayon_km
    ", [$lat, $lng, $lat])
    ->orderBy('distance_km')
    ->first();
    }
    /**
     * Calcule la distance en km entre deux points GPS
     * Formule de Haversine
     */
    // private static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    // {
    //     $earthRadius = 6371; 

    //     $deltaLat = deg2rad($lat2 - $lat1);
    //     $deltaLng = deg2rad($lng2 - $lng1);

    //     $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
    //         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
    //         sin($deltaLng / 2) * sin($deltaLng / 2);

    //     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    //     return $earthRadius * $c;
    // }




}