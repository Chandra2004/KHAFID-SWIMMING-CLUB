<?php

use Livewire\Volt\Component;
use App\Models\SwimmingStyle;
use Illuminate\Support\Str;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $name = '';
    public $code = '';
    public $editingStyleId = null;
    public $styleToDelete = null;
    public $showModal = false;
    public $showDeleteModal = false;
    public $modalMode = 'create'; // 'create' or 'edit'

    public function with()
    {
        $stylesQuery = SwimmingStyle::query();

        if ($this->search) {
            $stylesQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
        }

        return [
            'styles' => $stylesQuery->latest()->paginate(10),
        ];
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'code', 'editingStyleId']);
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal($uid)
    {
        $style = SwimmingStyle::where('uid', $uid)->firstOrFail();
        $this->editingStyleId = $style->uid;
        $this->name = $style->name;
        $this->code = $style->code;
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function save()
    {
        $this->authorize($this->modalMode === 'create' ? 'master-gaya.create' : 'master-gaya.edit');

        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        if ($this->modalMode === 'create') {
            SwimmingStyle::create([
                'name' => $this->name,
                'code' => $this->code,
                'slug' => Str::slug($this->name . '-' . Str::random(5)),
            ]);
            $message = 'Gaya renang berhasil ditambahkan';
        } else {
            $style = SwimmingStyle::where('uid', $this->editingStyleId)->firstOrFail();
            $style->update([
                'name' => $this->name,
                'code' => $this->code,
                'slug' => Str::slug($this->name . '-' . Str::random(2)),
            ]);
            $message = 'Gaya renang berhasil diperbarui';
        }

        $this->showModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => $message
        ]);
    }

    public function confirmDelete($uid)
    {
        $this->styleToDelete = SwimmingStyle::where('uid', $uid)->firstOrFail();
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->authorize('master-gaya.delete');

        $style = $this->styleToDelete;
        if ($style) {
            $style->delete();
            $this->showDeleteModal = false;
            $this->styleToDelete = null;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Gaya renang berhasil dihapus'
            ]);
        }
    }
}; ?>

<div class="p-4 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Master Gaya Renang</h2>
            <p class="text-sm text-slate-500 font-medium">Definisikan jenis gaya renang yang akan diperlombakan</p>
        </div>
        @can('master-gaya.create')
            <button wire:click="openCreateModal"
                class="flex items-center gap-2 bg-ksc-blue hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition shadow-xl shadow-blue-100 transform hover:-translate-y-0.5">
                <x-lucide-award class="w-5 h-5" />
                <span>Tambah Gaya</span>
            </button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="relative">
            <x-lucide-search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari gaya atau kode..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Gaya</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kode</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Slug</th>
                        @canany(['master-gaya.edit', 'master-gaya.delete'])
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Aksi</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($styles as $style)
                        <tr wire:key="style-{{ $style->uid }}" class="hover:bg-slate-50/50 transition group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                                        <x-lucide-waves class="text-ksc-blue w-5 h-5" />
                                    </div>
                                    <span class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $style->name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-lg font-black text-[10px] uppercase tracking-widest border border-slate-200">
                                    {{ $style->code ?: '-' }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-[11px] font-medium text-slate-400 italic">{{ $style->slug }}</span>
                            </td>
                            @canany(['master-gaya.edit', 'master-gaya.delete'])
                                <td class="px-8 py-6">
                                    <div class="flex justify-center gap-2">
                                        @can('master-gaya.edit')
                                            <button wire:click="openEditModal('{{ $style->uid }}')" wire:loading.attr="disabled" wire:target="openEditModal"
                                                class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-ksc-blue hover:bg-blue-50 rounded-xl transition">
                                                <x-lucide-pencil class="w-5 h-5" />
                                            </button>
                                        @endcan
                                        @can('master-gaya.delete')
                                            <button wire:click="confirmDelete('{{ $style->uid }}')"
                                                class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition">
                                                <x-lucide-trash-2 class="w-5 h-5" />
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">
                                Tidak ada data gaya renang
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $styles->links() }}
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-2xl z-[2010] border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">
                        {{ $modalMode === 'create' ? 'Tambah Gaya Baru' : 'Edit Gaya Renang' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Gaya</label>
                            <input type="text" wire:model="name" placeholder="Contoh: Gaya Bebas"
                                class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            @error('name') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Kode Gaya (Optional)</label>
                            <input type="text" wire:model="code" placeholder="Contoh: FREE"
                                class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition text-center">
                            @error('code') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center pt-8 mt-8 border-t border-slate-100 gap-4">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="flex-1 px-6 py-4 bg-ksc-blue text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-xl shadow-blue-100 flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="save">
                                {{ $modalMode === 'create' ? 'Simpan Data' : 'Perbarui Data' }}
                            </span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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

    {{-- Modal Delete Confirmation --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showDeleteModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-md z-[2010] border border-slate-100">
                <div class="p-12 text-center">
                    <div
                        class="w-20 h-20 bg-rose-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-rose-100">
                        <x-lucide-alert-circle class="w-10 h-10 text-rose-600" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Gaya?</h3>
                    <p class="text-slate-500 font-medium mb-10 px-10 leading-relaxed uppercase text-[10px] tracking-widest">Gaya <span
                            class="text-rose-600 font-black">"{{ $styleToDelete?->name }}"</span>
                        akan dihapus permanen dari sistem.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition uppercase text-[10px] tracking-widest flex-1">Batal</button>
                        <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                            class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2 min-w-[160px] uppercase text-[10px] tracking-widest flex-1">
                            <span wire:loading.remove wire:target="delete">Ya, Hapus Gaya</span>
                            <span wire:loading wire:target="delete" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
