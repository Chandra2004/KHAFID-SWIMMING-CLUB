<?php

namespace App\Livewire\Homepage;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class EventList extends Component
{
    use WithPagination;

    public $search = '';
    
    // Reset pagination saat searching
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Event::query()
            ->with(['categories.category', 'author.profile'])
            ->where('status', '!=', 'draft');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%');
            });
        }

        $events = $query->orderBy('start_date', 'desc')->paginate(9);

        // Transform data agar sesuai dengan format yang diinginkan di view
        $events->getCollection()->transform(function($event) {
            $totalReg = DB::table('registrations')
                ->join('event_categories', 'registrations.event_category_uid', '=', 'event_categories.uid')
                ->where('event_categories.event_uid', $event->uid)
                ->count();

            return [
                'uid' => $event->uid,
                'slug' => $event->slug,
                'nama_event' => $event->name,
                'banner_event' => $event->banner,
                'biaya_event' => $event->categories->min('registration_fee') ?? 0,
                'status_event' => $this->mapStatus($event->status),
                'tanggal_mulai' => $event->start_date,
                'lokasi_event' => $event->location,
                'registrations_count' => $totalReg,
                'jumlah_lintasan' => $event->lane_count,
                'kategori' => $event->categories->first()->category->name ?? 'Swimming'
            ];
        });

        return view('livewire.homepage.event-list', [
            'events' => $events
        ]);
    }

    private function mapStatus($status)
    {
        $map = [
            'ongoing' => 'berjalan',
            'upcoming' => 'mendatang',
            'completed' => 'selesai',
            'cancelled' => 'dibatalkan',
            'draft' => 'draft'
        ];
        return $map[$status] ?? $status;
    }
}
