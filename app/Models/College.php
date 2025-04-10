<?php

namespace App\Models;

use App\Models\LibrarayStaff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PharIo\Manifest\Library;

class College extends Model
{
    /** @use HasFactory<\Database\Factories\CollegeFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'universitie_id',
        'updated_at',
        'created_at',
    ];
     public function university()
      {
        return $this->belongsTo(University::class, 'universitie_id');
      }
      public function department()
      {
        return $this->hasMany(Department::class, 'college_id');
      }
      public function libraryStaffs()
       {
         return $this->hasMany(LibrarayStaff::class, 'college_id');
       }

       public function supervisors()
        {
            return $this->hasMany(Supervisor::class, 'college_id');
        }


}
