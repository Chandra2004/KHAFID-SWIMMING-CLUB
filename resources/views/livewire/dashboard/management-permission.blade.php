<?php

use Livewire\Volt\Component;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

new class extends Component {
    public $search = '';
    public $name = '';
    public $editingPermissionId = null;
    public $deletingPermissionId = null;

    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    public function with()
    {
        $permissionsQuery = Permission::query();

        if ($this->search) {
            $permissionsQuery->where('name', 'like', '%' . $this->search . '%');
        }

        return [
            'permissions' => $permissionsQuery->latest()->get(),
        ];
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'editingPermissionId']);
        $this->showCreateModal = true;
    }

    public function savePermission()
    {
        $this->authorize('permissions.create');

        $this->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        Permission::create([
            'uid' => Str::uuid(),
            'name' => strtolower($this->name),
            'guard_name' => 'web'
        ]);

        $this->showCreateModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Izin berhasil ditambahkan'
        ]);
    }

    public function openEditModal($id)
    {
        $permission = Permission::findOrFail($id);
        $this->editingPermissionId = $permission->id;
        $this->name = $permission->name;
        $this->showEditModal = true;
    }

    public function updatePermission()
    {
        $this->authorize('permissions.edit');

        $this->validate([
            'name' => 'required|string|unique:permissions,name,' . $this->editingPermissionId,
        ]);

        $permission = Permission::findOrFail($this->editingPermissionId);
        $permission->update([
            'name' => strtolower($this->name)
        ]);

        $this->showEditModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Izin berhasil diperbarui'
        ]);
    }

    public function openDeleteModal($id)
    {
        $this->deletingPermissionId = $id;
        $this->showDeleteModal = true;
    }

    public function deletePermission()
    {
        $this->authorize('permissions.delete');

        $permission = Permission::findOrFail($this->deletingPermissionId);
        $permission->delete();

        $this->showDeleteModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Izin berhasil dihapus'
        ]);
    }
}; ?>

<div class="p-4 md:p-8 overflow-y-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Manajemen Izin Akses</h2>
            <p class="text-sm text-slate-500 font-medium italic">Daftar seluruh hak akses (permissions) granular dalam sistem</p>
        </div>
        @can('permissions.create')
        <button wire:click="openCreateModal"
            class="flex items-center gap-2 bg-ksc-blue hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition shadow-xl shadow-blue-100 transform hover:-translate-y-0.5">
            <x-lucide-key class="w-5 h-5" />
            <span>Tambah Izin</span>
        </button>
        @endcan
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-blue-600 rounded-[2rem] p-8 text-white shadow-xl shadow-blue-100 relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-blue-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Izin</p>
                <h3 class="text-4xl font-black">{{ $permissions->count() }}</h3>
            </div>
            <x-lucide-shield-check class="absolute -right-6 -bottom-6 w-32 h-32 text-blue-500/20" />
        </div>
        <div class="bg-white border border-slate-100 rounded-[2rem] p-8 shadow-sm">
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Guard Aktif</p>
            <h3 class="text-3xl font-black text-slate-900">WEB</h3>
        </div>
        <div class="bg-white border border-slate-100 rounded-[2rem] p-8 shadow-sm">
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Status Sistem</p>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                <h3 class="text-lg font-black text-slate-900 uppercase">Secure</h3>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="relative">
            <x-lucide-search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama izin..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition">
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Izin</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kategori</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Guard</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($permissions as $permission)
                        <tr class="hover:bg-slate-50/50 transition group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center group-hover:bg-blue-50 transition">
                                        <x-lucide-lock class="text-slate-300 group-hover:text-ksc-blue w-5 h-5 transition" />
                                    </div>
                                    <code class="text-xs font-black text-ksc-blue tracking-tight">{{ $permission->name }}</code>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                @php
                                    $category = explode('.', $permission->name)[0] ?? 'general';
                                @endphp
                                <span class="text-xs font-bold text-slate-600 uppercase tracking-widest">{{ str_replace('_', ' ', $category) }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-400">
                                    {{ $permission->guard_name }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex justify-center gap-2">
                                    @can('permissions.edit')
                                    <button wire:click="openEditModal({{ $permission->id }})"
                                        class="w-10 h-10 flex items-center justify-center text-slate-300 hover:text-ksc-blue hover:bg-blue-50 rounded-xl transition">
                                        <x-lucide-pencil class="w-5 h-5" />
                                    </button>
                                    @endcan
                                    @can('permissions.delete')
                                    <button wire:click="openDeleteModal({{ $permission->id }})"
                                        class="w-10 h-10 flex items-center justify-center text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition">
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
                                    <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center mb-4">
                                        <x-lucide-shield-alert class="w-10 h-10 text-slate-200" />
                                    </div>
                                    <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest">Tidak ada data</h3>
                                    <p class="text-xs text-slate-300 font-bold mt-1">Coba sesuaikan pencarian Anda</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Create Permission --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showCreateModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-lg z-[2010] border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Tambah Izin Akses</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <form wire:submit.prevent="savePermission" class="p-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Izin</label>
                            <input type="text" wire:model="name" placeholder="Contoh: users.create"
                                class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            @error('name') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                            <p class="mt-3 text-[10px] text-slate-400 font-bold uppercase tracking-tight italic">* Gunakan dot notation (misal: users.edit)</p>
                        </div>
                    </div>

                    <div class="flex items-center pt-8 mt-8 border-t border-slate-100 gap-4">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                            class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 px-6 py-4 bg-ksc-blue text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-xl shadow-blue-100 flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="savePermission">Simpan Izin</span>
                            <span wire:loading wire:target="savePermission" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Memproses...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Edit Permission --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showEditModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-lg z-[2010] border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Ubah Izin: {{ $name }}</h3>
                    <button wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <form wire:submit.prevent="updatePermission" class="p-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Izin</label>
                            <input type="text" wire:model="name"
                                class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            @error('name') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center pt-8 mt-8 border-t border-slate-100 gap-4">
                        <button type="button" wire:click="$set('showEditModal', false)"
                            class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 px-6 py-4 bg-ksc-blue text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-xl shadow-blue-100 flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="updatePermission">Simpan Perubahan</span>
                            <span wire:loading wire:target="updatePermission" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Memproses...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Delete Permission --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showDeleteModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-md z-[2010] border border-slate-100">
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-rose-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-rose-100">
                        <x-lucide-key class="w-10 h-10 text-rose-600" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Izin?</h3>
                    <p class="text-slate-500 font-medium mb-10 px-10">Data izin akan dihapus permanen. Izin ini akan dicabut dari semua role yang memilikinya.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                        <button wire:click="deletePermission" wire:loading.attr="disabled"
                            class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2 min-w-[160px]">
                            <span wire:loading.remove wire:target="deletePermission">Ya, Hapus Data</span>
                            <span wire:loading wire:target="deletePermission" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
