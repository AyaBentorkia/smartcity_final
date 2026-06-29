<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Media;
use App\Models\Category;
use App\Models\Zone;
use App\Models\Comment;
use App\Models\Assignment;
use App\Models\City;
class Incident extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'title',
        'description',
        'latitude',
        'longitude',
        'zone_id',
        // 'city_id',
        'address_text',
        'status',
        'citizen_id',
        'media_id',

        'reported_at',
        'resolved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
            // 'priority_score' => 'float',
        ];
    }
//     public function city() {
//     return $this->belongsTo(City::class, 'city_id', 'id');
// }
    public function citizen()
    {
        return $this->belongsTo(User::class , 'citizen_id', 'id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class , 'category_id', 'id');
    }
    public function medias()
    {
        return $this->belongsTo(Media::class , 'media_id', 'id');
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class , 'zone_id', 'id');
    }
    public function assignments()
    {
        return $this->hasMany(Assignment::class , 'incident_id', 'id');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class , 'incident_id', 'id');
    }
}