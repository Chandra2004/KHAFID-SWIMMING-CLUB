<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\DataUser;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use WithFileUploads;

    public $user;
    public $profile;
    public $clubs = [];

    // Properti Form (Sesuai Skema Database Asli)
    public $nama_lengkap, $nama_panggilan, $username, $email, $no_telepon, $no_telepon_darurat;
    public $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $nomor_ktp, $club_uid;
    public $alamat_lengkap, $tinggi_badan, $berat_badan, $riwayat_penyakit;
    public $custom_club_name = '';
    public $password;
    public $showDeleteModal = false;

    // File Uploads
    public $foto_profil, $foto_ktp, $foto_akta, $foto_kk;

    public function mount()
    {
        // Otentikasi Akses
        if (!auth()->user()->can('my-profile.view')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat profil.');
        }

        // 1. Ambil data user yang sedang login
        $this->user = auth()->user();

        $this->profile = DataUser::where('user_uid', $this->user->uid)->first() ?? new DataUser();

        // 2. Load List Club untuk dropdown
        $this->clubs = \App\Models\Club::all();

        // 3. Mapping data ke Properti Livewire
        $this->nama_lengkap = $this->profile->full_name ?? ($this->user->username ?? $this->user->name);
        $this->nama_panggilan = $this->profile->nickname;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->no_telepon = $this->profile->phone_number;
        $this->no_telepon_darurat = $this->profile->backup_phone_number;
        $this->tempat_lahir = $this->profile->birth_place;
        $this->club_uid = $this->profile->club_uid;

        // Handling Tanggal Lahir (Format Y-m-d untuk input date HTML)
        if ($this->profile->birth_date) {
            $this->tanggal_lahir = \Carbon\Carbon::parse($this->profile->birth_date)->format('Y-m-d');
        }

        $this->jenis_kelamin = ($this->profile->gender ?? '') == 'female' ? 'P' : 'L';
        $this->nomor_ktp = $this->profile->identity_number;
        $this->alamat_lengkap = $this->profile->address;
        $this->tinggi_badan = $this->profile->height;
        $this->berat_badan = $this->profile->weight;
        $this->riwayat_penyakit = $this->profile->medical_history;
    }

    protected function rules()
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'nama_panggilan' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $this->user->id,
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'no_telepon' => 'required|string|max:20',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'nomor_ktp' => 'required|string|max:50',
            'alamat_lengkap' => 'required|string',
            'club_uid' => 'required|string',
            'custom_club_name' => 'required_if:club_uid,other|max:255',
            'foto_profil' => ($this->profile->profile_picture ? 'nullable' : 'required') . '|image|mimes:jpeg,jpg,png,webp|max:5120',
            'foto_ktp' => ($this->profile->identity_photo ? 'nullable' : 'required') . '|image|mimes:jpeg,jpg,png,webp|max:5120',
            'foto_akta' => ($this->profile->birth_certificate_photo ? 'nullable' : 'required') . '|image|mimes:jpeg,jpg,png,webp|max:5120',
            'foto_kk' => ($this->profile->family_card_photo ? 'nullable' : 'required') . '|image|mimes:jpeg,jpg,png,webp|max:5120',
            'password' => 'nullable|min:8',
        ];
    }

    protected function messages()
    {
        return [
            'required' => ':attribute wajib diisi.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat: jpg, jpeg, png, atau webp.',
            'max' => ':attribute tidak boleh lebih dari 5MB.',
            'unique' => ':attribute sudah digunakan.',
            'min' => ':attribute minimal :min karakter.',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'nama_lengkap' => 'Nama Lengkap',
            'nama_panggilan' => 'Nama Panggilan',
            'username' => 'Username',
            'email' => 'Alamat Email',
            'no_telepon' => 'No. WhatsApp',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'nomor_ktp' => 'NIK / No. KTP',
            'alamat_lengkap' => 'Alamat',
            'club_uid' => 'Klub/Asal Sekolah',
            'custom_club_name' => 'Nama Klub/Asal Sekolah Baru',
            'foto_profil' => 'Foto Profil',
            'foto_ktp' => 'Foto KTP',
            'foto_akta' => 'Foto Akta',
            'foto_kk' => 'Foto KK',
            'password' => 'Password',
        ];
    }

    public function updated($propertyName)
    {
        try {
            $this->validateOnly($propertyName);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => $e->validator->errors()->first($propertyName),
            ]);
            throw $e;
        }
    }

    public function save()
    {
        try {
            // Otorisasi Edit
            if (!auth()->user()->can('my-profile.edit')) {
                throw new \Exception("Anda tidak memiliki izin untuk mengedit profil.");
            }

            $this->validate();

            // Update User Account
            $this->user->update([
                'username' => $this->username,
                'email' => $this->email,
            ]);

            if ($this->password) {
                // Otorisasi Ganti Password
                if (!auth()->user()->can('my-profile.change-password')) {
                    throw new \Exception("Anda tidak memiliki izin untuk mengganti password.");
                }
                $this->user->update(['password' => Hash::make($this->password)]);
            }

            // Resolve club/school
            $final_club_uid = $this->club_uid;
            if ($this->club_uid === 'other' && !empty($this->custom_club_name)) {
                $existingClub = \App\Models\Club::where('name', $this->custom_club_name)->first();
                if ($existingClub) {
                    $final_club_uid = $existingClub->uid;
                } else {
                    $newClub = \App\Models\Club::create([
                        'name' => $this->custom_club_name,
                        'short_name' => substr($this->custom_club_name, 0, 15),
                    ]);
                    $final_club_uid = $newClub->uid;
                }
            }

            // Mapping data ke kolom database asli
            $profileData = [
                'full_name' => $this->nama_lengkap,
                'nickname' => $this->nama_panggilan,
                'phone_number' => $this->no_telepon,
                'backup_phone_number' => $this->no_telepon_darurat,
                'birth_place' => $this->tempat_lahir,
                'birth_date' => $this->tanggal_lahir,
                'gender' => $this->jenis_kelamin == 'P' ? 'female' : 'male',
                'identity_number' => $this->nomor_ktp,
                'address' => $this->alamat_lengkap,
                'height' => $this->tinggi_badan,
                'weight' => $this->berat_badan,
                'medical_history' => $this->riwayat_penyakit,
                'club_uid' => $final_club_uid,
            ];

            // Upload & Konversi ke WebP via Global Helper
            if ($this->foto_profil) {
                $path = ImageHelper::uploadToWebp($this->foto_profil, 'profiles', $this->profile->profile_picture);
                if (!$path) throw new \Exception("Format Foto Profil tidak didukung atau file rusak.");
                $profileData['profile_picture'] = $path;
            }
            if ($this->foto_ktp) {
                $path = ImageHelper::uploadToWebp($this->foto_ktp, 'ktp_documents', $this->profile->identity_photo);
                if (!$path) throw new \Exception("Format Foto KTP tidak didukung atau file rusak.");
                $profileData['identity_photo'] = $path;
            }
            if ($this->foto_akta) {
                $path = ImageHelper::uploadToWebp($this->foto_akta, 'akta_documents', $this->profile->birth_certificate_photo);
                if (!$path) throw new \Exception("Format Foto Akta tidak didukung atau file rusak.");
                $profileData['birth_certificate_photo'] = $path;
            }
            if ($this->foto_kk) {
                $path = ImageHelper::uploadToWebp($this->foto_kk, 'kk_documents', $this->profile->family_card_photo);
                if (!$path) throw new \Exception("Format Foto KK tidak didukung atau file rusak.");
                $profileData['family_card_photo'] = $path;
            }

            DataUser::updateOrCreate(['user_uid' => $this->user->uid], $profileData);

            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Profil berhasil diperbarui!',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Silakan periksa kembali form Anda, Anda harus mengisi data wajib dengan lengkap dan benar.',
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Gagal memperbarui profil: ' . $e->getMessage(),
            ]);
        }
    }

    public function deleteAccount()
    {
        try {
            // Otorisasi Hapus
            if (!auth()->user()->can('my-profile.delete')) {
                throw new \Exception("Anda tidak memiliki izin untuk menghapus akun.");
            }

            $user = auth()->user();

            // Hapus Profile & User (Soft Delete)
            DataUser::where('user_uid', $user->uid)->delete();
            $user->delete();

            // Logout & Redirect
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login')->with('success', 'Akun Anda berhasil dihapus.');

        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Gagal menghapus akun: ' . $e->getMessage(),
            ]);
            $this->showDeleteModal = false;
        }
    }
};
?>

<div class="p-4 md:p-8 overflow-y-auto h-screen bg-slate-50/50 pb-32 text-left">
    @can('my-profile.view')
        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-900 leading-tight tracking-tight uppercase">Pengaturan Profil</h2>
            <p class="text-sm text-slate-500 font-medium italic">Kelola identitas digital dan dokumen verifikasi sesuai skema database.</p>
        </div>

        <form @submit.prevent="
            let promises = [];
            let fp = document.getElementById('mp_foto_profil_galeri')?.files[0] || document.getElementById('mp_foto_profil_kamera')?.files[0];
            let fk = document.getElementById('mp_foto_ktp_galeri')?.files[0] || document.getElementById('mp_foto_ktp_kamera')?.files[0];
            let fa = document.getElementById('mp_foto_akta_galeri')?.files[0] || document.getElementById('mp_foto_akta_kamera')?.files[0];
            let fkk = document.getElementById('mp_foto_kk_galeri')?.files[0] || document.getElementById('mp_foto_kk_kamera')?.files[0];
            if (fp) promises.push(new Promise((res, rej) => { @this.upload('foto_profil', fp, res, rej); }));
            if (fk) promises.push(new Promise((res, rej) => { @this.upload('foto_ktp', fk, res, rej); }));
            if (fa) promises.push(new Promise((res, rej) => { @this.upload('foto_akta', fa, res, rej); }));
            if (fkk) promises.push(new Promise((res, rej) => { @this.upload('foto_kk', fkk, res, rej); }));
            if (promises.length > 0) {
                $refs.mpSaveBtn.disabled = true;
                $refs.mpSaveText.classList.add('hidden');
                $refs.mpLoadingText.classList.remove('hidden');
                Promise.all(promises).then(() => {
                    @this.call('save');
                    setTimeout(() => { $refs.mpSaveBtn.disabled = false; $refs.mpSaveText.classList.remove('hidden'); $refs.mpLoadingText.classList.add('hidden'); }, 2000);
                }).catch(() => {
                    alert('Gagal mengunggah file. Silakan coba lagi.');
                    $refs.mpSaveBtn.disabled = false;
                    $refs.mpSaveText.classList.remove('hidden');
                    $refs.mpLoadingText.classList.add('hidden');
                });
            } else {
                @this.call('save');
            }
        " class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="space-y-6">
                {{-- Foto Profil --}}
                <div class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-sm text-center group">
                    <div class="relative w-40 h-40 mx-auto mb-6" wire:ignore>
                        <img id="mp_preview_foto_profil"
                            src="{{ $profile->profile_picture ? asset($profile->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($user->username) . '&background=f8fafc&color=1e40af&size=256' }}"
                            class="w-full h-full rounded-[2.5rem] object-cover border-4 border-slate-50 shadow-xl group-hover:scale-105 transition-transform duration-500">

                        @can('my-profile.edit')
                            <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                                <label class="bg-white border border-slate-200 p-3 rounded-2xl shadow-xl hover:text-blue-600 transition-all active:scale-95 cursor-pointer group/btn" title="Ambil dari Galeri">
                                    <x-lucide-image class="w-5 h-5 text-slate-500 group-hover/btn:text-blue-600" />
                                    <input type="file" id="mp_foto_profil_galeri" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="previewSingleImage(this, 'mp_preview_foto_profil'); document.getElementById('mp_foto_profil_kamera').value='';">
                                </label>
                                <label class="bg-white border border-slate-200 p-3 rounded-2xl shadow-xl hover:text-emerald-600 transition-all active:scale-95 cursor-pointer group/btn" title="Buka Kamera">
                                    <x-lucide-camera class="w-5 h-5 text-slate-500 group-hover/btn:text-emerald-600" />
                                    <input type="file" id="mp_foto_profil_kamera" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="user" onchange="previewSingleImage(this, 'mp_preview_foto_profil'); document.getElementById('mp_foto_profil_galeri').value='';">
                                </label>
                            </div>
                        @endcan
                    </div>
                    @error('foto_profil')
                        <span class="text-[10px] text-red-500 font-bold uppercase block mt-4 mb-4 text-center">{{ $message }}</span>
                    @enderror
                    <h3 class="text-xl font-bold text-slate-900 uppercase tracking-tight">{{ $nama_lengkap }}</h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1 italic">Profil Utama <span class="text-rose-500">*</span></p>
                </div>

                {{-- Dokumen --}}
                <div class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-sm space-y-6">
                    <div>
                        <label class="block mb-4 text-[11px] font-black text-slate-400 uppercase tracking-widest text-left">Foto KTP / Kartu Pelajar <span class="text-rose-500">*</span></label>
                        <div class="relative w-full h-40 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden group flex items-center justify-center" wire:ignore>
                            @if($profile->identity_photo)
                                <img id="mp_preview_foto_ktp" src="{{ route('document.view', ['type' => 'ktp', 'filename' => basename($profile->identity_photo)]) }}" class="w-full h-full object-cover">
                            @else
                                <img id="mp_preview_foto_ktp" src="" class="w-full h-full object-cover hidden">
                                <div id="mp_placeholder_ktp" class="text-center">
                                    <x-lucide-image-plus class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Upload KTP</span>
                                </div>
                            @endif

                            @can('my-profile.edit')
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4">
                                    <label class="p-3 bg-white rounded-xl cursor-pointer hover:scale-110 transition active:scale-95" title="Galeri">
                                        <x-lucide-image class="w-5 h-5 text-slate-700" />
                                        <input type="file" id="mp_foto_ktp_galeri" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="previewSingleImage(this, 'mp_preview_foto_ktp', 'mp_placeholder_ktp'); document.getElementById('mp_foto_ktp_kamera').value='';">
                                    </label>
                                    <label class="p-3 bg-white rounded-xl cursor-pointer hover:scale-110 transition active:scale-95" title="Kamera">
                                        <x-lucide-camera class="w-5 h-5 text-slate-700" />
                                        <input type="file" id="mp_foto_ktp_kamera" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="previewSingleImage(this, 'mp_preview_foto_ktp', 'mp_placeholder_ktp'); document.getElementById('mp_foto_ktp_galeri').value='';">
                                    </label>
                                </div>
                                {{-- Mobile View Buttons --}}
                                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2 md:hidden">
                                    <label class="p-2 bg-white/90 backdrop-blur rounded-lg shadow-lg border border-slate-200">
                                        <x-lucide-image class="w-4 h-4 text-slate-600" />
                                        <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="previewSingleImage(this, 'mp_preview_foto_ktp', 'mp_placeholder_ktp'); document.getElementById('mp_foto_ktp_galeri').files = this.files;">
                                    </label>
                                    <label class="p-2 bg-white/90 backdrop-blur rounded-lg shadow-lg border border-slate-200">
                                        <x-lucide-camera class="w-4 h-4 text-slate-600" />
                                        <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="previewSingleImage(this, 'mp_preview_foto_ktp', 'mp_placeholder_ktp'); document.getElementById('mp_foto_ktp_galeri').files = this.files;">
                                    </label>
                                </div>
                            @endcan
                        </div>
                        @error('foto_ktp')
                            <span class="text-[10px] text-red-500 font-bold uppercase mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-4 text-[11px] font-black text-slate-400 uppercase tracking-widest text-left">Foto Akta Kelahiran <span class="text-rose-500">*</span></label>
                        <div class="relative w-full h-40 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden group flex items-center justify-center" wire:ignore>
                            @if($profile->birth_certificate_photo)
                                <img id="mp_preview_foto_akta" src="{{ route('document.view', ['type' => 'akta', 'filename' => basename($profile->birth_certificate_photo)]) }}" class="w-full h-full object-cover">
                            @else
                                <img id="mp_preview_foto_akta" src="" class="w-full h-full object-cover hidden">
                                <div id="mp_placeholder_akta" class="text-center">
                                    <x-lucide-file-plus class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Upload Akta</span>
                                </div>
                            @endif

                            @can('my-profile.edit')
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4">
                                    <label class="p-3 bg-white rounded-xl cursor-pointer hover:scale-110 transition active:scale-95" title="Galeri">
                                        <x-lucide-image class="w-5 h-5 text-slate-700" />
                                        <input type="file" id="mp_foto_akta_galeri" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="previewSingleImage(this, 'mp_preview_foto_akta', 'mp_placeholder_akta'); document.getElementById('mp_foto_akta_kamera').value='';">
                                    </label>
                                    <label class="p-3 bg-white rounded-xl cursor-pointer hover:scale-110 transition active:scale-95" title="Kamera">
                                        <x-lucide-camera class="w-5 h-5 text-slate-700" />
                                        <input type="file" id="mp_foto_akta_kamera" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="previewSingleImage(this, 'mp_preview_foto_akta', 'mp_placeholder_akta'); document.getElementById('mp_foto_akta_galeri').value='';">
                                    </label>
                                </div>
                                {{-- Mobile View Buttons --}}
                                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2 md:hidden">
                                    <label class="p-2 bg-white/90 backdrop-blur rounded-lg shadow-lg border border-slate-200">
                                        <x-lucide-image class="w-4 h-4 text-slate-600" />
                                        <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="previewSingleImage(this, 'mp_preview_foto_akta', 'mp_placeholder_akta'); document.getElementById('mp_foto_akta_galeri').files = this.files;">
                                    </label>
                                    <label class="p-2 bg-white/90 backdrop-blur rounded-lg shadow-lg border border-slate-200">
                                        <x-lucide-camera class="w-4 h-4 text-slate-600" />
                                        <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="previewSingleImage(this, 'mp_preview_foto_akta', 'mp_placeholder_akta'); document.getElementById('mp_foto_akta_galeri').files = this.files;">
                                    </label>
                                </div>
                            @endcan
                        </div>
                        @error('foto_akta')
                            <span class="text-[10px] text-red-500 font-bold uppercase mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-4 text-[11px] font-black text-slate-400 uppercase tracking-widest text-left">Foto Kartu Keluarga (KK) <span class="text-rose-500">*</span></label>
                        <div class="relative w-full h-40 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden group flex items-center justify-center" wire:ignore>
                            @if($profile->family_card_photo)
                                <img id="mp_preview_foto_kk" src="{{ route('document.view', ['type' => 'kk', 'filename' => basename($profile->family_card_photo)]) }}" class="w-full h-full object-cover">
                            @else
                                <img id="mp_preview_foto_kk" src="" class="w-full h-full object-cover hidden">
                                <div id="mp_placeholder_kk" class="text-center">
                                    <x-lucide-users class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Upload KK</span>
                                </div>
                            @endif

                            @can('my-profile.edit')
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4">
                                    <label class="p-3 bg-white rounded-xl cursor-pointer hover:scale-110 transition active:scale-95" title="Galeri">
                                        <x-lucide-image class="w-5 h-5 text-slate-700" />
                                        <input type="file" id="mp_foto_kk_galeri" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="previewSingleImage(this, 'mp_preview_foto_kk', 'mp_placeholder_kk'); document.getElementById('mp_foto_kk_kamera').value='';">
                                    </label>
                                    <label class="p-3 bg-white rounded-xl cursor-pointer hover:scale-110 transition active:scale-95" title="Kamera">
                                        <x-lucide-camera class="w-5 h-5 text-slate-700" />
                                        <input type="file" id="mp_foto_kk_kamera" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="previewSingleImage(this, 'mp_preview_foto_kk', 'mp_placeholder_kk'); document.getElementById('mp_foto_kk_galeri').value='';">
                                    </label>
                                </div>
                                {{-- Mobile View Buttons --}}
                                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2 md:hidden">
                                    <label class="p-2 bg-white/90 backdrop-blur rounded-lg shadow-lg border border-slate-200">
                                        <x-lucide-image class="w-4 h-4 text-slate-600" />
                                        <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="previewSingleImage(this, 'mp_preview_foto_kk', 'mp_placeholder_kk'); document.getElementById('mp_foto_kk_galeri').files = this.files;">
                                    </label>
                                    <label class="p-2 bg-white/90 backdrop-blur rounded-lg shadow-lg border border-slate-200">
                                        <x-lucide-camera class="w-4 h-4 text-slate-600" />
                                        <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="previewSingleImage(this, 'mp_preview_foto_kk', 'mp_placeholder_kk'); document.getElementById('mp_foto_kk_galeri').files = this.files;">
                                    </label>
                                </div>
                            @endcan
                        </div>
                        @error('foto_kk')
                            <span class="text-[10px] text-red-500 font-bold uppercase mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white border border-slate-200 rounded-[2.5rem] shadow-sm overflow-hidden text-left">
                    <div class="border-b border-slate-100 p-8 bg-slate-50/30 flex items-center gap-3">
                        <x-lucide-user-cog class="w-5 h-5 text-ksc-blue" />
                        <h4 class="font-bold text-slate-900 uppercase tracking-tight">Data Personal</h4>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        @php $isDisabled = !auth()->user()->can('my-profile.edit'); @endphp
                        <div>
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="nama_lengkap" @disabled($isDisabled)
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                            @error('nama_lengkap') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Nama Panggilan <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="nama_panggilan" @disabled($isDisabled)
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                            @error('nama_panggilan') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Username <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="username" @disabled($isDisabled)
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                            @error('username') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Email <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="email" @disabled($isDisabled)
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                            @error('email') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">No. WhatsApp <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="no_telepon" @disabled($isDisabled)
                                    class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                                @error('no_telepon') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">WhatsApp Darurat</label>
                                <input type="text" wire:model="no_telepon_darurat" @disabled($isDisabled)
                                    class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                                @error('no_telepon_darurat') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Tempat Lahir <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="tempat_lahir" @disabled($isDisabled) placeholder="Jawa Timur, Sidoarjo, Krian"
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                            @error('tempat_lahir') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Tanggal Lahir <span class="text-rose-500">*</span></label>
                            <input type="date" wire:model="tanggal_lahir" @disabled($isDisabled)
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                            @error('tanggal_lahir') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Jenis Kelamin <span class="text-rose-500">*</span></label>
                            <select wire:model="jenis_kelamin" @disabled($isDisabled)
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                                <option value="">Pilih</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            @error('jenis_kelamin') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">NIK / No. KTP <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="nomor_ktp" @disabled($isDisabled)
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                            @error('nomor_ktp') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Alamat <span class="text-rose-500">*</span></label>
                            <textarea wire:model="alamat_lengkap" rows="2" @disabled($isDisabled)
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70"></textarea>
                            @error('alamat_lengkap') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Klub/Asal Sekolah <span class="text-rose-500">*</span></label>
                            <select wire:model.live="club_uid" @disabled($isDisabled)
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70 transition mb-3">
                                <option value="">Pilih Klub/Sekolah</option>
                                @foreach ($clubs as $club)
                                    <option value="{{ $club->uid }}">{{ $club->name }} ({{ $club->short_name }})</option>
                                @endforeach
                                <option value="other">Lainnya (Input Manual)</option>
                            </select>
                            @error('club_uid') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase block mb-3">{{ $message }}</span> @enderror

                            @if($club_uid === 'other')
                                <input type="text" wire:model="custom_club_name" @disabled($isDisabled) placeholder="Masukkan nama klub atau asal sekolah"
                                    class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                                @error('custom_club_name') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase block">{{ $message }}</span> @enderror
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Fisik & Keahlian --}}
                <div class="bg-white border border-slate-200 rounded-[2.5rem] shadow-sm overflow-hidden text-left">
                    <div class="border-b border-slate-100 p-8 bg-slate-50/30 flex items-center gap-3">
                        <x-lucide-activity class="w-5 h-5 text-red-500" />
                        <h4 class="font-bold text-slate-900 uppercase tracking-tight">Data Fisik & Keahlian</h4>
                    </div>
                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Tinggi (cm)</label>
                                <input type="number" wire:model="tinggi_badan" @disabled($isDisabled)
                                    class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                            </div>
                            <div>
                                <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Berat (kg)</label>
                                <input type="number" wire:model="berat_badan" @disabled($isDisabled)
                                    class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Riwayat Medis (Optional)</label>
                            <textarea wire:model="riwayat_penyakit" rows="2" placeholder="Contoh: Asma, Alergi Kaporit, dll" @disabled($isDisabled)
                                class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none disabled:opacity-70"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Keamanan --}}
                @can('my-profile.change-password')
                    <div class="bg-white border border-slate-200 rounded-[2.5rem] shadow-sm overflow-hidden text-left">
                        <div class="border-b border-slate-100 p-8 bg-slate-50/30 flex items-center gap-3">
                            <x-lucide-lock class="w-5 h-5 text-slate-600" />
                            <h4 class="font-bold text-slate-900 uppercase tracking-tight">Keamanan & Password</h4>
                        </div>
                        <div class="p-8">
                            <label class="block mb-2 text-[11px] font-black text-slate-400 uppercase tracking-widest">Ganti Password (Kosongkan jika tidak ingin mengubah)</label>
                            <div class="relative group" x-data="{ show: false }">
                                <input :type="show ? 'text' : 'password'" wire:model="password" placeholder="Masukkan password baru jika ingin mengganti"
                                    class="bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-2xl focus:ring-4 focus:ring-blue-50 block w-full p-4 outline-none transition">
                                <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                    <template x-if="!show">
                                        <x-lucide-eye class="w-4 h-4" />
                                    </template>
                                    <template x-if="show">
                                        <x-lucide-eye-off class="w-4 h-4" />
                                    </template>
                                </button>
                            </div>
                            @error('password') <span class="text-[10px] text-red-500 font-bold mt-1 uppercase">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endcan

                @can('my-profile.edit')
                    <div class="flex justify-end pt-4">
                        <button type="submit" x-ref="mpSaveBtn"
                            class="bg-slate-900 hover:bg-black text-white px-10 py-5 rounded-2xl font-black text-xs transition shadow-2xl shadow-slate-300 flex items-center gap-3 uppercase tracking-[0.2em] transform hover:-translate-y-1 active:scale-95 group disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none">
                            <span x-ref="mpSaveText">Simpan Perubahan</span>
                            <span x-ref="mpLoadingText" class="hidden">Memproses...</span>
                            <x-lucide-save class="w-5 h-5 text-ksc-blue transition-transform group-hover:rotate-12" />
                        </button>
                    </div>
                @endcan

                {{-- Danger Zone --}}
                @can('my-profile.delete')
                    <div class="bg-rose-50/50 border border-rose-100 rounded-[2.5rem] p-8 mt-12">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left">
                            <div>
                                <h4 class="text-lg font-black text-rose-900 uppercase tracking-tight">Zona Berbahaya</h4>
                                <p class="text-[10px] text-rose-600 font-bold uppercase tracking-widest mt-1">Hapus akun secara permanen</p>
                                <p class="text-xs text-rose-500 font-medium mt-2">Setelah dihapus, data profil dan akses Anda tidak dapat dipulihkan secara mandiri.</p>
                            </div>
                            <button type="button" wire:click="$set('showDeleteModal', true)"
                                class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest transition shadow-xl shadow-rose-200 shrink-0 transform hover:-translate-y-1 active:scale-95">
                                Hapus Akun
                            </button>
                        </div>
                    </div>
                @endcan
            </div>
        </form>
    @else
        <div class="flex flex-col items-center justify-center h-full py-20 text-center">
            <div class="w-24 h-24 bg-rose-50 text-rose-500 rounded-4xl flex items-center justify-center mb-6 shadow-inner">
                <x-lucide-shield-off class="w-12 h-12" />
            </div>
            <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic">Akses Ditolak</h3>
            <p class="text-sm text-slate-500 font-medium mt-2">Anda tidak memiliki izin untuk mengelola profil.</p>
        </div>
    @endcan

    {{-- Modal Konfirmasi Hapus --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative bg-white rounded-[3rem] shadow-2xl max-w-md w-full p-10 text-center transform transition-all">
                <div class="w-24 h-24 bg-rose-50 text-rose-600 rounded-4xl flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <x-lucide-alert-triangle class="w-12 h-12" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic leading-tight">Konfirmasi<br>Penghapusan Akun</h3>
                <p class="text-sm text-slate-500 font-medium mt-4">Apakah Anda yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan secara mandiri.</p>

                <div class="grid grid-cols-2 gap-4 mt-10">
                    <button wire:click="$set('showDeleteModal', false)"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-600 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest transition">
                        Batal
                    </button>
                    <button wire:click="deleteAccount" wire:loading.attr="disabled"
                        class="bg-rose-600 hover:bg-rose-700 text-white py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="deleteAccount">Ya, Hapus</span>
                        <span wire:loading wire:target="deleteAccount">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
