<?php

use Livewire\Volt\Component;
use App\Models\RequirementParameter;
use Illuminate\Support\Str;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';

    // Form fields
    public $display_name = '';
    public $parameter_key = '';
    public $source = 'core'; // 'core' or 'attribute'
    public $input_type = 'text';
    public $options = []; // Array of options for select type
    public $newOption = '';
    public $validation_rules = '';
    public $error_message = '';
    public $operators = ['=']; // Selected operators
    public $description = '';
    public $is_active = true;

    public $editingParamId = null;
    public $paramToDelete = null;
    public $showModal = false;
    public $showDeleteModal = false;
    public $modalMode = 'create';

    public function with()
    {
        $paramsQuery = RequirementParameter::query();

        if ($this->search) {
            $paramsQuery->where('display_name', 'like', '%' . $this->search . '%')->orWhere('parameter_key', 'like', '%' . $this->search . '%');
        }

        return [
            'parameters' => $paramsQuery->latest()->paginate(10),
            'availableCoreKeys' => collect(\Schema::getColumnListing('data_users'))->reject(fn($column) => in_array($column, ['id', 'uid', 'user_uid', 'created_at', 'updated_at', 'deleted_at', 'profile_picture']))->values()->toArray(),
            'availableAttributeKeys' => \DB::table('user_attributes')->distinct()->pluck('name')->toArray(),
            'allPossibleOperators' => ['=', '>', '<', '>=', '<=', '!=', 'IN', 'LIKE'],
        ];
    }

    public function openCreateModal()
    {
        $this->reset(['display_name', 'parameter_key', 'source', 'input_type', 'options', 'newOption', 'validation_rules', 'error_message', 'operators', 'description', 'is_active', 'editingParamId']);
        $this->operators = ['='];
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal($uid)
    {
        $param = RequirementParameter::where('uid', $uid)->firstOrFail();
        $this->editingParamId = $param->uid;
        $this->display_name = $param->display_name;
        $this->parameter_key = $param->parameter_key;
        $this->source = $param->source;
        $this->input_type = $param->input_type;
        $this->options = is_array($param->input_options) ? $param->input_options : [];
        $this->validation_rules = $param->validation_rules;
        $this->error_message = $param->error_message;
        $this->operators = is_array($param->allowed_operators) ? $param->allowed_operators : ['='];
        $this->description = $param->description;
        $this->is_active = $param->is_active;
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function addOption()
    {
        if ($this->newOption && !in_array($this->newOption, $this->options)) {
            $this->options[] = $this->newOption;
            $this->newOption = '';
        }
    }

    public function removeOption($index)
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function save()
    {
        $this->authorize($this->modalMode === 'create' ? 'master-parameter.create' : 'master-parameter.edit');

        $this->validate([
            'display_name' => 'required|string|max:255',
            'parameter_key' => 'required|string|max:100|unique:requirement_parameters,parameter_key,' . ($this->editingParamId ? RequirementParameter::where('uid', $this->editingParamId)->first()->id : 'NULL'),
            'source' => 'required|in:core,attribute',
            'input_type' => 'required|in:text,number,select,date,range,boolean,email,tel,textarea,file',
            'operators' => 'required|array|min:1',
        ]);

        $data = [
            'display_name' => $this->display_name,
            'parameter_key' => $this->parameter_key,
            'source' => $this->source,
            'input_type' => $this->input_type,
            'input_options' => $this->input_type === 'select' ? $this->options : null,
            'validation_rules' => $this->validation_rules,
            'error_message' => $this->error_message,
            'allowed_operators' => $this->operators,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->modalMode === 'create') {
            RequirementParameter::create($data);
            $message = 'Parameter berhasil ditambahkan';
        } else {
            $param = RequirementParameter::where('uid', $this->editingParamId)->firstOrFail();
            $param->update($data);
            $message = 'Parameter berhasil diperbarui';
        }

        $this->showModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => $message,
        ]);
    }

    public function confirmDelete($uid)
    {
        $this->paramToDelete = RequirementParameter::where('uid', $uid)->firstOrFail();
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->authorize('master-parameter.delete');
        $param = $this->paramToDelete;
        if ($param) {
            $param->delete();
            $this->showDeleteModal = false;
            $this->paramToDelete = null;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Parameter berhasil dihapus',
            ]);
        }
    }

    public function toggleStatus($uid)
    {
        $this->authorize('master-parameter.edit');
        $param = RequirementParameter::where('uid', $uid)->first();
        if ($param) {
            $param->is_active = !$param->is_active;
            $param->save();
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Status parameter berhasil diubah',
            ]);
        }
    }
}; ?>

<div class="p-4 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Master Parameter</h2>
            <p class="text-sm text-slate-500 font-medium">Kelola parameter syarat partisipasi event renang</p>
        </div>
        @can('master-parameter.create')
            <button wire:click="openCreateModal"
                class="flex items-center gap-2 bg-ksc-blue hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition shadow-xl shadow-blue-100 transform hover:-translate-y-0.5">
                <x-lucide-settings-2 class="w-5 h-5" />
                <span>Tambah Parameter</span>
            </button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="relative">
            <x-lucide-search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari parameter atau key..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Parameter
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Source
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Key</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tipe</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">
                            Status</th>
                        @canany(['master-parameter.edit', 'master-parameter.delete'])
                            <th
                                class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">
                                Aksi</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($parameters as $param)
                        <tr wire:key="param-{{ $param->uid }}" class="hover:bg-slate-50/50 transition group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
                                        <x-lucide-list-checks class="text-slate-600 w-5 h-5" />
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm font-black text-slate-900 uppercase tracking-tight block">{{ $param->display_name }}</span>
                                        <span
                                            class="text-[10px] text-slate-400 font-medium italic">{{ Str::limit($param->description, 30) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <span
                                    class="px-2 py-1 {{ $param->source === 'core' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-purple-50 text-purple-600 border-purple-100' }} rounded-lg text-[9px] font-black uppercase tracking-widest border">
                                    {{ $param->source }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <span
                                    class="px-3 py-1 bg-slate-50 text-slate-500 rounded-lg font-mono text-[10px] border border-slate-200">
                                    {{ $param->parameter_key }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <span
                                    class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-wider border border-blue-100">
                                    {{ $param->input_type }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <button wire:click="toggleStatus('{{ $param->uid }}')"
                                    class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest transition border
                                    {{ $param->is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100 hover:bg-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-100' }}">
                                    {{ $param->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            @canany(['master-parameter.edit', 'master-parameter.delete'])
                                <td class="px-8 py-6">
                                    <div class="flex justify-center gap-2">
                                        @can('master-parameter.edit')
                                            <button wire:click="openEditModal('{{ $param->uid }}')"
                                                wire:loading.attr="disabled"
                                                class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition">
                                                <x-lucide-pencil class="w-5 h-5" />
                                            </button>
                                        @endcan
                                        @can('master-parameter.delete')
                                            <button wire:click="confirmDelete('{{ $param->uid }}')"
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
                                Tidak ada data parameter
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $parameters->links() }}
    </div>

    {{-- Modal Create/Edit --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-3xl z-[2010] border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">
                        {{ $modalMode === 'create' ? 'Tambah Parameter' : 'Edit Parameter' }}
                    </h3>
                    <button wire:click="$set('showModal', false)"
                        class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Left Column --}}
                        <div class="space-y-6">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Nama
                                    Tampilan</label>
                                <input type="text" wire:model="display_name" placeholder="Contoh: Tahun Lahir"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition shadow-inner">
                                @error('display_name')
                                    <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Sumber
                                    Data</label>
                                <select wire:model.live="source"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition shadow-inner appearance-none">
                                    <option value="core">Core (Tabel data_users)</option>
                                    <option value="attribute">Attribute (Tabel user_attributes)</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Key
                                    Parameter</label>
                                <div class="relative" x-data="{ open: false }">
                                    <input type="text" wire:model="parameter_key" @focus="open = true"
                                        @click.away="open = false" placeholder="Pilih atau ketik key..."
                                        class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-mono text-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition shadow-inner">

                                    <div x-show="open"
                                        class="absolute z-10 w-full mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 max-h-48 overflow-y-auto py-2">
                                        @if ($source === 'core')
                                            @foreach ($availableCoreKeys as $k)
                                                <button type="button"
                                                    wire:click="$set('parameter_key', '{{ $k }}'); open = false"
                                                    class="w-full text-left px-5 py-2 text-xs font-mono hover:bg-slate-50 transition">{{ $k }}</button>
                                            @endforeach
                                        @else
                                            @forelse($availableAttributeKeys as $k)
                                                <button type="button"
                                                    wire:click="$set('parameter_key', '{{ $k }}'); open = false"
                                                    class="w-full text-left px-5 py-2 text-xs font-mono hover:bg-slate-50 transition">{{ $k }}</button>
                                            @empty
                                                <div class="px-5 py-2 text-[10px] text-slate-400 italic">Belum ada
                                                    atribut dinamis</div>
                                            @endforelse
                                        @endif
                                    </div>
                                </div>
                                @error('parameter_key')
                                    <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Tipe
                                    Input</label>
                                <select wire:model.live="input_type"
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition shadow-inner appearance-none">
                                    <option value="text">Text Biasa</option>
                                    <option value="number">Angka (Number)</option>
                                    <option value="select">Pilihan (Select)</option>
                                    <option value="date">Tanggal (Date)</option>
                                    <option value="range">Rentang (Range)</option>
                                    <option value="boolean">Ya/Tidak (Boolean)</option>
                                    <option value="email">Email</option>
                                    <option value="tel">Telepon (Tel)</option>
                                    <option value="textarea">Area Teks (Textarea)</option>
                                    <option value="file">File Upload</option>
                                </select>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="space-y-6">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Operator
                                    Diizinkan</label>
                                <div
                                    class="grid grid-cols-4 gap-2 bg-slate-50 p-4 rounded-2xl shadow-inner border border-slate-100">
                                    @foreach ($allPossibleOperators as $op)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="checkbox" wire:model="operators"
                                                value="{{ $op }}"
                                                class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 transition">
                                            <span
                                                class="text-xs font-black text-slate-600 group-hover:text-slate-900">{{ $op }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('operators')
                                    <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span>
                                @enderror
                            </div>

                            @if ($input_type === 'select')
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Opsi
                                        Pilihan (Select)</label>
                                    <div class="flex gap-2 mb-3">
                                        <input type="text" wire:model="newOption"
                                            wire:keydown.enter.prevent="addOption" placeholder="Tambah opsi..."
                                            class="flex-1 px-4 py-2 bg-slate-100 border-none rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-slate-300 outline-none transition">
                                        <button type="button" wire:click="addOption"
                                            class="p-2 bg-slate-900 text-white rounded-xl hover:bg-black transition">
                                            <x-lucide-plus class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-1">
                                        @foreach ($options as $index => $opt)
                                            <span
                                                class="flex items-center gap-1 px-3 py-1 bg-white border border-slate-200 rounded-full text-[10px] font-black text-slate-600 shadow-sm">
                                                {{ $opt }}
                                                <button type="button" wire:click="removeOption({{ $index }})"
                                                    class="text-rose-400 hover:text-rose-600"><x-lucide-x
                                                        class="w-3 h-3" /></button>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Deskripsi
                                    & Status</label>
                                <textarea wire:model="description" placeholder="Penjelasan singkat parameter..."
                                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-medium text-slate-900 focus:ring-4 focus:ring-slate-100 outline-none transition shadow-inner h-24 mb-4"></textarea>

                                <div class="flex items-center h-14 px-5 bg-slate-50 rounded-2xl shadow-inner">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                        <div
                                            class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                                        </div>
                                        <span
                                            class="ml-3 text-sm font-bold text-slate-600 tracking-tight">{{ $is_active ? 'Parameter Aktif' : 'Nonaktif' }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center pt-8 mt-8 border-t border-slate-100 gap-4">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition uppercase text-[10px] tracking-widest">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                            class="flex-1 px-8 py-4 bg-slate-900 text-white rounded-2xl font-bold hover:bg-black transition shadow-xl shadow-slate-100 flex items-center justify-center gap-2 uppercase text-[10px] tracking-widest">
                            <span wire:loading.remove wire:target="save">
                                {{ $modalMode === 'create' ? 'Simpan Master' : 'Perbarui Master' }}
                            </span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <x-lucide-loader-2 class="w-4 h-4 animate-spin" />
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
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Parameter?
                    </h3>
                    <p
                        class="text-slate-500 font-medium mb-10 px-10 leading-relaxed uppercase text-[10px] tracking-widest text-center">
                        Parameter <span class="text-rose-600 font-black">"{{ $paramToDelete?->display_name }}"</span>
                        akan dihapus permanen dari sistem.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition uppercase text-[10px] tracking-widest flex-1">Batal</button>
                        <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                            class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2 min-w-[160px] uppercase text-[10px] tracking-widest flex-1">
                            <span wire:loading.remove wire:target="delete">Ya, Hapus Data</span>
                            <span wire:loading wire:target="delete" class="flex items-center gap-2">
                                <x-lucide-loader-2 class="w-4 h-4 animate-spin" />
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
