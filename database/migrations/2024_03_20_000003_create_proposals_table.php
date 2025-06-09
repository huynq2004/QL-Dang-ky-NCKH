<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('field');
            $table->text('description')->nullable();
            $table->foreignId('lecturer_id')->constrained('lecturers')->onDelete('cascade');
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('proposals');
    }
}; 