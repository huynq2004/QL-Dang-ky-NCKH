<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

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

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:student,lecturer,admin'],
            'student_id' => ['required_if:role,student', 'string', 'unique:students,student_id'],
            'lecturer_id' => ['required_if:role,lecturer', 'string', 'unique:lecturers,lecturer_id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Create associated role model
        if ($request->role === 'student') {
            Student::create([
                'user_id' => $user->id,
                'student_id' => $request->student_id,
            ]);
        } elseif ($request->role === 'lecturer') {
            Lecturer::create([
                'user_id' => $user->id,
                'lecturer_id' => $request->lecturer_id,
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => $request->password ? [Rules\Password::defaults()] : [],
            'role' => ['required', 'string', 'in:student,lecturer,admin'],
            'student_id' => [
                'required_if:role,student',
                'string',
                'unique:students,student_id,' . ($user->student->id ?? 'NULL') . ',id',
            ],
            'lecturer_id' => [
                'required_if:role,lecturer',
                'string',
                'unique:lecturers,lecturer_id,' . ($user->lecturer->id ?? 'NULL') . ',id',
            ],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        // Handle role change
        if ($user->role !== $request->role) {
            // Remove old role model
            if ($user->role === 'student') {
                $user->student()->delete();
            } elseif ($user->role === 'lecturer') {
                $user->lecturer()->delete();
            }

            // Create new role model
            if ($request->role === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'student_id' => $request->student_id,
                ]);
            } elseif ($request->role === 'lecturer') {
                Lecturer::create([
                    'user_id' => $user->id,
                    'lecturer_id' => $request->lecturer_id,
                ]);
            }

            $user->role = $request->role;
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
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
} 