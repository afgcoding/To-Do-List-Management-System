@extends('layouts.app')
@php
    $pageTitle = 'Edit User';
@endphp

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit user</h1>
        <p class="mt-1 text-sm text-slate-500">Update profile, role, and account details for {{ $user->name }}.</p>
    </div>

    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('users.partials.form', ['user' => $user])
        <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('users.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancel</a>
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save changes</button>
        </div>
    </form>
</div>
@endsection
