<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Result;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BukuHasilExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $eventUid;

    public function __construct($eventUid)
    {
        $this->eventUid = $eventUid;
    }

    public function title(): string
    {
        return 'Buku Hasil Lomba';
    }

    public function view(): View
    {
        $events = [];
        if ($this->eventUid === 'all') {
            $events = Event::orderBy('start_date', 'asc')->get();
        } else {
            $events = [Event::where('uid', $this->eventUid)->firstOrFail()];
        }

        $allEventResults = [];

        foreach ($events as $event) {
            $categories = EventCategory::where('event_uid', $event->uid)
                ->with('category')
                ->orderBy('acara_number')
                ->get();

            $globalData = [];

            foreach ($categories as $cat) {
                $results = Result::whereHas('registration', function ($q) use ($cat) {
                        $q->where('event_category_uid', $cat->uid);
                    })
                    ->with(['registration.user.profile.club'])
                    ->orderByRaw("CASE WHEN status = 'FINISH' THEN 0 ELSE 1 END")
                    ->orderBy('rank', 'asc')
                    ->orderBy('total_milliseconds', 'asc')
                    ->get()
                    ->map(function ($res) {
                        $profile = $res->registration?->user?->profile;
                        return [
                            'full_name'    => $profile?->full_name ?? $res->registration?->user?->username ?? '-',
                            'birth_date'   => $profile?->birth_date,
                            'club_name'    => $profile?->club?->name ?? '-',
                            'final_time'   => $res->final_time,
                            'status'       => $res->status,
                        ];
                    });

                $globalData[] = [
                    'acara'   => $cat,
                    'results' => $results,
                ];
            }

            $allEventResults[] = [
                'event'      => $event,
                'globalData' => $globalData,
            ];
        }

        return view('format-document.excel.buku-hasil', [
            'allEventResults' => $allEventResults,
        ]);
    }
}
