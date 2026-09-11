@extends('layouts.app')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Create New User</h1>
        <p class="text-sm text-slate-500">Add a new team member to your workspace.</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter full name" class="w-full p-2 rounded-lg border-gray-400 border-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" class="w-full p-2 rounded-lg border-gray-400 border-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" placeholder="Enter password" class="w-full p-2 rounded-lg border-gray-400 border-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                @error('password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Department -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
                <select name="department_id" class="w-full p-2 rounded-lg border-gray-400 border-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Select Department (Optional)</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select name="status" class="w-full p-2 rounded-lg border-gray-400 border-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('users.index') }}" class="px-4 py-2 bg-white border border-gray-400 hover:bg-slate-50 text-slate-700 font-medium text-sm rounded-lg shadow-sm">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg shadow-sm">Save User</button>
            </div>
        </form>
    </div>
</div>
@endsection