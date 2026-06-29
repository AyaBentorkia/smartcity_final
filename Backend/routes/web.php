<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;


Route::get('/export-incidents', function () {

    $incidents = DB::table('incidents as i')
        ->join('zones as z', 'i.zone_id', '=', 'z.id')
        ->join('municipalities as m', 'z.municipality_id', '=', 'm.id')
        ->select(
            'i.id',
            'i.category_id',
            'i.zone_id',
            'i.status',
            'i.reported_at',
            'i.resolved_at',

            DB::raw('MONTH(i.reported_at) as mois'),
            DB::raw('QUARTER(i.reported_at) as trimestre'),
            DB::raw('DAYOFWEEK(i.reported_at) as jour_semaine'),
            DB::raw('YEAR(i.reported_at) as annee'),

            'z.latitude_center',
            'z.longitude_center',
            'z.rayon_km',

            'm.number_of_inhabitants',
            'm.surface'
        )
        ->get();

    $fileName = 'incidents.csv';
    $filePath = storage_path($fileName);

    $file = fopen($filePath, 'w');

    // header
    if ($incidents->count() > 0) {
        fputcsv($file, array_keys((array)$incidents->first()));
    }

    // data
    foreach ($incidents as $row) {
        fputcsv($file, (array)$row);
    }

    fclose($file);

    return response()->download($filePath);
});