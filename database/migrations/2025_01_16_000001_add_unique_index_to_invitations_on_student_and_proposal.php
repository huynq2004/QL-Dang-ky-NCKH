<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('invitations', 'student_id') || !Schema::hasColumn('invitations', 'proposal_id')) {
                return;
            }
            $table->unique(['student_id', 'proposal_id'], 'invitations_student_proposal_unique');
        });
    }

    public function down()
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropUnique('invitations_student_proposal_unique');
        });
    }
};


