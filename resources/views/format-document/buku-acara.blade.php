<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Buku Acara' }}</title>
    <style>
        @page {
            margin: 1.2cm 1cm 1.2cm 1cm;
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

        /* Compact Acara Header */
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

        /* Heat/Seri Header */
        .seri-label {
            font-size: 12px;
            font-weight: bold;
            margin: 8px 0 3px 0;
            padding: 2px 0;
            text-transform: uppercase;
            border-bottom: 0.5px solid #000;
        }

        /* Tables - PERUBAHAN UTAMA: Dikunci menjadi kaku (fixed) */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #fff;
            padding: 4px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
        }
        .data-table td {
            padding: 4px 4px;
            border-bottom: 0.2px solid #ddd;
            vertical-align: middle;
            font-size: 9px;
            word-wrap: break-word; /* Mencegah teks meluber keluar batas kolom */
        }

        /* Utilities Alignment */
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }

        /* Kolom Fixed Berbasis Pixel (px) */
        .col-lint { width: 35px; }
        .col-nama { width: 220px; }
        .col-lahir { width: 45px; }
        .col-tim { width: 145px; }
        .col-entry { width: 75px; }
        .col-hasil { width: 75px; }

        /* Result Box */
        .result-box {
            border: 0.5px solid #000;
            height: 14px;
            width: 100%;
            display: block;
        }

        /* Page Management */
        .acara-block {
            page-break-inside: avoid;
        }
        .footer {
            position: fixed;
            bottom: -0.5cm;
            left: 0;
            right: 0;
            font-size: 7px;
            text-align: center;
            color: #999;
            padding-top: 5px;
            border-top: 0.5px solid #eee;
        }
    </style>
</head>

<body>

    @foreach ($globalData as $item)
        @php
            $event = $item['event'];
            $dataAcara = $item['dataAcara'];

            $defaultLogoPath = public_path('assets/ico/icon-bar.png');
            $defaultLogoBase64 = null;
            if (file_exists($defaultLogoPath)) {
                $defaultLogoBase64 = 'data:' . mime_content_type($defaultLogoPath) . ';base64,' . base64_encode(file_get_contents($defaultLogoPath));
            }

            $logoL = (isset($event['logo_left']) && str_starts_with($event['logo_left'], 'data:image')) ? $event['logo_left'] : $defaultLogoBase64;
            $logoR = (isset($event['logo_right']) && str_starts_with($event['logo_right'], 'data:image')) ? $event['logo_right'] : $defaultLogoBase64;
        @endphp

        <table class="header-table">
            <tr>
                <td class="logo-box">
                    @if($logoL) <img src="{{ $logoL }}" class="logo-img"> @endif
                </td>
                <td class="header-content">
                    <div class="event-name">{{ $event['nama_event'] }}</div>
                    <div class="event-detail">{{ $event['lokasi_event'] }} | {{ $event['tanggal_mulai'] ? \Carbon\Carbon::parse($event['tanggal_mulai'])->isoFormat('D MMMM YYYY') : '' }}</div>
                    <div class="report-title">BUKU ACARA (PROGRAM BOOK)</div>
                </td>
                <td class="logo-box" style="text-align: right;">
                    @if($logoR) <img src="{{ $logoR }}" class="logo-img" style="margin-left: auto;"> @endif
                </td>
            </tr>
        </table>

        <div class="header-separator"></div>

        @foreach ($dataAcara as $acara)
            <div class="acara-block">
                <div class="acara-header-bar">
                    <div class="acara-title-cell acara-number">Acara {{ $acara['nomor_acara'] }}</div>
                    <div class="acara-title-cell acara-main-name">{{ $acara['nama_acara'] }}</div>
                    <div class="acara-title-cell acara-ku-info">{{ $acara['main_requirement'] }}</div>
                </div>

                @foreach ($acara['seri'] as $seriNum => $athletes)
                    <div class="seri-label">Seri {{ $seriNum }}</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <!-- Penataan lebar kolom kaku berbasis pixel -->
                                <th class="col-lint text-center">Lint</th>
                                <th class="col-nama">Nama Lengkap Atlet</th>
                                <th class="col-lahir text-center">Lahir</th>
                                <th class="col-tim">Klub/Asal Sekolah</th>
                                <th class="col-entry text-center">Prestasi</th>
                                <th class="col-hasil text-center">Hasil Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($athletes as $at)
                                <tr>
                                    <td class="col-lint text-center">{{ $at['nomor_lintasan'] ?: '-' }}</td>
                                    <td class="col-nama">
                                        <span class="font-bold">{{ strtoupper($at['nama_lengkap']) }}</span>
                                        <span style="font-size: 6.5px; color: #888; margin-left: 5px;">({{ $at['registration_number'] }})</span>
                                    </td>
                                    <td class="col-lahir text-center">{{ !empty($at['tanggal_lahir']) ? date('Y', strtotime($at['tanggal_lahir'])) : '-' }}</td>
                                    <td class="col-tim">{{ strtoupper($at['klub_renang']) }}</td>
                                    <td class="col-entry text-center">{{ $at['prestasi'] ?: 'NT' }}</td>
                                    <td class="col-hasil">
                                        <div class="result-box"></div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            </div>
        @endforeach

        <div style="page-break-after: always;"></div>
    @endforeach

    <div class="footer">
        Dicetak otomatis oleh Khafid Swimming Club Management System - {{ date('d/m/Y H:i') }}
    </div>

</body>

</html>
