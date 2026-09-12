<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Enums\UserStatus;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Department;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);
        $actor = $request->user();
        $users = User::query()
            ->with(['department', 'roles', 'permissions'])
            ->withCount([
                'assignedTasks',
                'assignedTasks as completed_tasks_count' => fn ($query) => $query->where('status', TaskStatus::Completed),
                'assignedTasks as overdue_tasks_count' => fn ($query) => $query->overdue(),
            ])
            ->latest()
            ->paginate(12);

        $directory = $users->getCollection()->mapWithKeys(function (User $user) use ($actor): array {
            return [$user->id => [
                'id' => $user->id,
                'name' => $user->name,
                'jobTitle' => $user->job_title ?: 'No job title',
                'email' => $user->email,
                'phone' => $user->phone ?: 'No phone',
                'avatar' => filled($user->avatar) ? $user->avatar_url : null,
                'role' => $user->roles->first()?->name ?? $user->role?->label() ?? 'Employee',
                'status' => $user->isActive() ? 'Active' : 'Inactive',
                'isActive' => $user->isActive(),
                'department' => $user->department?->name ?: 'No department',
                'manager' => '—',
                'joined' => format_date($user->created_at) ?? '—',
                'lastLogin' => format_date($user->last_login_at) ?? 'Never',
                'directPermissions' => $user->getDirectPermissions()->pluck('name')->values()->all(),
                'rolePermissions' => $user->getPermissionsViaRoles()->pluck('name')->values()->all(),
                'assignedTasks' => $user->assigned_tasks_count,
                'completedTasks' => $user->completed_tasks_count,
                'overdueTasks' => $user->overdue_tasks_count,
                'canView' => $actor?->can('view', $user) ?? false,
                'canUpdate' => $actor?->can('update', $user) ?? false,
                'canToggle' => $actor?->can('toggleStatus', $user) ?? false,
                'canDelete' => $actor?->can('delete', $user) ?? false,
                'busy' => false,
                'editUrl' => route('users.edit', $user),
                'tasksUrl' => route('tasks.index', ['user_id' => $user->id]),
                'toggleUrl' => route('users.toggle-status', $user),
                'deleteUrl' => route('users.destroy', $user),
            ]];
        });

        return view('users.index', [
            'users' => $users,
            'directory' => $directory->all(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', $this->formData());
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);
        $data = $request->safe()->except(['avatar', 'permissions', 'role']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->storeAvatar($request->file('avatar'));
        }

        $user = User::query()->create($data);
        $this->syncRoleAndGrants($request, $user);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);
        $user->load(['department', 'createdTasks', 'assignedTasks', 'roles']);

        return view('users.show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);
        $user->load(['roles', 'permissions']);

        return view('users.edit', $this->formData($user));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $data = $request->safe()->except(['avatar', 'remove_avatar', 'permissions', 'role']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($request->boolean('remove_avatar') && ! $request->hasFile('avatar')) {
            $this->deleteAvatar($user->avatar);
            $data['avatar'] = null;
        } elseif ($request->hasFile('avatar')) {
            $this->deleteAvatar($user->avatar);
            $data['avatar'] = $this->storeAvatar($request->file('avatar'));
        }

        $user->update($data);
        $this->syncRoleAndGrants($request, $user);

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse|JsonResponse
    {
        abort_if($request->user()?->is($user), 403);
        $this->authorize('toggleStatus', $user);

        $user->update([
            'status' => $user->status === UserStatus::Active
                ? UserStatus::Inactive
                : UserStatus::Active,
        ]);

        $user->refresh();
        $label = $user->isActive() ? 'Active' : 'Inactive';
        $message = "User status updated to {$label}";

        if ($request->expectsJson()) {
            return response()->json([
                'is_active' => $user->isActive(),
                'status' => $label,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->hasRole('Super Admin'), 403);
        $this->authorize('delete', $user);
        $this->deleteAvatar($user->avatar);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }

    /**
     * @return array{departments: Collection<int, Department>, roles: Collection<int, Role>, permissionGroups: array<string, list<array{name: string, label: string}>>, user?: User}
     */
    private function formData(?User $user = null): array
    {
        $actor = request()->user();
        $roles = Role::query()->orderBy('name');

        if (! $actor?->hasRole('Super Admin')) {
            $roles->where('name', '!=', 'Super Admin');
        }

        $data = [
            'departments' => Department::query()->orderBy('name')->get(),
            'roles' => $roles->get(),
            'permissionGroups' => $actor ? PermissionCatalog::groupedFor($actor) : [],
        ];

        if ($user !== null) {
            $data['user'] = $user;
        }

        return $data;
    }

    private function syncRoleAndGrants(StoreUserRequest|UpdateUserRequest $request, User $user): void
    {
        $actor = $request->user();

        if (! $actor?->can('manageRoles', $user)) {
            if ($user->wasRecentlyCreated) {
                $user->syncWorkspaceRole('Employee');
            }

            return;
        }

        $roleName = $request->validated('role') ?? 'Employee';

        if ($roleName === 'Super Admin' && ! $actor->hasRole('Super Admin')) {
            $roleName = $user->roles->first()?->name ?? 'Employee';
        }

        $user->syncWorkspaceRole($roleName);
        $user->syncPermissions(PermissionCatalog::grantableBy(
            $actor,
            $request->validated('permissions') ?? [],
        ));
    }

    private function storeAvatar(UploadedFile $file): string
    {
        return $file->store('avatars', 'public');
    }

    private function deleteAvatar(?string $path): void
    {
        if (filled($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
