@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Users & Team</h1>
            <p class="text-sm text-slate-500">Manage user accounts, departments, and active statuses.</p>
        </div>
        <a href="{{ route('users.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg shadow-sm transition inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add User
        </a>
    </div>

    <!-- Users Data Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500">
                    <th class="py-3 px-4">User</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Department</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 px-4 font-medium text-slate-800 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            {{ $user->name }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $user->email }}</td>
                        <td class="py-3 px-4">
                            @if($user->department)
                                <span class="px-2.5 py-1 text-xs font-medium bg-slate-100 text-slate-700 rounded-md">
                                    {{ $user->department->name }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">Unassigned</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($user->status === 'active')
                                <span class="px-2.5 py-1 text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">Active</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('users.show', $user) }}" class="text-xs font-medium text-slate-600 hover:text-indigo-600">View</a>
                                <a href="{{ route('users.edit', $user) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-800">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-500">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection