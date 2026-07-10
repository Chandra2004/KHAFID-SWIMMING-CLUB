<?php

use Livewire\Volt\Component;
use App\Models\Event;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Helpers\ImageHelper;

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $filterStatus = '';

    // Form fields
    public $name = '';
    public $description = '';
    public $location = '';
    public $start_date = '';
    public $end_date = '';
    public $start_time = '';
    public $lane_count = 8;
    public $status = 'draft';
    public $group_link = '';
    public $banner;
    public $logo_left;
    public $logo_right;
    public $payment_method_uid = '';
    public $existingBanner;
    public $existingLogoLeft;
    public $existingLogoRight;

    public $editingEventId = null;
    public $eventToDelete = null;
    public $showModal = false;
    public $showDeleteModal = false;
    public $modalMode = 'create';

    public function with()
    {
        $eventsQuery = Event::with('categories');

        if ($this->search) {
            $eventsQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $eventsQuery->where('status', $this->filterStatus);
        }

        // Stats
        $stats = [
            'total' => Event::count(),
            'ongoing' => Event::where('status', 'ongoing')->count(),
            'upcoming' => Event::where('status', 'upcoming')->count(),
        ];

        return [
            'events' => $eventsQuery->latest()->paginate(12),
            'stats' => $stats,
            'financeAccounts' => \App\Models\FinanceAccount::where('is_active', true)->get()
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'description', 'location', 'start_date', 'end_date', 'start_time', 'lane_count', 'status', 'group_link', 'banner', 'logo_left', 'logo_right', 'existingBanner', 'existingLogoLeft', 'existingLogoRight', 'editingEventId', 'payment_method_uid']);
        $this->lane_count = 8;
        $this->status = 'draft';
        $this->modalMode = 'create';
        $this->showModal = true;
        $this->dispatch('modal-opened');
    }

    public function openEditModal($uid)
    {
        $event = Event::where('uid', $uid)->firstOrFail();
        $this->editingEventId = $event->uid;
        $this->name = $event->name;
        $this->description = $event->description;
        $this->location = $event->location;
        $this->start_date = $event->start_date?->format('Y-m-d');
        $this->end_date = $event->end_date?->format('Y-m-d');
        $this->start_time = $event->start_time;
        $this->lane_count = $event->lane_count;
        $this->status = $event->status;
        $this->group_link = $event->group_link;
        $this->existingBanner = $event->banner;
        $this->existingLogoLeft = $event->logo_left;
        $this->existingLogoRight = $event->logo_right;
        $this->payment_method_uid = $event->payment_method_uid;
        $this->modalMode = 'edit';
        $this->showModal = true;
        $this->dispatch('modal-opened');
    }

    public function save()
    {
        $this->authorize($this->modalMode === 'create' ? 'master-event.create' : 'master-event.edit');

        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'lane_count' => 'required|integer|min:1|max:20',
            'status' => 'required|in:draft,upcoming,ongoing,completed,cancelled',
            'group_link' => 'nullable|url|max:255',
            'payment_method_uid' => 'nullable|exists:finance_accounts,uid',
            'banner' => 'nullable|string',
            'logo_left' => 'nullable|string',
            'logo_right' => 'nullable|string',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'start_time' => $this->start_time ?: null,
            'lane_count' => $this->lane_count,
            'status' => $this->status,
            'payment_method_uid' => $this->payment_method_uid ?: null,
            'group_link' => $this->group_link ?: null,
            'author_uid' => auth()->user()->uid,
        ];

        if ($this->modalMode === 'create') {
            if ($this->banner) {
                $data['banner'] = ImageHelper::uploadToWebp($this->banner, 'events/banners');
            }
            if ($this->logo_left) {
                $data['logo_left'] = ImageHelper::uploadToWebp($this->logo_left, 'events/logos');
            }
            if ($this->logo_right) {
                $data['logo_right'] = ImageHelper::uploadToWebp($this->logo_right, 'events/logos');
            }
            Event::create($data);
            $message = 'Event berhasil ditambahkan';
        } else {
            $event = Event::where('uid', $this->editingEventId)->firstOrFail();

            if ($this->banner) {
                $data['banner'] = ImageHelper::uploadToWebp($this->banner, 'events/banners', $event->banner);
            }
            if ($this->logo_left) {
                $data['logo_left'] = ImageHelper::uploadToWebp($this->logo_left, 'events/logos', $event->logo_left);
            }
            if ($this->logo_right) {
                $data['logo_right'] = ImageHelper::uploadToWebp($this->logo_right, 'events/logos', $event->logo_right);
            }
            $event->update($data);
            $message = 'Event berhasil diperbarui';
        }

        $this->showModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => $message
        ]);
    }

    public function confirmDelete($uid)
    {
        $this->eventToDelete = Event::where('uid', $uid)->firstOrFail();
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->authorize('master-event.delete');
        $event = $this->eventToDelete;
        if ($event) {
            if ($event->banner) @unlink(public_path($event->banner));
            if ($event->logo_left) @unlink(public_path($event->logo_left));
            if ($event->logo_right) @unlink(public_path($event->logo_right));

            $event->delete();
            $this->showDeleteModal = false;
            $this->eventToDelete = null;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Event berhasil dihapus'
            ]);
        }
    }
}; ?>

<div class="p-4 md:p-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none">Master Event</h2>
            <p class="text-sm text-slate-500 font-medium mt-2 uppercase tracking-widest italic">Kelola kompetisi renang & agenda klub</p>
        </div>
        @can('master-event.create')
            <button wire:click="openCreateModal"
                class="flex items-center gap-3 bg-ksc-blue hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-black transition shadow-xl shadow-blue-100 transform hover:-translate-y-1 uppercase text-xs tracking-widest">
                <x-lucide-award class="w-5 h-5" />
                <span>Buat Event Baru</span>
            </button>
        @endcan
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-ksc-blue rounded-[2.5rem] p-8 text-white shadow-xl shadow-blue-100 relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-blue-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Event</p>
                <h3 class="text-4xl font-black">{{ $stats['total'] }}</h3>
            </div>
            <x-lucide-award class="absolute -right-6 -bottom-6 w-32 h-32 text-white/10" />
        </div>
        <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Sedang Berjalan</p>
                <h3 class="text-3xl font-black text-slate-900">{{ $stats['ongoing'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600">
                <x-lucide-activity class="w-6 h-6" />
            </div>
        </div>
        <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Akan Datang</p>
                <h3 class="text-3xl font-black text-slate-900">{{ $stats['upcoming'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600">
                <x-lucide-calendar class="w-6 h-6" />
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col md:flex-row gap-4 mb-8">
        <div class="flex-1 relative">
            <x-lucide-search class="w-5 h-5 absolute left-5 top-1/2 -translate-y-1/2 text-slate-300" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama event atau lokasi kegiatan..."
                class="w-full pl-14 pr-6 py-4 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition shadow-sm">
        </div>
        <div class="w-full md:w-64 relative">
            <select wire:model.live="filterStatus" class="w-full pl-6 pr-12 py-4 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition appearance-none shadow-sm uppercase tracking-widest">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="upcoming">Upcoming</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <x-lucide-chevron-down class="w-4 h-4 absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none" />
        </div>
    </div>

    {{-- Table Content --}}
    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Informasi Event</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Lokasi & Jadwal</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Kuota</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Status</th>
                        @canany(['master-event.edit', 'master-event.delete', 'master-lomba.view'])
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Aksi</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($events as $event)
                        <tr wire:key="event-{{ $event->uid }}" class="hover:bg-slate-50/50 transition group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center -space-x-4">
                                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 border border-slate-50 shrink-0 shadow-sm relative z-20">
                                            @if($event->logo_left)
                                                <img src="{{ asset($event->logo_left) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                    <x-lucide-award class="w-5 h-5" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 border border-slate-50 shrink-0 shadow-sm relative z-10">
                                            @if($event->logo_right)
                                                <img src="{{ asset($event->logo_right) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                    <x-lucide-award class="w-5 h-5" />
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 uppercase tracking-tight leading-tight">{{ $event->name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest italic">UUID: {{ substr($event->uid, 0, 8) }}...</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center gap-2">
                                        <x-lucide-map-pin class="w-3.5 h-3.5 text-slate-300" />
                                        <span class="text-xs font-bold text-slate-600 tracking-tight">{{ $event->location ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-lucide-calendar class="w-3.5 h-3.5 text-slate-300" />
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $event->start_date ? $event->start_date->format('d F Y') : 'TBA' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <div class="inline-flex flex-col">
                                    <span class="text-sm font-black text-slate-900">{{ $event->categories->sum(fn($cat) => (int)$event->lane_count * (int)$cat->total_series) }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Peserta</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border
                                    {{ $event->status === 'draft' ? 'bg-slate-50 text-slate-500 border-slate-100' : '' }}
                                    {{ $event->status === 'upcoming' ? 'bg-blue-50 text-blue-600 border-blue-100' : '' }}
                                    {{ $event->status === 'ongoing' ? 'bg-amber-50 text-amber-600 border-amber-100' : '' }}
                                    {{ $event->status === 'completed' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : '' }}
                                    {{ $event->status === 'cancelled' ? 'bg-rose-50 text-rose-600 border-rose-100' : '' }}">
                                    {{ $event->status }}
                                </span>
                            </td>
                            @canany(['master-event.edit', 'master-event.delete', 'master-lomba.view'])
                                <td class="px-8 py-6">
                                    <div class="flex justify-center gap-2">
                                        @can('master-lomba.view')
                                            <a href="{{ route('master.event.lomba', $event->uid) }}"
                                                class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition" title="Kelola Lomba">
                                                <x-lucide-swatch-book class="w-5 h-5" />
                                            </a>
                                        @endcan
                                        @can('master-event.edit')
                                            <button wire:click="openEditModal('{{ $event->uid }}')"
                                                class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition">
                                                <x-lucide-pencil class="w-5 h-5" />
                                            </button>
                                        @endcan
                                        @can('master-event.delete')
                                            <button wire:click="confirmDelete('{{ $event->uid }}')"
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
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-slate-50 rounded-[2.5rem] flex items-center justify-center mb-4">
                                        <x-lucide-calendar-x class="w-10 h-10 text-slate-200" />
                                    </div>
                                    <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest">Tidak ada event</h3>
                                    <p class="text-xs text-slate-300 font-bold mt-1">Gunakan tombol "Buat Event Baru" untuk memulai</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-12">
        {{ $events->links() }}
    </div>

    {{-- Modal Create/Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-4xl z-[2010] border border-slate-100 flex flex-col h-full max-h-[90vh]">
                {{-- Modal Header --}}
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">
                        {{ $modalMode === 'create' ? 'Tambah Event Baru' : 'Ubah Data Event' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <form @submit.prevent id="eventForm" class="p-8">
                        <div class="space-y-8 px-2">
                            {{-- Section 1: Dasar --}}
                            <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <x-lucide-award class="w-4 h-4 text-ksc-blue" />
                                    </div>
                                    <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Informasi Utama</h4>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Event</label>
                                        <input type="text" wire:model="name" placeholder="Masukan nama lengkap event..."
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                        @error('name') <span class="text-[10px] text-rose-500 font-bold ml-1 mt-1 block uppercase">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Lokasi</label>
                                        <div class="relative">
                                            <x-lucide-map-pin class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                                            <input type="text" wire:model="location" placeholder="Lokasi kolam renang..."
                                                class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                        </div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Link Group Koordinasi (WhatsApp/Telegram)</label>
                                        <div class="relative">
                                            <x-lucide-link class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                                            <input type="url" wire:model="group_link" placeholder="https://chat.whatsapp.com/..."
                                                class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                        </div>
                                        @error('group_link') <span class="text-[10px] text-rose-500 font-bold ml-1 mt-1 block uppercase">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Jumlah Lintasan</label>
                                        <input type="number" wire:model.live="lane_count"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                            {{-- Section 2: Jadwal & Status --}}
                            <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <x-lucide-calendar class="w-4 h-4 text-blue-600" />
                                    </div>
                                    <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Jadwal & Status</h4>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tanggal Mulai</label>
                                        <input type="date" wire:model="start_date"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tanggal Selesai</label>
                                        <input type="date" wire:model="end_date"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Jam Mulai</label>
                                        <input type="time" wire:model="start_time"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition text-center">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Status Publikasi</label>
                                        <div class="relative">
                                            <select wire:model="status"
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition appearance-none uppercase tracking-widest">
                                                <option value="draft">Draft</option>
                                                <option value="upcoming">Upcoming</option>
                                                <option value="ongoing">Ongoing</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                            <x-lucide-chevron-down class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section 3: Keuangan --}}
                            <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center">
                                        <x-lucide-landmark class="w-4 h-4 text-amber-600" />
                                    </div>
                                    <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Informasi Pembayaran (Bank)</h4>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Rekening Tujuan Transfer</label>
                                    <div class="relative">
                                        <select wire:model="payment_method_uid"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-100 outline-none transition appearance-none uppercase tracking-widest">
                                            <option value="">-- Tanpa Rekening (Gratis / Bayar Tunai) --</option>
                                            @foreach($financeAccounts as $acc)
                                                <option value="{{ $acc->uid }}">{{ $acc->bank_name }} - {{ $acc->account_number }} ({{ $acc->account_name }})</option>
                                            @endforeach
                                        </select>
                                        <x-lucide-chevron-down class="w-4 h-4 absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none" />
                                    </div>
                                    @error('payment_method_uid') <span class="text-[10px] text-rose-500 font-bold ml-1 mt-1 block uppercase">{{ $message }}</span> @enderror
                                    <p class="text-[9px] text-slate-400 font-bold mt-2 ml-1 uppercase italic tracking-widest leading-relaxed">* Semua lomba dalam event ini akan dibayarkan ke satu pintu rekening di atas.</p>
                                </div>
                            </div>

                            {{-- Section 4: Media --}}
                            <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-purple-100 rounded-xl flex items-center justify-center">
                                        <x-lucide-image class="w-4 h-4 text-purple-600" />
                                    </div>
                                    <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Media Event</h4>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Logo Event (Kiri & Kanan)</label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div wire:ignore class="relative group h-32 bg-white rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition hover:border-ksc-blue">
                                                <div class="absolute inset-0 w-full h-full z-0 pointer-events-none">
                                                    <img id="preview_me_logo_left" src="{{ $existingLogoLeft ? asset($existingLogoLeft) : '' }}" class="w-full h-full object-cover {{ $existingLogoLeft ? '' : 'hidden' }}">
                                                </div>
                                                <div id="placeholder_me_logo_left" class="text-center relative z-10 pointer-events-none {{ $existingLogoLeft ? 'hidden' : '' }}">
                                                    <x-lucide-award class="w-6 h-6 text-slate-300 group-hover:text-ksc-blue transition mb-2" />
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-ksc-blue transition">Logo Kiri</span>
                                                </div>
                                                <input type="file" id="me_logo_left" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-30" onchange="previewSingleImage(this, 'preview_me_logo_left', 'placeholder_me_logo_left'); readAndSetBase64(this, base64 => @this.set('logo_left', base64))">
                                            </div>
                                            <div wire:ignore class="relative group h-32 bg-white rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition hover:border-ksc-blue">
                                                <div class="absolute inset-0 w-full h-full z-0 pointer-events-none">
                                                    <img id="preview_me_logo_right" src="{{ $existingLogoRight ? asset($existingLogoRight) : '' }}" class="w-full h-full object-cover {{ $existingLogoRight ? '' : 'hidden' }}">
                                                </div>
                                                <div id="placeholder_me_logo_right" class="text-center relative z-10 pointer-events-none {{ $existingLogoRight ? 'hidden' : '' }}">
                                                    <x-lucide-award class="w-6 h-6 text-slate-300 group-hover:text-ksc-blue transition mb-2" />
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-ksc-blue transition">Logo Kanan</span>
                                                </div>
                                                <input type="file" id="me_logo_right" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-30" onchange="previewSingleImage(this, 'preview_me_logo_right', 'placeholder_me_logo_right'); readAndSetBase64(this, base64 => @this.set('logo_right', base64))">
                                            </div>
                                        </div>
                                        <div class="flex justify-between mt-1">
                                            @error('logo_left') <span class="text-[9px] text-rose-500 font-bold uppercase">{{ $message }}</span> @enderror
                                            @error('logo_right') <span class="text-[9px] text-rose-500 font-bold uppercase">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Banner Event</label>
                                        <div wire:ignore class="relative group h-32 bg-white rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition hover:border-ksc-blue">
                                            <div class="absolute inset-0 w-full h-full z-0 pointer-events-none">
                                                <img id="preview_me_banner" src="{{ $existingBanner ? asset($existingBanner) : '' }}" class="w-full h-full object-cover {{ $existingBanner ? '' : 'hidden' }}">
                                            </div>
                                            <div id="placeholder_me_banner" class="text-center relative z-10 pointer-events-none {{ $existingBanner ? 'hidden' : '' }}">
                                                <x-lucide-image class="w-6 h-6 text-slate-300 group-hover:text-ksc-blue transition mb-2" />
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-ksc-blue transition">Unggah Banner</span>
                                            </div>
                                            <input type="file" id="me_banner" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-30" onchange="previewSingleImage(this, 'preview_me_banner', 'placeholder_me_banner'); readAndSetBase64(this, base64 => @this.set('banner', base64))">
                                        </div>
                                        @error('banner') <span class="text-[9px] text-rose-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Section 4: Deskripsi --}}
                            <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-emerald-100 rounded-xl flex items-center justify-center">
                                        <x-lucide-file-text class="w-4 h-4 text-emerald-600" />
                                    </div>
                                    <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Deskripsi & Rincian</h4>
                                </div>
                                <div wire:ignore x-data="{
                                        init() {
                                            if (tinymce.get('tinymce-editor')) {
                                                tinymce.remove('#tinymce-editor');
                                            }

                                            // Watch for Livewire changes to update editor
                                            this.$watch('$wire.description', (value) => {
                                                const editor = tinymce.get('tinymce-editor');
                                                if (editor && value !== editor.getContent()) {
                                                    editor.setContent(value || '');
                                                }
                                            });

                                            tinymce.init({
                                                selector: '#tinymce-editor',
                                                height: 350,
                                                license_key: 'gpl',
                                                menubar: false,
                                                statusbar: false,
                                                plugins: 'lists link help wordcount',
                                                toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | removeformat | help',
                                                content_style: 'body { font-family: \'Plus Jakarta Sans\', sans-serif; font-size: 14px; }',
                                                setup: (editor) => {
                                                    editor.on('init', () => {
                                                        editor.setContent($wire.get('description') || '');
                                                    });
                                                    editor.on('blur', () => {
                                                        $wire.set('description', editor.getContent());
                                                    });
                                                }
                                            });
                                        }
                                    }">
                                    <textarea id="tinymce-editor" wire:model="description" class="w-full bg-white border border-slate-200 rounded-2xl shadow-sm outline-none p-4 min-h-48"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Modal Footer --}}
                <div class="p-8 border-t border-slate-50 flex items-center gap-4 bg-slate-50/30">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="flex-1 px-8 py-4 bg-white border border-slate-200 text-slate-500 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="button" id="meSubmitBtn"
                        @click="
                            const editor = tinymce.get('tinymce-editor');
                            if(editor) { $wire.description = editor.getContent(); }
                            
                            const btn = document.getElementById('meSubmitBtn');
                            const txtEl = document.getElementById('meSubmitText');
                            const loadEl = document.getElementById('meSubmitLoading');
                            
                            btn.disabled = true;
                            txtEl.classList.add('hidden');
                            loadEl.classList.remove('hidden');
                            
                            $wire.save().then(() => {
                                setTimeout(() => {
                                    btn.disabled = false;
                                    txtEl.classList.remove('hidden');
                                    loadEl.classList.add('hidden');
                                }, 1000);
                            }).catch(() => {
                                btn.disabled = false;
                                txtEl.classList.remove('hidden');
                                loadEl.classList.add('hidden');
                            });
                        "
                        class="flex-1 px-8 py-4 bg-ksc-blue text-white rounded-2xl font-black shadow-xl shadow-blue-100 uppercase text-[10px] tracking-widest hover:bg-blue-700 transition flex items-center justify-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span id="meSubmitText">
                            {{ $modalMode === 'create' ? 'Simpan Event' : 'Perbarui Data' }}
                        </span>
                        <span id="meSubmitLoading" class="hidden flex items-center gap-2">
                            <x-lucide-loader-2 class="w-4 h-4 animate-spin" />
                            <span>Memproses...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Delete Confirmation --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showDeleteModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-md z-[2010] border border-slate-100">
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-rose-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-rose-100">
                        <x-lucide-trash-2 class="w-10 h-10 text-rose-600" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Event?</h3>
                    <p class="text-slate-500 font-medium mb-10 px-10 leading-relaxed uppercase text-[10px] tracking-widest">Event <span class="text-rose-600 font-black">"{{ $eventToDelete?->name }}"</span> akan dihapus permanen dari sistem.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition uppercase text-[10px] tracking-widest flex-1">Batal</button>
                        <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                            class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2 min-w-[160px] uppercase text-[10px] tracking-widest flex-1">
                            <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                            <x-lucide-loader-2 wire:loading wire:target="delete" class="w-4 h-4 animate-spin" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
    <script>
        // Cleanup on modal close or navigation
        document.addEventListener('livewire:navigated', () => {
            if (tinymce.get('tinymce-editor')) {
                tinymce.remove('#tinymce-editor');
            }
        });
    </script>
    @endscript
</div>
