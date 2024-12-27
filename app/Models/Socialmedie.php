<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Socialmedie extends Model
{
    /** @use HasFactory<\Database\Factories\SocialmedieFactory> */
    use HasFactory;
    protected $fillable = [
        'linkes',
        'student_id',
        'updated_at',
        'created_at',
    ];
    public function student()
{
    return $this->belongsTo(Student::class, 'student_id');
}

}
