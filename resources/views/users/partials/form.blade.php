@php
    $user = $user ?? null;
    $field = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500';
    $preview = $user?->avatar ? $user->avatar_url : null;
@endphp

<div class="flex flex-wrap items-center gap-5"
    x-data="{
        preview: @js($preview),
        removeAvatar: false,
        onFile(event) {
            const file = event.target.files?.[0];
            if (! file) {
                return;
            }
            this.removeAvatar = false;
            this.preview = URL.createObjectURL(file);
        },
        clearPhoto() {
            this.removeAvatar = true;
            this.preview = null;
            this.$refs.avatarInput.value = '';
        }
    }">
    <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm">
        <template x-if="preview">
            <img :src="preview" alt="Avatar preview" class="h-full w-full object-cover">
        </template>
        <template x-if="!preview">
            <span class="px-2 text-center text-xs font-medium text-slate-400">No photo</span>
        </template>
    </div>
    <div>
        <div class="flex flex-wrap items-center gap-2">
            <label class="inline-flex cursor-pointer items-center rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                Upload Photo
                <input x-ref="avatarInput" class="sr-only" type="file" name="avatar" accept="image/*" @change="onFile($event)">
            </label>
            @if ($user)
                <button type="button" x-show="preview" @click="clearPhoto()" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Remove
                </button>
                <input type="hidden" name="remove_avatar" :value="removeAvatar ? '1' : '0'">
            @endif
        </div>
        <p class="mt-2 text-xs text-slate-400">PNG, JPG, or WebP up to 2MB.</p>
        <x-input-error class="mt-1" :messages="$errors->get('avatar')" />
    </div>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Full name</label>
        <input type="text" name="name" required maxlength="255" value="{{ old('name', $user?->name) }}" class="{{ $field }}">
        <x-input-error class="mt-1" :messages="$errors->get('name')" />
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Email</label>
        <input type="email" name="email" required maxlength="255" value="{{ old('email', $user?->email) }}" class="{{ $field }}">
        <x-input-error class="mt-1" :messages="$errors->get('email')" />
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Phone number</label>
        <input type="text" name="phone" maxlength="50" value="{{ old('phone', $user?->phone) }}" class="{{ $field }}" placeholder="+93 700 000 000">
        <x-input-error class="mt-1" :messages="$errors->get('phone')" />
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Job title</label>
        <input type="text" name="job_title" maxlength="255" value="{{ old('job_title', $user?->job_title) }}" class="{{ $field }}" placeholder="Senior Laravel Developer">
        <x-input-error class="mt-1" :messages="$errors->get('job_title')" />
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Department</label>
        <select name="department_id" class="{{ $field }}">
            <option value="">Unassigned</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected((string) old('department_id', $user?->department_id) === (string) $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-1" :messages="$errors->get('department_id')" />
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Role</label>
        @can('manageRoles', App\Models\User::class)
            <select name="role" class="{{ $field }}">
                @foreach ($roles ?? [] as $workspaceRole)
                    <option value="{{ $workspaceRole->name }}" @selected(old('role', $user?->roles->first()?->name ?? 'Employee') === $workspaceRole->name)>{{ $workspaceRole->name }}</option>
                @endforeach
            </select>
        @else
            <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700">{{ $user?->roles->first()?->name ?? 'Employee' }}</p>
        @endcan
        <x-input-error class="mt-1" :messages="$errors->get('role')" />
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Password @if($user)<span class="font-normal text-slate-400">(leave blank to keep)</span>@endif</label>
        <input type="password" name="password" @required(! $user) minlength="8" class="{{ $field }}">
        <x-input-error class="mt-1" :messages="$errors->get('password')" />
    </div>
    <div>
        <p class="mb-2 text-xs font-semibold text-slate-700">Account status</p>
        <div class="flex flex-wrap gap-3">
            <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60">
                <input type="radio" name="status" value="active" class="text-indigo-600 focus:ring-indigo-500" @checked(old('status', $user?->status?->value ?? 'active') === 'active')>
                Active
            </label>
            <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60">
                <input type="radio" name="status" value="inactive" class="text-indigo-600 focus:ring-indigo-500" @checked(old('status', $user?->status?->value) === 'inactive')>
                Inactive
            </label>
        </div>
        <x-input-error class="mt-1" :messages="$errors->get('status')" />
    </div>
</div>

@can('manageRoles', App\Models\User::class)
    <details class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4" {{ $user ? 'open' : '' }}>
        <summary class="cursor-pointer text-sm font-semibold text-slate-800">Direct permissions / extra grants</summary>
        <p class="mt-1 text-xs text-slate-500">These grants apply to this person on top of their role.</p>
        <div class="mt-4">
            @include('roles.partials.permission-grid', [
                'permissionGroups' => $permissionGroups ?? [],
                'selected' => old('permissions', $user?->getDirectPermissions()->pluck('name')->all() ?? []),
            ])
        </div>
    </details>
@endcan
