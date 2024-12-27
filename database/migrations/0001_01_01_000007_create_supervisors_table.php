<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supervisors', function (Blueprint $table) {
            $table->id();
            $table->string('supervisorDgree')->nullable();
            $table->foreignId('user_id')
                   ->constrained()
                   ->casacdeOnUpdate()
                   ->cascadeOnDelete();  
           $table->foreignId('college_id')
                    ->constrained()
                    ->casacdeOnUpdate()
                    ->cascadeOnDelete();   

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisors');
    }
};
