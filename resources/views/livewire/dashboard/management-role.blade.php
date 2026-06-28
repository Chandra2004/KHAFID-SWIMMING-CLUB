<?php

use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

new class extends Component {
    public $search = '';
    public $name = '';
    public $selectedPermissions = [];
    public $editingRoleId = null;
    public $deletingRoleId = null;

    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    public function with()
    {
        $rolesQuery = Role::with('permissions');

        if ($this->search) {
            $rolesQuery->where('name', 'like', '%' . $this->search . '%');
        }

        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0] ?? 'Lainnya';
        });

        return [
            'roles' => $rolesQuery->latest()->get(),
            'groupedPermissions' => $permissions,
        ];
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'selectedPermissions', 'editingRoleId']);
        $this->showCreateModal = true;
    }

    public function saveRole()
    {
        $this->authorize('roles.create');

        $this->validate([
            'name' => 'required|string|unique:roles,name',
            'selectedPermissions' => 'array',
        ]);

        $role = Role::create([
            'uid' => Str::uuid(),
            'name' => strtolower($this->name),
            'guard_name' => 'web',
        ]);

        if (!empty($this->selectedPermissions)) {
            $role->syncPermissions($this->selectedPermissions);
        }

        $this->showCreateModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Role berhasil ditambahkan',
        ]);
    }

    public function openEditModal($id)
    {
        $role = Role::findOrFail($id);
        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showEditModal = true;
    }

    public function togglePermission($permissionName)
    {
        if ($this->editingRoleId) {
            $this->authorize('roles.edit');
            $role = Role::findOrFail($this->editingRoleId);

            if ($role->hasPermissionTo($permissionName)) {
                $role->revokePermissionTo($permissionName);
            } else {
                $role->givePermissionTo($permissionName);
            }
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
            $this->dispatch('notification', ['status' => 'success', 'message' => 'Izin berhasil diperbarui']);
        } else {
            // Mode Create: Hanya update array lokal
            if (in_array($permissionName, $this->selectedPermissions)) {
                $this->selectedPermissions = array_diff($this->selectedPermissions, [$permissionName]);
            } else {
                $this->selectedPermissions[] = $permissionName;
            }
        }
    }

    public function toggleGroupPermissions($group, $select = true)
    {
        $permissions = Permission::where('name', 'like', $group . '.%')
            ->pluck('name')
            ->toArray();

        if ($this->editingRoleId) {
            $this->authorize('roles.edit');
            $role = Role::findOrFail($this->editingRoleId);

            if ($select) {
                $role->givePermissionTo($permissions);
            } else {
                $role->revokePermissionTo($permissions);
            }

            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
            $this->dispatch('notification', ['status' => 'success', 'message' => 'Grup izin berhasil diperbarui']);
        } else {
            // Mode Create: Hanya update array lokal
            if ($select) {
                $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $permissions));
            } else {
                $this->selectedPermissions = array_diff($this->selectedPermissions, $permissions);
            }
        }
    }

    public function updateRole()
    {
        $this->authorize('roles.edit');

        $this->validate([
            'name' => 'required|string|unique:roles,name,' . $this->editingRoleId,
            'selectedPermissions' => 'array',
        ]);

        $role = Role::findOrFail($this->editingRoleId);
        $role->update([
            'name' => strtolower($this->name),
        ]);

        $role->syncPermissions($this->selectedPermissions);

        $this->showEditModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Role berhasil diperbarui',
        ]);
    }

    public function openDeleteModal($id)
    {
        $this->deletingRoleId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteRole()
    {
        $this->authorize('roles.delete');

        $role = Role::findOrFail($this->deletingRoleId);

        // Prevent deleting admin role if necessary, but for now just delete
        $role->delete();

        $this->showDeleteModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Role berhasil dihapus',
        ]);
    }
}; ?>

<div class="p-4 md:p-8 overflow-y-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Manajemen Hak Akses</h2>
            <p class="text-sm text-slate-500 font-medium italic">Kelola peran (roles) dan pembatasan izin akses sistem
            </p>
        </div>
        @can('roles.create')
            <button wire:click="openCreateModal"
                class="flex items-center gap-2 bg-ksc-blue hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition shadow-xl shadow-blue-100 transform hover:-translate-y-0.5">
                <x-lucide-shield-plus class="w-5 h-5" />
                <span>Tambah Role</span>
            </button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="relative">
            <x-lucide-search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama role..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition">
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm">
        <div class="overflow-x-visible pb-64">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Role
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Guard
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Izin
                            Akses</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($roles as $role)
                        <tr class="hover:bg-slate-50/50 transition group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                                        <x-lucide-shield class="text-ksc-blue w-5 h-5" />
                                    </div>
                                    <span
                                        class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $role->name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span
                                    class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-500">
                                    {{ $role->guard_name }}
                                </span>
                            </td>
                            <td class="px-8 py-6 overflow-visible">
                                <div class="flex flex-wrap gap-1.5 items-center max-w-md">
                                    @php
                                        $perms = $role->permissions;
                                        $firstLimit = 3;
                                        $displayPerms = $perms->take($firstLimit);
                                        $remainingCount = $perms->count() - $firstLimit;
                                    @endphp

                                    @foreach ($displayPerms as $permission)
                                        <span
                                            class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-blue-50 text-ksc-blue border border-blue-100 uppercase tracking-tight">
                                            {{ str_replace('.', ' ', $permission->name) }}
                                        </span>
                                    @endforeach

                                    @if ($remainingCount > 0)
                                        <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false"
                                            class="relative inline-block">
                                            <button
                                                class="px-2 py-0.5 rounded-md text-[9px] font-black bg-slate-900 text-white border border-slate-900 uppercase tracking-tight hover:bg-slate-700 transition cursor-help whitespace-nowrap">
                                                +{{ $remainingCount }} Lainnya
                                            </button>

                                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 -translate-y-1"
                                                x-transition:enter-end="opacity-100 translate-y-0" x-cloak
                                                class="absolute z-[9999] top-full left-1/2 -translate-x-1/2 mt-3 w-80 p-5 bg-white rounded-3xl shadow-2xl border border-slate-100">
                                                <div class="max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                                                    @php
                                                        $grouped = $perms->groupBy(function ($p) {
                                                            return explode('.', $p->name)[0] ?? 'Lainnya';
                                                        });
                                                    @endphp

                                                    @foreach ($grouped as $category => $items)
                                                        <div class="mb-3 last:mb-0">
                                                            <p
                                                                class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 border-b border-slate-50 pb-1">
                                                                {{ $category }}</p>
                                                            <div class="flex flex-wrap gap-1">
                                                                @foreach ($items as $item)
                                                                    <span
                                                                        class="text-[8px] font-bold text-slate-600 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100 uppercase">
                                                                        {{ str_replace($category . '.', '', $item->name) }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                {{-- Tooltip arrow --}}
                                                <div
                                                    class="absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-white border-t border-l border-slate-100 rotate-45">
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($perms->isEmpty())
                                        <span
                                            class="text-[10px] text-slate-300 font-bold italic uppercase tracking-widest">Tidak
                                            ada izin</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex justify-center gap-2">
                                    @can('roles.edit')
                                        <button wire:click="openEditModal({{ $role->id }})"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-ksc-blue hover:bg-blue-50 rounded-xl transition">
                                            <x-lucide-pencil class="w-5 h-5" />
                                        </button>
                                    @endcan
                                    @can('roles.delete')
                                        <button wire:click="openDeleteModal({{ $role->id }})"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition">
                                            <x-lucide-trash-2 class="w-5 h-5" />
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center mb-4">
                                        <x-lucide-shield-off class="w-10 h-10 text-slate-200" />
                                    </div>
                                    <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest">Tidak ada
                                        data</h3>
                                    <p class="text-xs text-slate-300 font-bold mt-1">Coba sesuaikan pencarian Anda</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Create Role --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showCreateModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-3xl z-[2010] border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Tambah Role Baru</h3>
                    <button wire:click="$set('showCreateModal', false)"
                        class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <form wire:submit.prevent="saveRole" class="p-8">
                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama
                                Role</label>
                            <input type="text" wire:model="name" placeholder="Contoh: editor_konten"
                                class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            @error('name')
                                <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div x-data="{
                            searchTerm: '',
                        }">
                            <div class="flex justify-between items-center mb-4 ml-1">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Pilih
                                    Izin Akses</label>
                                <div class="relative w-48">
                                    <x-lucide-search
                                        class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input type="text" x-model="searchTerm" placeholder="Cari izin..."
                                        class="w-full pl-8 pr-3 py-1.5 bg-slate-100 border-none rounded-xl text-[10px] font-bold focus:ring-2 focus:ring-blue-100 outline-none transition">
                                </div>
                            </div>

                            <div
                                class="space-y-4 max-h-[50vh] overflow-y-auto p-4 bg-slate-50 rounded-3xl border border-slate-100 custom-scrollbar">
                                @foreach ($groupedPermissions as $group => $permissions)
                                    <div x-show="searchTerm === '' || '{{ strtolower($group) }}'.includes(searchTerm.toLowerCase()) || {{ json_encode($permissions->pluck('name')) }}.some(p => p.toLowerCase().includes(searchTerm.toLowerCase()))"
                                        class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                                        <div
                                            class="flex justify-between items-center mb-3 pb-2 border-b border-slate-50">
                                            <h5 class="text-[10px] font-black text-ksc-blue uppercase tracking-widest">
                                                {{ str_replace('_', ' ', $group) }}</h5>
                                            <div class="flex gap-2">
                                                <button type="button"
                                                    wire:click="toggleGroupPermissions('{{ $group }}', true)"
                                                    class="text-[8px] font-black text-emerald-600 hover:text-emerald-700 uppercase tracking-tighter">Pilih
                                                    Semua</button>
                                                <span class="text-slate-200">|</span>
                                                <button type="button"
                                                    wire:click="toggleGroupPermissions('{{ $group }}', false)"
                                                    class="text-[8px] font-black text-rose-500 hover:text-rose-600 uppercase tracking-tighter">Hapus
                                                    Semua</button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach ($permissions as $permission)
                                                <div wire:key="create-role-perm-{{ $permission->id }}"
                                                    x-show="searchTerm === '' || '{{ strtolower($permission->name) }}'.includes(searchTerm.toLowerCase())"
                                                    class="flex items-center justify-between p-3 bg-slate-50/50 rounded-xl border border-slate-50 hover:bg-white hover:border-slate-200 transition-all group">
                                                    <div class="flex flex-col">
                                                        <span
                                                            class="text-[10px] font-black text-slate-700 uppercase tracking-tight">{{ Str::headline(Str::after($permission->name, $group . '.')) }}</span>
                                                        <span
                                                            class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ $permission->name }}</span>
                                                    </div>

                                                    {{-- Toggle Switch --}}
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox"
                                                            wire:click="togglePermission('{{ $permission->name }}')"
                                                            {{ in_array($permission->name, $this->selectedPermissions) ? 'checked' : '' }}
                                                            class="sr-only peer">
                                                        <div
                                                            class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-ksc-blue">
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center pt-8 mt-8 border-t border-slate-100 gap-4">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                            class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 px-6 py-4 bg-ksc-blue text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-xl shadow-blue-100 flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="saveRole">Simpan Role</span>
                            <span wire:loading wire:target="saveRole" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>Memproses...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Edit Role --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showEditModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-3xl z-[2010] border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Ubah Role:
                        {{ $name }}</h3>
                    <button wire:click="$set('showEditModal', false)"
                        class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <form wire:submit.prevent="updateRole" class="p-8">
                    @if (auth()->user()->hasRole($name))
                        <div class="bg-amber-50 rounded-2xl p-4 mb-6 border border-amber-100 flex items-start gap-3">
                            <x-lucide-alert-triangle class="w-5 h-5 text-amber-600 mt-0.5" />
                            <div>
                                <p class="text-[11px] text-amber-900 font-black uppercase tracking-tight">Peringatan
                                    Keamanan!</p>
                                <p
                                    class="text-[10px] text-amber-700 font-bold leading-relaxed uppercase tracking-tight">
                                    Anda sedang mengedit Role yang saat ini Anda gunakan. Berhati-hatilah saat menghapus
                                    izin akses agar tidak mengunci diri Anda sendiri keluar dari sistem.
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama
                                Role</label>
                            <input type="text" wire:model="name"
                                class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            @error('name')
                                <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div x-data="{ searchTerm: '' }">
                            <div class="flex justify-between items-center mb-4 ml-1">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Kelola
                                    Izin Akses (Auto-Save)</label>
                                <div class="relative w-48">
                                    <x-lucide-search
                                        class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input type="text" x-model="searchTerm" placeholder="Cari izin..."
                                        class="w-full pl-8 pr-3 py-1.5 bg-slate-100 border-none rounded-xl text-[10px] font-bold focus:ring-2 focus:ring-blue-100 outline-none transition">
                                </div>
                            </div>

                            <div
                                class="space-y-4 max-h-[50vh] overflow-y-auto p-4 bg-slate-50 rounded-3xl border border-slate-100 custom-scrollbar">
                                @foreach ($groupedPermissions as $group => $permissions)
                                    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm mb-4">
                                        <div
                                            class="flex justify-between items-center mb-4 pb-2 border-b border-slate-50">
                                            <h5
                                                class="text-[10px] font-black text-ksc-blue uppercase tracking-widest flex items-center gap-2">
                                                <x-lucide-folder class="w-3 h-3" />
                                                {{ str_replace('_', ' ', $group) }}
                                            </h5>
                                            <div class="flex gap-2">
                                                <button type="button"
                                                    wire:click="toggleGroupPermissions('{{ $group }}', true)"
                                                    class="text-[8px] font-black text-emerald-600 hover:text-emerald-700 uppercase tracking-tighter">Pilih
                                                    Semua</button>
                                                <span class="text-slate-200">|</span>
                                                <button type="button"
                                                    wire:click="toggleGroupPermissions('{{ $group }}', false)"
                                                    class="text-[8px] font-black text-rose-500 hover:text-rose-600 uppercase tracking-tighter">Hapus
                                                    Semua</button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 gap-3">
                                            @foreach ($permissions as $permission)
                                                <div wire:key="edit-role-perm-{{ $permission->id }}"
                                                    class="flex items-center justify-between p-3 bg-slate-50/50 rounded-xl border border-slate-50 hover:bg-white hover:border-slate-200 transition-all group">
                                                    <div class="flex flex-col">
                                                        <span
                                                            class="text-[10px] font-black text-slate-700 uppercase tracking-tight">{{ Str::headline(Str::after($permission->name, $group . '.')) }}</span>
                                                        <span
                                                            class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ $permission->name }}</span>
                                                    </div>

                                                    {{-- Toggle Switch --}}
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox"
                                                            wire:click="togglePermission('{{ $permission->name }}')"
                                                            {{ in_array($permission->name, $this->selectedPermissions) ? 'checked' : '' }}
                                                            class="sr-only peer">
                                                        <div
                                                            class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-ksc-blue">
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center pt-8 mt-8 border-t border-slate-100 gap-4">
                        <button type="button" wire:click="$set('showEditModal', false)"
                            class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 px-6 py-4 bg-ksc-blue text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-xl shadow-blue-100 flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="updateRole">Simpan Perubahan</span>
                            <span wire:loading wire:target="updateRole" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>Memproses...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Delete Role --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showDeleteModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-lg z-[2010] border border-slate-100">
                <div class="p-12 text-center">
                    <div
                        class="w-20 h-20 bg-rose-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-rose-100">
                        <x-lucide-shield-off class="w-10 h-10 text-rose-600" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Role?</h3>
                    <p class="text-slate-500 font-medium mb-10 px-10">Data role akan dihapus permanen. Pengguna dengan
                        role ini akan kehilangan aksesnya.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                        <button wire:click="deleteRole" wire:loading.attr="disabled"
                            class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2 min-w-[160px]">
                            <span wire:loading.remove wire:target="deleteRole">Ya, Hapus Data</span>
                            <span wire:loading wire:target="deleteRole" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>Memproses...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
