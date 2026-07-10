<?php

use Livewire\Volt\Component;
use App\Models\Gallery;
use App\Models\Event;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Helpers\ImageHelper;

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    public $search = '';

    // Form fields
    public $event_uid = '';
    public $title = '';
    public $description = '';
    public $cover_image;
    public $existingCover;
    public $photos = []; // For multiple uploads
    public $existingPhotos = [];
    public $is_active = true;

    public $editingGalleryId = null;
    public $galleryToDelete = null;
    public $showModal = false;
    public $showDeleteModal = false;
    public $modalMode = 'create';

    public function with()
    {
        $galleriesQuery = Gallery::query()->with('event');

        if ($this->search) {
            $galleriesQuery->where('title', 'like', '%' . $this->search . '%')->orWhere('description', 'like', '%' . $this->search . '%');
        }

        // Calculate Stats for Storage Usage
        $allGalleries = Gallery::all();
        $totalPhotos = 0;
        $totalSizeBytes = 0;
        $photoLimit = 1000;
        $storageLimitBytes = 2 * 1024 * 1024 * 1024; // 2GB

        foreach ($allGalleries as $gallery) {
            // Count cover
            if ($gallery->cover_image) {
                $totalPhotos++;
                $path = public_path($gallery->cover_image);
                if (file_exists($path)) {
                    $totalSizeBytes += filesize($path);
                }
            }
            // Count images array
            if ($gallery->images) {
                $galleryImages = $gallery->images;
                $totalPhotos += count($galleryImages);
                foreach ($galleryImages as $img) {
                    $path = public_path($img);
                    if (file_exists($path)) {
                        $totalSizeBytes += filesize($path);
                    }
                }
            }
        }

        $totalSizeMB = round($totalSizeBytes / (1024 * 1024), 2);
        $usagePercentage = min(100, round(($totalSizeBytes / $storageLimitBytes) * 100, 1));

        return [
            'galleries' => $galleriesQuery->latest()->paginate(12),
            'events' => Event::orderBy('name')->get(),
            'stats' => [
                'total_photos' => $totalPhotos,
                'total_size_mb' => $totalSizeMB,
                'usage_percent' => $usagePercentage,
                'photo_limit' => $photoLimit,
            ],
        ];
    }

    public function openCreateModal()
    {
        $this->reset(['event_uid', 'title', 'description', 'cover_image', 'existingCover', 'photos', 'existingPhotos', 'is_active', 'editingGalleryId']);
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal($uid)
    {
        $gallery = Gallery::where('uid', $uid)->firstOrFail();
        $this->editingGalleryId = $gallery->uid;
        $this->event_uid = $gallery->event_uid;
        $this->title = $gallery->title;
        $this->description = $gallery->description;
        $this->existingCover = $gallery->cover_image;
        $this->existingPhotos = $gallery->images ?: [];
        $this->is_active = $gallery->is_active;
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function updatedPhotos()
    {
        $this->validate(
            [
                'photos.*' => 'image|max:5120',
                'photos' => 'nullable|array|max:50',
            ],
            [
                'photos.max' => 'Maksimal 50 foto dalam satu kali unggah.',
                'photos.*.max' => 'Ukuran satu foto tidak boleh lebih dari 2MB.',
                'photos.*.image' => 'File harus berupa gambar.',
            ],
        );
    }

    public function save()
    {
        $this->authorize($this->modalMode === 'create' ? 'master-galeri.create' : 'master-galeri.edit');

        $this->validate([
            'title' => 'required|string|max:255',
            'event_uid' => 'nullable|exists:events,uid',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:5120',
            'photos.*' => 'image|max:5120',
            'photos' => 'nullable|array|max:50',
            'is_active' => 'boolean',
        ]);

        $data = [
            'title' => $this->title,
            'event_uid' => $this->event_uid ?: null,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        // Handle multiple photos
        $uploadedPhotos = [];
        if (!empty($this->photos)) {
            foreach ($this->photos as $photo) {
                $uploadedPhotos[] = ImageHelper::uploadToWebp($photo, 'galleries/photos');
            }
        }

        if ($this->modalMode === 'create') {
            $data['uid'] = Str::uuid();
            if ($this->cover_image) {
                $data['cover_image'] = ImageHelper::uploadToWebp($this->cover_image, 'galleries');
            }
            $data['images'] = $uploadedPhotos;
            Gallery::create($data);
            $message = 'Album galeri berhasil ditambahkan';
        } else {
            $gallery = Gallery::where('uid', $this->editingGalleryId)->firstOrFail();

            if ($this->cover_image) {
                $data['cover_image'] = ImageHelper::uploadToWebp($this->cover_image, 'galleries', $gallery->cover_image);
            }

            // Merge existing photos with new ones
            $data['images'] = array_merge($this->existingPhotos, $uploadedPhotos);

            $gallery->update($data);
            $message = 'Album galeri berhasil diperbarui';
        }

        $this->showModal = false;
        $this->dispatch('notification', [
            'status' => 'success',
            'message' => $message,
        ]);
    }

    public function deletePhoto($index)
    {
        if (isset($this->existingPhotos[$index])) {
            $path = public_path($this->existingPhotos[$index]);
            if (file_exists($path)) {
                @unlink($path);
            }
            unset($this->existingPhotos[$index]);
            $this->existingPhotos = array_values($this->existingPhotos); // Reindex

            if ($this->editingGalleryId) {
                Gallery::where('uid', $this->editingGalleryId)->update(['images' => $this->existingPhotos]);
            }
        }
    }

    public function removeNewPhoto($index)
    {
        if (isset($this->photos[$index])) {
            unset($this->photos[$index]);
            $this->photos = array_values($this->photos); // Reindex
        }
    }

    public function confirmDelete($uid)
    {
        $this->galleryToDelete = Gallery::where('uid', $uid)->firstOrFail();
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->authorize('master-galeri.delete');
        $gallery = $this->galleryToDelete;
        if ($gallery) {
            // Delete cover
            if ($gallery->cover_image) {
                $path = public_path($gallery->cover_image);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            // Delete all photos
            if ($gallery->images) {
                foreach ($gallery->images as $photo) {
                    $path = public_path($photo);
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }
            $gallery->delete();
            $this->showDeleteModal = false;
            $this->galleryToDelete = null;
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Album galeri berhasil dihapus',
            ]);
        }
    }

    public function toggleStatus($uid)
    {
        $this->authorize('master-galeri.edit');
        $gallery = Gallery::where('uid', $uid)->first();
        if ($gallery) {
            $gallery->is_active = !$gallery->is_active;
            $gallery->save();
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Status album berhasil diubah',
            ]);
        }
    }
}; ?>

<div class="p-4 md:p-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Master Galeri</h2>
            <p class="text-sm text-slate-500 font-medium">Kelola album foto dokumentasi event dan koleksi foto klub</p>
        </div>
        @can('master-galeri.create')
            <button wire:click="openCreateModal"
                class="flex items-center gap-2 bg-ksc-blue hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold transition shadow-xl shadow-blue-100 transform hover:-translate-y-0.5">
                <x-lucide-image-plus class="w-5 h-5" />
                <span>Tambah Album</span>
            </button>
        @endcan
    </div>

    {{-- Storage Usage Widget --}}
    <div class="mb-10 max-w-md">
        <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Storage Usage</h4>
                <span class="text-xs font-black text-slate-900">{{ $stats['usage_percent'] }}%</span>
            </div>

            {{-- Progress Bar --}}
            <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden mb-4">
                <div class="h-full bg-ksc-blue rounded-full transition-all duration-1000"
                    style="width: {{ $stats['usage_percent'] }}%"></div>
            </div>

            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span
                        class="text-[10px] font-black text-ksc-blue uppercase tracking-widest">{{ $stats['total_photos'] }}/{{ $stats['photo_limit'] }}</span>
                    <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">Photos</span>
                </div>
                <div class="text-[10px] font-black text-slate-900 uppercase tracking-widest">
                    <span>{{ $stats['total_size_mb'] }}MB</span>
                    <span class="text-slate-300 mx-1">/</span>
                    <span class="text-slate-400">2GB</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="relative">
            <x-lucide-search class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari judul atau deskripsi..."
                class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium text-slate-700 focus:ring-4 focus:ring-blue-50 outline-none transition">
        </div>
    </div>

    {{-- Masonry Grid for Gallery Albums --}}
    @if ($galleries->isEmpty())
        <div
            class="py-24 bg-white border border-dashed border-slate-200 rounded-[2.5rem] flex flex-col items-center justify-center text-center shadow-sm">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                <x-lucide-images class="w-10 h-10 text-slate-200" />
            </div>
            <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest">Belum Ada Album Galeri</h4>
            <p class="text-[10px] font-bold text-slate-300 mt-2 uppercase tracking-widest">Mulai dengan menambahkan
                album baru</p>
        </div>
    @else
        <div class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6">
            @foreach ($galleries as $gallery)
                <div wire:key="gallery-{{ $gallery->uid }}"
                    class="break-inside-avoid bg-white rounded-[2.5rem] overflow-hidden shadow-sm border border-slate-100 hover:shadow-xl hover:shadow-indigo-50/50 transition-all duration-500 group relative">

                    {{-- Cover Image --}}
                    <div class="relative aspect-video overflow-hidden">
                        @if ($gallery->cover_image)
                            <img src="{{ asset($gallery->cover_image) }}"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        @else
                            <div class="w-full h-full bg-slate-50 flex items-center justify-center">
                                <x-lucide-camera class="text-slate-200 w-12 h-12" />
                            </div>
                        @endif

                        {{-- Badges Overlay --}}
                        <div class="absolute top-4 left-4 flex flex-col gap-2">
                            @if ($gallery->event)
                                <span
                                    class="px-3 py-1 bg-white/90 backdrop-blur-md rounded-full text-[9px] font-black text-indigo-600 uppercase tracking-widest shadow-sm">
                                    {{ $gallery->event->name }}
                                </span>
                            @endif
                            <span
                                class="w-fit px-3 py-1 {{ $gallery->is_active ? 'bg-emerald-500 shadow-emerald-100' : 'bg-slate-400 shadow-slate-100' }} text-white rounded-full text-[9px] font-black uppercase tracking-widest shadow-lg">
                                {{ $gallery->is_active ? 'Publik' : 'Draft' }}
                            </span>
                        </div>

                        {{-- Photo Count Overlay --}}
                        <div
                            class="absolute bottom-4 right-4 bg-slate-900/40 backdrop-blur-md px-3 py-1.5 rounded-xl flex items-center gap-2 border border-white/20">
                            <x-lucide-images class="w-3.5 h-3.5 text-white" />
                            <span
                                class="text-[10px] font-black text-white uppercase tracking-widest">{{ count($gallery->images ?: []) }}
                                Foto</span>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-6">
                        <h3
                            class="text-lg font-black text-slate-900 uppercase tracking-tighter leading-tight mb-2 group-hover:text-indigo-600 transition-colors">
                            {{ $gallery->title }}
                        </h3>
                        <p class="text-xs font-medium text-slate-500 line-clamp-2 mb-6 uppercase tracking-tight">
                            {{ $gallery->description ?: 'Tidak ada deskripsi untuk album ini.' }}
                        </p>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                            <div class="flex items-center gap-2">
                                @can('master-galeri.edit')
                                    <button wire:click="openEditModal('{{ $gallery->uid }}')"
                                        class="w-10 h-10 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition shadow-sm">
                                        <x-lucide-pencil class="w-4 h-4" />
                                    </button>
                                    <button wire:click="toggleStatus('{{ $gallery->uid }}')"
                                        class="w-10 h-10 flex items-center justify-center bg-slate-50 text-slate-400 rounded-xl hover:bg-slate-900 hover:text-white transition shadow-sm">
                                        <x-lucide-power class="w-4 h-4" />
                                    </button>
                                @endcan
                            </div>

                            @can('master-galeri.delete')
                                <button wire:click="confirmDelete('{{ $gallery->uid }}')"
                                    class="w-10 h-10 flex items-center justify-center bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition shadow-sm">
                                    <x-lucide-trash-2 class="w-4 h-4" />
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-6">
        {{ $galleries->links() }}
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[2000] overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center">
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-4xl z-[2010] border border-slate-100 max-h-[90vh] flex flex-col">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100">
                            <x-lucide-camera class="text-white w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tighter uppercase">
                                {{ $modalMode === 'create' ? 'Tambah Album Baru' : 'Edit Album Galeri' }}
                            </h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kelola detail dan
                                koleksi foto album (Maks 50 Foto)</p>
                        </div>
                    </div>
                    <button wire:click="$set('showModal', false)"
                        class="text-slate-400 hover:text-slate-600 transition">
                        <x-lucide-x class="w-6 h-6" />
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 p-8 custom-scrollbar">
                    <form x-data
                        @submit.prevent="
                        let promises = [];

                        // Upload cover image jika ada
                        let ci = document.getElementById('mg_cover_image')?.files[0];
                        if (ci) promises.push(new Promise((resolve, reject) => { @this.upload('cover_image', ci, resolve, reject); }));

                        // Upload multiple photos jika ada
                        const photosInput = document.getElementById('mg_photos');
                        if (photosInput && photosInput.files.length > 0) {
                            const filesArr = Array.from(photosInput.files);
                            filesArr.forEach(file => {
                                promises.push(new Promise((resolve, reject) => { @this.upload('photos[]', file, resolve, reject); }));
                            });
                        }

                        const btn = document.getElementById('mgSubmitBtn');
                        const txtEl = document.getElementById('mgSubmitText');
                        const loadEl = document.getElementById('mgSubmitLoading');

                        if (promises.length > 0) {
                            btn.disabled = true;
                            txtEl.classList.add('hidden');
                            loadEl.classList.remove('hidden');
                            Promise.all(promises).then(() => {
                                @this.call('save');
                                setTimeout(() => { btn.disabled = false; txtEl.classList.remove('hidden'); loadEl.classList.add('hidden'); }, 2000);
                            }).catch(() => {
                                alert('Gagal mengunggah file. Silakan coba lagi.');
                                btn.disabled = false;
                                txtEl.classList.remove('hidden');
                                loadEl.classList.add('hidden');
                            });
                        } else {
                            @this.call('save');
                        }
                    "
                        id="galleryForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Info Section --}}
                            <div class="space-y-6">
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Judul
                                        Album</label>
                                    <input type="text" wire:model="title" placeholder="Nama album galeri..."
                                        class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-indigo-100 outline-none transition uppercase">
                                    @error('title')
                                        <span
                                            class="text-[10px] text-rose-500 font-bold ml-1 uppercase">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Event
                                        Terkait</label>
                                    <div class="relative">
                                        <select wire:model="event_uid"
                                            class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-indigo-100 outline-none transition appearance-none">
                                            <option value="">-- Tidak Terkait Event --</option>
                                            @foreach ($events as $event)
                                                <option value="{{ $event->uid }}">{{ $event->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-lucide-chevron-down
                                            class="w-4 h-4 absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Deskripsi</label>
                                    <textarea wire:model="description" rows="3" placeholder="Ceritakan sedikit tentang album ini..."
                                        class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-indigo-100 outline-none transition resize-none uppercase"></textarea>
                                </div>

                                <div class="flex items-center gap-3 px-1">
                                    <input type="checkbox" wire:model="is_active" id="is_active_gallery_2"
                                        class="w-5 h-5 text-indigo-600 rounded-lg border-slate-200 focus:ring-indigo-500">
                                    <label for="is_active_gallery_2"
                                        class="text-xs font-black text-slate-500 uppercase tracking-widest">Publikasikan
                                        Album</label>
                                </div>
                            </div>

                            {{-- Media Section --}}
                            <div class="space-y-8">
                                {{-- Cover Upload --}}
                                <div>
                                    <label
                                        class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4 ml-1 text-center">Album
                                        Cover</label>
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-full h-40 bg-slate-50 rounded-[2rem] overflow-hidden border-4 border-white shadow-xl flex items-center justify-center relative">
                                            
                                            @if($existingCover)
                                                <img src="{{ asset($existingCover) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="flex items-center justify-center w-full h-full">
                                                    <x-lucide-image class="w-10 h-10 text-slate-300" />
                                                </div>
                                            @endif
                                            
                                            <label class="absolute bottom-4 bg-slate-900 text-white px-6 py-3 rounded-2xl cursor-pointer hover:bg-slate-800 transition font-bold text-[10px] uppercase tracking-[0.2em] shadow-lg">
                                                Ganti Sampul
                                                <input type="file" wire:model="cover" class="hidden" accept="image/*">
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Photos Upload --}}
                                <div>
                                    <div class="flex justify-between items-center mb-4 px-1">
                                        <div>
                                            <label
                                                class="block text-xs font-black text-slate-400 uppercase tracking-widest">
                                                Koleksi Foto
                                            </label>
                                            <p class="text-[9px] font-bold text-slate-300 uppercase mt-1">Maksimal 50
                                                foto per upload</p>
                                        </div>
                                        <label
                                            class="text-indigo-600 font-black text-[10px] uppercase tracking-widest cursor-pointer hover:underline">
                                            + Tambah Foto
                                            <input type="file" wire:model="photos" class="hidden" accept="image/*"
                                                multiple>
                                        </label>
                                    </div>

                                    {{-- Masonry / Grid Display --}}
                                    <div
                                        class="bg-slate-50 p-4 rounded-[2rem] min-h-[200px] max-h-[400px] overflow-y-auto relative custom-scrollbar border border-slate-100">

                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            @foreach ($existingPhotos as $index => $photo)
                                                <div
                                                    class="relative group aspect-square rounded-xl overflow-hidden border-2 border-white shadow-sm">
                                                    <img src="{{ asset($photo) }}"
                                                        class="w-full h-full object-cover">
                                                    <button type="button"
                                                        wire:click="deletePhoto({{ $index }})"
                                                        class="absolute inset-0 bg-rose-500/80 text-white opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                                        <x-lucide-trash-2 class="w-5 h-5" />
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>

                                        @if (empty($existingPhotos))
                                            <div id="mgNoPhotos"
                                                class="flex flex-col items-center justify-center py-12 text-slate-300">
                                                <x-lucide-images class="w-10 h-10 mb-2 opacity-30" />
                                                <p class="text-[10px] font-bold uppercase tracking-widest text-center">
                                                    Belum ada foto</p>
                                            </div>
                                        @endif
                                    </div>
                                    @error('photos.*')
                                        <span
                                            class="text-[10px] text-rose-500 font-bold block mt-2 uppercase text-center">{{ $message }}</span>
                                    @enderror
                                    @error('photos')
                                        <span
                                            class="text-[10px] text-rose-500 font-bold block mt-2 uppercase text-center">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="p-8 border-t border-slate-50 flex items-center gap-4 bg-slate-50/30">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition uppercase text-[10px] tracking-widest">Batal</button>
                    <button type="submit" form="galleryForm" id="mgSubmitBtn"
                        class="flex-1 px-6 py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 flex items-center justify-center gap-2 uppercase text-[10px] tracking-widest disabled:opacity-70 disabled:cursor-not-allowed">
                        <span id="mgSubmitText">
                            {{ $modalMode === 'create' ? 'Simpan Album' : 'Perbarui Album' }}
                        </span>
                        <span id="mgSubmitLoading" class="hidden flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
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
            <div class="fixed inset-0 transform transition-all" wire:click="$set('showModal', false)">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            </div>

            <div
                class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full sm:max-w-2xl z-[2010] border border-slate-100">
                <div class="p-12 text-center">
                    <div
                        class="w-20 h-20 bg-rose-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-rose-100">
                        <x-lucide-trash-2 class="w-10 h-10 text-rose-600" />
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-4">Hapus Album?</h3>
                    <p class="text-slate-500 font-medium mb-10 px-10">Seluruh foto di dalam album <span
                            class="text-rose-600 font-black">"{{ $galleryToDelete?->title }}"</span> akan dihapus
                        permanen dari server.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="$set('showDeleteModal', false)"
                            class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition uppercase text-[10px] tracking-widest">Batal</button>
                        <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                            class="px-8 py-4 bg-rose-600 text-white rounded-2xl font-bold hover:bg-rose-700 transition shadow-xl shadow-rose-200 flex items-center justify-center gap-2 min-w-[160px] uppercase text-[10px] tracking-widest">
                            <span wire:loading.remove wire:target="delete">Ya, Hapus Album</span>
                            <span wire:loading wire:target="delete" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>Memproses...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
