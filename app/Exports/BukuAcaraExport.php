<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Registration;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BukuAcaraExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $eventUid;

    public function __construct($eventUid)
    {
        $this->eventUid = $eventUid;
    }

    public function title(): string
    {
        return 'Buku Acara';
    }

    public function view(): View
    {
        $events = [];
        if ($this->eventUid === 'all') {
            $events = Event::orderBy('start_date', 'asc')->get();
        } else {
            $events = [Event::where('uid', $this->eventUid)->firstOrFail()];
        }

        $globalData = [];

        foreach ($events as $event) {
            $eventData = [
                'nama_event'      => $event->name,
                'lokasi_event'    => $event->location,
                'tanggal_mulai'   => $event->start_date,
            ];

            $categories = EventCategory::where('event_uid', $event->uid)
                ->with('category')
                ->orderBy('acara_number')
                ->get();

            $dataAcara = [];

            foreach ($categories as $cat) {
                // Atur ulang seri dan lintasan berdasarkan waktu (seeding rules)
                \App\Services\SeedingService::seedEventCategory($cat);

                $registrations = Registration::where('event_category_uid', $cat->uid)
                    ->where('status', 'confirmed')
                    ->with(['user.profile.club', 'schedule'])
                    ->get();

                $seri = [];

                foreach ($registrations as $reg) {
                    $schedule = $reg->schedule;
                    $heatNumber = $schedule?->heat_number ?? 1;
                    $laneNumber = $schedule?->lane_number ?? 0;

                    $profile = $reg->user?->profile;
                    $birthDate = $profile?->birth_date;

                    $prestasi = $reg->seed_time;
                    if (empty($prestasi) || strtoupper($prestasi) === 'NT') {
                        $bestResult = \App\Models\Result::whereHas('registration', function ($query) use ($reg, $cat) {
                            $query->where('user_uid', $reg->user_uid)
                                  ->whereHas('eventCategory', function ($query2) use ($cat) {
                                      $query2->where('acara_name', $cat->acara_name);
                                  });
                        })
                        ->where('status', 'FINISH')
                        ->orderBy('total_milliseconds', 'asc')
                        ->first();
                        $prestasi = $bestResult ? $bestResult->final_time : 'NT';
                    }

                    $seri[$heatNumber][] = [
                        'registration_number' => $reg->registration_number ?? '-',
                        'nomor_lintasan' => $laneNumber,
                        'nama_lengkap'   => $profile?->full_name ?? $reg->user?->username ?? '-',
                        'tanggal_lahir'  => $birthDate instanceof \Carbon\Carbon ? $birthDate->format('Y') : '',
                        'klub_renang'    => $profile?->club?->name ?? '-',
                        'prestasi'       => $prestasi,
                    ];
                }

                ksort($seri);

                foreach ($seri as &$athletes) {
                    usort($athletes, function ($a, $b) {
                        return $a['nomor_lintasan'] <=> $b['nomor_lintasan'];
                    });
                }
                unset($athletes);

                $dataAcara[] = [
                    'nomor_acara' => $cat->acara_number,
                    'nama_acara'  => $cat->acara_name,
                    'main_requirement' => $cat->main_requirement ?? ($cat->category?->name ?? 'UMUM'),
                    'seri'        => $seri,
                ];
            }

            $globalData[] = [
                'event'     => $eventData,
                'dataAcara' => $dataAcara,
            ];
        }

        return view('format-document.excel.buku-acara', [
            'globalData' => $globalData,
        ]);
    }
}
