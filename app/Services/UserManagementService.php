<?php

namespace App\Services;

use App\Contracts\UserManagementInterface;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
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

            // Create associated role model with external code
            if ($role === 'student' && isset($data['student_id'])) {
                Student::create([
                    'user_id' => $user->id,
                    'student_id' => $data['student_id'],
                ]);
            }
            if ($role === 'lecturer' && isset($data['lecturer_id'])) {
                Lecturer::create([
                    'user_id' => $user->id,
                    'lecturer_id' => $data['lecturer_id'],
                ]);
            }

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

            // Optional: update password when provided
            if (isset($data['password']) && $data['password']) {
                $user->update(['password' => Hash::make($data['password'])]);
            }

            // Handle role change and external codes
            if (isset($data['role']) && $data['role'] !== $user->role) {
                // Remove old relation
                if ($user->role === 'student') {
                    $user->student()->delete();
                } elseif ($user->role === 'lecturer') {
                    $user->lecturer()->delete();
                }

                // Create new relation with code
                if ($data['role'] === 'student') {
                    Student::create([
                        'user_id' => $user->id,
                        'student_id' => $data['student_id'] ?? '',
                    ]);
                } elseif ($data['role'] === 'lecturer') {
                    Lecturer::create([
                        'user_id' => $user->id,
                        'lecturer_id' => $data['lecturer_id'] ?? '',
                    ]);
                }

                $user->update(['role' => $data['role']]);
            } else {
                // Update existing code values when role unchanged
                if ($user->role === 'student' && isset($data['student_id']) && $user->student) {
                    $user->student->update(['student_id' => $data['student_id']]);
                }
                if ($user->role === 'lecturer' && isset($data['lecturer_id']) && $user->lecturer) {
                    $user->lecturer->update(['lecturer_id' => $data['lecturer_id']]);
                }
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

            // Delete role relations if exist
            if ($user->role === 'student') {
                $user->student()->delete();
            } elseif ($user->role === 'lecturer') {
                $user->lecturer()->delete();
            }

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