@props([
    'permissionGroups',
    'selected' => [],
])

@php
    $selected = collect($selected)->map(fn ($name) => (string) $name)->all();
@endphp

<div
    class="roles-permissions"
    x-data="{
        search: '',
        selected: @js($selected),
        visible(label, name) {
            const q = this.search.trim().toLowerCase();
            if (! q) {
                return true;
            }
            return label.toLowerCase().includes(q) || name.toLowerCase().includes(q);
        },
        namesOf(group) {
            return group.map((item) => item.name);
        },
        allChecked(names) {
            return names.every((name) => this.selected.includes(name));
        },
        toggleGroup(names) {
            if (this.allChecked(names)) {
                this.selected = this.selected.filter((name) => ! names.includes(name));
                return;
            }
            this.selected = [...new Set([...this.selected, ...names])];
        }
    }">
    <div class="roles-filter-field">
        <label class="roles-form-label" for="permission-filter">Filter permissions</label>
        <div class="roles-input-wrap">
            <svg class="roles-input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <input id="permission-filter" type="search" x-model="search" placeholder="View users, tasks.create" class="roles-permission-search">
        </div>
        <p class="roles-form-hint">Search by the permission name or its code.</p>
    </div>

    @foreach ($permissionGroups as $group => $permissions)
        <section class="roles-permission-group"
            x-show="{{ collect($permissions)->map(fn ($p) => 'visible('.json_encode($p['label']).', '.json_encode($p['name']).')')->implode(' || ') ?: 'true' }}">
            <div class="roles-group-head">
                <h3 class="roles-group-title">{{ $group }}</h3>
                <label class="roles-select-all">
                    <input type="checkbox"
                        class="roles-check"
                        :checked="allChecked({{ Js::from(array_column($permissions, 'name')) }})"
                        @change="toggleGroup({{ Js::from(array_column($permissions, 'name')) }})">
                    Select all
                </label>
            </div>
            <div class="roles-permission-grid">
                @foreach ($permissions as $permission)
                    <label class="roles-permission-item"
                        x-show="visible({{ json_encode($permission['label']) }}, {{ json_encode($permission['name']) }})">
                        <input type="checkbox" name="permissions[]" value="{{ $permission['name'] }}"
                            class="roles-check"
                            :checked="selected.includes({{ json_encode($permission['name']) }})"
                            @change="selected.includes({{ json_encode($permission['name']) }})
                                ? selected = selected.filter((name) => name !== {{ json_encode($permission['name']) }})
                                : selected.push({{ json_encode($permission['name']) }})">
                        <span class="roles-permission-copy">
                            <span class="roles-permission-label">{{ $permission['label'] }}</span>
                            <span class="roles-permission-code">{{ $permission['name'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
