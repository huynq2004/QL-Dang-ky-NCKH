<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ProposalFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'proposal';
    }
} 