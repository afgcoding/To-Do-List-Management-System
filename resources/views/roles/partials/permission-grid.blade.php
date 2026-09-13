@props([
    'permissionGroups',
    'selected' => [],
])

@php
    $selected = collect($selected)->map(fn ($name) => (string) $name)->all();
@endphp

<div
    class="space-y-4"
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
    <input type="search" x-model="search" placeholder="Filter permissions..."
        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">

    @foreach ($permissionGroups as $group => $permissions)
        <section class="rounded-xl border border-slate-200 bg-slate-50/60 p-4"
            x-show="{{ collect($permissions)->map(fn ($p) => 'visible('.json_encode($p['label']).', '.json_encode($p['name']).')')->implode(' || ') ?: 'true' }}">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-semibold text-slate-800">{{ $group }}</h3>
                <label class="inline-flex items-center gap-2 text-xs font-medium text-indigo-600">
                    <input type="checkbox"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        :checked="allChecked({{ Js::from(array_column($permissions, 'name')) }})"
                        @change="toggleGroup({{ Js::from(array_column($permissions, 'name')) }})">
                    Select all
                </label>
            </div>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($permissions as $permission)
                    <label class="flex items-start gap-2 rounded-lg bg-white px-3 py-2 text-sm text-slate-700 shadow-sm"
                        x-show="visible({{ json_encode($permission['label']) }}, {{ json_encode($permission['name']) }})">
                        <input type="checkbox" name="permissions[]" value="{{ $permission['name'] }}"
                            class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            :checked="selected.includes({{ json_encode($permission['name']) }})"
                            @change="selected.includes({{ json_encode($permission['name']) }})
                                ? selected = selected.filter((name) => name !== {{ json_encode($permission['name']) }})
                                : selected.push({{ json_encode($permission['name']) }})">
                        <span>
                            <span class="block font-medium">{{ $permission['label'] }}</span>
                            <span class="block text-[11px] text-slate-400">{{ $permission['name'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
