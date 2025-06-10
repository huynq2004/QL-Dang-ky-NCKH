<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection getAllUsers()
 * @method static \App\Models\User createUser(array $data, string $role)
 * @method static \App\Models\User updateUser(int $id, array $data)
 * @method static bool deleteUser(int $id)
 * @method static bool resetPassword(int $id)
 * @method static bool changeRole(int $userId, string $role)
 */
class UserManagementFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'user-management';
    }
} 