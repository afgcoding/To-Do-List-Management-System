@extends('layouts.app')
@php
    $pageTitle = $department->name;
@endphp

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <x-back-link :href="route('departments.index')">Back to departments</x-back-link>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $department->name }}</h1>
            <div class="mt-2 flex flex-wrap gap-2">
                @if($department->code)
                    <span class="inline-flex rounded-md border border-indigo-100 bg-indigo-50 px-2 py-0.5 font-mono text-xs font-semibold text-indigo-700">{{ $department->code }}</span>
                @endif
                <x-badge :tone="$department->is_active ? 'emerald' : 'slate'">{{ $department->is_active ? 'Active' : 'Inactive' }}</x-badge>
            </div>
        </div>
        <a href="{{ route('departments.index', ['edit' => $department->id]) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 sm:w-auto">Edit department</a>
    </div>

    {{-- ==================== ASSIGNED USERS ==================== --}}
    <div class="space-y-3 md:hidden">
        @forelse ($department->users as $user)
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-slate-800">{{ $user->name }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $user->email }}</p>
                <div class="mt-2"><x-badge :tone="$user->isActive() ? 'emerald' : 'slate'">{{ ucfirst($user->status?->value ?? 'inactive') }}</x-badge></div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-200 bg-white py-10 text-center text-sm text-slate-500">No users are assigned to this department.</div>
        @endforelse
    </div>

    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm md:block">
        <div class="w-full overflow-x-auto">
        <table class="w-full min-w-[480px] text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3">Assigned user</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($department->users as $user)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-4 text-sm font-medium text-slate-800">{{ $user->name }}</td>
                        <td class="px-5 py-4 text-sm text-slate-500">{{ $user->email }}</td>
                        <td class="px-5 py-4"><x-badge :tone="$user->isActive() ? 'emerald' : 'slate'">{{ ucfirst($user->status?->value ?? 'inactive') }}</x-badge></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-sm text-slate-500">No users are assigned to this department.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
