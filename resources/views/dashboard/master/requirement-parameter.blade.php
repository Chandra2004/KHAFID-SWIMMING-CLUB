@extends('layouts.layout-dashboard.app')

@section('dashboard-section')
    <div class="p-4 md:p-8 overflow-y-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 leading-tight tracking-tight">Master Parameter Lomba</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium">Definisikan parameter syarat partisipasi yang bisa dipilih saat membuat event.</p>
            </div>
            <button data-modal-target="modal-tambah-parameter" data-modal-toggle="modal-tambah-parameter"
                class="flex items-center gap-2 bg-slate-900 hover:bg-black text-white px-5 py-3 rounded-xl font-bold transition-all shadow-lg active:scale-95 group"
                type="button">
                <i data-lucide="settings-2" class="w-5 h-5 group-hover:rotate-12 transition"></i>
                <span>Tambah Parameter</span>
            </button>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Parameter</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Key</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Tipe Input</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($parameters['data'] as $param)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $param['display_name'] }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-lg font-mono text-[11px] border border-slate-200">{{ $param['parameter_key'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded text-[10px] font-bold uppercase tracking-wider border border-blue-100">{{ $param['input_type'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="px-2.5 py-1 {{ $param['is_active'] ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100' }} border rounded-full text-[9px] font-black uppercase">
                                        {{ $param['is_active'] ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex justify-center gap-3">
                                        <button data-modal-target="modal-edit-parameter-{{ $param['uid'] }}"
                                            data-modal-toggle="modal-edit-parameter-{{ $param['uid'] }}"
                                            class="text-blue-600 hover:text-blue-800 transition p-1 hover:bg-blue-50 rounded">
                                            <i data-lucide="edit" class="w-5 h-5"></i>
                                        </button>
                                        <button data-modal-target="modal-hapus-parameter-{{ $param['uid'] }}"
                                            data-modal-toggle="modal-hapus-parameter-{{ $param['uid'] }}"
                                            class="text-red-600 hover:text-red-800 transition p-1 hover:bg-red-50 rounded">
                                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div id="modal-tambah-parameter" tabindex="-1" aria-hidden="true"
        class="hidden fixed top-0 left-0 right-0 z-[70] w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-full items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
        <div class="relative w-full max-w-lg max-h-full">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Buat Parameter Baru</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-900 rounded-lg text-sm w-9 h-9 flex justify-center items-center" data-modal-hide="modal-tambah-parameter">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form action="{{ url('/' . $user['nama_role'] . '/' . $user['uid'] . '/dashboard/management-requirement-parameter/create/process') }}" method="POST" x-data="{ type: 'text' }">
                    @csrf
                    <div class="p-6 space-y-5 text-left">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Nama Tampilan</label>
                                <input name="display_name" type="text" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-ksc-blue p-3 outline-none" placeholder="Contoh: Tahun Lahir" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Key Parameter (Slug)</label>
                                <input name="parameter_key" type="text" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm font-mono rounded-xl focus:ring-ksc-blue p-3 outline-none" placeholder="birth_year" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Tipe Input</label>
                                <select name="input_type" x-model="type" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-ksc-blue p-3 outline-none font-bold">
                                    <option value="text">Text Biasa</option>
                                    <option value="number">Angka (Number)</option>
                                    <option value="select">Pilihan (Dropdown)</option>
                                    <option value="date">Tanggal (Date)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Status</label>
                                <label class="relative inline-flex items-center cursor-pointer mt-2">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-ksc-blue"></div>
                                    <span class="ms-3 text-sm font-bold text-slate-700">Aktif</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="type === 'select'">
                            <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Opsi Pilihan (JSON Array)</label>
                            <textarea name="input_options" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm font-mono rounded-xl focus:ring-ksc-blue p-3 outline-none h-20" placeholder='["Putra", "Putri"]'></textarea>
                            <p class="mt-1 text-[10px] text-slate-400 font-medium italic">*Wajib format JSON: ["Opsi 1", "Opsi 2"]</p>
                        </div>

                        <div>
                            <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Operator yang Diizinkan (JSON Array)</label>
                            <input name="allowed_operators" type="text" class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm font-mono rounded-xl focus:ring-ksc-blue p-3 outline-none" value='["=", ">", "<", ">=", "<=", "!="]' required>
                            <p class="mt-1 text-[10px] text-slate-400 font-medium italic">Bawaan: ["=", ">", "<", ">=", "<=", "!=", "IN"]</p>
                        </div>
                    </div>
                    <div class="flex items-center p-6 border-t border-slate-100 space-x-3 justify-end bg-slate-50/50">
                        <button data-modal-hide="modal-tambah-parameter" type="button" class="text-slate-500 bg-white hover:bg-slate-100 rounded-xl border border-slate-200 text-sm font-bold px-6 py-2.5 transition">Batal</button>
                        <button type="submit" class="text-white bg-slate-900 hover:bg-black font-bold rounded-xl text-sm px-8 py-2.5 transition shadow-lg">Simpan Master Parameter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($parameters['data'] as $param)
        <!-- Modal Edit -->
        <div id="modal-edit-parameter-{{ $param['uid'] }}" tabindex="-1" aria-hidden="true"
            class="hidden fixed top-0 left-0 right-0 z-[70] w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-full items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
            <div class="relative w-full max-w-lg max-h-full">
                <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-xl font-bold text-slate-900 uppercase tracking-tight">Ubah Master Parameter</h3>
                        <button type="button" class="text-slate-400 hover:text-slate-900 rounded-lg text-sm w-9 h-9 flex justify-center items-center" data-modal-hide="modal-edit-parameter-{{ $param['uid'] }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <form action="{{ url('/' . $user['nama_role'] . '/' . $user['uid'] . '/' . $param['uid'] . '/dashboard/management-requirement-parameter/edit/process') }}" method="POST" x-data="{ type: '{{ $param['input_type'] }}' }">
                        @csrf
                        <div class="p-6 space-y-5 text-left">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Nama Tampilan</label>
                                    <input name="display_name" type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-ksc-blue p-3 outline-none font-bold" value="{{ $param['display_name'] }}" required>
                                </div>
                                <div>
                                    <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Key Parameter (Slug)</label>
                                    <input name="parameter_key" type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-mono rounded-xl focus:ring-ksc-blue p-3 outline-none" value="{{ $param['parameter_key'] }}" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Tipe Input</label>
                                    <select name="input_type" x-model="type" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-ksc-blue p-3 outline-none font-bold">
                                        <option value="text">Text Biasa</option>
                                        <option value="number">Angka (Number)</option>
                                        <option value="select">Pilihan (Dropdown)</option>
                                        <option value="date">Tanggal (Date)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Status</label>
                                    <label class="relative inline-flex items-center cursor-pointer mt-2">
                                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $param['is_active'] ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-ksc-blue"></div>
                                        <span class="ms-3 text-sm font-bold text-slate-700">Aktif</span>
                                    </label>
                                </div>
                            </div>

                            <div x-show="type === 'select'">
                                <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Opsi Pilihan (JSON Array)</label>
                                <textarea name="input_options" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-mono rounded-xl focus:ring-ksc-blue p-3 outline-none h-20">{{ $param['input_options'] }}</textarea>
                            </div>

                            <div>
                                <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Operator yang Diizinkan (JSON Array)</label>
                                <input name="allowed_operators" type="text" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-mono rounded-xl focus:ring-ksc-blue p-3 outline-none" value='{{ $param['allowed_operators'] }}' required>
                            </div>
                        </div>
                        <div class="flex items-center p-6 border-t border-slate-100 space-x-3 justify-end bg-slate-50/50">
                            <button data-modal-hide="modal-edit-parameter-{{ $param['uid'] }}" type="button" class="text-slate-500 bg-white hover:bg-slate-50 rounded-xl border border-slate-200 text-sm font-bold px-6 py-2.5 transition">Batal</button>
                            <button type="submit" class="text-white bg-ksc-blue hover:bg-ksc-dark font-bold rounded-xl text-sm px-8 py-2.5 transition shadow-lg">Perbarui Parameter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="modal-hapus-parameter-{{ $param['uid'] }}" tabindex="-1" aria-hidden="true"
            class="hidden fixed top-0 left-0 right-0 z-[70] w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-full items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
            <div class="relative w-full max-w-sm max-h-full">
                <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200">
                    <div class="p-4 md:p-6 text-center">
                        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="alert-triangle" class="text-red-600 w-8 h-8"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-slate-900">Konfirmasi Hapus</h3>
                        <p class="mb-6 text-sm text-slate-500 text-center">Parameter <strong>{{ $param['display_name'] }}</strong> akan dihapus permanen. Lanjutkan?</p>
                        <div class="flex justify-center gap-3">
                            <button data-modal-hide="modal-hapus-parameter-{{ $param['uid'] }}" type="button" class="text-slate-500 bg-white hover:bg-slate-50 rounded-xl border border-slate-200 text-sm font-bold px-5 py-2.5 transition">Batal</button>
                            <form action="{{ url('/' . $user['nama_role'] . '/' . $user['uid'] . '/' . $param['uid'] . '/dashboard/management-requirement-parameter/delete/process') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-white bg-red-600 hover:bg-red-800 font-bold rounded-xl text-sm px-5 py-2.5 transition shadow-sm">Ya, Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
