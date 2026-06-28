<?php

namespace App\Services;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentExportService
{
    /**
     * Generate dummy data for preview purposes.
     */
    public static function getDummyData(string $dataSource, array $columns): array
    {
        $dummyPool = [
            'rank'             => [1, 2, 3, 4, 5],
            'no'               => [1, 2, 3, 4, 5],
            'lane'             => [1, 2, 3, 4, 5],
            'athlete_name'     => ['ARVINO NAZZRIL SULISTIANTO', 'M NAUFAL ABI PRATAMA', 'AZKA WILDAN SANTOSO', 'DAFA ARYA GHOSSAN', 'ABRIZAM ALMEER FAHLIA'],
            'club_name'        => ['ORCA AQUATIQ TUBAN', 'HAYAM WURUK AQUATIC CLUB', 'SWORDFISH AQUATIC CLUB', 'DELTA SC', 'AIR TERJUN SWIMMING CLUB'],
            'category'         => ['KU 2017', 'KU 2017', 'KU 2015', 'KU 2015', 'KU 2013'],
            'style'            => ['50M Gaya Bebas', '100M Gaya Kupu', '50M Gaya Punggung', '100M Gaya Dada', '200M Gaya Ganti'],
            'time'             => ['01:00.34', '00:58.60', 'NT', '00:53.49', '00:49.00'],
            'seed_time'        => ['NT', 'NT', '01:05.22', 'NT', '00:55.10'],
            'result'           => ['01:00.34', '00:58.60', 'NT', '', ''],
            'points'           => [25, 18, 15, 12, 10],
            'notes'            => ['', '', '', '', ''],
            'birth_year'       => ['2017', '2017', '2015', '2015', '2013'],
            'heat'             => ['Seri 1', 'Seri 1', 'Seri 2', 'Seri 2', 'Seri 3'],
            'status'           => ['Verified', 'Pending', 'Draft', 'Verified', 'Pending'],
            'invoice_no'       => ['INV-0001', 'INV-0002', 'INV-0003', 'INV-0004', 'INV-0005'],
            'total'            => ['Rp 500.000', 'Rp 750.000', 'Rp 300.000', 'Rp 1.200.000', 'Rp 450.000'],
            'method'           => ['Transfer', 'Cash', 'QRIS', 'Transfer', 'Cash'],
            'date'             => ['08/02/2026', '09/02/2026', '10/02/2026', '11/02/2026', '12/02/2026'],
            'coach'            => ['Budi Santoso', 'Adi Putra', 'Siti Rahayu', 'Joko W.', 'Dewi Lestari'],
            'total_athletes'   => [12, 8, 15, 6, 20],
            'phone'            => ['081234567890', '082345678901', '083456789012', '084567890123', '085678901234'],
            'address'          => ['Jl. Raya No.1', 'Jl. Merdeka No.5', 'Jl. Sudirman No.10', 'Jl. Ahmad Yani No.3', 'Jl. Diponegoro No.7'],
            'event_name'       => ['Sidoarjo Open 2026', 'Kejurnas Renang', 'Invitasi Jatim', 'SC Championship', 'Fun Swim Festival'],
            'location'         => ['GOR Sidoarjo', 'Senayan Jakarta', 'Surabaya', 'Bandung', 'Malang'],
            'total_categories' => [10, 8, 12, 6, 15],
        ];

        $rows = [];
        for ($i = 0; $i < 5; $i++) {
            $row = [];
            foreach ($columns as $col) {
                $key = $col['key'];
                $row[$key] = $dummyPool[$key][$i] ?? ($col['label'] . ' ' . ($i + 1));
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Generate PDF from a Document template.
     */
    public static function generatePdf(Document $document, array $realData = []): \Barryvdh\DomPDF\PDF
    {
        $layout = $document->layout_settings ?? Document::defaultLayout();
        $columns = $document->selected_columns ?? [];
        $data = !empty($realData) ? $realData : self::getDummyData($document->data_source ?? '', $columns);

        $orientation = $document->page_orientation ?? 'portrait';
        $paperSize = $document->page_size ?? 'A4';

        $pdf = Pdf::loadView('exports.pdf.document', [
            'document' => $document,
            'layout' => $layout,
            'columns' => $columns,
            'rows' => $data,
        ]);

        $pdf->setPaper($paperSize, $orientation);

        return $pdf;
    }
}
