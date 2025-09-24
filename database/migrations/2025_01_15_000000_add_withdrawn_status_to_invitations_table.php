<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modify the status column to include 'withdrawn' and 'expired'
        DB::statement("ALTER TABLE invitations MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'withdrawn', 'expired') DEFAULT 'pending'");
    }

    public function down()
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE invitations MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending'");
    }
};
