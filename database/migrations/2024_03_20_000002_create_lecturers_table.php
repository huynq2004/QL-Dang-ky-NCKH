<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('lecturer_id')->unique();
            $table->string('department')->nullable();
            $table->string('title')->nullable();
            $table->string('specialization')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('max_students')->default(5);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lecturers');
    }
}; 