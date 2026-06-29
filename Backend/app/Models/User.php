<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Media;
use App\Models\Incident; 
use App\Models\Assignment;
use App\Models\Agent;
use App\Models\MunicipalAdmin;
use App\Models\Comment;
use App\Models\Role;
use App\Models\Municipality;
use App\Models\Category;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\CustomResetPasswordNotification;


// use Laravel\Fortify\TwoFactorAuthenticatable;
// use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
// use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Auth\MustVerifyEmail;
class User extends Authenticatable implements JWTSubject
{
    use MustVerifyEmail, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'role',
        'status',
        'photo_id',
        'birthdate',
        'cin',
        'municipality_id',  // nullable — pour agent et municipal_admin
        'category_id',
        'google_id',
        'email_verified_at',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        // 'two_factor_secret',
        // 'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
     public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }
 
    public function getJWTCustomClaims(): array
    {
        return ['email' => $this->email,
            'name'  => $this->name,
            'role'  => $this->role,];
    }
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // 'two_factor_confirmed_at' => 'datetime',
            'birthdate' => 'date',
            'role'=> UserRole::class,
        ];
    }
//   public function role()
//     {
//         return $this->belongsTo(Role::class, 'role_id', 'id');
//     }
 public function sendPasswordResetNotification($token): void
{
    $this->notify(new CustomResetPasswordNotification($token));
}
    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id', 'id');
    }
 
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
 
    public function photo()
    {
        return $this->belongsTo(Media::class, 'photo_id', 'id');
    }
 
    public function incidents()
    {
        return $this->hasMany(Incident::class, 'citizen_id', 'id');
    }
 
    // Assignments où l'user est l'agent assigné
    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'agent_id', 'id');
    }
 
    // Assignments créés par cet user (municipal_admin)
    public function assignedByMe()
    {
        return $this->hasMany(Assignment::class, 'assigned_by', 'id');
    }
 
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id', 'id');
    }
 
    // ─── Helpers de rôle ─────────────────────────────────────────
 
    // public function isCitizen(): bool
    // {
    //     return $this->role->name === UserRole::CITIZEN;
    // }
 
    // public function isAgent(): bool
    // {
    //     return $this->role->name === UserRole::AGENT;
    // }
 
    // public function isMunicipalAdmin(): bool
    // {
    //     return $this->role->name === UserRole::ADMIN_MUNICIPAL;
    // }
 
    // public function isSuperAdmin(): bool
    // {
    //     return $this->role->name === UserRole::SUPER_ADMIN;

    // }
}