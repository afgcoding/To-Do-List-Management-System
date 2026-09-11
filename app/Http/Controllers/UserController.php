<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('department')->latest()->get();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $departments = Department::where('is_active', true)->get();

        return view('users.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        // مستقیماً دلته ویلیډېشن وکړه!
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8',
            'department_id' => 'nullable|exists:departments,id',
            'status'        => 'required|in:active,inactive',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function show(User $user): View
    {
        $user->load(['department', 'createdTasks', 'assignedTasks']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $departments = Department::where('is_active', true)->get();

        return view('users.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        // د اپډېټ لپاره ډېر ساده او روښانه ویلیډېشن
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'      => 'nullable|string|min:8',
            'department_id' => 'nullable|exists:departments,id',
            'status'        => 'required|in:active,inactive',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}