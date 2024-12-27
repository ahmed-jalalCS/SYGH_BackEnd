<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'college_id',
        'updated_at',
        'created_at',
    ];
      public function college()
        {
           return $this->belongsTo(College::class, 'college_id');
        }
        public function projects()
        {
            return $this->hasMany(Project::class, 'department_id');
        }
        public function students()
        {
            return $this->hasMany(Student::class, 'department_id');
        }
        
        

}
