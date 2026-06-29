<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Incident;

class Comment extends Model
{
    protected $fillable = [
        'incident_id',
        'user_id',
        'content',
    ];
    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id', 'id');
    }
}
