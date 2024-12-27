<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    /** @use HasFactory<\Database\Factories\UniversityFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'address',
        'image',
        'updated_at',
        'created_at',
    ];

    public function colleges()
     {
         return $this->hasMany(College::class, 'universitie_id');
     }
     


}
