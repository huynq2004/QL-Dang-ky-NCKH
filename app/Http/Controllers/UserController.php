<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use App\Facades\UserManagementFacade;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Hành động không được phép.');
        }

        $users = UserManagementFacade::getAllUsers();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Hành động không được phép.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:student,lecturer,admin'],
        ];

        // Add role-specific validation rules
        if ($request->input('role') === 'student') {
            $rules['student_id'] = ['required', 'string', 'unique:students,student_id'];
        } elseif ($request->input('role') === 'lecturer') {
            $rules['lecturer_id'] = ['required', 'string', 'unique:lecturers,lecturer_id'];
        }

        try {
            $validated = $request->validate($rules);

            UserManagementFacade::createUser($validated, $validated['role']);

            return redirect()->route('users.index')
                ->with('success', 'Người dùng đã được tạo thành công.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Có lỗi xảy ra khi tạo người dùng. Vui lòng thử lại.']);
        }
    }

    public function update(Request $request, User $user)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Hành động không được phép.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => $request->password ? [Rules\Password::defaults()] : [],
            'role' => ['required', 'string', 'in:student,lecturer,admin'],
        ];

        // Add role-specific validation rules
        if ($request->role === 'student') {
            $rules['student_id'] = [
                'required',
                'string',
                'unique:students,student_id,' . ($user->student->id ?? 'NULL') . ',id'
            ];
        } elseif ($request->role === 'lecturer') {
            $rules['lecturer_id'] = [
                'required',
                'string',
                'unique:lecturers,lecturer_id,' . ($user->lecturer->id ?? 'NULL') . ',id'
            ];
        }

        $validated = $request->validate($rules);

        UserManagementFacade::updateUser($user->id, $validated);

        return redirect()->route('users.index')
            ->with('success', 'Thông tin người dùng đã được cập nhật thành công.');
    }

    public function destroy(User $user)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Hành động không được phép.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'Bạn không thể tự xoá tài khoản của chính mình.');
        }

        UserManagementFacade::deleteUser($user->id);

        return redirect()->route('users.index')
            ->with('success', 'Xoá người dùng thành công.');
    }

    public function editProfile(): View
    {
        $user = Auth::user();
        return view('profile.edit', [
            'user' => $user,
            'canEdit' => true
        ]);
    }

    public function viewProfile(): View
    {
        $user = Auth::user();
        return view('profile.edit', [
            'user' => $user,
            'canEdit' => false
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        UserManagementFacade::updateUser($user->id, $validated);

        return redirect()->route('profile.edit')->with('success', 'Thông tin cá nhân đã được cập nhật thành công.');
    }

    public function destroyProfile(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        Auth::logout();

        UserManagementFacade::deleteUser($user->id);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
} 