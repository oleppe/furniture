<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users with search and role filtering.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim();
        $role = $request->string('role')->trim();

        $query = User::query();

        if ($search->isNotEmpty()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role->isNotEmpty()) {
            $query->where('role', $role);
        }

        $users = $query->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $roleCounts = [
            'all' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'editor' => User::where('role', 'editor')->count(),
            'user' => User::where('role', 'user')->count(),
        ];

        return view('admin.users.index', compact('users', 'roleCounts', 'search', 'role'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in(['admin', 'editor', 'user'])],
            'password' => ['required', 'string', Password::min(8)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$validated['name']}' was successfully created.");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['admin', 'editor', 'user'])],
            'password' => ['nullable', 'string', Password::min(8)],
        ]);

        // Self-protection rule: Logged-in admin cannot demote themselves from admin role
        if ($user->id === Auth::id() && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'Safety safeguard: You cannot remove the administrative role from your own active account.',
            ]);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' was successfully updated.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Self-protection rule: Logged-in admin cannot delete themselves
        if ($user->id === Auth::id()) {
            return back()->withErrors([
                'error' => 'Safety safeguard: You cannot delete your own active administrator account.',
            ]);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' was deleted.");
    }
}
