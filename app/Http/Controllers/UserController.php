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
                ->with('error', 'Unauthorized action.');
        }

        $users = User::with(['student', 'lecturer'])->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Unauthorized action.');
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

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            // Create associated role model
            if ($validated['role'] === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'student_id' => $validated['student_id'],
                ]);
            } elseif ($validated['role'] === 'lecturer') {
                Lecturer::create([
                    'user_id' => $user->id,
                    'lecturer_id' => $validated['lecturer_id'],
                ]);
            }

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
                ->with('error', 'Unauthorized action.');
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

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        // Handle role change
        if ($user->role !== $validated['role']) {
            // Remove old role model
            if ($user->role === 'student') {
                $user->student()->delete();
            } elseif ($user->role === 'lecturer') {
                $user->lecturer()->delete();
            }

            // Create new role model
            if ($validated['role'] === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'student_id' => $validated['student_id']
                ]);
            } elseif ($validated['role'] === 'lecturer') {
                Lecturer::create([
                    'user_id' => $user->id,
                    'lecturer_id' => $validated['lecturer_id']
                ]);
            }

            $user->role = $validated['role'];
        } else {
            // Update existing role model if ID changed
            if ($user->role === 'student' && isset($validated['student_id'])) {
                $user->student->update(['student_id' => $validated['student_id']]);
            } elseif ($user->role === 'lecturer' && isset($validated['lecturer_id'])) {
                $user->lecturer->update(['lecturer_id' => $validated['lecturer_id']]);
            }
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Thông tin người dùng đã được cập nhật thành công.');
    }

    public function destroy(User $user)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Unauthorized action.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Delete associated role model
        if ($user->role === 'student') {
            $user->student()->delete();
        } elseif ($user->role === 'lecturer') {
            $user->lecturer()->delete();
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
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

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
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