<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable =['pathDo','project_id','updated_at','created_at'];

    public function project(){
        return $this->belongsTo(Project::class);
    }

}
