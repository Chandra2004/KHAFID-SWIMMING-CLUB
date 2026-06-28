<?php

use Livewire\Volt\Component;
use App\Models\Club;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Helpers\ImageHelper;

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    public $search = '';

    // Form fields
    public $name = '';
    public $short_name = '';
    public $logo;
    public $existingLogo;
    public $coach_name = '';
    public $contact = '';
    public $address = '';
    public $website = '';

    public $editingClubId = null;
    public $clubToDelete = null;
    public $showModal = false;
    public $showDeleteModal = false;
    public $modalMode = 'create';

    public function with()
    {
        $clubsQuery = Club::query();

        if ($this->search) {
            $clubsQuery
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('short_name', 'like', '%' . $this->search . '%')
                ->orWhere('coach_name', 'like', '%' . $this->search . '%');
        }

        return [
            'clubs' => $clubsQuery->latest()->paginate(10),
        ];
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'short_name', 'logo', 'existingLogo', 'coach_name', 'contact', 'address', 'website', 'editingClubId']);
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal($uid)
    {
        $club = Club::where('uid', $uid)->firstOrFail();
        $this->editingClubId = $club->uid;
        $this->name = $club->name;
        $this->short_name = $club->short_name;
        $this->existingLogo = $club->logo;
        $this->coach_name = $club->coach_name;
        $this->contact = $club->contact;
        $this->address = $club->address;
        $this->website = $club->website;
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function save()
    {
        $this->authorize($this->modalMode === 'create' ? 'clubs.create' : 'clubs.edit');

        $this->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:5120',
            'coach_name' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
        ]);

        $data = [
            'name' => $this->name,
            'short_name' => $this->short_name,
            'coach_name' => $this->coach_name,
            'contact' => $this->contact,
            'address' => $this->address,
            'website' => $this->website,
        ];

        if ($this->modalMode === 'create') {
            $data['uid'] = Str::uuid();
            if ($this->logo) {
                $data['logo'] = ImageHelper::uploadToWebp($this->logo, 'clubs');
            }
            Club::create($data);
            $message = 'Klub berhasil ditambahkan';
        } else {
            $club = Club::where('uid', $this->editingClubId)->firstOrFail();
            if ($this->logo) {
                $data['logo'] = ImageHelper::uploadToWebp($this->logo, 'clubs', $club->logo);
            }
            $club->update($data);
            $message = 'Klub berhasil diperbarui';
        }

        $this->showModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => $message,
        ]);
    }

    public function confirmDelete($uid)
    {
        $this->clubToDelete = Club::where('uid', $uid)->firstOrFail();
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->authorize('clubs.delete');
        $club = $this->clubToDelete;
        if ($club) {
            // Optional: delete logo file
            if ($club->logo) {
                $oldPath = public_path($club->logo);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $club->delete();
            $this->showDeleteModal = false;
            $this->clubToDelete = null;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Klub berhasil dihapus',
            ]);
        }
    }
}; ?>

<div class="p-4 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Manajemen Klub</h2>
            <p class="text-sm text-slate-500 font-medium">Kelola data klub renang yang terdaftar dalam sistem</p>
        </div>
        @can('clubs.create')
            <button wire:click="openCreateModal"
                class="flex items-center gap-2 bg-ksc-blue hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition shadow-xl shadow-blue-100 transform hover:-translate-y-0.5">
                <x-lucide-plus-circle class="w-5 h-5" />
                <span>Tambah Klub</span>
            </button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="relative">
            <x-lucide-search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama klub atau pelatih..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Klub</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Pelatih
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kontak
                        </th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($clubs as $club)
                        <tr wire:key="club-{{ $club->uid }}" class="hover:bg-slate-50/50 transition group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    @if ($club->logo)
                                        <img src="{{ asset($club->logo) }}" alt="Logo"
                                            class="w-12 h-12 rounded-xl object-cover border border-slate-100 shadow-sm">
                                    @else
                                        <div
                                            class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center border border-slate-50">
                                            <x-lucide-building-2 class="text-slate-400 w-6 h-6" />
                                        </div>
                                    @endif
                                    <div>
                                        <p
                                            class="text-sm font-black text-slate-900 uppercase tracking-tight leading-none">
                                            {{ $club->name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase">
                                            {{ $club->short_name ?: '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2">
                                    <x-lucide-user class="w-4 h-4 text-slate-400" />
                                    <span
                                        class="text-xs font-bold text-slate-600">{{ $club->coach_name ?: 'Tidak ada pelatih' }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <x-lucide-phone class="w-3.5 h-3.5 text-slate-400" />
                                        <span
                                            class="text-[11px] font-medium text-slate-600">{{ $club->contact ?: '-' }}</span>
                                    </div>
                                    @if ($club->website)
                                        <div class="flex items-center gap-2">
                                            <x-lucide-globe class="w-3.5 h-3.5 text-slate-400" />
                                            <a href="{{ $club->website }}" target="_blank"
                                                class="text-[11px] font-bold text-ksc-blue hover:underline tracking-tight">{{ Str::limit($club->website, 20) }}</a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex justify-center gap-2">
                                    @can('clubs.edit')
                                        <button wire:click="openEditModal('{{ $club->uid }}')" wire:loading.attr="disabled" wire:target="openEditModal"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-ksc-blue hover:bg-blue-50 rounded-xl transition">
                                            <x-lucide-pencil class="w-5 h-5" />
                                        </button>
                                    @endcan
                                    @can('clubs.delete')
                                        <button wire:click="confirmDelete('{{ $club->uid }}')"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition">
                                            <x-lucide-trash-2 class="w-5 h-5" />
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4"
                                class="px-8 py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">
                                Tidak ada data klub ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $clubs->links() }}
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-2xl z-[2010] border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-ksc-blue rounded-2xl flex items-center justify-center shadow-lg shadow-blue-100">
                            <x-lucide-building-2 class="text-white w-6 h-6" />
                        </div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">
                            {{ $modalMode === 'create' ? 'Daftarkan Klub Baru' : 'Perbarui Informasi Klub' }}
                        </h3>
                    </div>
                    <button wire:click="$set('showModal', false)"
                        class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 flex justify-center mb-4">
                            <div class="relative group">
                                <div
                                    class="w-32 h-32 bg-slate-50 rounded-[2rem] overflow-hidden border-4 border-white shadow-xl flex items-center justify-center">
                                    @if ($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @elseif($existingLogo)
                                        <img src="{{ asset($existingLogo) }}" class="w-full h-full object-cover">
                                    @else
                                        <x-lucide-image class="w-10 h-10 text-slate-300" />
                                    @endif
                                </div>
                                <label
                                    class="absolute -bottom-2 -right-2 bg-ksc-blue text-white p-3 rounded-2xl shadow-xl cursor-pointer hover:bg-blue-700 transition">
                                    <x-lucide-camera class="w-5 h-5" />
                                    <input type="file" wire:model="logo" class="hidden" accept="image/*" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Nama
                                Lengkap Klub</label>
                            <input type="text" wire:model="name" placeholder="Contoh: Delta Swimming Club"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            @error('name')
                                <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Singkatan
                                / Short Name</label>
                            <input type="text" wire:model="short_name" placeholder="Contoh: DELTA SC"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            @error('short_name')
                                <span
                                    class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Nama
                                Pelatih Kepala</label>
                            <input type="text" wire:model="coach_name" placeholder="Nama Pelatih"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            @error('coach_name')
                                <span
                                    class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">No.
                                Kontak / WA</label>
                            <input type="text" wire:model="contact" placeholder="08xxxxxxxxxx"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            @error('contact')
                                <span
                                    class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Website
                                (Optional)</label>
                            <input type="text" wire:model="website" placeholder="https://example.com"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition">
                            @error('website')
                                <span
                                    class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Alamat
                                Lengkap</label>
                            <textarea wire:model="address" rows="3" placeholder="Alamat sekretariat klub..."
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition resize-none"></textarea>
                            @error('address')
                                <span
                                    class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center pt-8 mt-8 border-t border-slate-100 gap-4">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="flex-1 px-6 py-4 bg-ksc-blue text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-xl shadow-blue-100 flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="save">
                                {{ $modalMode === 'create' ? 'Simpan Klub' : 'Perbarui Informasi' }}
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
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Klub?</h3>
                    <p class="text-slate-500 font-medium mb-10 px-10 leading-relaxed uppercase text-[10px] tracking-widest text-center">Klub <span
                            class="text-rose-600 font-black">"{{ $clubToDelete?->name }}"</span>
                        akan dihapus permanen dari sistem.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition uppercase text-[10px] tracking-widest flex-1">Batal</button>
                        <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                            class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2 min-w-[160px] uppercase text-[10px] tracking-widest flex-1">
                            <span wire:loading.remove wire:target="delete">Ya, Hapus Klub</span>
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
