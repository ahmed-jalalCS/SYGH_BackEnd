<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    /** @use HasFactory<\Database\Factories\SupervisorFactory> */
    use HasFactory;
    protected $fillable = [
        'supervisorDgree',
        'college_id',
        'user_id',
        'updated_at',
        'created_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }
    public function projects()
    {
        return $this->hasMany(Project::class, 'supervisor_id');
    }


}
