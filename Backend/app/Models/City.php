<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'governorate_id',   // FK — remplace le champ string 'governorate'
    ];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class, 'governorate_id', 'id');
    }

    public function municipalities()
    {
        return $this->hasMany(Municipality::class, 'city_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'city_id', 'id');
    }
}