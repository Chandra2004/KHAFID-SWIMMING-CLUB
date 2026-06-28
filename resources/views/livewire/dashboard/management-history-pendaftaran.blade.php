<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Registration;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $filterStatus = 'all'; // all, active, trashed
    public $filterEvent = '';

    // Modals
    public $showDetailModal = false;
    public $selectedRegistration = null;

    // Confirm Modal
    public $showConfirmModal = false;
    public $confirmAction = ''; // 'trash', 'restore', 'delete'
    public $confirmId = null;
    public $confirmTitle = '';
    public $confirmMessage = '';

    public function with()
    {
        $user = Auth::user();
        $canViewAll = $user->can('master-history-pendaftaran.view.all');
        $canViewSelf = $user->can('master-history-pendaftaran.view.self');
        
        $query = Registration::withTrashed()
            ->with(['user.profile', 'eventCategory.event', 'payment', 'result', 'schedule'])
            ->when(!$canViewAll && $canViewSelf, function($q) use ($user) {
                $q->where('user_uid', $user->uid);
            })
            ->when(!$canViewAll && !$canViewSelf, function($q) {
                $q->where('uid', '0'); // Show nothing if no view permission
            })
            ->when($this->filterStatus === 'trashed', function($q) {
                $q->onlyTrashed();
            })
            ->when($this->filterStatus === 'active', function($q) {
                $q->withoutTrashed();
            })
            ->when($this->filterEvent, function($q) {
                $q->whereHas('eventCategory', function($eq) {
                    $eq->where('event_uid', $this->filterEvent);
                });
            })
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('registration_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user.profile', function($up) {
                            $up->where('full_name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->latest('created_at');

        return [
            'registrations' => $query->paginate(15),
            'events' => Event::all(),
            'stats' => [
                'total' => Registration::withTrashed()->count(),
                'active' => Registration::count(),
                'trashed' => Registration::onlyTrashed()->count(),
            ]
        ];
    }

    public function openDetail($uid)
    {
        $this->authorize('master-history-pendaftaran.detail');
        $this->selectedRegistration = Registration::withTrashed()
            ->with(['user.profile', 'eventCategory.event', 'payment', 'result', 'schedule'])
            ->where('uid', $uid)
            ->firstOrFail();
        $this->showDetailModal = true;
    }

    public function confirmTrash($uid)
    {
        $this->confirmId = $uid;
        $this->confirmAction = 'trash';
        $this->confirmTitle = 'Pindahkan ke Sampah?';
        $this->confirmMessage = 'Data pendaftaran ini akan dipindahkan ke folder sampah untuk sementara.';
        $this->showConfirmModal = true;
    }

    public function confirmRestore($uid)
    {
        $this->confirmId = $uid;
        $this->confirmAction = 'restore';
        $this->confirmTitle = 'Pulihkan Data?';
        $this->confirmMessage = 'Apakah Anda yakin ingin mengembalikan pendaftaran ini menjadi aktif kembali?';
        $this->showConfirmModal = true;
    }

    public function confirmDelete($uid)
    {
        $this->confirmId = $uid;
        $this->confirmAction = 'delete';
        $this->confirmTitle = 'Hapus Permanen?';
        $this->confirmMessage = 'PERINGATAN: Tindakan ini tidak dapat dibatalkan. Data akan dihapus selamanya dari sistem.';
        $this->showConfirmModal = true;
    }

    public function executeConfirm()
    {
        if ($this->confirmAction === 'trash') {
            $this->authorize('master-history-pendaftaran.delete');
            Registration::where('uid', $this->confirmId)->delete();
            $msg = 'Pendaftaran berhasil dipindahkan ke sampah.';
        } elseif ($this->confirmAction === 'restore') {
            $this->authorize('master-history-pendaftaran.edit');
            Registration::onlyTrashed()->where('uid', $this->confirmId)->restore();
            $msg = 'Pendaftaran berhasil dipulihkan.';
        } elseif ($this->confirmAction === 'delete') {
            $this->authorize('master-history-pendaftaran.delete');
            Registration::withTrashed()->where('uid', $this->confirmId)->forceDelete();
            $msg = 'Data pendaftaran dihapus permanen.';
        }

        $this->dispatch('notification', [
            'status' => 'success',
            'message' => $msg
        ]);

        $this->showConfirmModal = false;
        $this->resetConfirm();
    }

    public function resetConfirm()
    {
        $this->confirmId = null;
        $this->confirmAction = '';
        $this->confirmTitle = '';
        $this->confirmMessage = '';
    }

    public function exportHistory()
    {
        $this->authorize('master-history-pendaftaran.export');
        $this->dispatch('notification', [
            'status' => 'info',
            'message' => 'Fitur ekspor CSV sedang disiapkan.'
        ]);
    }
}; ?>

<div class="p-6 md:p-10">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none">Arsip Histori pendaftaran</h2>
            <p class="text-sm text-slate-500 font-medium mt-2 uppercase tracking-widest italic">Log Seluruh Pendaftaran & Data Terhapus</p>
        </div>

        @can('master-history-pendaftaran.export')
            <button wire:click="exportHistory" 
                wire:loading.attr="disabled"
                class="flex items-center gap-3 bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-4 rounded-2xl font-black transition shadow-xl shadow-emerald-100 transform hover:-translate-y-1 uppercase text-xs tracking-widest disabled:opacity-50">
                <x-lucide-download wire:loading.remove wire:target="exportHistory" class="w-5 h-5" />
                <x-lucide-loader-2 wire:loading wire:target="exportHistory" class="w-5 h-5 animate-spin" />
                <span wire:loading.remove wire:target="exportHistory">Ekspor Data</span>
                <span wire:loading wire:target="exportHistory">Memproses...</span>
            </button>
        @endcan
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-slate-200 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Entri</p>
                <h3 class="text-4xl font-black">{{ $stats['total'] }}</h3>
            </div>
            <x-lucide-archive class="absolute -right-6 -bottom-6 w-32 h-32 text-white/5" />
        </div>
        <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Pendaftaran Aktif</p>
                <h3 class="text-3xl font-black text-blue-600">{{ $stats['active'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600">
                <x-lucide-check-circle class="w-6 h-6" />
            </div>
        </div>
        <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Item di Sampah</p>
                <h3 class="text-3xl font-black text-rose-500">{{ $stats['trashed'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500">
                <x-lucide-trash-2 class="w-6 h-6" />
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col md:flex-row gap-4 mb-8">
        <div class="flex-1 relative">
            <x-lucide-search class="w-5 h-5 absolute left-5 top-1/2 -translate-y-1/2 text-slate-300" />
            <input type="text" wire:model.live.debounce.300ms="search" 
                placeholder="Cari atlet atau nomor pendaftaran..."
                class="w-full pl-14 pr-6 py-4 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 outline-none transition shadow-sm uppercase">
        </div>
        <div class="w-full md:w-48 relative">
            <select wire:model.live="filterStatus" class="w-full pl-6 pr-12 py-4 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 outline-none transition appearance-none shadow-sm uppercase tracking-widest">
                <option value="all">Semua Data</option>
                <option value="active">Aktif Saja</option>
                <option value="trashed">Tempat Sampah</option>
            </select>
            <x-lucide-chevron-down class="w-4 h-4 absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none" />
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Pendaftaran</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Atlet</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Acara Lomba</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Status Data</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($registrations as $reg)
                        <tr class="hover:bg-slate-50/30 transition group {{ $reg->trashed() ? 'bg-rose-50/10' : '' }}">
                            <td class="px-8 py-6">
                                <span class="block text-sm font-black text-slate-900 uppercase tracking-tight">{{ $reg->registration_number }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">{{ $reg->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-slate-100 text-slate-500 rounded-2xl flex items-center justify-center font-black text-lg border border-white shadow-sm shrink-0">
                                        {{ substr($reg->user?->profile?->full_name ?: '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="block text-sm font-black text-slate-800 uppercase tracking-tight leading-none mb-1">{{ $reg->user?->profile?->full_name }}</span>
                                        <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest italic">{{ $reg->user?->profile?->club?->name ?: 'INDEPENDENT' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="block text-sm font-black text-slate-700 uppercase leading-none mb-1">{{ $reg->eventCategory?->acara_name }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic">{{ $reg->eventCategory?->event?->name }}</span>
                                @if($reg->schedule)
                                    <div class="mt-2 flex gap-2">
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[9px] font-black uppercase">Seri {{ $reg->schedule->heat_number }}</span>
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[9px] font-black uppercase">Lintas {{ $reg->schedule->lane_number }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-center">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'confirmed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'cancelled' => 'bg-slate-100 text-slate-500 border-slate-200',
                                        'rejected' => 'bg-rose-50 text-rose-600 border-rose-100',
                                    ];
                                    $statusLabel = [
                                        'pending' => 'MENUNGGU',
                                        'confirmed' => 'DISETUJUI',
                                        'cancelled' => 'DIBATALKAN',
                                        'rejected' => 'DITOLAK',
                                    ];
                                    $currentStatus = $reg->status;
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $statusClasses[$currentStatus] ?? 'bg-slate-50 text-slate-400 border-slate-100' }}">
                                    {{ $statusLabel[$currentStatus] ?? $currentStatus }}
                                </span>
                                @if($reg->trashed())
                                    <div class="mt-1">
                                        <span class="text-[8px] font-bold text-rose-400 uppercase italic tracking-tighter">Dalam Tempat Sampah</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center justify-center gap-2">
                                    @can('master-history-pendaftaran.detail')
                                        <button wire:click="openDetail('{{ $reg->uid }}')" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition" title="Lihat Detail">
                                            <x-lucide-eye class="w-5 h-5" />
                                        </button>
                                    @endcan

                                    @if($reg->status === 'confirmed')
                                        <a href="{{ route('dashboard.pendaftaran.print-bukti', $reg->uid) }}" target="_blank" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="Download Bukti">
                                            <x-lucide-download class="w-5 h-5" />
                                        </a>
                                    @endif

                                    @if($reg->trashed())
                                        @can('master-history-pendaftaran.edit')
                                            <button wire:click="confirmRestore('{{ $reg->uid }}')" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition" title="Pulihkan">
                                                <x-lucide-rotate-ccw class="w-5 h-5" />
                                            </button>
                                        @endcan
                                        @can('master-history-pendaftaran.delete')
                                            <button wire:click="confirmDelete('{{ $reg->uid }}')" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Hapus Permanen">
                                                <x-lucide-trash-2 class="w-5 h-5" />
                                            </button>
                                        @endcan
                                    @else
                                        @can('master-history-pendaftaran.delete')
                                            <button wire:click="confirmTrash('{{ $reg->uid }}')" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Pindahkan ke Sampah">
                                                <x-lucide-trash class="w-5 h-5" />
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <p class="text-xs font-black text-slate-300 uppercase tracking-widest">Tidak ada histori pendaftaran ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">
        {{ $registrations->links() }}
    </div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedRegistration)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showDetailModal', false)"></div>
            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-2xl relative z-50 border border-slate-100 max-h-[90vh] flex flex-col">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Rincian Log Pendaftaran</h3>
                    <button wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                    <div class="space-y-8">
                        <div class="flex items-center gap-6">
                            <div class="w-24 h-24 bg-slate-100 rounded-[2rem] flex items-center justify-center text-3xl font-black text-slate-400 border-4 border-white shadow-sm">
                                {{ substr($selectedRegistration->user?->profile?->full_name ?: '?', 0, 1) }}
                            </div>
                            <div>
                                <h4 class="text-2xl font-black text-slate-900 uppercase tracking-tight">{{ $selectedRegistration->user?->profile?->full_name }}</h4>
                                <p class="text-xs font-bold text-blue-600 uppercase tracking-widest italic">{{ $selectedRegistration->user?->profile?->club?->name ?: 'INDEPENDENT' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div class="bg-slate-50 p-6 rounded-3xl">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nomor Registrasi</p>
                                <p class="text-sm font-black text-slate-800">{{ $selectedRegistration->registration_number }}</p>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-3xl">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tanggal Input</p>
                                <p class="text-sm font-black text-slate-800">{{ $selectedRegistration->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-3xl col-span-2">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Acara Lomba</p>
                                <p class="text-sm font-black text-slate-800 uppercase">{{ $selectedRegistration->eventCategory?->acara_name }} ({{ $selectedRegistration->eventCategory?->event?->name }})</p>
                            </div>
                            @if($selectedRegistration->schedule)
                                <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100">
                                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-1">Nomor Seri (Heat)</p>
                                    <p class="text-lg font-black text-blue-600 uppercase">{{ $selectedRegistration->schedule->heat_number }}</p>
                                </div>
                                <div class="bg-slate-900 p-6 rounded-3xl">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nomor Lintasan (Lane)</p>
                                    <p class="text-lg font-black text-white uppercase">{{ $selectedRegistration->schedule->lane_number }}</p>
                                </div>
                            @else
                                <div class="bg-slate-50 p-6 rounded-3xl col-span-2 border border-dashed border-slate-200">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Jadwal Lintasan</p>
                                    <p class="text-xs font-bold text-slate-400 italic">Belum diatur oleh panitia</p>
                                </div>
                            @endif
                            <div class="bg-slate-50 p-6 rounded-3xl col-span-2">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status Pendaftaran</p>
                                <div class="flex items-center gap-3">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-amber-50 text-amber-600 border-amber-100',
                                            'confirmed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'cancelled' => 'bg-slate-100 text-slate-500 border-slate-200',
                                            'rejected' => 'bg-rose-50 text-rose-600 border-rose-100',
                                        ];
                                        $statusLabel = [
                                            'pending' => 'MENUNGGU VERIFIKASI',
                                            'confirmed' => 'DISETUJUI / AKTIF',
                                            'cancelled' => 'DIBATALKAN',
                                            'rejected' => 'DITOLAK PANITIA',
                                        ];
                                    @endphp
                                    <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border {{ $statusClasses[$selectedRegistration->status] ?? '' }}">
                                        {{ $statusLabel[$selectedRegistration->status] ?? $selectedRegistration->status }}
                                    </span>
                                    
                                    @if($selectedRegistration->trashed())
                                        <span class="px-4 py-1.5 bg-rose-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-rose-100">
                                            Data di Arsip Sampah
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if(in_array($selectedRegistration->status, ['rejected', 'cancelled']) && $selectedRegistration->notes)
                                <div class="col-span-2 bg-rose-50/50 border border-rose-100 p-6 rounded-3xl animate-in fade-in slide-in-from-top-2">
                                    <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1 flex items-center gap-2">
                                        <x-lucide-info class="w-3 h-3" /> Alasan / Catatan Panitia
                                    </p>
                                    <p class="text-sm font-bold text-rose-700 leading-relaxed italic">"{{ $selectedRegistration->notes }}"</p>
                                </div>
                            @endif

                            @if($selectedRegistration->status === 'confirmed')
                                @php
                                    $eventGroupLink = $selectedRegistration->eventCategory?->event?->group_link;
                                    $categoryGroupLink = $selectedRegistration->eventCategory?->group_link;
                                @endphp
                                
                                <div class="col-span-2 space-y-4 mt-4">
                                    @if($eventGroupLink)
                                        <div class="bg-blue-50 p-6 rounded-3xl border border-blue-100">
                                            <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-2">Grup Koordinasi Event Utama</p>
                                            <div class="flex items-center justify-between">
                                                <p class="text-xs font-bold text-slate-600 truncate mr-4">{{ $eventGroupLink }}</p>
                                                <a href="{{ $eventGroupLink }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition flex items-center gap-2 shrink-0">
                                                    <x-lucide-external-link class="w-3 h-3" />
                                                    Gabung Grup Event
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    @if($categoryGroupLink)
                                        <div class="bg-emerald-50 p-6 rounded-3xl border border-emerald-100">
                                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2">Grup Teknis Nomor Lomba</p>
                                            <div class="flex items-center justify-between">
                                                <p class="text-xs font-bold text-slate-600 truncate mr-4">{{ $categoryGroupLink }}</p>
                                                <a href="{{ $categoryGroupLink }}" target="_blank" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition flex items-center gap-2 shrink-0">
                                                    <x-lucide-external-link class="w-3 h-3" />
                                                    Gabung Grup Lomba
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="p-8 border-t border-slate-50 flex justify-end gap-3">
                    @if($selectedRegistration->status === 'confirmed')
                        <a href="{{ route('dashboard.pendaftaran.print-bukti', $selectedRegistration->uid) }}" target="_blank" class="px-8 py-4 bg-emerald-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition shadow-xl shadow-emerald-100 flex items-center gap-2">
                            <x-lucide-download class="w-4 h-4" />
                            Download Bukti
                        </a>
                    @endif
                    <button wire:click="$set('showDetailModal', false)" class="px-10 py-4 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition shadow-xl shadow-slate-200">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Custom Confirm Modal --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-md" wire:click="$set('showConfirmModal', false)"></div>
            <div class="bg-white rounded-[3rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-lg relative z-[110] border border-slate-100">
                <div class="p-10 text-center">
                    <div class="w-20 h-20 bg-rose-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                        @if($confirmAction === 'restore')
                            <x-lucide-rotate-ccw class="w-10 h-10 text-emerald-600" />
                        @else
                            <x-lucide-alert-triangle class="w-10 h-10 text-rose-600" />
                        @endif
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">{{ $confirmTitle }}</h3>
                    <p class="text-sm font-bold text-slate-400 leading-relaxed">{{ $confirmMessage }}</p>

                    <div class="grid grid-cols-2 gap-4 mt-10">
                        <button wire:click="$set('showConfirmModal', false)" 
                            class="px-8 py-4 bg-slate-100 text-slate-400 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button wire:click="executeConfirm" 
                            wire:loading.attr="disabled"
                            class="px-8 py-4 {{ $confirmAction === 'restore' ? 'bg-emerald-600 shadow-emerald-100' : 'bg-rose-600 shadow-rose-100' }} text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:scale-105 transition shadow-xl flex items-center justify-center gap-2 disabled:opacity-50">
                            <x-lucide-loader-2 wire:loading wire:target="executeConfirm" class="w-4 h-4 animate-spin" />
                            <span>Ya, Lanjutkan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
