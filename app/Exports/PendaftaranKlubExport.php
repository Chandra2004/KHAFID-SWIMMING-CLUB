<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PendaftaranKlubExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $eventUid;
    protected $clubUid;

    public function __construct($eventUid, $clubUid = null)
    {
        $this->eventUid = $eventUid;
        $this->clubUid = $clubUid;
    }

    public function view(): View
    {
        $query = Registration::where('status', 'confirmed')->with([
            'user.profile.club',
            'eventCategory.event',
            'eventCategory.category',
        ]);

        if ($this->eventUid && $this->eventUid !== 'all') {
            $query->whereHas('eventCategory', function ($q) {
                $q->where('event_uid', $this->eventUid);
            });
        }

        if ($this->clubUid) {
            $query->whereHas('user.profile', function ($q) {
                $q->where('club_uid', $this->clubUid);
            });
        }

        $registrations = $query->get();

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
        if ($this->eventUid && $this->eventUid !== 'all') {
            $event = Event::where('uid', $this->eventUid)->first();
            if ($event) {
                $eventData = [
                    'nama_event'    => $event->name,
                    'lokasi_event'  => $event->location,
                    'tanggal_mulai' => $event->start_date,
                ];
            }
        }

        return view('format-document.excel.data-pendaftaran-klub', [
            'eventData' => $eventData,
            'clubData'  => $clubData,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'A:J' => ['alignment' => ['vertical' => Alignment::VERTICAL_CENTER]],
        ];
    }
}
