import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('usersIndex', (directory = {}) => ({
        directory,
        menu: { id: null, top: 0, right: 0 },
        profileId: null,
        confirm: null,
        toast: null,
        row(id) {
            if (id === null || id === undefined) {
                return null;
            }

            return this.directory[id] ?? this.directory[String(id)] ?? null;
        },
        get profile() {
            return this.row(this.profileId);
        },
        get selected() {
            return this.row(this.menu.id);
        },
        get pending() {
            return this.confirm ? this.row(this.confirm.id) : null;
        },
        openMenu(id, event) {
            event.stopPropagation();
            const rect = event.currentTarget.getBoundingClientRect();
            this.menu = this.menu.id === id
                ? { id: null, top: 0, right: 0 }
                : { id, top: rect.bottom + 8, right: window.innerWidth - rect.right };
        },
        closeMenu() {
            this.menu = { id: null, top: 0, right: 0 };
        },
        openProfile(id) {
            this.closeMenu();
            this.profileId = id;
        },
        ask(id, type) {
            this.closeMenu();
            this.confirm = { id, type };
        },
        async toggleStatus(id) {
            const user = this.row(id);
            if (! user?.canToggle || user.busy) {
                return;
            }

            this.directory[id] = { ...user, busy: true };

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(user.toggleUrl, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token ?? '',
                    },
                });

                if (! response.ok) {
                    this.directory[id] = { ...this.row(id), busy: false };
                    this.showToast('Unable to update user status.');
                    return;
                }

                const data = await response.json();
                this.directory[id] = {
                    ...this.row(id),
                    isActive: data.is_active,
                    status: data.status,
                    busy: false,
                };
                this.showToast(data.message);
            } catch {
                this.directory[id] = { ...this.row(id), busy: false };
                this.showToast('Unable to update user status.');
            }
        },
        showToast(message) {
            this.toast = message;
            window.setTimeout(() => {
                if (this.toast === message) {
                    this.toast = null;
                }
            }, 2800);
        },
    }));
});

Alpine.start();
