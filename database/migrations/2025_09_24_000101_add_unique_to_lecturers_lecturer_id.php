<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexName = 'lecturers_lecturer_id_unique';
        $exists = DB::select("SHOW INDEX FROM lecturers WHERE Key_name = ?", [$indexName]);
        if (count($exists) === 0) {
            Schema::table('lecturers', function (Blueprint $table) use ($indexName) {
                // Unique index for organization-issued lecturer codes
                $table->unique('lecturer_id', $indexName);
            });
        }
    }

    public function down(): void
    {
        $indexName = 'lecturers_lecturer_id_unique';
        $exists = DB::select("SHOW INDEX FROM lecturers WHERE Key_name = ?", [$indexName]);
        if (count($exists) > 0) {
            Schema::table('lecturers', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }
    }
};


