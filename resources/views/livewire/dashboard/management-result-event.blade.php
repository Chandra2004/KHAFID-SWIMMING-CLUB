<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    public function with()
    {
        if (!Auth::user()->can('master-result.view') && !Auth::user()->can('master-result.detail.self') && !Auth::user()->can('master-result.detail.team')) {
            abort(403);
        }

        $events = Event::latest()->get();
        return [
            'events' => $events,
            'isAdmin' => Auth::user()->hasAnyRole(['superadmin', 'admin'])
        ];
    }
}; ?>

<div class="p-6 md:p-10 bg-white min-h-screen">
    <div class="mb-10 flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none">Hasil Pertandingan</h2>
            <p class="text-sm text-slate-500 font-medium mt-2 uppercase tracking-widest italic">Pilih event untuk melihat skor & peringkat</p>
        </div>
        <x-lucide-trophy class="w-12 h-12 text-blue-600 opacity-20" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($events as $event)
            <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-500 group flex flex-col">
                <div class="h-48 bg-slate-100 relative overflow-hidden">
                    @if($event->banner)
                        <img src="{{ asset($event->banner) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-slate-100">
                            <x-lucide-award class="w-12 h-12 text-blue-200" />
                        </div>
                    @endif
                    <div class="absolute top-6 left-6 px-3 py-1 bg-white/90 backdrop-blur-sm rounded-lg shadow-sm">
                        <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">
                            {{ $event->start_date ? $event->start_date->format('d M Y') : 'TBA' }}
                        </span>
                    </div>
                </div>

                <div class="p-8 flex-1">
                    <h3 class="text-xl font-black text-slate-900 leading-tight uppercase tracking-tight mb-2">{{ $event->name }}</h3>
                    <div class="flex items-start gap-2 text-slate-400 mb-6">
                        <x-lucide-map-pin class="w-4 h-4 shrink-0 mt-0.5" />
                        <span class="text-xs font-bold uppercase tracking-widest leading-relaxed">{{ $event->location ?: 'Lokasi TBA' }}</span>
                    </div>

                    <a href="{{ route('dashboard.result-event.detail', ['event_uid' => $event->uid]) }}"
                        class="w-full bg-slate-900 hover:bg-blue-600 text-white py-4 rounded-2xl font-black transition-all duration-300 flex items-center justify-center gap-3 group/btn">
                        <x-lucide-layout-list class="w-5 h-5 group-hover/btn:scale-110 transition" />
                        <span class="text-xs uppercase tracking-widest">Lihat Hasil</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
