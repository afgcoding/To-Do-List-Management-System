@extends('layouts.app')
@php
    $pageTitle = $department->name;
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div class="departments-show">
    <div class="departments-show-header">
        <div class="departments-show-heading">
            <div class="departments-show-back">
                <x-back-link :href="route('departments.index')">Back to departments</x-back-link>
            </div>
            <h1 class="departments-show-title">{{ $department->name }}</h1>
            <div class="departments-show-meta">
                @if($department->code)
                    <span class="departments-show-code">{{ $department->code }}</span>
                @endif
                <x-badge :tone="$department->is_active ? 'emerald' : 'slate'">{{ $department->is_active ? 'Active' : 'Inactive' }}</x-badge>
            </div>
        </div>
        <a href="{{ route('departments.index', ['edit' => $department->id]) }}" class="departments-show-edit">Edit department</a>
    </div>

    <div class="departments-show-users-mobile">
        @forelse ($department->users as $user)
            <article class="departments-show-user-card">
                <p class="departments-show-user-name">{{ $user->name }}</p>
                <p class="departments-show-user-email">{{ $user->email }}</p>
                <div class="departments-show-user-status"><x-badge :tone="$user->isActive() ? 'emerald' : 'slate'">{{ ucfirst($user->status?->value ?? 'inactive') }}</x-badge></div>
            </article>
        @empty
            <div class="departments-show-empty">No users are assigned to this department.</div>
        @endforelse
    </div>

    <div class="departments-show-users-table">
        <div class="departments-show-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Assigned user</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($department->users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td class="departments-show-user-email">{{ $user->email }}</td>
                            <td><x-badge :tone="$user->isActive() ? 'emerald' : 'slate'">{{ ucfirst($user->status?->value ?? 'inactive') }}</x-badge></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No users are assigned to this department.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
