<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluate extends Model
{
    /** @use HasFactory<\Database\Factories\EvaluateFactory> */
    use HasFactory;
    protected $fillable = [
        'rating',
        'user_id',
        'project_id',
        'updated_at',
        'created_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

}
