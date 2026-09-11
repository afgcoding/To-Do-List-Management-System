<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    // List departments with search, active filter, and related counts.
    public function index(Request $request): View
    {
        $departments = Department::query()
            ->withCount(['users', 'tasks'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = '%'.$request->string('search')->toString().'%';
                $query->where(function ($builder) use ($term): void {
                    $builder->where('name', 'like', $term)->orWhere('code', 'like', $term);
                });
            })
            ->when($request->filled('active'), fn ($query) => $query->where('is_active', $request->boolean('active')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('departments.index');
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        Department::query()->create($request->validated());

        return redirect()->route('departments.index')->with('success', 'Department created successfully!');
    }

    public function show(Department $department): View
    {
        $department->load('users');

        return view('departments.show', compact('department'));
    }

    public function edit(Department $department): RedirectResponse
    {
        return redirect()->route('departments.index', ['edit' => $department->id]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return redirect()->route('departments.index')->with('success', 'Department updated successfully!');
    }

    // Flip Active / Inactive without opening the edit drawer.
    public function toggleActive(Department $department): RedirectResponse
    {
        $department->update([
            'is_active' => ! $department->is_active,
        ]);

        return back()->with('success', 'Department status updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully!');
    }
}
