@extends('layouts.app')

@section('content')
<div class="p-6 max-w-4xl mx-auto space-y-6">
    <!-- User Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xl flex items-center justify-center">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">{{ $user->name }}</h1>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-700 rounded-md">
                        {{ $user->department->name ?? 'No Department' }}
                    </span>
                    <span class="px-2.5 py-0.5 text-xs font-medium border rounded-full {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
            </div>
        </div>
        <div>
            <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg shadow-sm">Edit Profile</a>
        </div>
    </div>
</div>
@endsection