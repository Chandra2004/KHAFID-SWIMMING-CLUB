<?php

namespace App\Exports;

use App\Models\Registration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class RegistrationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $eventUid;
    protected $rowNumber = 0;

    public function __construct($eventUid)
    {
        $this->eventUid = $eventUid;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Registration::with([
            'user.profile.club',
            'eventCategory.event',
            'eventCategory.category',
        ])->orderBy('created_at', 'asc');

        if ($this->eventUid && $this->eventUid !== 'all') {
            $query->whereHas('eventCategory', function ($q) {
                $q->where('event_uid', $this->eventUid);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Pendaftaran',
            'Nama Lengkap',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Klub',
            'Event',
            'Lomba',
            'Status',
            'Biaya (Rp)',
            'Tanggal Daftar',
        ];
    }

    /**
    * @var Registration $registration
    */
    public function map($registration): array
    {
        $this->rowNumber++;
        $profile = $registration->user?->profile;

        return [
            $this->rowNumber,
            $registration->registration_number,
            $profile?->full_name ?? $registration->user?->username,
            $profile?->birth_date ? $profile->birth_date->format('Y-m-d') : '-',
            $profile?->gender === 'female' ? 'P' : 'L',
            $profile?->club?->name ?? 'INDEPENDENT',
            $registration->eventCategory?->event?->name ?? '-',
            $registration->eventCategory?->acara_name ?? '-',
            strtoupper($registration->status),
            $registration->eventCategory?->registration_fee ?? 0,
            $registration->created_at->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                for ($row = 2; $row <= $highestRow; $row++) {
                    $status = $sheet->getCell('I' . $row)->getValue();
                    $color = '';

                    switch ($status) {
                        case 'CONFIRMED':
                            $color = 'C6EFCE'; // Light Green
                            $fontColor = '006100'; // Dark Green
                            break;
                        case 'PENDING':
                            $color = 'FFEB9C'; // Light Yellow
                            $fontColor = '9C6500'; // Dark Yellow
                            break;
                        case 'REJECTED':
                            $color = 'FFC7CE'; // Light Red
                            $fontColor = '9C0006'; // Dark Red
                            break;
                        case 'CANCELLED':
                            $color = 'E2E2E2'; // Grey
                            $fontColor = '3F3F3F'; // Dark Grey
                            break;
                    }

                    if ($color) {
                        $sheet->getStyle('I' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $color],
                            ],
                            'font' => [
                                'color' => ['rgb' => $fontColor],
                                'bold' => true,
                            ],
                        ]);
                    }
                }
            },
        ];
    }
}
