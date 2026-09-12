<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get();

        return view('roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('roles.create', [
            'permissionGroups' => PermissionCatalog::groupedFor(request()->user()),
            'selected' => old('permissions', []),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role = Role::query()->create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        $this->syncGrantablePermissions($role, $request->user(), $request->validated('permissions') ?? []);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('roles.edit', [
            'role' => $role,
            'permissionGroups' => PermissionCatalog::groupedFor(request()->user()),
            'selected' => old('permissions', $role->permissions->pluck('name')->all()),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $role->update([
            'name' => $request->validated('name'),
        ]);

        $this->syncGrantablePermissions(
            $role,
            $request->user(),
            $request->validated('permissions') ?? [],
            $role->permissions->pluck('name')->all(),
        );

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $role->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    /**
     * @param  list<string>  $requested
     * @param  list<string>  $existing
     */
    private function syncGrantablePermissions(Role $role, User $actor, array $requested, array $existing = []): void
    {
        $role->syncPermissions(PermissionCatalog::grantableBy($actor, $requested, $existing));
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
