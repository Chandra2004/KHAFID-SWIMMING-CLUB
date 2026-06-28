<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 1.2cm 1cm 1.2cm 1cm;
            size: A4 landscape;
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
        .logo-right { margin-left: auto; }

        .header-separator {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 3px;
            margin-bottom: 15px;
        }
        
        .club-name-label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

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
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            color: #000;
        }
        .data-table td {
            padding: 6px 4px;
            border-bottom: 0.2px solid #ddd;
            vertical-align: middle;
            font-size: 10px;
            word-wrap: break-word;
            color: #000;
        }

        .summary-table {
            width: 50%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
            font-weight: bold;
            color: #000;
        }

        .summary-table td {
            padding: 4px;
        }
        
        .summary-table td:first-child {
            width: 120px;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .page-break { page-break-after: always; }
        
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
    @php
        $logoSrcL = null; $logoSrcR = null;
        if(isset($eventData)) {
            $defaultLogoPath = public_path('assets/ico/icon-bar.png');
            $defaultLogoBase64 = null;
            if (file_exists($defaultLogoPath)) {
                $defaultLogoBase64 = 'data:' . mime_content_type($defaultLogoPath) . ';base64,' . base64_encode(file_get_contents($defaultLogoPath));
            }

            $logoSrcL = (isset($eventData['logo_left']) && str_starts_with($eventData['logo_left'], 'data:image')) ? $eventData['logo_left'] : $defaultLogoBase64;
            $logoSrcR = (isset($eventData['logo_right']) && str_starts_with($eventData['logo_right'], 'data:image')) ? $eventData['logo_right'] : $defaultLogoBase64;
        }
    @endphp

    @forelse($clubData as $idx => $data)
        <table class="header-table">
            <tr>
                <td class="logo-box">
                    @if($logoSrcL) <img src="{{ $logoSrcL }}" class="logo-img"> @endif
                </td>
                <td class="header-content">
                    @if (isset($eventData))
                        <div class="event-name">{{ $eventData['nama_event'] }}</div>
                        <div class="event-detail">{{ $eventData['lokasi_event'] }} | {{ $eventData['tanggal_mulai'] ? \Carbon\Carbon::parse($eventData['tanggal_mulai'])->isoFormat('D MMMM YYYY') : '' }}</div>
                    @else
                        <div class="event-name">Khafid Swimming Club</div>
                        <div class="event-detail">Laporan Rekapitulasi Pendaftaran Global</div>
                    @endif
                    <div class="report-title">LIST PENDAFTARAN</div>
                </td>
                <td class="logo-box" style="text-align: right;">
                    @if($logoSrcR) <img src="{{ $logoSrcR }}" class="logo-img logo-right"> @endif
                </td>
            </tr>
        </table>

        <div class="header-separator"></div>
        
        <div class="club-name-label">Individu</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 30px;">No</th>
                    <th>Nama Lengkap</th>
                    <th class="text-center" style="width: 50px;">Lahir</th>
                    <th class="text-center" style="width: 80px;">Jenis Kelamin</th>
                    <th>Tim/ Club</th>
                    <th>Kategori</th>
                    <th>Tipe Gaya</th>
                    <th class="text-center">Prestasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['registrations'] as $index => $reg)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ strtoupper($reg['nama_lengkap']) }}</td>
                        <td class="text-center">{{ !empty($reg['tanggal_lahir']) ? date('Y', strtotime($reg['tanggal_lahir'])) : '-' }}</td>
                        <td class="text-center">{{ strtolower($reg['jenis_kelamin']) === 'female' ? 'Perempuan' : 'Laki-laki' }}</td>
                        <td>{{ strtoupper($reg['klub_renang']) }}</td>
                        <td>{{ strtoupper($reg['kategori']) }}</td>
                        <td>{{ strtoupper($reg['tipe_gaya']) }}</td>
                        <td class="text-center">{{ $reg['prestasi'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Belum ada pendaftaran untuk klub ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td>Tim/ Club</td>
                <td>: {{ strtoupper($data['club_name']) }}</td>
            </tr>
            <tr>
                <td>Nama Pelatih</td>
                <td>: {{ strtoupper($data['coach_name']) }}</td>
            </tr>
            <tr>
                <td>Nomor Pelatih</td>
                <td>: {{ $data['coach_phone'] }}</td>
            </tr>
            <tr>
                <td style="padding-top: 15px;">Tagihan Individu</td>
                <td style="padding-top: 15px;">: <span style="display:inline-block; margin-left: 20px;">{{ number_format($data['total_tagihan'], 0, ',', '.') }}</span></td>
            </tr>
            <tr>
                <td style="padding-top: 15px;">Total Tagihan</td>
                <td style="padding-top: 15px;">: <span style="display:inline-block; margin-left: 20px; font-size: 14px;">{{ number_format($data['total_tagihan'], 0, ',', '.') }}</span></td>
            </tr>
        </table>
        
        <div class="footer">
            {{ isset($eventData) ? $eventData['nama_event'] : 'Swimming Club Management System' }} - Halaman &copy; {{ date('Y') }}
        </div>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @empty
        <div style="text-align: center; padding: 50px; font-size: 16px;">Tidak ada data pendaftaran.</div>
    @endforelse

</body>
</html>
