<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add unique index only if it does not already exist
        $indexName = 'students_student_id_unique';
        $exists = DB::select("SHOW INDEX FROM students WHERE Key_name = ?", [$indexName]);
        if (count($exists) === 0) {
            Schema::table('students', function (Blueprint $table) use ($indexName) {
                // Unique index for organization-issued student codes
                $table->unique('student_id', $indexName);
            });
        }
    }

    public function down(): void
    {
        $indexName = 'students_student_id_unique';
        $exists = DB::select("SHOW INDEX FROM students WHERE Key_name = ?", [$indexName]);
        if (count($exists) > 0) {
            Schema::table('students', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }
    }
};


