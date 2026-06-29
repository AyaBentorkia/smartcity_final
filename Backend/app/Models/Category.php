<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Incident;
use App\Models\User;

class Category extends Model
{
  protected $fillable = [
        'name',           
        'description',    
        'color',

        ];
  function incidents()
    {
        return $this->hasMany(Incident::class, 'category_id', 'id');
    }
      public function agents()
    {
        return $this->hasMany(User::class, 'category_id', 'id');
                    // ->whereHas('role', fn($q) => $q->where('name', 'agent'));
    }
   

  
   
}