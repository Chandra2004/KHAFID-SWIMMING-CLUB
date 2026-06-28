<?php

use Livewire\Volt\Component;

use App\Models\Notification;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


new class extends Component {
    public $searchQuery = '';
    public $activeMsg = null;
    public $mobileView = 'list';
    public $showDeleteAllModal = false;
    public $filter = 'all'; // all, unread


    // Compose Fields
    public $target_type = 'all';
    public $target_role_uid = '';
    public $target_user_uids = [];
    public $searchUser = '';
    public $judul = '';
    public $pesan = '';

    public function with()
    {
        $this->authorize('notifications.view');
        
        $user = Auth::user();

        $notifications = Notification::where('user_uid', $user->uid)
            ->select('uid', 'user_uid', 'title as judul', 'message as pesan', 'is_read', 'read_at', 'created_at', 'deleted_at');

        if ($this->filter === 'archived') {
            $notifications->onlyTrashed();
        }

        $notifications->where(function ($q) {
            $q->where('title', 'like', '%' . $this->searchQuery . '%')->orWhere('message', 'like', '%' . $this->searchQuery . '%');
        });

        if ($this->filter === 'unread') {
            $notifications->where('is_read', false);
        }

        $notifications = $notifications->latest()->get();


        $can_create = $user->can('notifications.create');
        $can_delete = $user->can('notifications.delete');
        
        $roles = $can_create ? Role::all() : [];
        $users = $can_create
            ? User::with('profile')
                ->when($this->searchUser, function($q) {
                    $q->where('username', 'like', '%' . $this->searchUser . '%')
                      ->orWhere('email', 'like', '%' . $this->searchUser . '%')
                      ->orWhereHas('profile', function($pq) {
                          $pq->where('full_name', 'like', '%' . $this->searchUser . '%');
                      });
                })
                ->limit(50)
                ->get()
                ->map(function ($u) {
                    return [
                        'uid' => $u->uid,
                        'nama_lengkap' => $u->profile->full_name ?? $u->username,
                        'email' => $u->email,
                    ];
                })
            : [];

        return [
            'notifications' => $notifications,
            'roles' => $roles,
            'users' => $users,
            'can_create' => $can_create,
            'can_delete' => $can_delete,
        ];
    }

    public function selectMessage($uid)
    {
        $msg = Notification::withTrashed()->where('uid', $uid)->first();
        if ($msg) {
            $this->activeMsg = [
                'uid' => $msg->uid,
                'judul' => $msg->title,
                'pesan' => $msg->message,
                'created_at' => $msg->created_at,
                'is_read' => $msg->is_read,
                'is_archived' => $msg->trashed(),
            ];
            $this->mobileView = 'detail';

            if (!$msg->is_read && !$msg->trashed()) {
                $this->markAsRead($uid);
            }
        }
    }

    public function markAsRead($uid)
    {
        $notification = Notification::where('uid', $uid)
            ->where('user_uid', Auth::user()->uid)
            ->first();

        if ($notification) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            $this->activeMsg['is_read'] = true;

            // Refresh unread count in header
            $this->dispatch('notification-updated');
        }
    }

    public function markAsUnread($uid)
    {
        $notification = Notification::where('uid', $uid)
            ->where('user_uid', Auth::user()->uid)
            ->first();

        if ($notification) {
            $notification->update([
                'is_read' => false,
                'read_at' => null,
            ]);

            if ($this->activeMsg && $this->activeMsg['uid'] === $uid) {
                $this->activeMsg['is_read'] = false;
            }

            $this->dispatch('notification-updated');
        }
    }


    public function markAllAsRead()
    {
        Notification::where('user_uid', Auth::user()->uid)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Semua pesan ditandai sudah baca.',
        ]);
        $this->dispatch('notification-updated');
    }

    public function delete($uid)
    {
        $this->authorize('notifications.delete');
        
        $notification = Notification::where('uid', $uid)
            ->where('user_uid', Auth::user()->uid)
            ->first();

        if ($notification) {
            $notification->delete(); // Soft Delete (Archive)
            if ($this->activeMsg && $this->activeMsg['uid'] === $uid) {
                $this->activeMsg['is_archived'] = true;
            }
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Pesan dipindahkan ke arsip.',
            ]);
            $this->dispatch('notification-updated');
        }
    }

    public function restore($uid)
    {
        $notification = Notification::onlyTrashed()
            ->where('uid', $uid)
            ->where('user_uid', Auth::user()->uid)
            ->first();

        if ($notification) {
            $notification->restore();
            if ($this->activeMsg && $this->activeMsg['uid'] === $uid) {
                $this->activeMsg['is_archived'] = false;
            }
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Pesan dikembalikan ke inbox.',
            ]);
            $this->dispatch('notification-updated');
        }
    }

    public function forceDelete($uid)
    {
        $this->authorize('notifications.delete');
        
        $notification = Notification::withTrashed()
            ->where('uid', $uid)
            ->where('user_uid', Auth::user()->uid)
            ->first();

        if ($notification) {
            $notification->forceDelete(); // Hard Delete
            if ($this->activeMsg && $this->activeMsg['uid'] === $uid) {
                $this->activeMsg = null;
                $this->mobileView = 'list';
            }
            $this->dispatch('notification', [
                'status' => 'success',
                'message' => 'Pesan dihapus permanen.',
            ]);
            $this->dispatch('notification-updated');
        }
    }

    public function deleteAll()
    {
        $this->authorize('notifications.delete');
        
        $query = Notification::where('user_uid', Auth::user()->uid);
        
        if ($this->filter === 'archived') {
            $query->onlyTrashed()->forceDelete();
            $message = 'Arsip berhasil dikosongkan permanen.';
        } else {
            $query->delete();
            $message = 'Inbox berhasil dipindahkan ke arsip.';
        }

        $this->activeMsg = null;
        $this->mobileView = 'list';
        $this->showDeleteAllModal = false;

        $this->dispatch('notification', [
            'status' => 'success',
            'message' => $message,
        ]);
        $this->dispatch('notification-updated');
    }

    public function create()
    {
        $this->authorize('notifications.create');
        
        $this->validate([
            'judul' => 'required|string|max:255',
            'pesan' => 'required|string',
            'target_type' => 'required|in:all,role,specific',
        ]);

        $target_users = [];

        if ($this->target_type === 'all') {
            $target_users = User::pluck('uid');
        } elseif ($this->target_type === 'role') {
            $role = Role::where('uid', $this->target_role_uid)->first();
            if ($role) {
                $target_users = User::role($role->name)->pluck('uid');
            }
        } elseif ($this->target_type === 'specific') {
            $target_users = $this->target_user_uids;
        }

        foreach ($target_users as $user_uid) {
            Notification::create([
                'uid' => Str::uuid(),
                'user_uid' => $user_uid,
                'title' => $this->judul,
                'message' => $this->pesan,
                'is_read' => false,
            ]);
        }

        $this->reset(['judul', 'pesan', 'target_type', 'target_role_uid', 'target_user_uids']);
        $this->mobileView = 'list';

        $this->dispatch('notification', [
            'status' => 'success',
            'message' => 'Notifikasi berhasil dikirim ke ' . count($target_users) . ' penerima.',
        ]);
    }

    public function openCompose()
    {
        $this->mobileView = 'compose';
        $this->dispatch('view-changed');
    }
};


?>



<div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .prose-custom {
            font-family: 'Plus Jakarta Sans', sans-serif;
            line-height: 1.6;
            color: #334155;
        }

        .prose-custom p { margin-bottom: 1rem; }
        .prose-custom b, .prose-custom strong { font-weight: 700; color: #0f172a; }
        .prose-custom i, .prose-custom em { font-style: italic; }
        .prose-custom ul { list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1rem; }
        .prose-custom ol { list-style-type: decimal; margin-left: 1.5rem; margin-bottom: 1rem; }
        .prose-custom li { margin-bottom: 0.25rem; }
        .prose-custom h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 1rem; color: #0f172a; }
        .prose-custom h2 { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem; color: #0f172a; }
        .prose-custom a { color: #2563eb; text-decoration: underline; }
    </style>

    <div class="flex flex-col h-screen overflow-hidden bg-slate-50">
        <div class="flex flex-1 overflow-hidden relative">
            {{-- List Area --}}
            <div
                class="w-full md:w-96 flex flex-col border-r border-slate-200 bg-white shadow-xl z-10 {{ $mobileView === 'detail' || $mobileView === 'compose' ? 'hidden md:flex' : 'flex' }}">
                <div class="p-6 border-b border-slate-100 bg-white bg-opacity-70 backdrop-blur-md sticky top-0 z-20">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Inbox</h2>
                        <div class="flex gap-2">
                            <button wire:click="markAllAsRead" title="Tandai Semua Sudah Baca"
                                class="p-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition">
                                <x-lucide-check-check class="w-5 h-5" />
                            </button>
                            @if ($can_create)
                                <button wire:click="openCompose"
                                    class="p-2.5 bg-ksc-blue text-white rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition transform hover:-translate-y-0.5">
                                    <x-lucide-plus class="w-5 h-5" />
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="relative">
                        <x-lucide-search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari pesan..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-100 border-none rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>

                    {{-- Tabs --}}
                    <div class="flex gap-4 mt-6 border-b border-slate-50">
                        <button wire:click="$set('filter', 'all')"
                            class="pb-3 text-[10px] font-black uppercase tracking-widest transition relative {{ $filter === 'all' ? 'text-ksc-blue' : 'text-slate-400 hover:text-slate-600' }}">
                            Semua
                            @if ($filter === 'all')
                                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-ksc-blue rounded-full"></div>
                            @endif
                        </button>
                        <button wire:click="$set('filter', 'unread')"
                            class="pb-3 text-[10px] font-black uppercase tracking-widest transition relative {{ $filter === 'unread' ? 'text-ksc-blue' : 'text-slate-400 hover:text-slate-600' }}">
                            Belum Dibaca
                            @if ($filter === 'unread')
                                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-ksc-blue rounded-full"></div>
                            @endif
                        </button>
                        <button wire:click="$set('filter', 'archived')"
                            class="pb-3 text-[10px] font-black uppercase tracking-widest transition relative {{ $filter === 'archived' ? 'text-ksc-blue' : 'text-slate-400 hover:text-slate-600' }}">
                            Arsip
                            @if ($filter === 'archived')
                                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-ksc-blue rounded-full"></div>
                            @endif
                        </button>
                    </div>
                </div>


                <div class="flex-1 overflow-y-auto divide-y divide-slate-50 no-scrollbar">
                    @forelse ($notifications as $msg)
                        <div wire:click="selectMessage('{{ $msg->uid }}')" wire:key="list-item-{{ $msg->uid }}"
                            class="p-5 cursor-pointer transition relative group overflow-hidden {{ $activeMsg && $activeMsg['uid'] === $msg->uid ? 'bg-blue-50/50' : 'hover:bg-slate-50' }}">

                            @if (!$msg->is_read)
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-ksc-blue"></div>
                            @endif

                            <div class="flex justify-between items-start mb-1">
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest {{ !$msg->is_read ? 'text-ksc-blue' : 'text-slate-400' }}">
                                    {{ $msg->created_at->diffForHumans() }}
                                </span>
                                @if (!$msg->is_read)
                                    <div class="w-2 h-2 bg-ksc-blue rounded-full shadow-lg shadow-blue-200"></div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between gap-2">
                                <h4
                                    class="text-sm font-black mb-1 truncate tracking-tight flex-1 {{ !$msg->is_read ? 'text-slate-900' : 'text-slate-600' }}">
                                    {{ $msg->judul }}
                                </h4>
                                @if ($can_delete)
                                <div class="hidden group-hover:flex items-center gap-1">
                                    @if($filter === 'archived')
                                        <button wire:click.stop="forceDelete('{{ $msg->uid }}')"
                                            title="Hapus Permanen"
                                            class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition">
                                            <x-lucide-trash-2 class="w-4 h-4" />
                                        </button>
                                    @else
                                        <button wire:click.stop="delete('{{ $msg->uid }}')"
                                            title="Arsipkan"
                                            class="p-1.5 text-amber-600 hover:bg-amber-100 rounded-lg transition">
                                            <x-lucide-archive class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 font-medium line-clamp-1">{{ strip_tags($msg->pesan) }}</p>
                        </div>
                    @empty
                        <div wire:key="empty-inbox" class="p-12 text-center">
                            <div
                                class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <x-lucide-inbox class="w-8 h-8 text-slate-200" />
                            </div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tidak ada pesan</p>
                        </div>
                    @endforelse

                    @if ($notifications->count() > 0 && $can_delete)
                        <div class="p-4 bg-slate-50/50">
                            <button wire:click="$set('showDeleteAllModal', true)"
                                class="w-full py-3 text-[10px] font-black text-red-600 uppercase tracking-widest hover:bg-red-50 rounded-xl transition flex items-center justify-center gap-2">
                                <x-lucide-trash-2 class="w-4 h-4" />
                                {{ $filter === 'archived' ? 'Kosongkan Arsip' : 'Arsipkan Seluruh Inbox' }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Detail & Compose Area --}}
            <div
                class="flex-1 flex flex-col bg-slate-50/30 overflow-hidden {{ $mobileView === 'list' ? 'hidden md:flex' : 'flex' }}">
                @if ($mobileView === 'list')
                    <div wire:key="select-message-prompt"
                        class="flex-1 flex flex-col items-center justify-center p-12 text-slate-300">
                        <div
                            class="w-32 h-32 bg-white rounded-[3rem] shadow-xl shadow-slate-200/50 flex items-center justify-center mb-8 transform rotate-6 hover:rotate-0 transition-transform duration-500">
                            <x-lucide-mail-open class="w-12 h-12 text-slate-200" />
                        </div>
                        <h3 class="text-lg font-black text-slate-400 uppercase tracking-[0.2em]">Pilih pesan untuk
                            dibaca</h3>
                    </div>

                @elseif($mobileView === 'detail' && $activeMsg)
                    <div wire:key="detail-{{ $activeMsg['uid'] }}"
                        class="flex flex-col h-full bg-white shadow-2xl md:m-6 md:rounded-[2.5rem] border border-slate-100 overflow-hidden">

                        <div
                            class="p-4 md:p-6 border-b border-slate-50 flex justify-between items-center bg-white sticky top-0 z-20">
                            <button wire:click="$set('mobileView', 'list')"
                                class="md:hidden w-10 h-10 flex items-center justify-center text-slate-500 hover:bg-slate-50 rounded-xl transition">
                                <x-lucide-arrow-left class="w-5 h-5" />
                            </button>
                             <div class="flex gap-2 ml-auto">
                                @if (!$activeMsg['is_archived'])
                                    @if ($activeMsg['is_read'])
                                        <button wire:click="markAsUnread('{{ $activeMsg['uid'] }}')"
                                            title="Tandai Belum Baca"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-ksc-blue hover:bg-blue-50 rounded-xl transition">
                                            <x-lucide-mail class="w-5 h-5" />
                                        </button>
                                    @endif
                                    @if ($can_delete)
                                        <button wire:click="delete('{{ $activeMsg['uid'] }}')"
                                            title="Arsipkan Pesan"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition">
                                            <x-lucide-archive class="w-5 h-5" />
                                        </button>
                                    @endif
                                @else
                                    <button wire:click="restore('{{ $activeMsg['uid'] }}')"
                                        title="Kembalikan ke Inbox"
                                        class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-xl transition">
                                        <x-lucide-archive-restore class="w-5 h-5" />
                                    </button>
                                    @if ($can_delete)
                                        <button wire:click="forceDelete('{{ $activeMsg['uid'] }}')"
                                            title="Hapus Permanen"
                                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition">
                                            <x-lucide-trash-2 class="w-5 h-5" />
                                        </button>
                                    @endif
                                @endif
                            </div>

                        </div>
                        <div class="flex-1 overflow-y-auto p-8 md:p-12 text-left">
                            <div class="max-w-3xl mx-auto">
                                <span
                                    class="inline-block px-3 py-1 bg-blue-50 text-ksc-blue text-[10px] font-black uppercase tracking-[0.2em] rounded-full mb-4">Pesan
                                    Masuk</span>
                                <h1
                                    class="text-3xl md:text-5xl font-black text-slate-900 leading-tight tracking-tighter mb-8">
                                    {{ $activeMsg['judul'] }}</h1>
                                <div
                                    class="flex items-center gap-4 bg-slate-50/50 p-4 rounded-2xl border border-slate-100 mb-8">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-ksc-blue flex items-center justify-center font-black text-white text-lg shadow-lg shadow-blue-200">
                                        {{ substr($activeMsg['judul'], 0, 1) }}</div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 uppercase tracking-tight">System
                                            Administrator</p>
                                        <p class="text-xs text-slate-400 font-bold">
                                            {{ \Carbon\Carbon::parse($activeMsg['created_at'])->translatedFormat('d F Y • H:i') }}
                                            WIB</p>
                                    </div>
                                </div>
                                <div
                                    class="prose-custom max-w-none">
                                    {!! $activeMsg['pesan'] !!}
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($mobileView === 'compose' && $can_create)
                    <div wire:key="compose-view"
                        class="flex flex-col h-full bg-white shadow-2xl md:m-6 md:rounded-[2.5rem] border border-slate-100 overflow-hidden">

                        <div
                            class="p-4 md:p-6 border-b border-slate-50 flex items-center gap-4 bg-white sticky top-0 z-20">
                            <button wire:click="$set('mobileView', 'list')"
                                class="w-10 h-10 flex items-center justify-center text-slate-400 hover:bg-slate-50 rounded-xl transition">
                                <x-lucide-arrow-left class="w-5 h-5" />
                            </button>
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Kirim Notifikasi Baru
                            </h3>
                        </div>
                        <div class="flex-1 overflow-y-auto p-8 md:p-12">
                            <div class="max-w-2xl mx-auto">
                                <form wire:submit="create" class="space-y-8">
                                    <div>
                                        <label
                                            class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Target
                                            Penerima</label>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                                            <label class="cursor-pointer">
                                                <input type="radio" wire:key="target-all" name="target_type" wire:model.live="target_type" value="all"
                                                    class="hidden peer">
                                                <div
                                                    class="p-4 border-2 border-slate-100 rounded-2xl peer-checked:border-ksc-blue peer-checked:bg-blue-50 transition group items-center flex flex-col text-center">
                                                    <x-lucide-users
                                                        class="w-6 h-6 mb-2 text-slate-400 group-peer-checked:text-ksc-blue" />
                                                    <span
                                                        class="text-[10px] font-black uppercase tracking-widest text-slate-500 peer-checked:text-ksc-blue">Semua</span>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer">
                                                <input type="radio" wire:key="target-role" name="target_type" wire:model.live="target_type" value="role"
                                                    class="hidden peer">
                                                <div
                                                    class="p-4 border-2 border-slate-100 rounded-2xl peer-checked:border-ksc-blue peer-checked:bg-blue-50 transition group items-center flex flex-col text-center">
                                                    <x-lucide-shield
                                                        class="w-6 h-6 mb-2 text-slate-400 group-peer-checked:text-ksc-blue" />
                                                    <span
                                                        class="text-[10px] font-black uppercase tracking-widest text-slate-500 peer-checked:text-ksc-blue">Per
                                                        Role</span>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer">
                                                <input type="radio" wire:key="target-specific" name="target_type" wire:model.live="target_type" value="specific"
                                                    class="hidden peer">
                                                <div
                                                    class="p-4 border-2 border-slate-100 rounded-2xl peer-checked:border-ksc-blue peer-checked:bg-blue-50 transition group items-center flex flex-col text-center">
                                                    <x-lucide-user
                                                        class="w-6 h-6 mb-2 text-slate-400 group-peer-checked:text-ksc-blue" />
                                                    <span
                                                        class="text-[10px] font-black uppercase tracking-widest text-slate-500 peer-checked:text-ksc-blue">Pilih
                                                        Orang</span>
                                                </div>
                                            </label>
                                        </div>

                                        @if ($target_type === 'role')
                                            <select wire:key="select-role" wire:model="target_role_uid"
                                                class="w-full bg-slate-50 border border-slate-100 text-sm font-bold rounded-xl p-4 outline-none focus:ring-2 focus:ring-blue-100 transition">
                                                <option value="">Pilih Role Target...</option>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->uid }}">{{ strtoupper($role->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif($target_type === 'specific')
                                            <div class="relative mb-3">
                                                <x-lucide-search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                                <input type="text" wire:model.live.debounce.300ms="searchUser" 
                                                    placeholder="Cari nama atau email..."
                                                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                            </div>
                                            <div
                                                class="max-h-48 overflow-y-auto border border-slate-100 rounded-2xl bg-slate-50 p-4 space-y-2 no-scrollbar">
                                                @foreach ($users as $u)
                                                    <label wire:key="user-{{ $u['uid'] }}"
                                                        class="flex items-center gap-3 p-2 hover:bg-white rounded-xl transition cursor-pointer">
                                                        <input type="checkbox" wire:model="target_user_uids"
                                                            value="{{ $u['uid'] }}"
                                                            class="rounded text-ksc-blue focus:ring-blue-200">
                                                        <div class="flex flex-col">
                                                            <span
                                                                class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ $u['nama_lengkap'] }}</span>
                                                            <span
                                                                class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $u['email'] }}</span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="space-y-6">
                                        <div>
                                            <label
                                                class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Subjek
                                                Notifikasi</label>
                                            <input type="text" wire:model="judul" required
                                                placeholder="Contoh: Jadwal Latihan Terbaru"
                                                class="w-full bg-slate-50 border border-slate-100 text-sm font-black rounded-xl p-4 outline-none focus:ring-2 focus:ring-blue-100 transition">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Pesan
                                                Lengkap</label>
                                            <div wire:ignore>
                                                <textarea id="tinymce-notification" wire:model="pesan" rows="6"
                                                    class="w-full bg-slate-50 border border-slate-100 text-sm font-medium rounded-xl p-4 outline-none focus:ring-2 focus:ring-blue-100 transition"
                                                    placeholder="Tulis isi pesan di sini..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-6">
                                        <button type="submit" wire:loading.attr="disabled"
                                            class="w-full bg-slate-900 hover:bg-black text-white font-black text-[10px] uppercase tracking-[0.3em] rounded-2xl px-6 py-5 shadow-2xl shadow-slate-200 transition transform hover:-translate-y-1 flex items-center justify-center gap-3">
                                            <span wire:loading.remove wire:target="create">Broadcast Notifikasi</span>
                                            <span wire:loading wire:target="create">Mengirim...</span>
                                            <x-lucide-send class="w-4 h-4" />
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Hapus Semua --}}
    @if ($showDeleteAllModal)
        <div
            class="fixed inset-0 z-[2000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-[2rem] p-8 max-w-md w-full shadow-2xl text-center border border-slate-100">
                <div
                    class="w-16 h-16 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-inner shadow-red-100">
                    <x-lucide-alert-octagon class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-3">
                    {{ $filter === 'archived' ? 'Kosongkan Seluruh Arsip?' : 'Arsipkan Seluruh Inbox?' }}
                </h3>
                <p class="text-sm text-slate-500 font-medium mb-8 leading-relaxed">
                    {{ $filter === 'archived' 
                        ? 'Seluruh pesan di arsip akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.' 
                        : 'Seluruh pesan di inbox akan dipindahkan ke folder arsip.' }}
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <button wire:click="$set('showDeleteAllModal', false)"
                        class="w-full py-3.5 text-[10px] font-black uppercase tracking-widest text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batalkan</button>
                    <button wire:click="deleteAll"
                        class="w-full py-3.5 text-[10px] font-black uppercase tracking-widest text-white {{ $filter === 'archived' ? 'bg-red-600 hover:bg-red-700' : 'bg-amber-600 hover:bg-amber-700' }} rounded-xl shadow-lg transition">
                        Ya, {{ $filter === 'archived' ? 'Kosongkan Permanen' : 'Pindahkan ke Arsip' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
    @script
    <script>
        function initTinyMCE() {
            // Hapus instance lama jika ada
            if (tinymce.get('tinymce-notification')) {
                tinymce.remove('#tinymce-notification');
            }

            // Inisialisasi baru
            tinymce.init({
                selector: '#tinymce-notification',
                height: 350,
                license_key: 'gpl',
                menubar: false,
                statusbar: false,
                plugins: 'lists link help wordcount',
                toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | removeformat | help',
                content_style: 'body { font-family: \'Plus Jakarta Sans\', sans-serif; font-size: 14px; }',
                setup: (editor) => {
                    editor.on('init', () => {
                        editor.setContent($wire.get('pesan') || '');
                    });
                    
                    // Sinkronisasi data ke Livewire setiap kali ada perubahan
                    editor.on('change keyup blur', () => {
                        $wire.pesan = editor.getContent();
                    });
                }
            });
        }

        // Jalankan saat navigasi Livewire selesai
        document.addEventListener('livewire:navigated', () => {
            initTinyMCE();
        });

        // Jalankan setiap kali ada update dari Livewire (untuk menangani perubahan view/modal)
        document.addEventListener('livewire:load', () => {
            initTinyMCE();
        });

        // Pantau perubahan properti Livewire untuk inisialisasi ulang jika diperlukan
        $wire.on('view-changed', () => {
            setTimeout(initTinyMCE, 100);
        });
        
        // Initial call
        initTinyMCE();
    </script>
    @endscript
</div>
