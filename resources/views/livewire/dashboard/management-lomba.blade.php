<?php

use Livewire\Volt\Component;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Category;
use App\Models\RequirementParameter;
use App\Models\CategoryRequirement;
use App\Models\FinanceAccount;
use Illuminate\Support\Str;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $event; // Can be null if viewing all lomba
    public $search = '';

    // Lomba Form fields
    public $event_uid = '';
    public $category_uid = '';
    public $acara_number = '';
    public $acara_name = '';
    public $parameter_uid = ''; // Parameter Utama
    public $operator = '='; // Operator Parameter Utama
    public $parameter_value = ''; // Nilai Parameter Utama
    public $main_requirement = ''; // Teks Dokumen
    public $type = 'paid';
    public $registration_fee = 0;
    public $total_series = 1;
    public $start_date = '';
    public $end_date = '';
    public $start_time = '';
    public $end_time = '';
    public $location = '';
    public $group_link = '';

    // Requirement Form fields (Supporting)
    public $selectedLombaUid = null;
    public $supp_parameter_uid = '';
    public $supp_operator = '=';
    public $supp_parameter_value = '';
    public $supp_is_required = true;
    public $editingRequirementUid = null;
    public $reqModalMode = 'create';

    public $editingLombaId = null;
    public $lombaToDelete = null;
    public $showLombaModal = false;
    public $showRequirementModal = false;
    public $showDeleteModal = false;
    public $modalMode = 'create';

    public function mount($event = null)
    {
        if ($event instanceof Event) {
            $this->event = $event;
            $this->event_uid = $event->uid;
        } else {
            $this->event = null;
        }
    }

    public function with()
    {
        $lombaQuery = EventCategory::with('event');

        if ($this->event && $this->event->exists) {
            $lombaQuery->where('event_uid', $this->event->uid);
        }

        if ($this->search) {
            $lombaQuery->where(function ($q) {
                $q->where('acara_name', 'like', '%' . $this->search . '%')->orWhere('acara_number', 'like', '%' . $this->search . '%');
            });
        }

        $selectedParam = RequirementParameter::where('uid', $this->supp_parameter_uid)->first();
        $availableOperators = $selectedParam ? $selectedParam->allowed_operators : ['='];

        $mainSelectedParam = RequirementParameter::where('uid', $this->parameter_uid)->first();
        $mainAvailableOperators = $mainSelectedParam ? $mainSelectedParam->allowed_operators : ['='];

        // Stats calculation
        $statsQuery = EventCategory::query();
        if ($this->event && $this->event->exists) {
            $statsQuery->where('event_uid', $this->event->uid);
        }

        $totalAcara = (clone $statsQuery)->count();
        $paidAcara = (clone $statsQuery)->where('type', 'paid')->count();
        $freeAcara = (clone $statsQuery)->where('type', 'free')->count();
        $totalCapacity = (clone $statsQuery)->with('event')->get()->sum(function ($l) {
            return (int) $l->event?->lane_count * (int) $l->total_series;
        });

        return [
            'lombas' => $lombaQuery->orderBy('acara_number')->paginate(10),
            'categories' => Category::all(),
            'events' => Event::all(),
            'parameters' => RequirementParameter::where('is_active', true)->get(),
            'availableOperators' => $availableOperators,
            'mainAvailableOperators' => $mainAvailableOperators,
            'selectedSuppParam' => $selectedParam,
            'financeAccounts' => FinanceAccount::where('is_active', true)->get(),
            'stats' => [
                'total' => $totalAcara,
                'paid' => $paidAcara,
                'free' => $freeAcara,
                'capacity' => $totalCapacity,
            ],
        ];
    }

    public function openCreateLombaModal()
    {
        $this->reset(['category_uid', 'acara_number', 'acara_name', 'parameter_uid', 'operator', 'parameter_value', 'main_requirement', 'type', 'registration_fee', 'total_series', 'start_date', 'end_date', 'start_time', 'end_time', 'location', 'group_link', 'editingLombaId']);
        if ($this->event) {
            $this->event_uid = $this->event->uid;
        }
        $this->modalMode = 'create';
        $this->showLombaModal = true;
    }

    public function openEditLombaModal($uid)
    {
        $lomba = EventCategory::where('uid', $uid)->firstOrFail();
        $this->editingLombaId = $lomba->uid;
        $this->event_uid = $lomba->event_uid;
        $this->category_uid = $lomba->category_uid;
        $this->acara_number = $lomba->acara_number;
        $this->acara_name = $lomba->acara_name;
        $this->parameter_uid = $lomba->parameter_uid;
        $this->operator = $lomba->operator ?: '=';
        $this->parameter_value = $lomba->parameter_value;
        $this->main_requirement = $lomba->main_requirement;
        $this->type = $lomba->type;
        $this->registration_fee = $lomba->registration_fee;
        $this->total_series = $lomba->total_series;
        $this->start_date = $lomba->start_date?->format('Y-m-d');
        $this->end_date = $lomba->end_date?->format('Y-m-d');
        $this->start_time = $lomba->start_time;
        $this->end_time = $lomba->end_time;
        $this->location = $lomba->location;
        $this->group_link = $lomba->group_link;
        $this->modalMode = 'edit';
        $this->showLombaModal = true;
    }

    public function saveLomba()
    {
        try {
            $this->authorize($this->modalMode === 'create' ? 'master-lomba.create' : 'master-lomba.edit');

            $this->validate([
                'event_uid' => 'required',
                'category_uid' => 'required',
                'acara_number' => 'required|string',
                'acara_name' => 'required|string|max:255',
                'type' => 'required|in:free,paid',
                'registration_fee' => 'required_if:type,paid|numeric|min:0',
                'parameter_uid' => 'required',
                'operator' => 'required',
                'parameter_value' => 'required',
                'total_series' => 'required|integer|min:1',
                'group_link' => 'nullable|url|max:255',
            ]);

            $data = [
                'event_uid' => $this->event_uid,
                'category_uid' => $this->category_uid ?: null,
                'acara_number' => $this->acara_number,
                'acara_name' => $this->acara_name,
                'parameter_uid' => $this->parameter_uid ?: null,
                'operator' => $this->operator ?: '=',
                'parameter_value' => $this->parameter_value,
                'main_requirement' => $this->main_requirement,
                'type' => $this->type,
                'registration_fee' => $this->type === 'paid' ? $this->registration_fee : 0,
                'total_series' => $this->total_series,
                'start_date' => $this->start_date ?: null,
                'end_date' => $this->end_date ?: null,
                'start_time' => $this->start_time ?: null,
                'end_time' => $this->end_time ?: null,
                'location' => $this->location,
                'group_link' => $this->group_link ?: null,
            ];

            if ($this->modalMode === 'create') {
                EventCategory::create($data);
                $message = 'Lomba berhasil ditambahkan';
            } else {
                $lomba = EventCategory::where('uid', $this->editingLombaId)->firstOrFail();
                $lomba->update($data);
                $message = 'Lomba berhasil diperbarui';
            }

            $this->showLombaModal = false;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notification', [
                'status' => 'warning',
                'message' => 'Mohon lengkapi formulir dengan benar.',
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ]);
        }
    }

    public function openRequirementModal($lombaUid, $reqUid = null)
    {
        $this->selectedLombaUid = $lombaUid;
        $this->editingRequirementUid = $reqUid;

        if ($reqUid) {
            $req = CategoryRequirement::where('uid', $reqUid)->firstOrFail();
            $this->supp_parameter_uid = $req->parameter_uid;
            $this->supp_operator = $req->operator;
            $this->supp_parameter_value = is_array($req->parameter_value) ? $req->parameter_value[0] ?? '' : $req->parameter_value;
            $this->supp_is_required = $req->is_required;
            $this->reqModalMode = 'edit';
        } else {
            $this->reset(['supp_parameter_uid', 'supp_operator', 'supp_parameter_value', 'supp_is_required']);
            $this->reqModalMode = 'create';
        }

        $this->showRequirementModal = true;
    }

    public function updatedSuppParameterUid()
    {
        $this->supp_parameter_value = '';
    }

    public function saveRequirement()
    {
        try {
            $this->authorize('master-lomba.edit');
            $this->validate([
                'supp_parameter_uid' => 'required',
                'supp_operator' => 'required',
                'supp_parameter_value' => 'required',
            ]);

            $param = RequirementParameter::where('uid', $this->supp_parameter_uid)->firstOrFail();

            $data = [
                'event_category_uid' => $this->selectedLombaUid,
                'parameter_uid' => $param->uid,
                'parameter_name' => $param->parameter_key,
                'parameter_type' => $param->input_type,
                'parameter_value' => [$this->supp_parameter_value],
                'operator' => $this->supp_operator,
                'is_main' => false,
                'is_required' => $this->supp_is_required,
            ];

            if ($this->reqModalMode === 'create') {
                CategoryRequirement::create($data);
                $message = 'Syarat pendukung ditambahkan';
            } else {
                CategoryRequirement::where('uid', $this->editingRequirementUid)->update($data);
                $message = 'Syarat pendukung diperbarui';
            }

            $this->showRequirementModal = false;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notification', [
                'status' => 'warning',
                'message' => 'Lengkapi syarat sistem.',
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Gagal: ' . $e->getMessage(),
            ]);
        }
    }

    public function deleteRequirement($uid)
    {
        $this->authorize('master-lomba.edit');
        CategoryRequirement::where('uid', $uid)->delete();
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Syarat dihapus',
        ]);
    }

    public function confirmDeleteLomba($uid)
    {
        $this->lombaToDelete = EventCategory::where('uid', $uid)->firstOrFail();
        $this->showDeleteModal = true;
    }

    public function deleteLomba()
    {
        try {
            $this->authorize('master-lomba.delete');
            if ($this->lombaToDelete) {
                $this->lombaToDelete->delete();
                $this->showDeleteModal = false;
                $this->lombaToDelete = null;
                $this->dispatch('notification', [
                    'status' => 'success',
                    'message' => 'Lomba berhasil dihapus',
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Gagal menghapus: ' . $e->getMessage(),
            ]);
        }
    }
}; ?>

<div class="p-4 md:p-10 bg-slate-50/50 min-h-screen">
    {{-- Header Section --}}
    <div class="relative mb-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 relative z-10">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center shadow-lg shadow-slate-200">
                        <x-lucide-swatch-book class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none">Manajemen
                            Lomba</h2>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.2em] mt-2 italic">
                            {{ $event ? 'Detail Acara: ' . $event->name : 'Daftar Seluruh Nomor Perlombaan' }}
                        </p>
                    </div>
                </div>
            </div>

            @can('master-lomba.create')
                <button wire:click="openCreateLombaModal"
                    class="flex items-center gap-3 bg-ksc-blue hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-black transition shadow-xl shadow-blue-100 transform hover:-translate-y-1 uppercase text-xs tracking-widest group relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
                    <x-lucide-plus-circle class="w-5 h-5 text-emerald-400 relative z-10" />
                    <span class="relative z-10">Tambah Nomor Lomba</span>
                </button>
            @endcan
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div
            class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition duration-500 group">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition duration-500">
                    <x-lucide-layers class="w-7 h-7" />
                </div>
                <div>
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total
                        Acara</span>
                    <span class="text-2xl font-black text-slate-900">{{ $stats['total'] }}</span>
                </div>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition duration-500 group">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition duration-500">
                    <x-lucide-credit-card class="w-7 h-7" />
                </div>
                <div>
                    <span
                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Berbayar</span>
                    <span class="text-2xl font-black text-slate-900">{{ $stats['paid'] }}</span>
                </div>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition duration-500 group">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition duration-500">
                    <x-lucide-gift class="w-7 h-7" />
                </div>
                <div>
                    <span
                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Gratis</span>
                    <span class="text-2xl font-black text-slate-900">{{ $stats['free'] }}</span>
                </div>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition duration-500 group">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition duration-500">
                    <x-lucide-users class="w-7 h-7" />
                </div>
                <div>
                    <span
                        class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kapasitas</span>
                    <span class="text-2xl font-black text-slate-900">{{ $stats['capacity'] }} <span
                            class="text-xs text-slate-400 font-bold uppercase ml-1">Atlet</span></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Action Bar --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-10">
        <div class="relative w-full md:w-1/2 group">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                <x-lucide-search class="h-5 w-5 text-slate-400 group-focus-within:text-slate-900 transition" />
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                class="block w-full pl-14 pr-6 py-5 bg-white border-none rounded-[2rem] text-sm font-bold text-slate-900 shadow-xl shadow-slate-200/50 focus:ring-4 focus:ring-slate-900/5 outline-none transition placeholder:text-slate-300 placeholder:font-black placeholder:uppercase placeholder:tracking-widest"
                placeholder="Cari nama acara atau nomor...">
        </div>

        @if ($event && $event->exists)
            <a href="{{ route('master.event') }}"
                class="flex items-center gap-2 text-slate-400 hover:text-slate-900 transition font-black uppercase text-[10px] tracking-widest bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-100">
                <x-lucide-chevron-left class="w-4 h-4" />
                Kembali ke Event
            </a>
        @endif
    </div>

    {{-- List Section --}}
    <div class="grid grid-cols-1 gap-8">
        @forelse($lombas as $lomba)
            <div wire:key="lomba-{{ $lomba->uid }}"
                class="bg-white border border-slate-100 rounded-[3rem] shadow-xl shadow-slate-200/30 overflow-hidden group hover:shadow-2xl hover:shadow-slate-300/50 transition-all duration-500 transform hover:-translate-y-1">
                <div class="relative">
                    <div
                        class="absolute top-0 right-0 p-10 opacity-[0.03] pointer-events-none group-hover:opacity-[0.07] transition duration-700">
                        <x-lucide-swatch-book class="w-64 h-64 -mr-20 -mt-20 rotate-12" />
                    </div>

                    <div class="p-10 relative z-10">
                        <div class="flex flex-col lg:flex-row justify-between gap-12">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-6 mb-8">
                                    <div
                                        class="w-20 h-20 bg-slate-900 rounded-[2rem] flex items-center justify-center text-white font-black text-3xl shadow-2xl shadow-slate-400 transform group-hover:rotate-6 transition duration-500">
                                        {{ $lomba->acara_number }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-3 mb-2">
                                            <span
                                                class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border border-emerald-100">
                                                {{ $lomba->category?->name ?: 'Gaya Renang' }}
                                            </span>
                                            <span
                                                class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[9px] font-black uppercase tracking-[0.2em]">
                                                {{ $lomba->type === 'paid' ? 'Paid Event' : 'Free Entry' }}
                                            </span>
                                        </div>
                                        <h4
                                            class="text-3xl font-black text-slate-900 uppercase tracking-tighter leading-none">
                                            {{ $lomba->acara_name }}</h4>
                                        <p
                                            class="text-slate-400 font-bold uppercase text-[10px] tracking-widest mt-2 flex items-center gap-2">
                                            <x-lucide-file-text class="w-3.5 h-3.5" />
                                            {{ $lomba->main_requirement ?: 'Syarat Dokumen Belum Diatur' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                    <div
                                        class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 group/tile hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition duration-500">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                                                <x-lucide-credit-card class="w-4 h-4" />
                                            </div>
                                            <span
                                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tipe
                                                / Harga</span>
                                        </div>
                                        <span
                                            class="text-lg font-black {{ $lomba->type === 'paid' ? 'text-blue-600' : 'text-emerald-600' }} uppercase leading-none block">
                                            {{ $lomba->type === 'paid' ? 'Rp ' . number_format($lomba->registration_fee, 0, ',', '.') : 'GRATIS' }}
                                        </span>
                                    </div>

                                    <div
                                        class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 group/tile hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition duration-500">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                                                <x-lucide-filter class="w-4 h-4" />
                                            </div>
                                            <span
                                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Parameter</span>
                                        </div>
                                        <span
                                            class="text-sm font-black text-slate-700 uppercase tracking-tight leading-tight block">
                                            @php $mainP = $lomba->parameter_uid ? RequirementParameter::where('uid', $lomba->parameter_uid)->first() : null; @endphp
                                            {{ $mainP ? $mainP->display_name : 'No Limit' }}
                                            <span class="text-[10px] text-purple-400 block mt-1">{{ $lomba->operator }}
                                                {{ $lomba->parameter_value }}</span>
                                        </span>
                                    </div>

                                    <div
                                        class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 group/tile hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition duration-500">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                                                <x-lucide-users class="w-4 h-4" />
                                            </div>
                                            <span
                                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kuota</span>
                                        </div>
                                        <span class="text-lg font-black text-slate-900 uppercase leading-none block">
                                            {{ (int) $lomba->event?->lane_count * (int) $lomba->total_series }} <span
                                                class="text-xs text-slate-400">Atlet</span>
                                        </span>
                                        <span
                                            class="text-[9px] text-slate-400 font-bold block mt-1 uppercase italic tracking-tighter">{{ $lomba->event?->lane_count }}
                                            LINTASAN x {{ $lomba->total_series }} SERI</span>
                                    </div>

                                    @if (!$event || !$event->exists)
                                        <div
                                            class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 group/tile hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition duration-500">
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                                                    <x-lucide-award class="w-4 h-4" />
                                                </div>
                                                <span
                                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Event</span>
                                            </div>
                                            <span
                                                class="text-[10px] font-black text-emerald-900 uppercase truncate block leading-tight">{{ $lomba->event?->name }}</span>
                                            <span
                                                class="text-[8px] text-emerald-400 font-bold block mt-1 uppercase tracking-widest">{{ $lomba->event?->start_date?->format('M Y') }}</span>
                                        </div>
                                    @else
                                        <div
                                            class="p-6 bg-slate-50/50 rounded-[2rem] border border-slate-100 group/tile hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition duration-500">
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                                                    <x-lucide-calendar class="w-4 h-4" />
                                                </div>
                                                <span
                                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu</span>
                                            </div>
                                            <span
                                                class="text-[10px] font-black text-slate-900 uppercase block leading-tight">
                                                {{ $lomba->start_date?->format('d M') ?: 'TBA' }}
                                            </span>
                                            <span
                                                class="text-[9px] text-slate-400 font-bold block mt-1 italic uppercase tracking-tighter">{{ $lomba->start_time ?: '00:00' }}
                                                - {{ $lomba->end_time ?: 'Selesai' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div
                                class="w-full lg:w-96 border-t lg:border-t-0 lg:border-l border-slate-100 pt-10 lg:pt-0 lg:pl-10">
                                <div class="flex justify-between items-center mb-6">
                                    <div>
                                        <h5 class="text-[11px] font-black text-slate-900 uppercase tracking-[0.2em]">
                                            Syarat Sistem</h5>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase mt-1">Supporting
                                            Requirements</p>
                                    </div>
                                    @can('master-lomba.edit')
                                        <button wire:click="openRequirementModal('{{ $lomba->uid }}')"
                                            class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                            <x-lucide-plus class="w-5 h-5" />
                                        </button>
                                    @endcan
                                </div>

                                <div class="space-y-3 max-h-48 overflow-y-auto pr-3 custom-scrollbar">
                                    @forelse($lomba->requirements as $req)
                                        <div wire:key="req-{{ $req->uid }}"
                                            class="group/req flex items-center justify-between p-4 bg-slate-50/50 border border-slate-100 rounded-[1.5rem] hover:bg-white hover:shadow-md transition duration-300">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-2 h-2 bg-emerald-400 rounded-full group-hover:scale-150 transition transition-all duration-500">
                                                </div>
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-[10px] font-black text-slate-700 uppercase leading-none mb-1">{{ $req->parameter_name }}</span>
                                                    <span
                                                        class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $req->operator }}
                                                        {{ is_array($req->parameter_value) ? implode(',', $req->parameter_value) : $req->parameter_value }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                @can('master-lomba.edit')
                                                    <button
                                                        wire:click="openRequirementModal('{{ $lomba->uid }}', '{{ $req->uid }}')"
                                                        class="p-1.5 text-slate-300 hover:text-blue-600 hover:bg-blue-50 rounded-lg opacity-0 group-hover/req:opacity-100 transition transition-all">
                                                        <x-lucide-edit-3 class="w-3.5 h-3.5" />
                                                    </button>
                                                    <button wire:click="deleteRequirement('{{ $req->uid }}')"
                                                        wire:loading.attr="disabled"
                                                        wire:target="deleteRequirement('{{ $req->uid }}')"
                                                        class="p-1.5 text-rose-300 hover:text-rose-600 hover:bg-rose-50 rounded-lg opacity-0 group-hover/req:opacity-100 transition transition-all disabled:opacity-50">
                                                        <x-lucide-trash-2 wire:loading.remove
                                                            wire:target="deleteRequirement('{{ $req->uid }}')"
                                                            class="w-3.5 h-3.5" />
                                                        <div wire:loading
                                                            wire:target="deleteRequirement('{{ $req->uid }}')">
                                                            <svg class="animate-spin h-3 w-3"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12"
                                                                    r="10" stroke="currentColor" stroke-width="4">
                                                                </circle>
                                                                <path class="opacity-75" fill="currentColor"
                                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    @empty
                                        <div
                                            class="py-8 text-center bg-slate-50/50 rounded-[2rem] border border-dashed border-slate-200">
                                            <x-lucide-shield-alert class="w-8 h-8 text-slate-200 mx-auto mb-2" />
                                            <p
                                                class="text-[10px] text-slate-300 font-black uppercase tracking-widest italic px-4">
                                                Tidak ada syarat sistem pendukung</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-12 pt-8 border-t border-slate-100">
                            @can('master-lomba.edit')
                                <button wire:click="openEditLombaModal('{{ $lomba->uid }}')"
                                    class="px-8 py-3.5 bg-slate-50 text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                                    Edit Acara
                                </button>
                            @endcan
                            @can('master-lomba.delete')
                                <button wire:click="confirmDeleteLomba('{{ $lomba->uid }}')"
                                    class="px-8 py-3.5 bg-rose-50 text-rose-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                    Hapus
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div
                class="bg-white border border-dashed border-slate-200 rounded-[3.5rem] p-32 text-center shadow-2xl shadow-slate-200/50">
                <div class="w-24 h-24 bg-slate-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8">
                    <x-lucide-swatch-book class="w-10 h-10 text-slate-200" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tighter">Belum Ada Nomor Lomba</h3>
                <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.2em] mt-2 italic">Mulai dengan
                    menambahkan kategori perlombaan baru</p>
            </div>
        @endforelse
    </div>

    <div class="mt-12">{{ $lombas->links() }}</div>

    {{-- Modal Lomba --}}
    @if ($showLombaModal)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showLombaModal', false)">
            </div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-4xl relative z-50 border border-slate-100 max-h-[90vh] flex flex-col">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Formulir Nomor Lomba</h3>
                    <button wire:click="$set('showLombaModal', false)"
                        class="text-slate-400 hover:text-slate-600"><x-lucide-x class="w-6 h-6" /></button>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-center gap-3 animate-pulse">
                            <x-lucide-alert-circle class="w-5 h-5 text-rose-600" />
                            <span class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Ada kesalahan pada formulir, silakan cek input Anda di bawah.</span>
                        </div>
                    @endif
                    <form wire:submit.prevent="saveLomba" id="lombaForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if (!$event || !$event->exists)
                            <div class="md:col-span-2">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Event
                                    Induk</label>
                                <select wire:model="event_uid"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-black text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner appearance-none">
                                    <option value="">-- Pilih Event --</option>
                                    @foreach ($events as $ev)
                                        <option value="{{ $ev->uid }}">{{ $ev->name }}</option>
                                    @endforeach
                                </select>
                                @error('event_uid')
                                    <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Nama
                                Acara</label>
                            <input type="text" wire:model="acara_name" placeholder="50M GAYA BEBAS PUTRA"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-black text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner uppercase">
                            @error('acara_name')
                                <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Nomor
                                Acara</label>
                            <input type="text" wire:model="acara_number"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner" placeholder="Contoh: 01">
                            @error('acara_number')
                                <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Kategori
                                Gaya</label>
                            <select wire:model="category_uid"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner appearance-none">
                                <option value="">-- Pilih Gaya --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->uid }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_uid')
                                <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Tipe
                                Lomba</label>
                            <select wire:model.live="type"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner appearance-none">
                                <option value="paid">Berbayar</option>
                                <option value="free">Gratis</option>
                            </select>
                            @error('type')
                                <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Biaya
                                Pendaftaran</label>
                            <input type="number" wire:model="registration_fee"
                                {{ $type === 'free' ? 'disabled' : '' }}
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner {{ $type === 'free' ? 'opacity-50 cursor-not-allowed' : '' }}">
                            @error('registration_fee')
                                <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Jumlah
                                Seri</label>
                            <input type="number" wire:model="total_series"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                            @error('total_series')
                                <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Link Group Koordinasi (WhatsApp/Telegram)</label>
                            <input type="url" wire:model="group_link" placeholder="https://chat.whatsapp.com/..."
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                            @error('group_link')
                                <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="md:col-span-2">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Parameter
                                    Utama (Wajib)</label>
                                <select wire:model.live="parameter_uid"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner appearance-none">
                                    <option value="">-- Pilih Parameter --</option>
                                    @foreach ($parameters as $p)
                                        <option value="{{ $p->uid }}">{{ $p->display_name }}</option>
                                    @endforeach
                                </select>
                                @error('parameter_uid')
                                    <span
                                        class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="md:col-span-1">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Operator</label>
                                <select wire:model="operator"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition appearance-none shadow-inner">
                                    @foreach ($mainAvailableOperators as $op)
                                        <option value="{{ $op }}">{{ $op }}</option>
                                    @endforeach
                                </select>
                                @error('operator')
                                    <span
                                        class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="md:col-span-1">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Nilai
                                    Parameter</label>
                                @php $selP = $parameters->where('uid', $parameter_uid)->first(); @endphp
                                @if ($selP)
                                    @if ($selP->input_type === 'select')
                                        <select wire:model="parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition appearance-none shadow-inner">
                                            <option value="">-- Pilih Nilai --</option>
                                            @foreach ($selP->input_options as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($selP->input_type === 'date')
                                        <input type="date" wire:model="parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @elseif($selP->input_type === 'number')
                                        <input type="number" wire:model="parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @elseif($selP->input_type === 'range')
                                        <input type="number" wire:model="parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @elseif($selP->input_type === 'boolean')
                                        <select wire:model="parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition appearance-none shadow-inner">
                                            <option value="">-- Pilih --</option>
                                            <option value="1">Ya</option>
                                            <option value="0">Tidak</option>
                                        </select>
                                    @elseif($selP->input_type === 'email')
                                        <input type="email" wire:model="parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @elseif($selP->input_type === 'tel')
                                        <input type="tel" wire:model="parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @elseif($selP->input_type === 'textarea')
                                        <textarea wire:model="parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner"></textarea>
                                    @else
                                        <input type="text" wire:model="parameter_value" placeholder="..."
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @endif
                                @else
                                    <input type="text" disabled placeholder="Pilih..."
                                        class="w-full px-5 py-4 bg-slate-100 border-none rounded-2xl text-sm font-bold text-slate-400 outline-none transition shadow-inner cursor-not-allowed">
                                @endif
                                @error('parameter_value')
                                    <span
                                        class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-6 pt-6 border-t border-slate-50">
                            <div class="col-span-1">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Tanggal
                                    Mulai</label>
                                <input type="date" wire:model="start_date"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                            </div>
                            <div class="col-span-1">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Tanggal
                                    Selesai</label>
                                <input type="date" wire:model="end_date"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                            </div>
                            <div class="col-span-1">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Jam
                                    Mulai</label>
                                <input type="time" wire:model="start_time"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                            </div>
                            <div class="col-span-1">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Jam
                                    Selesai</label>
                                <input type="time" wire:model="end_time"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Lokasi
                                Perlombaan (Opsional)</label>
                            <input type="text" wire:model="location" placeholder="Contoh: Kolam Renang GBK"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-black text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner uppercase">
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Syarat
                                Utama (Narasi Dokumen)</label>
                            <input type="text" wire:model="main_requirement"
                                placeholder="Contoh: KU 2017 (TAHUN LAHIR 2017)"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-black text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner uppercase">
                        </div>
                    </div>

                    </form>
                </div>

                <div class="p-8 border-t border-slate-50 flex items-center bg-slate-50/30 gap-4">
                    <button type="button" wire:click="$set('showLombaModal', false)"
                        class="flex-1 px-8 py-4 bg-white border border-slate-200 text-slate-500 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-slate-50 transition">Batal</button>
                    <button type="submit" form="lombaForm"
                        class="flex-1 px-8 py-4 bg-slate-900 text-white rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl shadow-slate-200 hover:bg-slate-800 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        wire:loading.attr="disabled" wire:target="saveLomba">
                        <span wire:loading.remove wire:target="saveLomba">Simpan Lomba</span>
                        <span wire:loading wire:target="saveLomba" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Supporting Requirement --}}
    @if ($showRequirementModal)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                wire:click="$set('showRequirementModal', false)"></div>
            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-xl relative z-50 border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase text-sm">Tambah Syarat
                        Sistem</h3>
                    <button wire:click="$set('showRequirementModal', false)" class="text-slate-400"><x-lucide-x
                            class="w-5 h-5" /></button>
                </div>
                <form wire:submit.prevent="saveRequirement" class="p-8">
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-2xl flex items-center gap-3">
                            <x-lucide-alert-circle class="w-5 h-5 text-rose-600" />
                            <span class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Cek kembali isian syarat Anda.</span>
                        </div>
                    @endif
                    <div class="space-y-6">
                        <div>
                            <label
                                class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Pilih
                                Parameter</label>
                            <select wire:model.live="supp_parameter_uid"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition appearance-none shadow-inner">
                                <option value="">-- Master Parameter --</option>
                                @foreach ($parameters as $p)
                                    <option value="{{ $p->uid }}">{{ $p->display_name }}</option>
                                @endforeach
                            </select>
                            @error('supp_parameter_uid')
                                <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Operator</label>
                                <select wire:model="supp_operator"
                                    class="w-full px-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition appearance-none shadow-inner">
                                    @foreach ($availableOperators as $op)
                                        <option value="{{ $op }}">{{ $op }}</option>
                                    @endforeach
                                </select>
                                @error('supp_operator')
                                    <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-span-2">
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Nilai
                                    Validasi</label>
                                @if ($selectedSuppParam)
                                    @if ($selectedSuppParam->input_type === 'select')
                                        <select wire:model="supp_parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition appearance-none shadow-inner">
                                            <option value="">-- Pilih Nilai --</option>
                                            @foreach ($selectedSuppParam->input_options as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($selectedSuppParam->input_type === 'date')
                                        <input type="date" wire:model="supp_parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @elseif($selectedSuppParam->input_type === 'number')
                                        <input type="number" wire:model="supp_parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @elseif($selectedSuppParam->input_type === 'range')
                                        <input type="number" wire:model="supp_parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @elseif($selectedSuppParam->input_type === 'boolean')
                                        <select wire:model="supp_parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition appearance-none shadow-inner">
                                            <option value="">-- Pilih --</option>
                                            <option value="1">Ya</option>
                                            <option value="0">Tidak</option>
                                        </select>
                                    @elseif($selectedSuppParam->input_type === 'email')
                                        <input type="email" wire:model="supp_parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @elseif($selectedSuppParam->input_type === 'tel')
                                        <input type="tel" wire:model="supp_parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @elseif($selectedSuppParam->input_type === 'textarea')
                                        <textarea wire:model="supp_parameter_value"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner"></textarea>
                                    @else
                                        <input type="text" wire:model="supp_parameter_value"
                                            placeholder="Masukkan nilai..."
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-100 outline-none transition shadow-inner">
                                    @endif
                                @else
                                    <input type="text" disabled placeholder="Pilih parameter dulu"
                                        class="w-full px-5 py-4 bg-slate-100 border-none rounded-2xl text-sm font-bold text-slate-400 outline-none transition shadow-inner cursor-not-allowed">
                                @endif
                                @error('supp_parameter_value')
                                    <span class="text-[10px] text-rose-500 font-bold mt-1 ml-1 block uppercase tracking-wider">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center pt-8 mt-8 border-t border-slate-100 gap-4">
                        <button type="submit"
                            class="w-full px-8 py-4 bg-slate-900 text-white rounded-2xl font-bold uppercase text-[10px] tracking-widest shadow-xl shadow-slate-200 hover:bg-slate-800 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            wire:loading.attr="disabled" wire:target="saveRequirement">
                            <span wire:loading.remove wire:target="saveRequirement">Terapkan Syarat</span>
                            <span wire:loading wire:target="saveRequirement" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Delete Lomba --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showDeleteModal', false)">
            </div>
            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-md relative z-50 border border-slate-100">
                <div class="p-12 text-center">
                    <div
                        class="w-20 h-20 bg-rose-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-rose-100">
                        <x-lucide-alert-circle class="w-10 h-10 text-rose-600" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Lomba?</h3>
                    <p
                        class="text-slate-500 font-medium mb-10 text-[10px] uppercase tracking-widest leading-relaxed px-6 text-center">
                        Data lomba <span class="text-rose-600 font-black">"{{ $lombaToDelete?->acara_name }}"</span>
                        akan dihapus permanen.</p>
                    <div class="flex gap-4">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold uppercase text-[10px] tracking-widest transition hover:bg-slate-200">Batal</button>
                        <button wire:click="deleteLomba"
                            class="flex-1 py-4 bg-rose-600 text-white rounded-2xl font-bold uppercase text-[10px] tracking-widest transition hover:bg-rose-700 shadow-xl shadow-rose-100 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            wire:loading.attr="disabled" wire:target="deleteLomba">
                            <span wire:loading.remove wire:target="deleteLomba">Hapus</span>
                            <span wire:loading wire:target="deleteLomba" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
