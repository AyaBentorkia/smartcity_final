<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Incident;
use App\Models\User;

class Media extends Model
{protected $table = 'medias';
    protected $fillable = [
        'id',
        'url',    
        ];

    function users()
    {
        return $this->hasOne(User::class, 'media_id', 'id');
    }
    function incidents()
    {
        return $this->hasOne(Incident::class, 'media_id', 'id');
    }
}
