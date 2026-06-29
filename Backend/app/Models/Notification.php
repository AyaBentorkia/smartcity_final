<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'incident_id',
        'assignment_id',
        'title',
        'body',
        'type',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
             'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id', 'id');
    }
public function isRead(): bool
{
    return $this->read_at !== null; // déjà correct
}
    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id', 'id');
    }
}