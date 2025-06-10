<?php

namespace App\Services;

use App\Contracts\UserManagementInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserManagementService implements UserManagementInterface
{
    /**
     * Get all users with their roles
     *
     * @return Collection
     */
    public function getAllUsers(): Collection
    {
        return User::with('roles')->get();
    }

    /**
     * Create a new user with role
     *
     * @param array $data
     * @param string $role
     * @return User
     */
    public function createUser(array $data, string $role): User
    {
        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Assign role
            $user->assignRole($role);

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update user and their role
     *
     * @param int $id
     * @param array $data
     * @return User
     */
    public function updateUser(int $id, array $data): User
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            
            // Update basic info
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            // Update role if provided
            if (isset($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a user
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            
            // Remove roles first
            $user->roles()->detach();
            
            // Delete user
            $user->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reset user password
     *
     * @param int $id
     * @return bool
     */
    public function resetPassword(int $id): bool
    {
        try {
            $user = User::findOrFail($id);
            $newPassword = Str::random(10);
            
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            // Here you might want to send email with new password
            // NotificationFacade::sendPasswordReset($user->email, $newPassword);

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Change user role
     *
     * @param int $userId
     * @param string $role
     * @return bool
     */
    public function changeRole(int $userId, string $role): bool
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($userId);
            $user->syncRoles([$role]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 