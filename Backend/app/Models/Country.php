<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',       // ex: TN, FR, MA
    ];

    public function governorates()
    {
        return $this->hasMany(Governorate::class, 'country_id', 'id');
    }
}