<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    protected $fillable = [
        'name',
        'city_id',
        'number_of_inhabitants',
        'surface',
        'address',
        'email',
        'phone',
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function agents()
    {
        return $this->hasMany(User::class, 'municipality_id', 'id');
    }

    public function municipalAdmins()
    {
        return $this->hasMany(User::class, 'municipality_id', 'id');
    }

    public function zones()
    {
        return $this->hasMany(Zone::class, 'municipality_id', 'id');
    }
}