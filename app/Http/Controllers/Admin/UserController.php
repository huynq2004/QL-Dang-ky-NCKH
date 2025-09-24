<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Facades\UserManagementFacade;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $users = UserManagementFacade::getAllUsers();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|string|in:student,lecturer,admin',
        ];

        if ($request->input('role') === 'student') {
            $rules['student_id'] = 'required|string|unique:students,student_id';
        } elseif ($request->input('role') === 'lecturer') {
            $rules['lecturer_id'] = 'required|string|unique:lecturers,lecturer_id';
        }

        $validated = $request->validate($rules);

        $user = UserManagementFacade::createUser($validated, $validated['role']);
        return redirect()->route('admin.users.index')->with('success', 'Tạo người dùng thành công.');
    }

    public function update(Request $request, $id)
    {
        $user = User::with(['student', 'lecturer'])->findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'sometimes|string|in:student,lecturer,admin',
            'password' => 'nullable|min:8',
        ];

        if ($request->input('role', $user->role) === 'student') {
            $ignore = $user->student->id ?? 'NULL';
            $rules['student_id'] = 'required|string|unique:students,student_id,' . $ignore . ',id';
        } elseif ($request->input('role', $user->role) === 'lecturer') {
            $ignore = $user->lecturer->id ?? 'NULL';
            $rules['lecturer_id'] = 'required|string|unique:lecturers,lecturer_id,' . $ignore . ',id';
        }

        $validated = $request->validate($rules);

        $user = UserManagementFacade::updateUser($id, $validated);
        return redirect()->route('admin.users.index')->with('success', 'Cập nhật người dùng thành công.');
    }

    public function destroy($id)
    {
        UserManagementFacade::deleteUser($id);
        return redirect()->route('admin.users.index')->with('success', 'Xoá người dùng thành công.');
    }

    public function resetPassword($id)
    {
        UserManagementFacade::resetPassword($id);
        return redirect()->route('admin.users.index')->with('success', 'Mật khẩu đã được đặt lại.');
    }

    public function changeRole(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => 'required|string'
        ]);

        UserManagementFacade::changeRole($id, $validated['role']);
        return redirect()->route('admin.users.index')->with('success', 'Cập nhật vai trò thành công.');
    }
} 