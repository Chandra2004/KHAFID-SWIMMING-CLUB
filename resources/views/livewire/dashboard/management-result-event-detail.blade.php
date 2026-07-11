<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Registration;
use App\Models\Event;
use App\Models\Result;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public $event_uid;
    public $search = '';
    public $statusFilter = ''; 

    // Modals
    public $showDetailModal = false;
    public $selectedRegistration = null;
    public $showFormModal = false;
    public $editingUid = null;
    public $showConfirmModal = false;
    public $confirmId = null;

    // Form fields
    public $form_status = 'FINISH'; 
    public $form_final_time = '';
    public $time_minutes = '';
    public $time_seconds = '';
    public $time_milliseconds = '';
    public $form_notes = '';

    public function mount($event_uid)
    {
        $this->event_uid = $event_uid;
    }

    public function with()
    {
        $user = Auth::user();
        $canViewAll = $user->can('master-result.detail');
        $canViewTeam = $user->can('master-result.detail.team');
        $canViewSelf = $user->can('master-result.detail.self');
        $isAdmin = $user->hasAnyRole(['superadmin', 'admin']);
        $selectedEvent = Event::where('uid', $this->event_uid)->firstOrFail();
        
        $query = Registration::withTrashed()
            ->with(['user.profile', 'eventCategory.event', 'payment', 'result'])
            ->where('registrations.status', 'confirmed')
            ->whereHas('eventCategory', function($q) {
                $q->where('event_uid', $this->event_uid);
            })
            ->when(!$canViewAll && $canViewTeam, function ($q) use ($user) {
                $q->whereHas('user.profile', function ($u) use ($user) {
                    $u->where('club_uid', $user->profile?->club_uid);
                });
            })
            ->when(!$canViewAll && !$canViewTeam && $canViewSelf, function ($q) use ($user) {
                $q->where('registrations.user_uid', $user->uid);
            })
            ->when(!$canViewAll && !$canViewTeam && !$canViewSelf, function ($q) {
                $q->where('registrations.uid', 'nothing'); // Deny all if no permission
            })
            ->when($this->search, function ($q) {
                $q->where(function($sub) {
                    $sub->where('registrations.registration_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user.profile', function ($u) {
                            $u->where('full_name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->whereHas('result', function($qr) {
                    $qr->where('status', $this->statusFilter);
                });
            })
            ->leftJoin('results', 'registrations.uid', '=', 'results.registration_uid')
            ->select('registrations.*')
            ->orderBy('registrations.event_category_uid')
            ->orderByRaw('results.status = "FINISH" DESC') 
            ->orderBy('results.total_milliseconds', 'asc') 
            ->latest('registrations.created_at')
            ->get();

        $groupedRegistrations = $query->groupBy('event_category_uid');

        return [
            'groupedRegistrations' => $groupedRegistrations,
            'selectedEvent' => $selectedEvent,
            'isAdmin' => $isAdmin
        ];
    }

    public function openDetailModal($uid)
    {
        $reg = Registration::withTrashed()->where('uid', $uid)->firstOrFail();
        
        $canViewAll = Auth::user()->can('master-result.detail');
        $canViewTeam = Auth::user()->can('master-result.detail.team');
        $canViewSelf = Auth::user()->can('master-result.detail.self');

        $isOwnResult = $reg->user_uid === Auth::user()->uid;
        $isTeamResult = Auth::user()->profile && $reg->user?->profile && ($reg->user->profile->club_uid === Auth::user()->profile->club_uid);

        if ($canViewAll || ($canViewTeam && $isTeamResult) || ($canViewSelf && $isOwnResult)) {
            $this->selectedRegistration = Registration::withTrashed()
                ->with(['user.profile', 'eventCategory.event', 'payment', 'result'])
                ->where('uid', $uid)
                ->firstOrFail();
            $this->showDetailModal = true;
        } else {
            abort(403);
        }
    }

    public function openEditModal($uid)
    {
        $this->authorize('master-result.detail.edit');
        $reg = Registration::withTrashed()->with('result')->where('uid', $uid)->firstOrFail();
        $this->editingUid = $uid;
        $this->form_notes = $reg->notes;
        
        if ($reg->result) {
            $this->form_final_time = $reg->result->final_time;
            $this->form_status = $reg->result->status ?: 'FINISH';
            
            if ($this->form_final_time) {
                $parts = explode(':', $this->form_final_time);
                if (count($parts) === 2) {
                    $this->time_minutes = $parts[0];
                    $secParts = explode('.', $parts[1]);
                    if (count($secParts) === 2) {
                        $this->time_seconds = $secParts[0];
                        $this->time_milliseconds = $secParts[1];
                    } else {
                        $this->time_seconds = $parts[1];
                        $this->time_milliseconds = '00';
                    }
                } else {
                    $this->time_minutes = '00';
                    $this->time_seconds = '00';
                    $this->time_milliseconds = '00';
                }
            } else {
                $this->time_minutes = '';
                $this->time_seconds = '';
                $this->time_milliseconds = '';
            }
        } else {
            $this->form_final_time = '';
            $this->time_minutes = '';
            $this->time_seconds = '';
            $this->time_milliseconds = '';
            $this->form_status = 'FINISH';
        }

        $this->showFormModal = true;
    }

    public function saveRegistration()
    {
        $this->authorize('master-result.detail.edit');

        if ($this->form_status === 'FINISH') {
            $this->validate([
                'time_minutes' => 'required|integer|min:0|max:99',
                'time_seconds' => 'required|integer|min:0|max:59',
                'time_milliseconds' => 'required|integer|min:0|max:99',
            ], [
                'time_minutes.required' => 'Menit wajib diisi',
                'time_minutes.integer' => 'Menit harus berupa angka',
                'time_seconds.required' => 'Detik wajib diisi',
                'time_seconds.integer' => 'Detik harus berupa angka',
                'time_seconds.max' => 'Detik maksimal 59',
                'time_milliseconds.required' => 'Milidetik wajib diisi',
                'time_milliseconds.integer' => 'Milidetik harus berupa angka',
            ]);

            $mins = str_pad((int)$this->time_minutes, 2, '0', STR_PAD_LEFT);
            $secs = str_pad((int)$this->time_seconds, 2, '0', STR_PAD_LEFT);
            $milis = str_pad((int)$this->time_milliseconds, 2, '0', STR_PAD_LEFT);
            $this->form_final_time = "{$mins}:{$secs}.{$milis}";
        } else {
            $this->form_final_time = null;
        }

        $reg = Registration::withTrashed()->where('uid', $this->editingUid)->firstOrFail();
        $totalMs = ($this->form_status === 'FINISH' && $this->form_final_time) ? $this->calculateMilliseconds($this->form_final_time) : null;

        Result::updateOrCreate(
            ['registration_uid' => $reg->uid],
            [
                'final_time' => $this->form_status === 'FINISH' ? $this->form_final_time : null,
                'total_milliseconds' => $totalMs,
                'status' => $this->form_status,
                'official_name' => Auth::user()->username,
            ]
        );

        $this->updateCategoryRanks($reg->event_category_uid);

        $this->dispatch('notification', ['status' => 'success', 'message' => 'Hasil disimpan.']);
        $this->showFormModal = false;
    }

    private function calculateMilliseconds($time)
    {
        try {
            $parts = explode(':', $time);
            $mins = (int)$parts[0];
            $secsParts = explode('.', $parts[1]);
            $secs = (int)$secsParts[0];
            $ms = (int)$secsParts[1];
            return ($mins * 60 * 1000) + ($secs * 1000) + ($ms * 10);
        } catch (\Exception $e) { return 0; }
    }

    private function updateCategoryRanks($categoryUid)
    {
        $results = Result::whereHas('registration', function($q) use ($categoryUid) {
                $q->where('event_category_uid', $categoryUid);
            })
            ->where('status', 'FINISH')
            ->whereNotNull('total_milliseconds')
            ->orderBy('total_milliseconds', 'asc')
            ->get();

        $rank = 1;
        foreach ($results as $res) {
            $res->update(['rank' => $rank++]);
        }

        Result::whereHas('registration', function($q) use ($categoryUid) {
                $q->where('event_category_uid', $categoryUid);
            })
            ->where('status', '!=', 'FINISH')
            ->update(['rank' => null]);
    }

    public function confirmDeleteResult($uid)
    {
        $this->confirmId = $uid;
        $this->showConfirmModal = true;
    }

    public function executeConfirm()
    {
        $this->authorize('master-result.detail.delete');
        $reg = Registration::withTrashed()->where('uid', $this->confirmId)->first();
        if ($reg && $reg->result) {
            $catUid = $reg->event_category_uid;
            $reg->result->forceDelete();
            $this->updateCategoryRanks($catUid);
            $this->dispatch('notification', ['status' => 'success', 'message' => 'Hasil dihapus.']);
        }
        $this->showConfirmModal = false;
    }
}; ?>

<div class="p-6 md:p-10 bg-white min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
        <div class="flex items-center gap-6">
            <a href="{{ route('dashboard.result-event') }}" class="w-12 h-12 flex items-center justify-center bg-slate-100 text-slate-400 hover:bg-slate-900 hover:text-white rounded-2xl transition-all shadow-sm">
                <x-lucide-arrow-left class="w-6 h-6" />
            </a>
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none">{{ $selectedEvent->name }}</h2>
                <p class="text-sm text-slate-500 font-medium mt-2 uppercase tracking-widest italic">Input Waktu & Auto-Ranking Berdasarkan Kategori</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col md:flex-row gap-4 mb-10">
        <div class="flex-1 relative">
            <x-lucide-search class="w-5 h-5 absolute left-5 top-1/2 -translate-y-1/2 text-slate-300" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari atlet..." class="w-full pl-14 pr-6 py-4 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 outline-none transition shadow-sm uppercase tracking-tight">
        </div>
        <div class="w-full md:w-56 relative">
            <select wire:model.live="statusFilter" class="w-full pl-6 pr-12 py-4 bg-white border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 outline-none appearance-none shadow-sm uppercase tracking-widest">
                <option value="">Status Hasil</option>
                <option value="FINISH">Hadir (Finish)</option>
                <option value="DNS">Tidak Hadir (DNS)</option>
                <option value="DQ">Diskualifikasi (DQ)</option>
            </select>
            <x-lucide-chevron-down class="w-4 h-4 absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none" />
        </div>
    </div>

    {{-- Grouped Content --}}
    <div class="space-y-16">
        @forelse($groupedRegistrations as $catUid => $registrations)
            @php $firstReg = $registrations->first(); @endphp
            <div class="space-y-6">
                <div class="flex items-center gap-4 px-2">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-100"><x-lucide-award class="w-5 h-5" /></div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ $firstReg->eventCategory?->acara_name }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">{{ $firstReg->eventCategory?->type }} • {{ $registrations->count() }} PESERTA</p>
                    </div>
                </div>

                <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Peserta</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Waktu</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Juara</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Status</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($registrations as $reg)
                                    <tr class="hover:bg-slate-50/30 transition group">
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
                                        <td class="px-8 py-6 text-center">
                                            <span class="text-lg font-black text-blue-600 uppercase tracking-tighter">
                                                {{ ($reg->result && $reg->result->status === 'FINISH') ? $reg->result->final_time : '-' }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            @if($reg->result && $reg->result->rank)
                                                <span class="text-2xl font-black {{ $reg->result->rank <= 3 ? 'text-amber-500' : 'text-slate-400' }}">{{ $reg->result->rank }}</span>
                                            @else
                                                <span class="text-xs text-slate-300 font-black">-</span>
                                            @endif
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            @php $rStatus = $reg->result?->status ?: 'PENDING'; @endphp
                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border
                                                {{ $rStatus === 'FINISH' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : '' }}
                                                {{ $rStatus === 'DNS' ? 'bg-slate-100 text-slate-400 border-slate-200' : '' }}
                                                {{ $rStatus === 'DQ' ? 'bg-rose-50 text-rose-600 border-rose-100' : '' }}
                                                {{ $rStatus === 'PENDING' ? 'bg-amber-50 text-amber-500 border-amber-100' : '' }}">
                                                {{ $rStatus === 'FINISH' ? 'Hadir' : ($rStatus === 'DNS' ? 'Tidak Hadir' : ($rStatus === 'DQ' ? 'Diskualifikasi' : 'Belum Input')) }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex items-center justify-center gap-2">
                                                @canAny(['master-result.detail', 'master-result.detail.self', 'master-result.detail.team'])
                                                    <button wire:click="openDetailModal('{{ $reg->uid }}')" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition"><x-lucide-eye class="w-5 h-5" /></button>
                                                @endcanAny
                                                @can('master-result.detail.edit')
                                                    <button wire:click="openEditModal('{{ $reg->uid }}')" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition"><x-lucide-timer class="w-5 h-5" /></button>
                                                @endcan
                                                @if($reg->result)
                                                    @can('master-result.detail.delete')
                                                        <button wire:click="confirmDeleteResult('{{ $reg->uid }}')" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition"><x-lucide-trash-2 class="w-5 h-5" /></button>
                                                    @endcan
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-20 text-center text-slate-300 font-black uppercase text-xs tracking-widest">Data tidak ditemukan</div>
        @endforelse
    </div>

    {{-- DETAIL MODAL --}}
    @if($showDetailModal && $selectedRegistration)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showDetailModal', false)"></div>
            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-2xl relative z-50 border border-slate-100 max-h-[90vh] flex flex-col">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Rincian Hasil Pertandingan</h3>
                    <button wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-slate-600 transition"><x-lucide-x class="w-6 h-6" /></button>
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
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Acara Lomba</p>
                                <p class="text-sm font-black text-slate-800 uppercase">{{ $selectedRegistration->eventCategory?->acara_name }}</p>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-3xl text-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Waktu Kecepatan</p>
                                <p class="text-2xl font-black text-blue-600 uppercase">{{ $selectedRegistration->result?->final_time ?: '-' }}</p>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-3xl text-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Juara / Peringkat</p>
                                <p class="text-2xl font-black text-amber-500 uppercase">{{ $selectedRegistration->result?->rank ?: '-' }}</p>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-3xl">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</p>
                                <span class="text-xs font-black uppercase tracking-widest">{{ $selectedRegistration->result?->status ?: 'PENDING' }}</span>
                            </div>
                            <div class="col-span-2 bg-blue-50/50 p-6 rounded-3xl flex items-center gap-4 border border-blue-100/50">
                                <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center shrink-0 shadow-lg shadow-blue-100">
                                    <x-lucide-user-check class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest">Petugas Verifikasi (Official)</p>
                                    <p class="text-xs font-black text-blue-900 uppercase tracking-tight italic">{{ $selectedRegistration->result?->official_name ?: 'Belum Ada' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-8 border-t border-slate-50 flex justify-end">
                    <button wire:click="$set('showDetailModal', false)" class="px-10 py-4 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition shadow-xl shadow-slate-200">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    {{-- FORM MODAL --}}
    @if($showFormModal)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showFormModal', false)"></div>
            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-xl relative z-50 border border-slate-100 flex flex-col">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Input Hasil Pertandingan</h3>
                    <button wire:click="$set('showFormModal', false)" class="text-slate-400 hover:text-slate-600 transition"><x-lucide-x class="w-6 h-6" /></button>
                </div>
                <div class="p-8">
                    <form wire:submit.prevent="saveRegistration" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Status Keikutsertaan</label>
                            <select wire:model.live="form_status" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-slate-100 outline-none appearance-none uppercase tracking-tight">
                                <option value="FINISH">HADIR (FINISH)</option>
                                <option value="DNS">TIDAK HADIR (DNS)</option>
                                <option value="DQ">DISKUALIFIKASI (DQ)</option>
                            </select>
                        </div>
                        @if($form_status === 'FINISH')
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Waktu Kecepatan</label>
                                <div class="grid grid-cols-3 gap-3 items-center">
                                    <div class="relative">
                                        <input type="number" min="0" max="99" placeholder="00" wire:model="time_minutes" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-center text-xl font-black text-blue-600 focus:ring-4 focus:ring-slate-100 outline-none transition">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 font-black text-slate-400">:</span>
                                        <span class="block text-[9px] text-center text-slate-400 uppercase font-bold mt-1">Menit</span>
                                    </div>
                                    <div class="relative">
                                        <input type="number" min="0" max="59" placeholder="00" wire:model="time_seconds" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-center text-xl font-black text-blue-600 focus:ring-4 focus:ring-slate-100 outline-none transition">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 font-black text-slate-400">.</span>
                                        <span class="block text-[9px] text-center text-slate-400 uppercase font-bold mt-1">Detik</span>
                                    </div>
                                    <div>
                                        <input type="number" min="0" max="99" placeholder="00" wire:model="time_milliseconds" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-center text-xl font-black text-blue-600 focus:ring-4 focus:ring-slate-100 outline-none transition">
                                        <span class="block text-[9px] text-center text-slate-400 uppercase font-bold mt-1">Mili</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-3 mt-1 px-1">
                                    <div>@error('time_minutes') <p class="text-rose-500 text-[9px] font-bold uppercase tracking-widest">{{ $message }}</p> @enderror</div>
                                    <div>@error('time_seconds') <p class="text-rose-500 text-[9px] font-bold uppercase tracking-widest">{{ $message }}</p> @enderror</div>
                                    <div>@error('time_milliseconds') <p class="text-rose-500 text-[9px] font-bold uppercase tracking-widest">{{ $message }}</p> @enderror</div>
                                </div>
                            </div>
                        @endif
                        <div class="pt-6 border-t border-slate-50 flex justify-end gap-4">
                            <button type="button" wire:click="$set('showFormModal', false)" class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition">Batal</button>
                            <button type="submit" wire:loading.attr="disabled" class="px-10 py-4 bg-slate-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 transition shadow-xl shadow-slate-200 flex items-center gap-2 disabled:opacity-50">
                                <x-lucide-loader-2 wire:loading wire:target="saveRegistration" class="w-4 h-4 animate-spin" />
                                <span>Simpan Hasil</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- CONFIRM MODAL --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-md" wire:click="$set('showConfirmModal', false)"></div>
            <div class="bg-white rounded-[3rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-lg relative z-[110] border border-slate-100">
                <div class="p-10 text-center">
                    <div class="w-20 h-20 bg-rose-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6"><x-lucide-alert-triangle class="w-10 h-10 text-rose-600" /></div>
                    <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-2">Reset Hasil Lomba?</h3>
                    <p class="text-sm font-bold text-slate-400 leading-relaxed">Data waktu dan peringkat akan dihapus permanen.</p>
                    <div class="grid grid-cols-2 gap-4 mt-10">
                        <button wire:click="$set('showConfirmModal', false)" class="px-8 py-4 bg-slate-100 text-slate-400 rounded-2xl text-xs font-black uppercase tracking-widest">Batal</button>
                        <button wire:click="executeConfirm" class="px-8 py-4 bg-rose-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl">Hapus Hasil</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
