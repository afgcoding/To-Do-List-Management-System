<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    // Categories with optional HEX color and task counts.
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('tasks')
            ->latest()
            ->paginate(12);

        return view('categories.index', compact('categories'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('categories.index');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    public function show(Category $category): RedirectResponse
    {
        return redirect()->route('categories.index', ['edit' => $category->id]);
    }

    public function edit(Category $category): RedirectResponse
    {
        return redirect()->route('categories.index', ['edit' => $category->id]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }
}
