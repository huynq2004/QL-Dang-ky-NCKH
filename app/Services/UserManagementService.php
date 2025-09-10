<?php

namespace App\Services;

use App\Contracts\UserManagementInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserManagementService implements UserManagementInterface
{
    /**
     * Get all users with pagination
     *
     * @return LengthAwarePaginator
     */
    public function getAllUsers(): LengthAwarePaginator
    {
        return User::with(['student', 'lecturer'])->orderByDesc('id')->paginate(10);
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

            // Assign role string field
            $user->update(['role' => $role]);

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

            // Update role if provided (string column)
            if (isset($data['role'])) {
                $user->update(['role' => $data['role']]);
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
            
            // No role relations to detach when using string roles
            
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
            $user->update(['role' => $role]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 