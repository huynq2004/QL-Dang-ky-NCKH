<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Facades\UserManagementFacade;

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|string'
        ]);

        $user = UserManagementFacade::createUser($validated, $validated['role']);
        return redirect()->route('admin.users.index')->with('success', 'Tạo người dùng thành công.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'sometimes|string'
        ]);

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