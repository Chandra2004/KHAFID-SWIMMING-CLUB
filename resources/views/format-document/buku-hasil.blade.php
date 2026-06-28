<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Buku Hasil Lomba' }}</title>
    <style>
        @page {
            margin: 1.2cm 1cm;
            size: A4 portrait;
        }

        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 400;
            src: url('https://fonts.gstatic.com/s/montserrat/v25/JTUSjIg1_i6t8kCHKm4df9WRJhzmgXNE3ez9.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 700;
            src: url('https://fonts.gstatic.com/s/montserrat/v25/JTURjIg1_i6t8kCHKm4df_8Phzz3qPNy.ttf') format('truetype');
        }

        /* Reset & Global Font */
        * {
            box-sizing: border-box;
            font-family: 'Montserrat', Helvetica, Arial, sans-serif !important;
        }

        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            line-height: 1.2;
            font-size: 8.5px;
        }

        /* Header Section */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td { vertical-align: middle; }
        .header-content { text-align: center; }
        .event-name { font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .event-detail { font-size: 9px; color: #333; }
        .report-title { font-size: 16px; font-weight: bold; margin-top: 8px; text-transform: uppercase; letter-spacing: 1px; }
        .logo-box { width: 12%; }
        .logo-img { max-height: 50px; max-width: 70px; display: block; }

        .header-separator {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 3px;
            margin-bottom: 15px;
        }

        /* Compact Acara Header Bar */
        .acara-header-bar {
            background-color: #f1f5f9;
            border: 1px solid #000;
            padding: 4px 8px;
            margin-top: 15px;
            display: table;
            width: 100%;
        }
        .acara-title-cell { display: table-cell; font-weight: bold; font-size: 10px; text-transform: uppercase; vertical-align: middle; }
        .acara-number { width: 15%; text-align: left; font-size: 14px; }
        .acara-main-name { width: 55%; text-align: center; font-size: 10px; }
        .acara-ku-info { width: 30%; text-align: right; font-size: 10px; }

        /* Tables - PERUBAHAN UTAMA: Dikunci menjadi kaku (fixed) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        .data-table th {
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            border-top: 1px solid #000;
            border-bottom: 1.5px solid #000;
        }
        .data-table td {
            padding: 5px 4px;
            border-bottom: 0.5px solid #eee;
            vertical-align: middle;
            font-size: 9px;
            word-wrap: break-word; /* Mengunci teks agar tidak merusak kolom lain */
        }

        /* Top 3 Ranking Styles */
        .rank-1 { background-color: #fdf2f2; font-weight: bold; }
        .rank-2 { background-color: #f8fafc; }
        .rank-3 { background-color: #fffaf0; }

        /* Utilities Alignment */
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }

        /* Kolom Fixed Berbasis Pixel (px) - Total Akumulasi: 590px */
        .col-pos { width: 45px; }
        .col-nama { width: 235px; }
        .col-lahir { width: 50px; }
        .col-tim { width: 160px; }
        .col-hasil { width: 100px; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -0.5cm;
            left: 0;
            right: 0;
            font-size: 7.5px;
            text-align: center;
            color: #999;
            border-top: 0.5px solid #eee;
            padding-top: 5px;
        }

        /* Page Breaks */
        .event-container { page-break-after: always; }
        .event-container:last-child { page-break-after: avoid; }
        .acara-block { page-break-inside: avoid; }
    </style>
</head>

<body>

    @foreach ($allEventResults as $eventResult)
        @php
            $event = $eventResult['event'];
            $globalData = $eventResult['globalData'];

            $defaultLogoPath = public_path('assets/ico/icon-bar.png');
            $defaultLogoBase64 = null;
            if (file_exists($defaultLogoPath)) {
                $defaultLogoBase64 = 'data:' . mime_content_type($defaultLogoPath) . ';base64,' . base64_encode(file_get_contents($defaultLogoPath));
            }

            $logoL = (isset($event['logo_left']) && str_starts_with($event['logo_left'], 'data:image')) ? $event['logo_left'] : $defaultLogoBase64;
            $logoR = (isset($event['logo_right']) && str_starts_with($event['logo_right'], 'data:image')) ? $event['logo_right'] : $defaultLogoBase64;
        @endphp

        <div class="event-container">
            <table class="header-table">
                <tr>
                    <td class="logo-box">
                        @if($logoL) <img src="{{ $logoL }}" class="logo-img"> @endif
                    </td>
                    <td class="header-content">
                        <div class="event-name">{{ $event['nama_event'] ?? $event['name'] ?? 'EVENT' }}</div>
                        <div class="event-detail">{{ $event['lokasi_event'] ?? $event['location'] ?? '' }} | {{ !empty($event['tanggal_mulai']) ? \Carbon\Carbon::parse($event['tanggal_mulai'])->isoFormat('D MMMM YYYY') : '' }}</div>
                        <div class="report-title">BUKU HASIL LOMBA (OFFICIAL RESULTS)</div>
                    </td>
                    <td class="logo-box" style="text-align: right;">
                        @if($logoR) <img src="{{ $logoR }}" class="logo-img" style="margin-left: auto;"> @endif
                    </td>
                </tr>
            </table>

            <div class="header-separator"></div>

            @foreach ($globalData as $item)
                <div class="acara-block">
                    <div class="acara-header-bar">
                        <div class="acara-title-cell acara-number">Acara {{ $item['acara']['nomor_acara'] ?? $item['acara']['acara_number'] ?? '-' }}</div>
                        <div class="acara-title-cell acara-main-name">{{ $item['acara']['nama_acara'] ?? $item['acara']['acara_name'] ?? '' }}</div>
                        <div class="acara-title-cell acara-ku-info">{{ $item['acara']['main_requirement'] }}</div>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <!-- Integrasi Class Width Pixel -->
                                <th class="col-pos text-center">Pos</th>
                                <th class="col-nama">Nama Lengkap Atlet</th>
                                <th class="col-lahir text-center">Lahir</th>
                                <th class="col-tim">Klub/Asal Sekolah</th>
                                <th class="col-hasil text-center">Waktu Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($item['results'] as $index => $res)
                                @php
                                    $isFinish = ($res['status'] ?? '') === 'FINISH';
                                    $rowClass = '';
                                    if ($isFinish && $index < 3) {
                                        $rowClass = 'rank-' . ($index + 1);
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="col-pos text-center font-bold">{{ $isFinish ? ($index + 1) : '-' }}</td>
                                    <td class="col-nama">
                                        <div class="font-bold">{{ strtoupper($res['nama_lengkap'] ?? $res['full_name'] ?? '') }}</div>
                                    </td>
                                    <td class="col-lahir text-center">
                                        {{ !empty($res['tanggal_lahir']) ? date('Y', strtotime($res['tanggal_lahir'])) : ( !empty($res['birth_date']) ? date('Y', strtotime($res['birth_date'])) : '-' ) }}
                                    </td>
                                    <td class="col-tim">{{ strtoupper($res['klub_renang'] ?: ($res['sekolah'] ?: '-')) }}</td>
                                    <td class="col-hasil text-center font-bold" style="font-size: 10px;">
                                        {{ $isFinish ? ($res['waktu_akhir'] ?? $res['final_time'] ?? '-') : ($res['status'] ?? 'NT') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center" style="padding: 15px; color: #888;">
                                        Data hasil perlombaan belum tersedia untuk nomor ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="footer">
        Dicetak resmi oleh Khafid Swimming Club Management System - {{ date('d/m/Y H:i') }}
    </div>

</body>

</html>
