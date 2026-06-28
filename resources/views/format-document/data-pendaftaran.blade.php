<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Rekapitulasi Pendaftaran' }}</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm;
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
            line-height: 1.4;
            font-size: 9px;
        }

        /* Header Section */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .header-table td { vertical-align: middle; }
        .header-content { text-align: center; padding: 0 10px; }
        .event-name { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .event-detail { font-size: 10px; color: #333; }
        .report-title { font-size: 18px; font-weight: bold; margin-top: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .logo-box { width: 15%; }
        .logo-img { max-height: 60px; max-width: 80px; display: block; }

        /* Separator */
        .header-separator {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 4px;
            margin-bottom: 20px;
        }

        /* Section Titles */
        .section-header {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 30px;
            margin-bottom: 10px;
            border-left: 5px solid #000;
            padding-left: 10px;
        }

        .event-group-label {
            font-size: 12px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 5px;
            text-decoration: underline;
        }

        .lomba-wrapper {
            margin-bottom: 25px;
        }

        .lomba-info-bar {
            background-color: #f9f9f9;
            padding: 6px 10px;
            border: 1px solid #ddd;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
            display: table;
            width: 100%;
        }
        .lomba-name { display: table-cell; text-align: left; font-size: 14px; }
        .lomba-fee { display: table-cell; text-align: right; font-size: 10px; }

        /* Tables - PERUBAHAN UTAMA: Ditambahkan table-layout fixed */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 5px;
        }
        .data-table th {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            background-color: #fff;
        }
        .data-table td {
            padding: 7px 4px;
            border-bottom: 0.5px solid #eee;
            vertical-align: middle;
            font-size: 9px;
            word-wrap: break-word; /* Mencegah teks meluber jika kolom sempit */
        }

        /* Alignment Utilities - Dipastikan presisi */
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }

        /* Subtotals */
        .subtotal-container {
            text-align: right;
            padding: 5px 10px;
            font-size: 9px;
            font-weight: bold;
            background-color: #fff;
            border: 1px solid #eee;
            border-top: none;
            margin-bottom: 20px;
        }

        /* Grand Total */
        .grand-total-box {
            margin-top: 20px;
            padding: 15px;
            border: 2px solid #000;
            text-align: right;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -0.5cm;
            left: 0;
            right: 0;
            font-size: 8px;
            text-align: center;
            color: #999;
            border-top: 0.5px solid #eee;
            padding-top: 5px;
        }

        /* Signature */
        .signature-section {
            margin-top: 20px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-container {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature-date { margin-bottom: 50px; }
        .signature-space { height: 70px; }

        /* Utility */
        .page-break { page-break-before: always; }
        .clear { clear: both; }
    </style>
</head>

<body>

    @php
        $defaultLogoPath = public_path('assets/ico/icon-bar.png');
        $logoSrcL = ''; $logoSrcR = '';

        $pathL = (isset($event) && !empty($event['logo_left']) && file_exists(public_path($event['logo_left'])))
                 ? public_path($event['logo_left']) : $defaultLogoPath;
        if (file_exists($pathL)) {
            $logoSrcL = 'data:' . mime_content_type($pathL) . ';base64,' . base64_encode(file_get_contents($pathL));
        }

        $pathR = (isset($event) && !empty($event['logo_right']) && file_exists(public_path($event['logo_right'])))
                 ? public_path($event['logo_right']) : $defaultLogoPath;
        if (file_exists($pathR)) {
            $logoSrcR = 'data:' . mime_content_type($pathR) . ';base64,' . base64_encode(file_get_contents($pathR));
        }

        $registrationsCollection = collect($registrations);

        $activeData = $registrationsCollection->filter(function($reg) {
            return in_array(strtolower($reg['status_pendaftaran']), ['pending', 'confirmed']);
        })->groupBy('nama_event');

        $inactiveData = $registrationsCollection->filter(function($reg) {
            return in_array(strtolower($reg['status_pendaftaran']), ['cancelled', 'rejected']);
        })->groupBy('nama_event');

        $grandTotalFee = 0;
        $grandTotalParticipants = 0;
    @endphp

    <table class="header-table">
        <tr>
            <td class="logo-box">
                @if($logoSrcL) <img src="{{ $logoSrcL }}" class="logo-img"> @endif
            </td>
            <td class="header-content">
                @if (isset($event))
                    <div class="event-name">{{ $event['nama_event'] ?? $event['name'] }}</div>
                    <div class="event-detail">{{ $event['lokasi_event'] ?? $event['location'] }} | {{ $event['tanggal_mulai'] ? \Carbon\Carbon::parse($event['tanggal_mulai'])->isoFormat('D MMMM YYYY') : '' }}</div>
                @else
                    <div class="event-name">Khafid Swimming Club</div>
                    <div class="event-detail">Laporan Rekapitulasi Pendaftaran Global</div>
                @endif
                <div class="report-title">Rekapitulasi Pendaftaran Atlet</div>
            </td>
            <td class="logo-box" style="text-align: right;">
                @if($logoSrcR) <img src="{{ $logoSrcR }}" class="logo-img" style="margin-left: auto;"> @endif
            </td>
        </tr>
    </table>

    <div class="header-separator"></div>

    {{-- SECTION A: ACTIVE --}}
    <div class="section-header">A. Pendaftaran Aktif (Terverifikasi)</div>

    @forelse($activeData as $evtName => $lombaGroups)
        <div class="event-group-label">EVENT: {{ $evtName }}</div>

        @foreach($lombaGroups->groupBy('nama_acara') as $lombaName => $regs)
            @php
                $feePerRace = (float)($regs->first()['biaya_pendaftaran'] ?? 0);
                $activeCount = $regs->count();
                $subTotal = $feePerRace * $activeCount;
                $grandTotalFee += $subTotal;
                $grandTotalParticipants += $activeCount;
            @endphp

            <div class="lomba-wrapper">
                <div class="lomba-info-bar">
                    <span class="lomba-name">LOMBA: {{ $lombaName }}</span>
                    <span class="lomba-fee">BIAYA: Rp {{ number_format($feePerRace, 0, ',', '.') }}</span>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <!-- Lebar kolom dikunci menggunakan ukuran pixel absolut -->
                            <th class="text-center" style="width: 30px;">No</th>
                            <th style="width: 90px;">Reg ID</th>
                            <th style="width: 170px;">Nama Lengkap</th>
                            <th class="text-center" style="width: 50px;">Lahir</th>
                            <th class="text-center" style="width: 40px;">JK</th>
                            <th style="width: 130px;">Klub/Asal Sekolah</th>
                            <th class="text-center" style="width: 80px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($regs as $idx => $reg)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td class="font-bold">{{ $reg['nomor_pendaftaran'] }}</td>
                                <td>{{ ucwords(strtolower($reg['nama_lengkap'])) }}</td>
                                <td class="text-center">{{ !empty($reg['tanggal_lahir']) ? date('Y', strtotime($reg['tanggal_lahir'])) : '-' }}</td>
                                <td class="text-center">{{ strtolower($reg['jenis_kelamin']) === 'female' ? 'P' : 'L' }}</td>
                                <td>{{ $reg['klub_renang'] }}</td>
                                <td class="text-center">{{ strtoupper($reg['status_pendaftaran']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="subtotal-container">
                    Sub-total ({{ $activeCount }} Peserta) : Rp {{ number_format($subTotal, 0, ',', '.') }}
                </div>
            </div>
        @endforeach
    @empty
        <div style="text-align: center; padding: 30px; color: #666; border: 1px dashed #ccc;">Belum ada data pendaftaran aktif.</div>
    @endforelse

    <div class="grand-total-box">
        TOTAL KESELURUHAN PESERTA AKTIF : {{ $grandTotalParticipants }} ATLET<br>
        TOTAL PENERIMAAN DANA : Rp {{ number_format($grandTotalFee, 0, ',', '.') }}
    </div>

    {{-- SIGNATURE SECTION A --}}
    <div class="signature-section">
        <div class="signature-container">
            <div class="signature-date">Dicetak pada: {{ date('d F Y') }}</div>
            <div class="signature-space"></div>
            <p><strong>( ________________________ )</strong></p>
        </div>
    </div>
    <div class="clear"></div>

    {{-- SECTION B: INACTIVE --}}
    <div class="page-break"></div>
    <div class="section-header" style="border-left-color: #666;">B. Pendaftaran Dibatalkan / Ditolak (Arsip)</div>

    @forelse($inactiveData as $evtName => $lombaGroups)
        <div class="event-group-label">EVENT: {{ $evtName }}</div>

        @foreach($lombaGroups->groupBy('nama_acara') as $lombaName => $regs)
            <div class="lomba-wrapper">
                <div class="lomba-info-bar" style="background-color: #eee;">
                    <span class="lomba-name">LOMBA: {{ $lombaName }}</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <!-- Lebar kolom dikunci menggunakan ukuran pixel absolut -->
                            <th class="text-center" style="width: 30px;">No</th>
                            <th style="width: 90px;">Reg ID</th>
                            <th style="width: 160px;">Nama Lengkap</th>
                            <th class="text-center" style="width: 80px;">Status</th>
                            <th style="width: 230px;">Alasan Penolakan / Pembatalan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($regs as $idx => $reg)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $reg['nomor_pendaftaran'] }}</td>
                                <td>{{ ucwords(strtolower($reg['nama_lengkap'])) }}</td>
                                <td class="text-center">{{ strtoupper($reg['status_pendaftaran']) }}</td>
                                <td style="font-style: italic; font-size: 8px; color: #444;">{{ $reg['notes'] ?: 'Tidak ada alasan spesifik' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @empty
        <div style="text-align: center; padding: 30px; color: #666; border: 1px dashed #ccc;">Tidak ada arsip pendaftaran yang dibatalkan.</div>
    @endforelse

    {{-- SIGNATURE SECTION B --}}
    <div class="signature-section">
        <div class="signature-container">
            <div class="signature-date">Dicetak pada: {{ date('d F Y') }}</div>
            <div class="signature-space"></div>
            <p><strong>( ________________________ )</strong></p>
        </div>
    </div>
    <div class="clear"></div>

    <div class="footer">
        Khafid Swimming Club - Laporan Rekapitulasi Pendaftaran Elektronik - Halaman &copy; {{ date('Y') }}
    </div>

</body>

</html>
