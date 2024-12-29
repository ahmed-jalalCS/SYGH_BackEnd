<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'videoUrl',
        'lbraryStatus',
        'supervisorStatus',
        'projectYear',
        'department_id',
        'supervisor_id',
        'updated_at',
        'created_at',
    ];
        public function department()
        {
            return $this->belongsTo(Department::class, 'department_id');
        }

        public function supervisor()
        {
            return $this->belongsTo(Supervisor::class, 'supervisor_id');
        }
        public function students()
        {
            return $this->hasMany(Student::class, 'project_id');
        }
        public function evaluates()
        {
            return $this->hasMany(Evaluate::class, 'project_id');
        }
        public function comments()
        {
            return $this->hasMany(Comment::class, 'project_id');
        }

}
