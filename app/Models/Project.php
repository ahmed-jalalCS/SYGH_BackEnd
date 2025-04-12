<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
        public function document(){

            return $this->hasOne(Document::class,'project_id', 'id');
        }

        public function getProjectDetails()
        {
            // Load necessary relationships
            $this->load([
                'document:id,project_id,pathDo',
                'supervisor.user:id,name',
                'students.user:id,name,email',
                'students.socialmedie:id,student_id,linkes',
                'comments.user:id,name',
                'evaluates' // Load ratings
            ]);

            // Calculate the average rating
            $averageRating = $this->evaluates->avg('rating') ?? 0; // Default to 0 if no ratings

            return [
                'title' => $this->title,
                'description' => $this->description,
                'videoUrl' => $this->videoUrl,
                'projectYear' => $this->projectYear,
                'average_rating' => round($averageRating, 2), // Round to 2 decimal places
                'document_path' => $this->document->pathDo ?? null, // Path of the document
                'supervisorName' => $this->supervisor->user->name ?? null, // Supervisor's name
                'students' => $this->students->map(function ($student) {
                    return [
                        'name' => $student->user->name ?? null,
                        'email' => $student->user->email ?? null,
                        'social_links' => $student->socialmedie->pluck('linkes'), // Social media links
                    ];
                }),
                'comments' => $this->comments->map(function ($comment) {
                    return [
                        'id'=>$comment->id,
                        'body' => $comment->body,
                        'user_name' => $comment->user->name ?? null, // Name of the commenter
                    ];
                }),
            ];
        }



}
