<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Incident;

class Assignment extends Model
{
    protected $fillable = [
        'incident_id',
        'agent_id',
        'assigned_by',
        'start_time',
        'end_time',
        'status',
    ];
    public function incident()
    {
        return $this->belongsTo(Incident::class , 'incident_id', 'id');
    }
    public function agents()
    {
        return $this->belongsTo(User::class , 'agent_id', 'id');
    }
    public function assignedBy()
    {
        return $this->belongsTo(User::class , 'assigned_by', 'id');
    }

}