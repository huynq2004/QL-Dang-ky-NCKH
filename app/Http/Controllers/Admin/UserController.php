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

        $messages = [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò không hợp lệ.',
            'student_id.required' => 'Vui lòng nhập mã sinh viên.',
            'student_id.unique' => 'Mã sinh viên đã được sử dụng.',
            'lecturer_id.required' => 'Vui lòng nhập mã giảng viên.',
            'lecturer_id.unique' => 'Mã giảng viên đã được sử dụng.',
        ];

        $validated = $request->validateWithBag('createUser', $rules, $messages);

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

        $messages = [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'role.in' => 'Vai trò không hợp lệ.',
            'student_id.required' => 'Vui lòng nhập mã sinh viên.',
            'student_id.unique' => 'Mã sinh viên đã được sử dụng.',
            'lecturer_id.required' => 'Vui lòng nhập mã giảng viên.',
            'lecturer_id.unique' => 'Mã giảng viên đã được sử dụng.',
        ];

        $validated = $request->validate($rules, $messages);

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

    public function checkUnique(Request $request)
    {
        $type = $request->query('type'); // email|student_id|lecturer_id
        $value = trim((string) $request->query('value'));
        if ($value === '') {
            return response()->json(['unique' => true]);
        }

        $exists = false;
        if ($type === 'email') {
            $exists = \App\Models\User::where('email', $value)->exists();
        } elseif ($type === 'student_id') {
            $exists = \App\Models\Student::where('student_id', $value)->exists();
        } elseif ($type === 'lecturer_id') {
            $exists = \App\Models\Lecturer::where('lecturer_id', $value)->exists();
        }

        return response()->json(['unique' => !$exists]);
    }
} 