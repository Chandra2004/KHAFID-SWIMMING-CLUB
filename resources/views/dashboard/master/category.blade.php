@extends('layouts.layout-dashboard.app')

@section('dashboard-section')
    <div class="p-4 md:p-8 overflow-y-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-900 leading-tight tracking-tight">Master Data Gaya Renang</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium">Definisikan jenis lomba dan gaya renang yang akan digunakan dalam event.</p>
            </div>
            <button data-modal-target="modal-tambah-kategori" data-modal-toggle="modal-tambah-kategori"
                class="flex items-center gap-2 bg-ksc-blue hover:bg-ksc-dark text-white px-5 py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-100 hover:shadow-blue-200 active:scale-95 group"
                type="button">
                <i data-lucide="award" class="w-5 h-5 group-hover:rotate-12 transition"></i>
                <span>Tambah Gaya Baru</span>
            </button>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">uid</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama
                                Kategori
                            </th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Slug</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($categories['data'] as $category)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $category['uid'] }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $category['nama_kategori'] }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span
                                        class="px-3 py-1 bg-slate-100 text-slate-500 rounded-lg font-mono text-[11px] border border-slate-200">{{ $category['slug_kategori'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex justify-center gap-3">
                                        <button data-modal-target="modal-edit-kategori-{{ $category['uid'] }}"
                                            data-modal-toggle="modal-edit-kategori-{{ $category['uid'] }}"
                                            class="text-blue-600 hover:text-blue-800 transition p-1 hover:bg-blue-50 rounded">
                                            <i data-lucide="edit" class="w-5 h-5"></i>
                                        </button>
                                        <button data-modal-target="modal-hapus-kategori-{{ $category['uid'] }}"
                                            data-modal-toggle="modal-hapus-kategori-{{ $category['uid'] }}"
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

        <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4 px-2 pb-10">
            <div class="text-left">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                    Halaman <span class="text-slate-900">{{ $categories['current_page'] }}</span>
                    dari <span class="text-slate-900">{{ $categories['last_page'] }}</span>
                    — Total <span class="text-ksc-blue">{{ $categories['total'] }}</span> Kategori
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if ($categories['current_page'] > 1)
                    <a href="{{ url('/' . $user['nama_role'] . '/dashboard/management-category/page/' . ($categories['current_page'] - 1)) }}"
                        class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm active:scale-95">
                        <i data-lucide="chevron-left" class="w-4 h-4 text-slate-400"></i>
                        <span>Prev</span>
                    </a>
                @else
                    <div
                        class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-100 text-slate-300 rounded-xl font-bold text-xs cursor-not-allowed opacity-60">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        <span>Prev</span>
                    </div>
                @endif

                <div class="flex items-center bg-white border border-slate-200 rounded-xl px-4 py-2.5 shadow-sm">
                    <span class="text-xs font-black text-slate-900">{{ $categories['current_page'] }}</span>
                </div>

                @if ($categories['current_page'] < $categories['last_page'])
                    <a href="{{ url('/' . $user['nama_role'] . '/dashboard/management-category/page/' . ($categories['current_page'] + 1)) }}"
                        class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm active:scale-95">
                        <span>Next</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
                    </a>
                @else
                    <div
                        class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-100 text-slate-300 rounded-xl font-bold text-xs cursor-not-allowed opacity-60">
                        <span>Next</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="modal-tambah-kategori" tabindex="-1" aria-hidden="true"
        class="hidden fixed top-0 left-0 right-0 z-[70] w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-full items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
        <div class="relative w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between p-4 md:p-5 border-b border-slate-100 rounded-t">
                    <h3 class="text-lg font-bold text-slate-900 leading-tight">Buat Kategori Baru</h3>
                    <button type="button"
                        class="text-slate-400 bg-transparent hover:bg-slate-100 hover:text-slate-900 rounded-lg text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                        data-modal-hide="modal-tambah-kategori">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form
                    action="{{ url('/' . $user['nama_role'] . '/' . $user['uid'] . '/dashboard/management-category/create/process') }}"
                    method="POST">
                    @csrf

                    <div class="p-4 md:p-5 space-y-5">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-slate-700">Nama Kategori</label>
                            <input name="nama_kategori" type="text"
                                class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-ksc-blue focus:border-ksc-blue block w-full p-3 outline-none transition"
                                placeholder="Contoh: renang gaya bebas" required>
                        </div>
                    </div>
                    <div class="flex items-center p-4 md:p-5 border-t border-slate-100 space-x-3 justify-end">
                        <button data-modal-hide="modal-tambah-kategori" type="button"
                            class="text-slate-500 bg-white hover:bg-slate-100 focus:ring-4 focus:outline-none focus:ring-slate-200 rounded-lg border border-slate-200 text-sm font-medium px-5 py-2.5 transition">Batal</button>
                        <button type="submit"
                            class="text-white bg-ksc-blue hover:bg-ksc-dark focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-lg text-sm px-6 py-2.5 text-center transition">Simpan
                            Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($categories['data'] as $category)
        <div id="modal-edit-kategori-{{ $category['uid'] }}" tabindex="-1" aria-hidden="true"
            class="hidden fixed top-0 left-0 right-0 z-[70] w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-full items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
            <div class="relative w-full max-w-md max-h-full">
                <div class="relative bg-white rounded-lg shadow-2xl border border-slate-200">
                    <div class="flex items-center justify-between p-4 md:p-5 border-b border-slate-100 rounded-t">
                        <h3 class="text-lg font-bold text-slate-900 leading-tight">Ubah Data Kategori</h3>
                        <button type="button"
                            class="text-slate-400 bg-transparent hover:bg-slate-100 hover:text-slate-900 rounded-lg text-sm w-9 h-9 ms-auto inline-flex justify-center items-center"
                            data-modal-hide="modal-edit-kategori-{{ $category['uid'] }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <form
                        action="{{ url('/' . $user['nama_role'] . '/' . $user['uid'] . '/' . $category['uid'] . '/dashboard/management-category/edit/process') }}"
                        method="POST">
                        @csrf
                        <div class="p-4 md:p-5 space-y-5">
                            <div>
                                <label class="block mb-2 text-sm font-semibold text-slate-700">Nama Kategori</label>
                                <input name="nama_kategori" type="text"
                                    class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-ksc-blue focus:border-ksc-blue block w-full p-3 outline-none transition"
                                    value="{{ $category['nama_kategori'] }}" required>
                            </div>
                        </div>
                        <div class="flex items-center p-4 md:p-5 border-t border-slate-100 space-x-3 justify-end">
                            <button data-modal-hide="modal-edit-kategori-{{ $category['uid'] }}" type="button"
                                class="text-slate-500 bg-white hover:bg-slate-100 focus:ring-4 focus:outline-none focus:ring-slate-200 rounded-lg border border-slate-200 text-sm font-medium px-5 py-2.5 transition">Batal</button>
                            <button type="submit"
                                class="text-white bg-ksc-blue hover:bg-ksc-dark focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-lg text-sm px-6 py-2.5 text-center transition">Perbarui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="modal-hapus-kategori-{{ $category['uid'] }}" tabindex="-1" aria-hidden="true"
            class="hidden fixed top-0 left-0 right-0 z-[70] w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-full items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity">
            <div class="relative w-full max-w-sm max-h-full">
                <div class="relative bg-white rounded-lg shadow-2xl border border-slate-200">
                    <div class="p-4 md:p-6 text-center">
                        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="alert-triangle" class="text-red-600 w-8 h-8"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-slate-900">Konfirmasi Hapus</h3>
                        <p class="mb-6 text-sm text-slate-500">Kategori <strong>{{ $category['nama_kategori'] }}</strong>
                            ini akan dihapus
                            permanen. Lanjutkan?</p>
                        <div class="flex justify-center gap-3">
                            <button data-modal-hide="modal-hapus-kategori-{{ $category['uid'] }}" type="button"
                                class="text-slate-500 bg-white hover:bg-slate-100 rounded-lg border border-slate-200 text-sm font-medium px-5 py-2.5 transition">Batal</button>
                            <form
                                action="{{ url('/' . $user['nama_role'] . '/' . $user['uid'] . '/' . $category['uid'] . '/dashboard/management-category/delete/process') }}"
                                method="POST">
                                @csrf
                                <button type="submit"
                                    class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-bold rounded-lg text-sm px-5 py-2.5 transition shadow-sm">Ya,
                                    Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
