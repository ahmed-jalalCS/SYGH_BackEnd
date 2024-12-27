<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibrarayStaff extends Model
{
    /** @use HasFactory<\Database\Factories\LibrarayStaffFactory> */
    use HasFactory;
    protected $table = 'library_staffs'; // Ensure the correct table name
    protected $fillable = [
        'name',
        'isSuperAdmin',
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

}
