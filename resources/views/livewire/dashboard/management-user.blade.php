<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\DataUser;
use App\Models\Club;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Helpers\ImageHelper;

new class extends Component {
    use WithFileUploads;
    use WithPagination;

    public $search = '';
    public $filterRole = '';
    public $filterDeleted = false; // true untuk melihat data sampah

    // Form properties
    public $uid, $username, $email, $password, $password_confirm;
    public $nama_lengkap, $nama_panggilan, $no_telepon, $no_telepon_darurat;
    public $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $nomor_ktp, $nama_klub, $alamat;
    public $tinggi_badan, $berat_badan, $riwayat_penyakit, $tingkat_keahlian;
    public $foto_profil, $foto_ktp, $foto_akta, $foto_kk;
    public $existing_foto_profil, $existing_foto_ktp, $existing_foto_akta, $existing_foto_kk;
    public $uid_role, $is_active = true;
    public $selectedDirectPermissions = [];

    public $showModal = false;
    public $showPermissionModal = false;
    public $modalMode = 'create'; // create, edit, delete

    protected $listeners = ['refreshUserList' => '$refresh'];

    public function with()
    {
        $usersQuery = User::with(['profile', 'roles', 'profile.club']);

        if ($this->search) {
            $usersQuery->where(function($q) {
                $q->where('username', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhereHas('profile', function($pq) {
                      $pq->where('full_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filterRole) {
            $usersQuery->role($this->filterRole);
        }

        if ($this->filterDeleted == '1') {
            $usersQuery->onlyTrashed();
        }

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0] ?? 'Lainnya';
        });

        return [
            'users' => $usersQuery->latest()->paginate(10),
            'roles' => Role::all(),
            'clubs' => Club::all(),
            'groupedPermissions' => $permissions,
        ];
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal($uid)
    {
        $this->resetForm();
        $user = User::with(['profile', 'roles'])->where('uid', $uid)->first();
        if ($user) {
            $this->uid = $user->uid;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->is_active = $user->is_active;
            $this->uid_role = $user->roles->first()->id ?? null;

            if ($user->profile) {
                $this->nama_lengkap = $user->profile->full_name;
                $this->nama_panggilan = $user->profile->nickname;
                $this->no_telepon = $user->profile->phone_number;
                $this->no_telepon_darurat = $user->profile->backup_phone_number;
                $this->tempat_lahir = $user->profile->birth_place;
                $this->tanggal_lahir = $user->profile->birth_date ? \Carbon\Carbon::parse($user->profile->birth_date)->format('Y-m-d') : null;
                $this->jenis_kelamin = $user->profile->gender;
                $this->nomor_ktp = $user->profile->identity_number;
                $this->nama_klub = $user->profile->club_uid;
                $this->alamat = $user->profile->address;
                $this->tinggi_badan = $user->profile->height;
                $this->berat_badan = $user->profile->weight;
                $this->riwayat_penyakit = $user->profile->medical_history;
                $this->tingkat_keahlian = $user->profile->skill_level;

                $this->existing_foto_profil = $user->profile->profile_picture;
                $this->existing_foto_ktp = $user->profile->identity_photo;
                $this->existing_foto_akta = $user->profile->birth_certificate_photo;
                $this->existing_foto_kk = $user->profile->family_card_photo;
            }

            $this->modalMode = 'edit';
            $this->showModal = true;
        }
    }

    public function openDeleteModal($uid)
    {
        $this->uid = $uid;
        $this->modalMode = 'delete';
        $this->showModal = true;
    }

    public function openRestoreModal($uid)
    {
        $this->resetForm();
        $this->uid = $uid;
        $this->modalMode = 'restore';
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->modalMode === 'create') {
            $this->authorize('users.create');
        } else {
            $this->authorize('users.edit');
        }

        try {
            $rules = [
                'nama_lengkap' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . ($this->uid ? User::where('uid', $this->uid)->first()->id : 'NULL'),
                'email' => 'required|email|unique:users,email,' . ($this->uid ? User::where('uid', $this->uid)->first()->id : 'NULL'),
                'uid_role' => 'required',
            ];

            if ($this->modalMode === 'create') {
                $rules['password'] = 'required|min:8';
                $rules['password_confirm'] = 'same:password';
            }

            $rules['foto_profil'] = 'nullable|image|mimes:jpeg,png,webp|max:5120';
            $rules['foto_ktp'] = 'nullable|image|mimes:jpeg,png,webp|max:5120';
            $rules['foto_akta'] = 'nullable|image|mimes:jpeg,png,webp|max:5120';
            $rules['foto_kk'] = 'nullable|image|mimes:jpeg,png,webp|max:5120';

            $this->validate($rules);

            if ($this->modalMode === 'create') {
                $user = User::create([
                    'username' => $this->username,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'is_active' => $this->is_active,
                ]);

                $user_uid = $user->uid;
            } else {
                $user = User::where('uid', $this->uid)->first();
                $user->update([
                    'username' => $this->username,
                    'email' => $this->email,
                    'is_active' => $this->is_active,
                ]);

                if ($this->password) {
                    $user->update(['password' => Hash::make($this->password)]);
                }

                $user_uid = $user->uid;
            }

            // Assign Role
            $role = Role::find($this->uid_role);
            if ($role) {
                $user->syncRoles([$role->name]);
            }


            // Handle Profile
            $profileData = [
                'full_name' => $this->nama_lengkap,
                'nickname' => $this->nama_panggilan,
                'phone_number' => $this->no_telepon,
                'backup_phone_number' => $this->no_telepon_darurat,
                'birth_place' => $this->tempat_lahir,
                'birth_date' => $this->tanggal_lahir,
                'gender' => $this->jenis_kelamin,
                'identity_number' => $this->nomor_ktp,
                'club_uid' => $this->nama_klub,
                'address' => $this->alamat,
                'height' => $this->tinggi_badan,
                'weight' => $this->berat_badan,
                'medical_history' => $this->riwayat_penyakit,
                'skill_level' => $this->tingkat_keahlian,
            ];

            if ($this->foto_profil) {
                $path = ImageHelper::uploadToWebp($this->foto_profil, 'profiles', $this->existing_foto_profil);
                if (!$path) throw new \Exception("Format Foto Profil tidak didukung atau file rusak.");
                $profileData['profile_picture'] = $path;
            }

            if ($this->foto_ktp) {
                $path = ImageHelper::uploadToWebp($this->foto_ktp, 'ktp_documents', $this->existing_foto_ktp);
                if (!$path) throw new \Exception("Format Foto KTP tidak didukung atau file rusak.");
                $profileData['identity_photo'] = $path;
            }

            if ($this->foto_akta) {
                $path = ImageHelper::uploadToWebp($this->foto_akta, 'akta_documents', $this->existing_foto_akta);
                if (!$path) throw new \Exception("Format Foto Akta tidak didukung atau file rusak.");
                $profileData['birth_certificate_photo'] = $path;
            }
            if ($this->foto_kk) {
                $path = ImageHelper::uploadToWebp($this->foto_kk, 'kk_documents', $this->existing_foto_kk);
                if (!$path) throw new \Exception("Format Foto KK tidak didukung atau file rusak.");
                $profileData['family_card_photo'] = $path;
            }

            DataUser::updateOrCreate(
                ['user_uid' => $user_uid],
                $profileData
            );

            $this->showModal = false;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Data pengguna berhasil ' . ($this->modalMode === 'create' ? 'ditambahkan' : 'diperbarui')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'status' => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ]);
        }
    }

    public function openPermissionModal($uid)
    {
        $this->resetForm();
        $user = User::where('uid', $uid)->first();
        if ($user) {
            $this->uid = $user->uid;
            $this->nama_lengkap = $user->profile->full_name ?? $user->username;
            // Ambil SEMUA izin (dari Role + Direct) agar centang tidak kosong
            $this->selectedDirectPermissions = $user->getAllPermissions()->pluck('name')->map(fn($name) => (string)$name)->toArray();
            $this->showPermissionModal = true;
        }
    }

    public function toggleDirectPermission($permissionName)
    {
        $this->authorize('roles.edit');

        $user = User::where('uid', $this->uid)->first();
        if ($user) {
            if (in_array($permissionName, $this->selectedDirectPermissions)) {
                $this->selectedDirectPermissions = array_diff($this->selectedDirectPermissions, [$permissionName]);
            } else {
                $this->selectedDirectPermissions[] = $permissionName;
            }

            $user->syncPermissions($this->selectedDirectPermissions);

            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Hak akses khusus "' . $permissionName . '" diperbarui'
            ]);
        }
    }

    public function saveDirectPermissions()
    {
        $this->authorize('roles.edit');

        $user = User::where('uid', $this->uid)->first();
        if ($user) {
            $user->syncPermissions($this->selectedDirectPermissions);
            $this->showPermissionModal = false;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Hak akses khusus berhasil diperbarui'
            ]);
        }
    }

    public function delete()
    {
        $this->authorize('users.delete');

        $user = User::where('uid', $this->uid)->first();
        if ($user) {
            $user->delete();
            $this->showModal = false;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Pengguna berhasil dihapus'
            ]);
        }
    }

    public function restoreAccount()
    {
        $this->authorize('users.edit');

        $user = User::withTrashed()->where('uid', $this->uid)->first();
        
        if ($user) {
            // 1. Pulihkan User & Set Non-aktif
            $user->restore();
            $user->update(['is_active' => false]);

            // 2. Pulihkan Profil jika ada
            $profile = DataUser::withTrashed()->where('user_uid', $user->uid)->first();
            if ($profile) {
                $profile->restore();
            }

            $this->showModal = false;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Akun ' . ($user->username) . ' berhasil dipulihkan dengan status NON-AKTIF'
            ]);
        }
    }

    public function openForceDeleteModal($uid)
    {
        $this->uid = $uid;
        $this->modalMode = 'force_delete';
        $this->showModal = true;
    }

    public function forceDeleteAccount()
    {
        $this->authorize('users.delete');

        $user = User::withTrashed()->where('uid', $this->uid)->first();
        
        if ($user) {
            $profile = DataUser::withTrashed()->where('user_uid', $user->uid)->first();
            if ($profile) {
                // Hapus foto profil (di public)
                if ($profile->profile_picture) {
                    \App\Helpers\ImageHelper::deleteFile($profile->profile_picture);
                }
                
                // Hapus dokumen sensitif (di storage/app atau public)
                $sensitiveDocs = [
                    $profile->identity_photo,
                    $profile->birth_certificate_photo,
                    $profile->family_card_photo
                ];
                
                foreach ($sensitiveDocs as $doc) {
                    if ($doc) {
                        \App\Helpers\ImageHelper::deleteFile($doc);
                        @unlink(storage_path('app/' . $doc));
                    }
                }
                
                $profile->forceDelete();
            }

            $user->forceDelete();

            $this->showModal = false;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Akun dan seluruh datanya berhasil dihapus permanen'
            ]);
        }
    }

    private function resetForm()
    {
        $this->reset([
            'uid', 'username', 'email', 'password', 'password_confirm',
            'nama_lengkap', 'nama_panggilan', 'no_telepon', 'no_telepon_darurat',
            'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'nomor_ktp', 'nama_klub', 'alamat',
            'tinggi_badan', 'berat_badan', 'riwayat_penyakit', 'tingkat_keahlian',
            'foto_ktp', 'foto_profil', 'foto_akta', 'foto_kk', 'uid_role', 'selectedDirectPermissions',
            'existing_foto_profil', 'existing_foto_ktp', 'existing_foto_akta', 'existing_foto_kk'
        ]);
        $this->is_active = true;
    }
}; ?>

<div class="p-4 md:p-8 overflow-y-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Manajemen Pengguna</h2>
            <p class="text-sm text-slate-500 font-medium">Kelola data admin, pelatih, dan member sistem</p>
        </div>
        @can('users.create')
        <button wire:click="openCreateModal"
            class="flex items-center gap-2 bg-ksc-blue hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition shadow-xl shadow-blue-100 transform hover:-translate-y-0.5">
            <x-lucide-user-plus class="w-5 h-5" />
            <span>Tambah Pengguna</span>
        </button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="relative">
            <x-lucide-search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, email, username..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition">
        </div>
        <div class="relative">
            <x-lucide-filter class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <select wire:model.live="filterRole"
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition appearance-none">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>
        </div>
        <div class="relative">
            <x-lucide-database class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <select wire:model.live="filterDeleted"
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition appearance-none">
                <option value="0">Data Aktif</option>
                <option value="1">Tempat Sampah (Terhapus)</option>
            </select>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Profil & Akun</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kontak & Klub</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $user)
                        <tr wire:key="user-row-{{ $user->uid }}" class="hover:bg-slate-50/50 transition group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        @if ($user->profile && $user->profile->profile_picture)
                                            <img src="{{ asset($user->profile->profile_picture) }}"
                                                class="w-12 h-12 rounded-2xl border-2 border-white shadow-md object-cover">
                                        @else
                                            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center font-black text-ksc-blue text-lg shadow-sm">
                                                {{ substr($user->profile->full_name ?? $user->username, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900">{{ $user->profile?->full_name ?? $user->username }}</p>
                                        <p class="text-xs text-slate-400 font-bold tracking-tight">{{ $user->email }}</p>
                                        <div class="flex gap-1 mt-1">
                                            @foreach($user->getRoleNames() as $roleName)
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest
                                                    {{ $roleName == 'admin' ? 'bg-rose-50 text-rose-600' : ($roleName == 'coach' ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600') }}">
                                                    {{ $roleName }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2 mb-1">
                                        <x-lucide-phone class="w-3 h-3 text-slate-400" />
                                        <span class="text-xs font-bold text-slate-600">{{ $user->profile->phone_number ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-lucide-building-2 class="w-3 h-3 text-slate-400" />
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-tight">
                                            {{ $user->profile?->club->name ?? 'Independen' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-[0.1em]
                                    {{ $user->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex justify-center gap-2">
                                    @if($user->trashed())
                                        <button wire:click="openRestoreModal('{{ $user->uid }}')"
                                            class="flex items-center gap-2 bg-emerald-50 text-emerald-600 px-4 py-2 rounded-xl font-bold hover:bg-emerald-100 transition shadow-sm"
                                            title="Pulihkan Akun">
                                            <x-lucide-rotate-ccw class="w-4 h-4" />
                                            <span class="text-[10px] uppercase">Pulihkan</span>
                                        </button>
                                        @can('users.delete')
                                        <button wire:click="openForceDeleteModal('{{ $user->uid }}')"
                                            class="flex items-center gap-2 bg-rose-50 text-rose-600 px-4 py-2 rounded-xl font-bold hover:bg-rose-100 transition shadow-sm"
                                            title="Hapus Permanen">
                                            <x-lucide-trash-2 class="w-4 h-4" />
                                            <span class="text-[10px] uppercase">Hapus</span>
                                        </button>
                                        @endcan
                                    @else
                                        @can('roles.edit')
                                        <button wire:click="openPermissionModal('{{ $user->uid }}')"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-purple-600 hover:bg-purple-50 rounded-xl transition"
                                            title="Hak Akses Khusus">
                                            <x-lucide-shield-check class="w-5 h-5" />
                                        </button>
                                        @endcan
                                        @can('users.edit')
                                        <button wire:click="openEditModal('{{ $user->uid }}')"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-ksc-blue hover:bg-blue-50 rounded-xl transition">
                                            <x-lucide-pencil class="w-5 h-5" />
                                        </button>
                                        @endcan
                                        @can('users.delete')
                                        <button wire:click="openDeleteModal('{{ $user->uid }}')"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition">
                                            <x-lucide-trash-2 class="w-5 h-5" />
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center mb-4">
                                        <x-lucide-users class="w-10 h-10 text-slate-200" />
                                    </div>
                                    <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest">Tidak ada data</h3>
                                    <p class="text-xs text-slate-300 font-bold mt-1">Coba sesuaikan pencarian atau filter Anda</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $users->links() }}
    </div>

    {{-- Modals --}}
    @if($showModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-2xl z-[2010] border border-slate-100">
                @if($modalMode === 'delete')
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-rose-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-rose-100">
                            <x-lucide-user-x class="w-10 h-10 text-rose-600" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Pengguna?</h3>
                        <p class="text-slate-500 font-medium mb-10 px-10">Data pengguna akan dipindahkan ke tempat sampah dan tidak dapat diakses untuk sementara waktu.</p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <button wire:click="$set('showModal', false)"
                                class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                            <button wire:click="delete" wire:loading.attr="disabled"
                                class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2 min-w-[160px]">
                                <span wire:loading.remove wire:target="delete">Ya, Hapus Data</span>
                                <span wire:loading wire:target="delete" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Memproses...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @elseif($modalMode === 'restore')
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-emerald-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-emerald-100">
                            <x-lucide-rotate-ccw class="w-10 h-10 text-emerald-600" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Pulihkan Akun?</h3>
                        <p class="text-slate-500 font-medium mb-10 px-10">Akun akan diaktifkan kembali namun dalam status <strong class="text-rose-600">NON-AKTIF</strong>. Anda perlu mengaktifkannya secara manual setelah ini.</p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <button wire:click="$set('showModal', false)"
                                class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                            <button wire:click="restoreAccount" wire:loading.attr="disabled"
                                class="px-8 py-4 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 transition shadow-xl shadow-emerald-200 flex items-center justify-center gap-2 min-w-[160px]">
                                <span wire:loading.remove wire:target="restoreAccount">Ya, Pulihkan Akun</span>
                                <span wire:loading wire:target="restoreAccount" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Memproses...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @elseif($modalMode === 'force_delete')
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-rose-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-rose-100">
                            <x-lucide-trash-2 class="w-10 h-10 text-rose-600" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Permanen?</h3>
                        <p class="text-slate-500 font-medium mb-10 px-10">Akun, profil, dan seluruh foto terkait akan dihapus secara <strong class="text-rose-600">PERMANEN</strong> dan tidak dapat dikembalikan lagi.</p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <button wire:click="$set('showModal', false)"
                                class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition">Batal</button>
                            <button wire:click="forceDeleteAccount" wire:loading.attr="disabled"
                                class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2 min-w-[160px]">
                                <span wire:loading.remove wire:target="forceDeleteAccount">Ya, Hapus Permanen</span>
                                <span wire:loading wire:target="forceDeleteAccount" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Memproses...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                        <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">
                            {{ $modalMode === 'create' ? 'Tambah Pengguna Baru' : 'Ubah Data Pengguna' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 transition">
                            <x-lucide-x class="w-6 h-6" />
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="p-8">
                        <div class="space-y-8 max-h-[70vh] overflow-y-auto px-4 custom-scrollbar">
                            {{-- Bagian 1: Data Personal --}}
                            <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <x-lucide-user-cog class="w-4 h-4 text-ksc-blue" />
                                    </div>
                                    <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Data Personal</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Lengkap</label>
                                        <input type="text" wire:model="nama_lengkap"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                        @error('nama_lengkap') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Panggilan</label>
                                        <input type="text" wire:model="nama_panggilan"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Username</label>
                                        <input type="text" wire:model="username"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                        @error('username') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Email</label>
                                        <input type="email" wire:model="email"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                        @error('email') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Role</label>
                                        <select wire:model="uid_role"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition appearance-none">
                                            <option value="">Pilih Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                            @endforeach
                                        </select>
                                        @error('uid_role') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">No. WhatsApp</label>
                                        <input type="text" wire:model="no_telepon"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">WhatsApp Darurat</label>
                                        <input type="text" wire:model="no_telepon_darurat"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tempat Lahir</label>
                                        <input type="text" wire:model="tempat_lahir"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tanggal Lahir</label>
                                        <input type="date" wire:model="tanggal_lahir"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Jenis Kelamin</label>
                                        <select wire:model="jenis_kelamin"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition appearance-none">
                                            <option value="">Pilih</option>
                                            <option value="male">Laki-laki</option>
                                            <option value="female">Perempuan</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">NIK / No. KTP</label>
                                        <input type="text" wire:model="nomor_ktp"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Alamat Lengkap</label>
                                        <textarea wire:model="alamat" rows="2"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Bagian 2: Data Fisik & Keahlian --}}
                            <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-rose-100 rounded-xl flex items-center justify-center">
                                        <x-lucide-activity class="w-4 h-4 text-rose-600" />
                                    </div>
                                    <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Fisik & Keahlian</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tinggi (cm)</label>
                                            <input type="number" wire:model="tinggi_badan"
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Berat (kg)</label>
                                            <input type="number" wire:model="berat_badan"
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tingkat Keahlian</label>
                                        <select wire:model="tingkat_keahlian"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition appearance-none">
                                            <option value="">Pilih</option>
                                            <option value="beginner">Beginner</option>
                                            <option value="intermediate">Intermediate</option>
                                            <option value="advanced">Advanced</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Afiliasi Klub</label>
                                        <select wire:model="nama_klub"
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition appearance-none">
                                            <option value="">Independen</option>
                                            @foreach($clubs as $club)
                                                <option value="{{ $club->uid }}">{{ $club->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Riwayat Medis</label>
                                        <textarea wire:model="riwayat_penyakit" rows="2" placeholder="Contoh: Asma, Alergi Kaporit..."
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Bagian 3: Keamanan --}}
                            <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-slate-200 rounded-xl flex items-center justify-center">
                                        <x-lucide-lock class="w-4 h-4 text-slate-600" />
                                    </div>
                                    <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Keamanan</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                                        <div class="relative group" x-data="{ show: false }">
                                            <input :type="show ? 'text' : 'password'" wire:model="password" placeholder="••••••••"
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                                <template x-if="!show">
                                                    <x-lucide-eye class="w-4 h-4" />
                                                </template>
                                                <template x-if="show">
                                                    <x-lucide-eye-off class="w-4 h-4" />
                                                </template>
                                            </button>
                                        </div>
                                        @error('password') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Konfirmasi Password</label>
                                        <div class="relative group" x-data="{ show: false }">
                                            <input :type="show ? 'text' : 'password'" wire:model="password_confirm" placeholder="••••••••"
                                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-blue-50 outline-none transition">
                                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                                <template x-if="!show">
                                                    <x-lucide-eye class="w-4 h-4" />
                                                </template>
                                                <template x-if="show">
                                                    <x-lucide-eye-off class="w-4 h-4" />
                                                </template>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Bagian 4: Dokumen Foto --}}
                            <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 bg-emerald-100 rounded-xl flex items-center justify-center">
                                        <x-lucide-image class="w-4 h-4 text-emerald-600" />
                                    </div>
                                    <h4 class="font-black text-slate-900 uppercase tracking-tight text-sm">Dokumen Foto</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Foto Profil</label>
                                        <div class="relative group h-32 bg-white rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition hover:border-ksc-blue">
                                            @if($foto_profil)
                                                <img src="{{ $foto_profil->temporaryUrl() }}" class="w-full h-full object-cover">
                                            @elseif($existing_foto_profil)
                                                <img src="{{ asset($existing_foto_profil) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="text-center">
                                                    <x-lucide-camera class="w-6 h-6 text-slate-300 mx-auto mb-2" />
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Upload Foto</span>
                                                </div>
                                            @endif
                                            
                                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                                <label class="p-2 bg-white rounded-lg cursor-pointer hover:scale-110 transition active:scale-95" title="Galeri">
                                                    <x-lucide-image class="w-4 h-4 text-slate-700" />
                                                    <input type="file" wire:model="foto_profil" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                                <label class="p-2 bg-white rounded-lg cursor-pointer hover:scale-110 transition active:scale-95" title="Kamera">
                                                    <x-lucide-camera class="w-4 h-4 text-slate-700" />
                                                    <input type="file" wire:model="foto_profil" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="user" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                            </div>
                                            {{-- Mobile View --}}
                                            <div class="absolute bottom-1 flex gap-1 md:hidden">
                                                <label class="p-1.5 bg-white/90 rounded-md shadow">
                                                    <x-lucide-image class="w-3.5 h-3.5 text-slate-600" />
                                                    <input type="file" wire:model="foto_profil" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                                <label class="p-1.5 bg-white/90 rounded-md shadow">
                                                    <x-lucide-camera class="w-3.5 h-3.5 text-slate-600" />
                                                    <input type="file" wire:model="foto_profil" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="user" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                            </div>
                                        </div>
                                        @error('foto_profil') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Foto KTP</label>
                                        <div class="relative group h-32 bg-white rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition hover:border-ksc-blue">
                                            @if($foto_ktp)
                                                <img src="{{ $foto_ktp->temporaryUrl() }}" class="w-full h-full object-cover">
                                            @elseif($existing_foto_ktp)
                                                <img src="{{ route('document.view', ['type' => 'ktp', 'filename' => basename($existing_foto_ktp) ?: 'none']) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="text-center">
                                                    <x-lucide-id-card class="w-6 h-6 text-slate-300 mx-auto mb-2" />
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Upload KTP</span>
                                                </div>
                                            @endif

                                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                                <label class="p-2 bg-white rounded-lg cursor-pointer hover:scale-110 transition active:scale-95" title="Galeri">
                                                    <x-lucide-image class="w-4 h-4 text-slate-700" />
                                                    <input type="file" wire:model="foto_ktp" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                                <label class="p-2 bg-white rounded-lg cursor-pointer hover:scale-110 transition active:scale-95" title="Kamera">
                                                    <x-lucide-camera class="w-4 h-4 text-slate-700" />
                                                    <input type="file" wire:model="foto_ktp" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                            </div>
                                            {{-- Mobile View --}}
                                            <div class="absolute bottom-1 flex gap-1 md:hidden">
                                                <label class="p-1.5 bg-white/90 rounded-md shadow">
                                                    <x-lucide-image class="w-3.5 h-3.5 text-slate-600" />
                                                    <input type="file" wire:model="foto_ktp" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                                <label class="p-1.5 bg-white/90 rounded-md shadow">
                                                    <x-lucide-camera class="w-3.5 h-3.5 text-slate-600" />
                                                    <input type="file" wire:model="foto_ktp" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                            </div>
                                        </div>
                                        @error('foto_ktp') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Foto Akta</label>
                                        <div class="relative group h-32 bg-white rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition hover:border-ksc-blue">
                                            @if($foto_akta)
                                                <img src="{{ $foto_akta->temporaryUrl() }}" class="w-full h-full object-cover">
                                            @elseif($existing_foto_akta)
                                                <img src="{{ route('document.view', ['type' => 'akta', 'filename' => basename($existing_foto_akta) ?: 'none']) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="text-center">
                                                    <x-lucide-file-text class="w-6 h-6 text-slate-300 mx-auto mb-2" />
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Upload Akta</span>
                                                </div>
                                            @endif

                                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                                <label class="p-2 bg-white rounded-lg cursor-pointer hover:scale-110 transition active:scale-95" title="Galeri">
                                                    <x-lucide-image class="w-4 h-4 text-slate-700" />
                                                    <input type="file" wire:model="foto_akta" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                                <label class="p-2 bg-white rounded-lg cursor-pointer hover:scale-110 transition active:scale-95" title="Kamera">
                                                    <x-lucide-camera class="w-4 h-4 text-slate-700" />
                                                    <input type="file" wire:model="foto_akta" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                            </div>
                                            {{-- Mobile View --}}
                                            <div class="absolute bottom-1 flex gap-1 md:hidden">
                                                <label class="p-1.5 bg-white/90 rounded-md shadow">
                                                    <x-lucide-image class="w-3.5 h-3.5 text-slate-600" />
                                                    <input type="file" wire:model="foto_akta" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                                <label class="p-1.5 bg-white/90 rounded-md shadow">
                                                    <x-lucide-camera class="w-3.5 h-3.5 text-slate-600" />
                                                    <input type="file" wire:model="foto_akta" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                            </div>
                                        </div>
                                        @error('foto_akta') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Foto KK</label>
                                        <div class="relative group h-32 bg-white rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden transition hover:border-ksc-blue">
                                            @if($foto_kk)
                                                <img src="{{ $foto_kk->temporaryUrl() }}" class="w-full h-full object-cover">
                                            @elseif($existing_foto_kk)
                                                <img src="{{ route('document.view', ['type' => 'kk', 'filename' => basename($existing_foto_kk) ?: 'none']) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="text-center">
                                                    <x-lucide-users class="w-6 h-6 text-slate-300 mx-auto mb-2" />
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Upload KK</span>
                                                </div>
                                            @endif

                                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                                <label class="p-2 bg-white rounded-lg cursor-pointer hover:scale-110 transition active:scale-95" title="Galeri">
                                                    <x-lucide-image class="w-4 h-4 text-slate-700" />
                                                    <input type="file" wire:model="foto_kk" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                                <label class="p-2 bg-white rounded-lg cursor-pointer hover:scale-110 transition active:scale-95" title="Kamera">
                                                    <x-lucide-camera class="w-4 h-4 text-slate-700" />
                                                    <input type="file" wire:model="foto_kk" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                            </div>
                                            {{-- Mobile View --}}
                                            <div class="absolute bottom-1 flex gap-1 md:hidden">
                                                <label class="p-1.5 bg-white/90 rounded-md shadow">
                                                    <x-lucide-image class="w-3.5 h-3.5 text-slate-600" />
                                                    <input type="file" wire:model="foto_kk" class="hidden" accept=".jpg,.jpeg,.png,.webp" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                                <label class="p-1.5 bg-white/90 rounded-md shadow">
                                                    <x-lucide-camera class="w-3.5 h-3.5 text-slate-600" />
                                                    <input type="file" wire:model="foto_kk" class="hidden" accept=".jpg,.jpeg,.png,.webp" capture="environment" onchange="if(this.files[0] && this.files[0].size > 5242880){ alert('Ukuran file terlalu besar! Maksimal 5MB. Tolong kompres ukuran file Anda terlebih dahulu.'); this.value=''; return false; }">
                                                </label>
                                            </div>
                                        </div>
                                        @error('foto_kk') <span class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 p-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-ksc-blue"></div>
                                </label>
                                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Akun Aktif</span>
                            </div>
                        </div>

                        <div class="flex items-center pt-8 mt-4 border-t border-slate-100 gap-4">
                            <button type="button" wire:click="$set('showModal', false)"
                                class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition uppercase text-xs tracking-widest">Batal</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                                class="flex-1 px-6 py-4 bg-ksc-blue text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-xl shadow-blue-100 uppercase text-xs tracking-widest flex items-center justify-center gap-2">
                                <span wire:loading.remove wire:target="save">
                                    {{ $modalMode === 'create' ? 'Daftarkan User' : 'Simpan Perubahan' }}
                                </span>
                                <span wire:loading wire:target="save" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Memproses...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- Modal Direct Permissions --}}
    @if($showPermissionModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showPermissionModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-2xl z-[2010] border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Hak Akses Khusus</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $nama_lengkap }}</p>
                    </div>
                    <button wire:click="$set('showPermissionModal', false)" class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-8">
                    @if(auth()->user()->uid === $uid)
                        <div class="bg-amber-50 rounded-2xl p-4 mb-6 border border-amber-100 flex items-start gap-3">
                            <x-lucide-alert-triangle class="w-5 h-5 text-amber-600 mt-0.5" />
                            <div>
                                <p class="text-[11px] text-amber-900 font-black uppercase tracking-tight">Peringatan Keamanan!</p>
                                <p class="text-[10px] text-amber-700 font-bold leading-relaxed uppercase tracking-tight">
                                    Anda sedang mengedit izin Anda sendiri. Hati-hati saat mencabut izin akses, karena dapat mengakibatkan Anda kehilangan akses ke menu ini.
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="bg-blue-50 rounded-2xl p-4 mb-6 border border-blue-100 flex items-start gap-3">
                        <x-lucide-info class="w-5 h-5 text-ksc-blue mt-0.5" />
                        <p class="text-[10px] text-blue-700 font-bold uppercase tracking-tight leading-relaxed">
                            Izin yang Anda toggle di sini akan langsung tersimpan. Izin khusus ini akan menambah izin yang didapat dari Role pengguna.
                        </p>
                    </div>

                    <div class="space-y-6 max-h-[55vh] overflow-y-auto px-2 custom-scrollbar">
                        @foreach($groupedPermissions as $group => $permissions)
                            <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                                <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <x-lucide-layers class="w-3 h-3" />
                                    Modul {{ str_replace('_', ' ', $group) }}
                                </h5>
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($permissions as $permission)
                                        <div wire:key="direct-perm-{{ $permission->id }}" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-100 hover:border-ksc-blue transition-all group shadow-sm">
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-black text-slate-700 uppercase tracking-tight">{{ Str::headline(Str::after($permission->name, $group . '.')) }}</span>
                                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ $permission->name }}</span>
                                            </div>
                                            
                                            {{-- Toggle Switch --}}
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                    wire:click="toggleDirectPermission('{{ $permission->name }}')"
                                                    {{ in_array($permission->name, $this->selectedDirectPermissions) ? 'checked' : '' }}
                                                    class="sr-only peer">
                                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-8 mt-8 border-t border-slate-100 text-center">
                        <button type="button" wire:click="$set('showPermissionModal', false)"
                            class="w-full px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-black transition shadow-xl shadow-slate-200">
                            Selesai & Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
