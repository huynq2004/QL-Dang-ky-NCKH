<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserManagementInterface
{
    /**
     * Get all users with pagination
     *
     * @return LengthAwarePaginator
     */
    public function getAllUsers(): LengthAwarePaginator;

    /**
     * Create a new user with role
     *
     * @param array $data
     * @param string $role
     * @return User
     */
    public function createUser(array $data, string $role): User;

    /**
     * Update user and their role
     *
     * @param int $id
     * @param array $data
     * @return User
     */
    public function updateUser(int $id, array $data): User;

    /**
     * Delete a user
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool;

    /**
     * Reset user password
     *
     * @param int $id
     * @return bool
     */
    public function resetPassword(int $id): bool;

    /**
     * Change user role
     *
     * @param int $userId
     * @param string $role
     * @return bool
     */
    public function changeRole(int $userId, string $role): bool;
} 