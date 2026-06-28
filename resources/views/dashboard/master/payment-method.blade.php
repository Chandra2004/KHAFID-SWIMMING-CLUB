@extends('layouts.layout-dashboard.app')

@section('dashboard-section')
    <div class="p-4 md:p-8">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 uppercase tracking-tight">Metode Pembayaran</h2>
                <p class="text-slate-500 text-sm">Kelola rekening bank dan e-wallet untuk pendaftaran event.</p>
            </div>
            <button data-modal-target="modal-tambah-payment" data-modal-toggle="modal-tambah-payment"
                class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-blue-200 transition-all hover:-translate-y-1">
                <x-lucide-plus-circle class="w-5 h-5" />
                <span>Tambah Rekening</span>
            </button>
        </div>

        {{-- Main Table Card --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama
                                Bank/Provider</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nomor
                                Rekening</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Atas Nama
                            </th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($paymentMethods as $pay)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-8 bg-slate-50 border border-slate-100 rounded overflow-hidden flex items-center justify-center">
                                            @if ($pay['photo'])
                                                <img src="{{ url('/file/kode-bank/' . $pay['photo']) }}"
                                                    alt="Logo {{ $pay['bank'] }}" class="w-full h-full object-contain">
                                            @else
                                                <x-lucide-image class="w-4 h-4 text-slate-300" />
                                            @endif
                                        </div>
                                        <span class="font-bold text-slate-900">{{ $pay['bank'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm bg-slate-100 px-2 py-1 rounded text-slate-600">
                                        {{ $pay['rekening'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                    {{ $pay['atas_nama'] }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        {{-- Edit Button --}}
                                        <button data-modal-target="modal-edit-payment-{{ $pay['uid'] }}"
                                            data-modal-toggle="modal-edit-payment-{{ $pay['uid'] }}"
                                            class="p-2 text-amber-600 hover:bg-amber-50 rounded-xl transition-colors">
                                            <x-lucide-edit-3 class="w-5 h-5" />
                                        </button>
                                        {{-- Delete Button --}}
                                        <button data-modal-target="modal-hapus-payment-{{ $pay['uid'] }}"
                                            data-modal-toggle="modal-hapus-payment-{{ $pay['uid'] }}"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                                            <x-lucide-trash-2 class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- MODAL EDIT (Per Row) --}}
                            <div id="modal-edit-payment-{{ $pay['uid'] }}" tabindex="-1" aria-hidden="true"
                                class="hidden fixed top-0 left-0 right-0 z-80 w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-full items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
                                <div class="relative w-full max-w-lg max-h-full">
                                    <div
                                        class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                                        {{-- Header Modal --}}
                                        <div
                                            class="flex items-center justify-between p-4 md:p-6 border-b border-slate-100 bg-slate-50/50">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-100">
                                                    <x-lucide-edit-3 class="w-5 h-5" />
                                                </div>
                                                <h3 class="text-xl font-bold text-slate-900 uppercase tracking-tight">Edit
                                                    Rekening</h3>
                                            </div>
                                            <button type="button"
                                                class="text-slate-400 hover:bg-slate-100 rounded-xl w-9 h-9 flex justify-center items-center transition"
                                                data-modal-hide="modal-edit-payment-{{ $pay['uid'] }}">
                                                <x-lucide-x class="w-5 h-5" />
                                            </button>
                                        </div>

                                        <form
                                            action="{{ url('/dashboard/management-payment/edit/process/' . $pay['uid']) }}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="p-6 md:p-8 space-y-6">
                                                {{-- Area Upload Logo --}}
                                                <div>
                                                    <label
                                                        class="block mb-2 text-sm font-bold text-slate-700 uppercase tracking-wider text-left">Logo
                                                        Bank / Provider</label>
                                                    <div class="flex items-center justify-center w-full">
                                                        <label
                                                            class="flex flex-col items-center justify-center w-full h-40 border-2 border-slate-300 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition relative overflow-hidden group">
                                                            <div id="preview-container-edit-{{ $pay['uid'] }}"
                                                                class="absolute inset-0">
                                                                <img id="preview-logo-edit-{{ $pay['uid'] }}"
                                                                    src="{{ $pay['photo'] ? url('/file/kode-bank/' . $pay['photo']) : url('/file/dummy/dummy.webp') }}"
                                                                    class="w-full h-full object-contain p-4">
                                                                <div
                                                                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                                                    <p
                                                                        class="text-white text-[10px] font-bold uppercase tracking-widest">
                                                                        Klik untuk Ganti</p>
                                                                </div>
                                                            </div>
                                                            <input type="file" name="photo" class="hidden"
                                                                accept="image/*"
                                                                onchange="previewImage(this, 'edit-{{ $pay['uid'] }}')">
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    {{-- Bank Selection --}}
                                                    <div class="text-left">
                                                        <label
                                                            class="block mb-2 text-xs font-bold text-slate-700 uppercase tracking-wider">Nama
                                                            Bank / Provider</label>
                                                        <select name="bank"
                                                            class="bg-slate-50 border border-slate-300 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 block w-full p-3.5 outline-none"
                                                            required>
                                                            <optgroup label="Bank Nasional">
                                                                @foreach (['BCA', 'Mandiri', 'BNI', 'BRI', 'BSI', 'BTN', 'CIMB Niaga', 'Permata', 'Danamon', 'Mega'] as $bank)
                                                                    <option value="{{ $bank }}"
                                                                        {{ $pay['bank'] == $bank ? 'selected' : '' }}>
                                                                        {{ $bank }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                            <optgroup label="E-Wallet & Digital">
                                                                @foreach (['QRIS', 'Dana', 'OVO', 'Gopay', 'ShopeePay', 'LinkAja'] as $wallet)
                                                                    <option value="{{ $wallet }}"
                                                                        {{ $pay['bank'] == $wallet ? 'selected' : '' }}>
                                                                        {{ $wallet }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                        </select>
                                                    </div>

                                                    {{-- Nomor Rekening --}}
                                                    <div class="text-left">
                                                        <label
                                                            class="block mb-2 text-xs font-bold text-slate-700 uppercase tracking-wider">Nomor
                                                            Rekening</label>
                                                        <input type="number" name="rekening"
                                                            value="{{ $pay['rekening'] }}"
                                                            class="bg-slate-50 border border-slate-300 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 block w-full p-3.5 outline-none"
                                                            required>
                                                    </div>

                                                    {{-- Atas Nama --}}
                                                    <div class="md:col-span-2 text-left">
                                                        <label
                                                            class="block mb-2 text-xs font-bold text-slate-700 uppercase tracking-wider">Atas
                                                            Nama Pemilik</label>
                                                        <input type="text" name="atas_nama"
                                                            value="{{ $pay['atas_nama'] }}"
                                                            class="bg-slate-50 border border-slate-300 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 block w-full p-3.5 outline-none"
                                                            required>
                                                    </div>
                                                </div>

                                                {{-- Action Buttons --}}
                                                <div
                                                    class="flex items-center pt-6 border-t border-slate-100 space-x-3 justify-end">
                                                    <button data-modal-hide="modal-edit-payment-{{ $pay['uid'] }}"
                                                        type="button"
                                                        class="text-slate-500 bg-white border border-slate-200 text-sm font-bold px-8 py-3 rounded-xl transition hover:bg-slate-50">Batal</button>
                                                    <button type="submit"
                                                        class="text-white bg-blue-600 hover:bg-blue-700 font-bold rounded-xl text-sm px-10 py-3 shadow-lg shadow-blue-100 transition-all hover:-translate-y-1">Simpan
                                                        Perubahan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL HAPUS (Per Row) --}}
                            <div id="modal-hapus-payment-{{ $pay['uid'] }}" tabindex="-1" aria-hidden="true"
                                class="hidden fixed top-0 left-0 right-0 z-80 w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-full items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
                                <div class="relative w-full max-w-sm max-h-full">
                                    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200">
                                        <div class="p-4 md:p-6 text-center">
                                            <div
                                                class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-red-50">
                                                <x-lucide-alert-triangle class="text-red-600 w-8 h-8" />
                                            </div>
                                            <h3 class="mb-2 text-xl font-bold text-slate-900">Konfirmasi Hapus</h3>
                                            <p class="mb-6 text-sm text-slate-500 leading-relaxed px-4">
                                                Metode pembayaran <strong>{{ $pay['bank'] }} -
                                                    {{ $pay['rekening'] }}</strong>
                                                akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                                            </p>
                                            <div class="flex justify-center gap-3">
                                                <button data-modal-hide="modal-hapus-payment-{{ $pay['uid'] }}"
                                                    type="button"
                                                    class="flex-1 px-5 py-2.5 border border-slate-200 text-slate-500 font-bold rounded-xl hover:bg-slate-50 transition">Batal</button>
                                                <form
                                                    action="{{ url('/dashboard/management-payment/delete/process/' . $pay['uid']) }}"
                                                    method="POST" class="flex-1">
                                                    @csrf
                                                    <button type="submit"
                                                        class="w-full px-5 py-2.5 bg-red-600 text-white font-bold rounded-xl hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-100 transition shadow-lg shadow-red-100">Ya,
                                                        Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                    <x-lucide-database class="w-12 h-12 mx-auto mb-4 opacity-20" />
                                    <p>Belum ada metode pembayaran yang terdaftar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div id="modal-tambah-payment" tabindex="-1" aria-hidden="true"
        class="hidden fixed top-0 left-0 right-0 z-80 w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-full items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="relative w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-900 uppercase tracking-tight">Tambah Rekening Baru</h3>
                    <button type="button"
                        class="text-slate-400 hover:bg-slate-100 rounded-xl w-8 h-8 flex justify-center items-center"
                        data-modal-hide="modal-tambah-payment">
                        <x-lucide-x class="w-5 h-5" />
                    </button>
                </div>
                <form
                    action="{{ url('/dashboard/management-payment/create/process') }}"
                    method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf

                    {{-- Logo Upload --}}
                    <div>
                        <label
                            class="block mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-left">Logo
                            Bank / Provider</label>
                        <div class="relative group">
                            <div id="preview-container-tambah"
                                class="w-full h-40 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl overflow-hidden flex items-center justify-center group-hover:border-blue-400 transition-colors">
                                <div id="placeholder-tambah" class="text-center">
                                    <x-lucide-image-plus class="w-10 h-10 text-slate-300 mx-auto mb-2" />
                                    <p class="text-xs text-slate-400 font-medium">Klik atau drop gambar di sini</p>
                                    <p class="text-[10px] text-slate-300 mt-1 uppercase">PNG, JPG, WEBP (Max 1MB)</p>
                                </div>
                                <img id="preview-logo-tambah" class="hidden w-full h-full object-contain p-4">
                            </div>
                            <input type="file" name="photo" class="absolute inset-0 opacity-0 cursor-pointer"
                                onchange="previewImage(this, 'tambah')">
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Nama Bank / Provider</label>
                        <select name="bank"
                            class="bg-slate-50 border border-slate-300 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 block w-full p-3 outline-none"
                            required>
                            <option value="" disabled selected>-- Pilih Bank / E-Wallet --</option>
                            <optgroup label="Bank Nasional">
                                <option value="BCA">BCA</option>
                                <option value="Mandiri">Mandiri</option>
                                <option value="BNI">BNI</option>
                                <option value="BRI">BRI</option>
                                <option value="BSI">BSI (Bank Syariah Indonesia)</option>
                                <option value="BTN">BTN</option>
                                <option value="CIMB Niaga">CIMB Niaga</option>
                                <option value="Permata">Permata Bank</option>
                                <option value="Danamon">Danamon</option>
                                <option value="Mega">Bank Mega</option>
                            </optgroup>
                            <optgroup label="E-Wallet & Digital">
                                <option value="QRIS">QRIS (All Payment)</option>
                                <option value="Dana">DANA</option>
                                <option value="OVO">OVO</option>
                                <option value="Gopay">GoPay</option>
                                <option value="ShopeePay">ShopeePay</option>
                                <option value="LinkAja">LinkAja</option>
                            </optgroup>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Nomor Rekening</label>
                        <input type="number" name="rekening" placeholder="Masukkan nomor tanpa spasi"
                            class="bg-slate-50 border border-slate-300 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 block w-full p-3 outline-none"
                            required>
                    </div>
                    <div>
                        <label class="block mb-2 text-xs font-bold text-slate-700 uppercase">Atas Nama</label>
                        <input type="text" name="atas_nama" placeholder="Contoh: PT KSC Indonesia"
                            class="bg-slate-50 border border-slate-300 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 block w-full p-3 outline-none"
                            required>
                    </div>
                    <div class="pt-4">
                        <button type="submit"
                            class="w-full px-6 py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all hover:-translate-y-1">
                            Simpan Metode Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        // Preview Image Function
        function previewImage(input, type) {
            const preview = document.getElementById(`preview-logo-${type}`);
            const container = document.getElementById(`preview-container-${type}`);
            const placeholder = document.getElementById(`placeholder-${type}`);

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (preview) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                    if (container) {
                        container.classList.add('border-blue-400', 'bg-blue-50/30');
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection

