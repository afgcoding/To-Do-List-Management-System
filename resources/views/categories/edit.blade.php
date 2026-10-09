@extends('layouts.app')
@php
    $pageTitle = 'Edit Category';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div class="categories-page categories-standalone" x-data="{ name: @js(old('name', $category->name)), color: @js(old('color', $category->color ?: '#6366F1')) }">
    <div class="categories-show-back">
        <x-back-link :href="route('categories.index')">Back to categories</x-back-link>
    </div>
    <div class="categories-standalone-card">
        <div class="categories-standalone-head">
            <h1 class="workspace-title">Edit category</h1>
            <p class="mt-1 text-sm text-gray-500">Give the category a name and a color.</p>
        </div>

        <form method="POST" action="{{ route('categories.update', $category) }}" class="categories-form">
            @csrf
            @method('PUT')

            <div class="categories-form-field">
                <label for="name" class="categories-form-label">Category name <span class="categories-form-required">*</span></label>
                <input id="name" name="name" x-model="name" required placeholder="Frontend, Database, Recruitment" class="categories-form-input">
                <p class="categories-form-hint">Shown on tasks as a colored label.</p>
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="categories-color-picker">
                <x-color-picker />
            </div>

            <div class="categories-form-actions">
                <a href="{{ route('categories.index') }}" class="categories-form-cancel">Cancel</a>
                <button type="submit" class="categories-form-save">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
