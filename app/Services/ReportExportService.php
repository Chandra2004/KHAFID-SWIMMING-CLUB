<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Registration;
use App\Models\Result;
use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportExportService
{
    /**
     * Generate Buku Acara PDF for a given event.
     *
     * @param string $eventUid
     * @return \Barryvdh\DomPDF\PDF
     */
    public static function generateBukuAcara(string $eventUid): \Barryvdh\DomPDF\PDF
    {
        $events = [];
        if ($eventUid === 'all') {
            $events = Event::orderBy('start_date', 'asc')->get();
        } else {
            $events = [Event::where('uid', $eventUid)->firstOrFail()];
        }

        // Fetch document configuration if exists
        $document = null;
        if ($eventUid !== 'all') {
            $document = Document::where('event_uid', $eventUid)
                ->where('type', 'buku_acara')
                ->first();
        }

        $globalData = [];

        foreach ($events as $event) {
            $logoLeftPath = $event->logo_left ? public_path($event->logo_left) : null;
            $logoRightPath = $event->logo_right ? public_path($event->logo_right) : null;

            $logoLeftBase64 = null;
            if ($logoLeftPath && file_exists($logoLeftPath)) {
                $logoLeftBase64 = 'data:image/' . pathinfo($logoLeftPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoLeftPath));
            }

            $logoRightBase64 = null;
            if ($logoRightPath && file_exists($logoRightPath)) {
                $logoRightBase64 = 'data:image/' . pathinfo($logoRightPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoRightPath));
            }

            $eventData = [
                'nama_event'      => $event->name,
                'lokasi_event'    => $event->location,
                'tanggal_mulai'   => $event->start_date,
                'jumlah_lintasan' => $event->lane_count ?? 10,
                'logo_left'       => $logoLeftBase64,
                'logo_right'      => $logoRightBase64,
            ];

            // Build acara data grouped by event_category, then by heat/seri
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
                        'tanggal_lahir'  => $birthDate instanceof \Carbon\Carbon ? $birthDate->format('Y-m-d') : '',
                        'klub_renang'    => $profile?->club?->name ?? '-',
                        'prestasi'       => $prestasi,
                        'hasil'          => '',
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
                    'kode_ku'     => $cat->category?->code ?? 'UMUM',
                    'main_requirement' => $cat->main_requirement ?? ($cat->category?->name ?? 'UMUM'),
                    'seri'        => $seri,
                ];
            }

            $globalData[] = [
                'event'     => $eventData,
                'dataAcara' => $dataAcara,
            ];
        }

        $title = $eventUid === 'all' ? 'Buku Acara - Semua Event' : 'Buku Acara - ' . $events[0]->name;

        $pdf = Pdf::loadView('format-document.buku-acara', [
            'title'      => $title,
            'globalData' => $globalData,
            'footerText' => 'Swimming Club Management System',
            'document'   => $document,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
        return $pdf;
    }

    /**
     * Generate Buku Hasil Lomba PDF for a given event.
     *
     * @param string $eventUid
     * @return \Barryvdh\DomPDF\PDF
     */
    public static function generateBukuHasil(string $eventUid): \Barryvdh\DomPDF\PDF
    {
        $events = [];
        if ($eventUid === 'all') {
            $events = Event::orderBy('start_date', 'asc')->get();
        } else {
            $events = [Event::where('uid', $eventUid)->firstOrFail()];
        }

        $allEventResults = [];

        foreach ($events as $event) {
            $logoLeftPath = $event->logo_left ? public_path($event->logo_left) : null;
            $logoRightPath = $event->logo_right ? public_path($event->logo_right) : null;

            $logoLeftBase64 = null;
            if ($logoLeftPath && file_exists($logoLeftPath)) {
                $logoLeftBase64 = 'data:image/' . pathinfo($logoLeftPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoLeftPath));
            }

            $logoRightBase64 = null;
            if ($logoRightPath && file_exists($logoRightPath)) {
                $logoRightBase64 = 'data:image/' . pathinfo($logoRightPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoRightPath));
            }

            $eventData = [
                'nama_event'    => $event->name,
                'lokasi_event'  => $event->location,
                'tanggal_mulai' => $event->start_date,
                'logo_left'     => $logoLeftBase64,
                'logo_right'    => $logoRightBase64,
            ];

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
                    ->orderBy('rank')
                    ->orderBy('total_milliseconds')
                    ->get();

                $resultData = [];

                foreach ($results as $result) {
                    $reg = $result->registration;
                    $profile = $reg?->user?->profile;
                    $birthDateResult = $profile?->birth_date;

                    $resultData[] = [
                        'nama_lengkap'  => $profile?->full_name ?? $reg?->user?->username ?? '-',
                        'tanggal_lahir' => $birthDateResult instanceof \Carbon\Carbon ? $birthDateResult->format('Y-m-d') : '',
                        'klub_renang'   => $profile?->club?->name ?? '-',
                        'sekolah'       => '',
                        'waktu_akhir'   => $result->final_time ?? '-',
                        'status'        => $result->status ?? 'FINISH',
                        'rank'          => $result->rank,
                    ];
                }

                $globalData[] = [
                    'acara' => [
                        'nomor_acara' => $cat->acara_number,
                        'nama_acara'  => $cat->acara_name,
                        'main_requirement' => $cat->main_requirement ?? ($cat->category?->name ?? 'UMUM'),
                        'category'    => [
                            'kode_ku' => $cat->category?->code ?? 'UMUM',
                            'code'    => $cat->category?->code ?? 'UMUM',
                        ],
                    ],
                    'results' => $resultData,
                ];
            }

            $allEventResults[] = [
                'event'      => $eventData,
                'globalData' => $globalData,
            ];
        }

        $title = $eventUid === 'all' ? 'Buku Hasil - Semua Event' : 'Buku Hasil - ' . $events[0]->name;

        // Note: The template might need adjustment to loop through multiple events
        // For now, we'll pass the first one or adjust the template to handle multiple event sections
        $pdf = Pdf::loadView('format-document.buku-hasil', [
            'title'           => $title,
            'allEventResults' => $allEventResults,
            'footerText'      => 'Swimming Club Management System',
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
        return $pdf;
    }

    /**
     * Generate Data Pendaftaran Klub PDF for a given event (or all events).
     */
    public static function generateDataPendaftaranKlub(?string $eventUid = null, ?string $clubUid = null): \Barryvdh\DomPDF\PDF
    {
        $query = Registration::where('status', 'confirmed')->with([
            'user.profile.club',
            'eventCategory.event',
            'eventCategory.category',
        ]);

        if ($eventUid && $eventUid !== 'all') {
            $query->whereHas('eventCategory', function ($q) use ($eventUid) {
                $q->where('event_uid', $eventUid);
            });
        }
        
        if ($clubUid) {
            $query->whereHas('user.profile', function ($q) use ($clubUid) {
                $q->where('club_uid', $clubUid);
            });
        }

        $registrations = $query->get();
        
        // Group by Club Uid
        $groupedByClub = [];
        foreach ($registrations as $reg) {
            $club = $reg->user?->profile?->club;
            $cId = $club ? $club->uid : 'tanpa-klub';
            if (!isset($groupedByClub[$cId])) {
                $groupedByClub[$cId] = [
                    'club' => $club,
                    'registrations' => []
                ];
            }
            $groupedByClub[$cId]['registrations'][] = $reg;
        }

        $clubData = [];
        foreach ($groupedByClub as $cId => $data) {
            $club = $data['club'];
            $regs = $data['registrations'];
            
            // Sort by acara_number
            usort($regs, function($a, $b) {
                return ($a->eventCategory?->acara_number ?? 999) <=> ($b->eventCategory?->acara_number ?? 999);
            });
            
            $regList = [];
            $totalTagihan = 0;
            
            foreach ($regs as $reg) {
                $profile = $reg->user?->profile;
                $evCat = $reg->eventCategory;
                $birthDateReg = $profile?->birth_date;
                $fee = $evCat?->registration_fee ?? 0;
                $totalTagihan += $fee;

                $regList[] = [
                    'nama_lengkap'       => $profile?->full_name ?? $reg->user?->username ?? '-',
                    'tanggal_lahir'      => $birthDateReg instanceof \Carbon\Carbon ? $birthDateReg->format('Y-m-d') : '',
                    'jenis_kelamin'      => $profile?->gender ?? '-',
                    'klub_renang'        => $club ? $club->name : 'TANPA KLUB',
                    'kategori'           => $evCat?->category?->name ?? 'UMUM',
                    'tipe_gaya'          => $evCat?->acara_name ?? '-',
                    'prestasi'           => \App\Services\SeedingService::getPrestasi($reg),
                ];
            }
            
            $clubData[] = [
                'club_name' => $club ? $club->name : 'TANPA KLUB',
                'coach_name' => $club ? $club->coach_name : '-',
                'coach_phone' => $club ? $club->contact : '-',
                'total_tagihan' => $totalTagihan,
                'registrations' => $regList
            ];
        }

        $eventData = null;
        if ($eventUid && $eventUid !== 'all') {
            $event = Event::where('uid', $eventUid)->first();
            if ($event) {
                $logoLeftPath = $event->logo_left ? public_path($event->logo_left) : null;
                $logoRightPath = $event->logo_right ? public_path($event->logo_right) : null;
                
                $logoLeftBase64 = null;
                if ($logoLeftPath && file_exists($logoLeftPath)) {
                    $logoLeftBase64 = 'data:image/' . pathinfo($logoLeftPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoLeftPath));
                }
                
                $logoRightBase64 = null;
                if ($logoRightPath && file_exists($logoRightPath)) {
                    $logoRightBase64 = 'data:image/' . pathinfo($logoRightPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoRightPath));
                }

                $eventData = [
                    'nama_event'    => $event->name,
                    'lokasi_event'  => $event->location,
                    'tanggal_mulai' => $event->start_date,
                    'logo_left'     => $logoLeftBase64,
                    'logo_right'    => $logoRightBase64,
                ];
            }
        }

        $pdf = Pdf::loadView('format-document.data-pendaftaran-klub', [
            'title'      => 'List Pendaftaran Klub',
            'eventData'  => $eventData,
            'clubData'   => $clubData,
            'footerText' => 'Swimming Club Management System',
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
        return $pdf;
    }

    /**
     * Generate Data Pendaftaran PDF for a given event (or all events).
     *
     * @param string|null $eventUid Null for all events
     * @return \Barryvdh\DomPDF\PDF
     */
    public static function generateDataPendaftaran(?string $eventUid = null): \Barryvdh\DomPDF\PDF
    {
        $query = Registration::withTrashed()->with([
            'user.profile.club',
            'eventCategory.event',
            'eventCategory.category',
        ]);

        if ($eventUid && $eventUid !== 'all') {
            $query->whereHas('eventCategory', function ($q) use ($eventUid) {
                $q->where('event_uid', $eventUid);
            });
        }

        $registrations = $query->orderBy('created_at', 'asc')->get();

        $eventData = null;
        if ($eventUid && $eventUid !== 'all') {
            $event = Event::where('uid', $eventUid)->first();
            if ($event) {
                $eventData = [
                    'nama_event'    => $event->name,
                    'lokasi_event'  => $event->location,
                    'tanggal_mulai' => $event->start_date,
                    'logo_left'     => $event->logo_left ? public_path($event->logo_left) : null,
                    'logo_right'    => $event->logo_right ? public_path($event->logo_right) : null,
                ];
            }
        }

        $regData = [];
        foreach ($registrations as $reg) {
            $profile = $reg->user?->profile;
            $evCat = $reg->eventCategory;
            $birthDateReg = $profile?->birth_date;

            $regData[] = [
                'nomor_pendaftaran'  => $reg->registration_number ?? '-',
                'nama_lengkap'       => $profile?->full_name ?? $reg->user?->username ?? '-',
                'tanggal_lahir'      => $birthDateReg instanceof \Carbon\Carbon ? $birthDateReg->format('Y-m-d') : '',
                'jenis_kelamin'      => $profile?->gender ?? '-',
                'nama_acara'         => $evCat?->acara_name ?? '-',
                'nama_event'         => $evCat?->event?->name ?? '-',
                'klub_renang'        => $profile?->club?->name ?? '-',
                'biaya_pendaftaran'  => $evCat?->registration_fee ?? 0,
                'status_pendaftaran' => $reg->status ?? 'pending',
                'notes'              => $reg->notes ?? '-',
            ];
        }

        $titleSuffix = ($eventUid && $eventUid !== 'all') ? ($eventData['nama_event'] ?? '') : 'Semua Event';

        $pdf = Pdf::loadView('format-document.data-pendaftaran', [
            'title'         => 'Data Pendaftaran - ' . $titleSuffix,
            'event'         => $eventData,
            'registrations' => $regData,
            'footerText'    => 'Swimming Club Management System',
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
        return $pdf;
    }

    /**
     * Generate individual registration proof PDF.
     *
     * @param string $registrationUid
     * @return \Barryvdh\DomPDF\PDF
     */
    public static function generateBuktiPendaftaran(string $registrationUid): \Barryvdh\DomPDF\PDF
    {
        $registration = Registration::with(['user.profile.club', 'eventCategory.event', 'schedule'])
            ->where('uid', $registrationUid)
            ->firstOrFail();

        $event = $registration->eventCategory?->event;
        $logoSrcLeft = null;
        $logoSrcRight = null;

        if ($event) {
            if ($event->logo_left && file_exists(public_path($event->logo_left))) {
                $path = public_path($event->logo_left);
                $logoSrcLeft = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
            if ($event->logo_right && file_exists(public_path($event->logo_right))) {
                $path = public_path($event->logo_right);
                $logoSrcRight = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        $pdf = Pdf::loadView('format-document.bukti-pendaftaran', [
            'registration' => $registration,
            'logoSrcLeft' => $logoSrcLeft,
            'logoSrcRight' => $logoSrcRight,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
        return $pdf;
    }
}
