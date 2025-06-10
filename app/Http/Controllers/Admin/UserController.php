<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:student,lecturer,admin'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Create associated role model
        if ($request->role === 'student') {
            Student::create(['user_id' => $user->id]);
        } elseif ($request->role === 'lecturer') {
            Lecturer::create(['user_id' => $user->id]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => $request->password ? ['confirmed', Rules\Password::defaults()] : [],
            'role' => ['required', 'string', 'in:student,lecturer,admin'],
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
                Student::create(['user_id' => $user->id]);
            } elseif ($request->role === 'lecturer') {
                Lecturer::create(['user_id' => $user->id]);
            }

            $user->role = $request->role;
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // Delete associated role model
        if ($user->role === 'student') {
            $user->student()->delete();
        } elseif ($user->role === 'lecturer') {
            $user->lecturer()->delete();
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
} 