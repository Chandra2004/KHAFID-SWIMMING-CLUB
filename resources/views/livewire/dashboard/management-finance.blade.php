<?php

use Livewire\Volt\Component;
use App\Models\FinanceAccount;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Helpers\ImageHelper;

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    public $search = '';

    // Form fields
    public $bank_name = '';
    public $account_number = '';
    public $account_name = '';
    public $description = '';
    public $is_active = true;
    public $image;
    public $existingImage;

    public $editingAccountId = null;
    public $accountToDelete = null;
    public $showModal = false;
    public $showDeleteModal = false;
    public $modalMode = 'create';

    public $indonesianBanks = ['BCA', 'Mandiri', 'BNI', 'BRI', 'BTN', 'BSI', 'CIMB Niaga', 'Bank Permata', 'Bank Danamon', 'Bank Mega', 'Bank Sinarmas', 'Bank OCBC NISP', 'Maybank', 'Bank Bukopin', 'Jenius', 'BTPN', 'QRIS', 'OVO', 'GoPay', 'Dana', 'LinkAja', 'ShopeePay', 'Cash'];

    public function with()
    {
        $accountsQuery = FinanceAccount::query();

        if ($this->search) {
            $accountsQuery
                ->where('bank_name', 'like', '%' . $this->search . '%')
                ->orWhere('account_number', 'like', '%' . $this->search . '%')
                ->orWhere('account_name', 'like', '%' . $this->search . '%');
        }

        return [
            'accounts' => $accountsQuery->latest()->paginate(10),
        ];
    }

    public function openCreateModal()
    {
        $this->reset(['bank_name', 'account_number', 'account_name', 'description', 'is_active', 'image', 'existingImage', 'editingAccountId']);
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal($uid)
    {
        $account = FinanceAccount::where('uid', $uid)->firstOrFail();
        $this->editingAccountId = $account->uid;
        $this->bank_name = $account->bank_name;
        $this->account_number = $account->account_number;
        $this->account_name = $account->account_name;
        $this->description = $account->description;
        $this->is_active = $account->is_active;
        $this->existingImage = $account->image;
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function save()
    {
        $this->authorize($this->modalMode === 'create' ? 'master-keuangan.create' : 'master-keuangan.edit');

        $this->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|string',
        ]);

        $data = [
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'account_name' => $this->account_name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->modalMode === 'create') {
            $data['uid'] = Str::uuid();
            if ($this->image) {
                $data['image'] = ImageHelper::uploadToWebp($this->image, 'finance');
            }
            FinanceAccount::create($data);
            $message = 'Akun keuangan berhasil ditambahkan';
        } else {
            $account = FinanceAccount::where('uid', $this->editingAccountId)->firstOrFail();
            if ($this->image) {
                $data['image'] = ImageHelper::uploadToWebp($this->image, 'finance', $account->image);
            }
            $account->update($data);
            $message = 'Akun keuangan berhasil diperbarui';
        }

        $this->showModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => $message,
        ]);
    }

    public function confirmDelete($uid)
    {
        $this->accountToDelete = FinanceAccount::where('uid', $uid)->firstOrFail();
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->authorize('master-keuangan.delete');
        $account = $this->accountToDelete;
        if ($account) {
            if ($account->image) {
                $oldPath = public_path($account->image);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $account->delete();
            $this->showDeleteModal = false;
            $this->accountToDelete = null;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Akun keuangan berhasil dihapus',
            ]);
        }
    }

    public function toggleStatus($uid)
    {
        $this->authorize('master-keuangan.edit');
        $account = FinanceAccount::where('uid', $uid)->first();
        if ($account) {
            $account->is_active = !$account->is_active;
            $account->save();
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Status akun berhasil diubah',
            ]);
        }
    }
}; ?>

<div class="p-4 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Master Keuangan</h2>
            <p class="text-sm text-slate-500 font-medium">Kelola akun bank dan metode pembayaran untuk pendaftaran event
            </p>
        </div>
        @can('master-keuangan.create')
            <button wire:click="openCreateModal"
                class="flex items-center gap-2 bg-ksc-blue hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition shadow-xl shadow-blue-100 transform hover:-translate-y-0.5">
                <x-lucide-wallet class="w-5 h-5" />
                <span>Tambah Akun</span>
            </button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="relative">
            <x-lucide-search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari bank, nomor, atau nama..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Bank /
                            Metode</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nomor
                            Rekening</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Atas Nama
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                            Image/QRIS</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status
                        </th>
                        @canany(['master-keuangan.edit', 'master-keuangan.delete'])
                            <th
                                class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">
                                Aksi</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($accounts as $account)
                        <tr wire:key="finance-{{ $account->uid }}" class="hover:bg-slate-50/50 transition group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                                        <x-lucide-landmark class="text-emerald-600 w-5 h-5" />
                                    </div>
                                    <span
                                        class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ $account->bank_name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-sm font-mono text-slate-600 tracking-wider">
                                {{ $account->account_number ?: '-' }}
                            </td>
                            <td class="px-8 py-6 text-xs font-bold text-slate-500 uppercase">
                                {{ $account->account_name ?: '-' }}
                            </td>
                            <td class="px-8 py-6">
                                @if ($account->image)
                                    <a href="{{ asset($account->image) }}" target="_blank"
                                        class="block w-12 h-12 bg-slate-50 rounded-lg overflow-hidden border border-slate-200 hover:scale-105 transition shadow-sm">
                                        <img src="{{ asset($account->image) }}" class="w-full h-full object-cover">
                                    </a>
                                @else
                                    <span class="text-[10px] text-slate-300 font-bold uppercase italic">No Image</span>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <button wire:click="toggleStatus('{{ $account->uid }}')"
                                    class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest transition-colors
                                    {{ $account->is_active
                                        ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                                        : 'bg-slate-100 text-slate-400 hover:bg-slate-200' }}">
                                    {{ $account->is_active ? 'Aktif' : 'Non-aktif' }}
                                </button>
                            </td>
                            @canany(['master-keuangan.edit', 'master-keuangan.delete'])
                                <td class="px-8 py-6">
                                    <div class="flex justify-center gap-2">
                                        @can('master-keuangan.edit')
                                            <button wire:click="openEditModal('{{ $account->uid }}')"
                                                wire:loading.attr="disabled" wire:target="openEditModal"
                                                class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition">
                                                <x-lucide-pencil class="w-5 h-5" />
                                            </button>
                                        @endcan
                                        @can('master-keuangan.delete')
                                            <button wire:click="confirmDelete('{{ $account->uid }}')"
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
                            <td colspan="6"
                                class="px-8 py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">
                                Tidak ada data akun keuangan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $accounts->links() }}
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-lg z-[2010] border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">
                        {{ $modalMode === 'create' ? 'Tambah Akun Baru' : 'Edit Akun Keuangan' }}
                    </h3>
                    <button wire:click="$set('showModal', false)"
                        class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <form x-data @submit.prevent="
                    $refs.mfSubmitBtn.disabled = true;
                    $refs.mfSubmitText.classList.add('hidden');
                    $refs.mfLoadingText.classList.remove('hidden');
                    @this.call('save').then(() => {
                        setTimeout(() => {
                            $refs.mfSubmitBtn.disabled = false;
                            $refs.mfSubmitText.classList.remove('hidden');
                            $refs.mfLoadingText.classList.add('hidden');
                        }, 1000);
                    }).catch(() => {
                        $refs.mfSubmitBtn.disabled = false;
                        $refs.mfSubmitText.classList.remove('hidden');
                        $refs.mfLoadingText.classList.add('hidden');
                    });
                " class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama
                                Bank / Metode / E-Wallet</label>
                            <div class="relative">
                                <select wire:model="bank_name"
                                    class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition appearance-none">
                                    <option value="">Pilih Bank / Metode</option>
                                    @foreach ($indonesianBanks as $bank)
                                        <option value="{{ $bank }}">{{ $bank }}</option>
                                    @endforeach
                                </select>
                                <x-lucide-chevron-down
                                    class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                            </div>
                            @error('bank_name')
                                <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nomor
                                Rekening / HP</label>
                            <input type="text" wire:model="account_number" placeholder="Nomor rekening"
                                class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition">
                            @error('account_number')
                                <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Atas
                                Nama</label>
                            <input type="text" wire:model="account_name" placeholder="Nama pemilik rekening"
                                class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition">
                            @error('account_name')
                                <span class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1 text-center">Gambar
                                / QRIS (Optional)</label>
                            <div class="flex items-center justify-center gap-6">
                                <div wire:ignore
                                    class="w-32 h-32 bg-slate-50 rounded-[2rem] overflow-hidden border-4 border-white shadow-xl flex items-center justify-center relative">
                                    <img id="preview_mf_image" src="{{ $existingImage ? asset($existingImage) : '' }}" class="w-full h-full object-cover {{ $existingImage ? '' : 'hidden' }}">
                                    <div id="placeholder_mf_image" class="{{ $existingImage ? 'hidden' : 'flex' }} items-center justify-center w-full h-full absolute inset-0">
                                        <x-lucide-qr-code class="w-10 h-10 text-slate-300" />
                                    </div>
                                </div>
                                <label
                                    class="bg-emerald-600 text-white px-6 py-3 rounded-2xl shadow-xl shadow-emerald-100 cursor-pointer hover:bg-emerald-700 transition font-bold text-xs">
                                    Upload QRIS/Image
                                    <input type="file" id="mf_image" class="hidden" accept="image/*" onchange="previewSingleImage(this, 'preview_mf_image', 'placeholder_mf_image'); readAndSetBase64(this, base64 => @this.set('image', base64))">
                                </label>
                            </div>
                            @error('image')
                                <span
                                    class="text-[10px] text-rose-500 font-bold text-center block mt-2 uppercase">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Keterangan
                                (Optional)</label>
                            <textarea wire:model="description" rows="2" placeholder="Informasi tambahan (Cabang, dll)..."
                                class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition resize-none"></textarea>
                        </div>

                        <div class="flex items-center gap-3 px-1">
                            <input type="checkbox" wire:model="is_active" id="is_active"
                                class="w-5 h-5 text-emerald-600 rounded-lg border-slate-200 focus:ring-emerald-500">
                            <label for="is_active"
                                class="text-xs font-black text-slate-500 uppercase tracking-widest">Akun Aktif</label>
                        </div>
                    </div>

                    <div class="flex items-center pt-8 mt-8 border-t border-slate-100 gap-4">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" x-ref="mfSubmitBtn"
                            class="flex-1 px-6 py-4 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 transition shadow-xl shadow-emerald-100 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                            <span x-ref="mfSubmitText">
                                {{ $modalMode === 'create' ? 'Simpan Data' : 'Perbarui Data' }}
                            </span>
                            <span x-ref="mfLoadingText" class="hidden flex items-center gap-2">
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
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Akun?</h3>
                    <p
                        class="text-slate-500 font-medium mb-10 px-10 leading-relaxed uppercase text-[10px] tracking-widest">
                        Akun <span class="text-rose-600 font-black">"{{ $accountToDelete?->bank_name }} -
                            {{ $accountToDelete?->account_name }}"</span>
                        akan dihapus permanen dari sistem.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition uppercase text-[10px] tracking-widest flex-1">Batal</button>
                        <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                            class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2 min-w-[160px] uppercase text-[10px] tracking-widest flex-1">
                            <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                            <span wire:loading wire:target="delete" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
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
