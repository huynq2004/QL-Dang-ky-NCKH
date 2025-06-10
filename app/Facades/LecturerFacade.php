<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection getAvailableLecturers()
 * @method static int getActiveProposalsCount(\App\Models\Lecturer $lecturer)
 * @method static int getActiveStudentsCount(\App\Models\Lecturer $lecturer)
 */
class LecturerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'lecturer';
    }
} 