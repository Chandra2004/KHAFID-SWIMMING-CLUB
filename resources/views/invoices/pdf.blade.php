<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
            font-size: 9px;
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
        .logo-img { max-height: 55px; max-width: 75px; display: block; }
        .logo-right { margin-left: auto; }

        .header-separator {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            height: 3px;
            margin-bottom: 15px;
        }

        .club-name-label {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #fff;
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 1.5px solid #000;
            border-top: 1.5px solid #000;
            color: #000;
        }
        .data-table td {
            padding: 6px 4px;
            border-bottom: 0.5px solid #eee;
            vertical-align: middle;
            font-size: 9px;
            word-wrap: break-word;
            color: #000;
        }

        .summary-table {
            width: 60%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10px;
            font-weight: bold;
            color: #000;
            page-break-inside: avoid;
        }

        .summary-table td {
            padding: 4px 2px;
            vertical-align: top;
        }
        
        .summary-table td:first-child {
            width: 130px;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

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
        // Load the exact registrations associated with this invoice
        $allRegistrations = collect();
        if (!empty($invoice->registration_uids)) {
            $allRegistrations = \App\Models\Registration::whereIn('uid', $invoice->registration_uids)
                ->with(['eventCategory.category', 'eventCategory.event', 'payment', 'user.profile.club'])
                ->get();
        }

        $groupedRegistrations = $allRegistrations->groupBy(function($reg) {
            return $reg->eventCategory?->event_uid ?? 'unknown';
        });
    @endphp

    <div class="footer">
        Khafid Swimming Club - Laporan Invoice &copy; {{ date('Y') }} - {{ $invoice->invoice_number }}
    </div>

    @foreach($groupedRegistrations as $eventUid => $registrations)
        @php
            $firstReg = $registrations->first();
            $eventCategory = $firstReg?->eventCategory;
            $event = $eventCategory?->event;
            $user = $firstReg?->user;
            $profile = $user?->profile;
            $club = $profile?->club;

            $logoSrcL = null; 
            $logoSrcR = null;

            if ($event) {
                $pathL = $event->logo_left ? public_path($event->logo_left) : '';
                if ($pathL && file_exists($pathL)) {
                    $logoSrcL = 'data:' . mime_content_type($pathL) . ';base64,' . base64_encode(file_get_contents($pathL));
                }

                $pathR = $event->logo_right ? public_path($event->logo_right) : '';
                if ($pathR && file_exists($pathR)) {
                    $logoSrcR = 'data:' . mime_content_type($pathR) . ';base64,' . base64_encode(file_get_contents($pathR));
                }
            }

            $totalTagihan = 0;
        @endphp

        <table class="header-table">
            <tr>
                <td class="logo-box">
                    @if($logoSrcL) <img src="{{ $logoSrcL }}" class="logo-img"> @endif
                </td>
                <td class="header-content">
                    @if ($event)
                        <div class="event-name">{{ $event->name }}</div>
                        <div class="event-detail">{{ $event->location }} | {{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->isoFormat('D MMMM YYYY') : '' }}</div>
                    @else
                        <div class="event-name">Khafid Swimming Club</div>
                        <div class="event-detail">Laporan Invoice Pendaftaran</div>
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
                    <th class="text-right" style="width: 80px;">Biaya</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrations as $index => $reg)
                    @php
                        $fee = $reg->eventCategory?->registration_fee ?? 0;
                        $totalTagihan += $fee;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ strtoupper($profile?->full_name ?? $user?->username ?? '-') }}</td>
                        <td class="text-center">{{ $profile?->birth_date ? $profile->birth_date->format('Y') : '-' }}</td>
                        <td class="text-center">{{ strtolower($profile?->gender ?? '') === 'female' ? 'Perempuan' : 'Laki-laki' }}</td>
                        <td>{{ strtoupper($club?->name ?? 'KSC') }}</td>
                        <td>{{ strtoupper($reg->eventCategory?->category?->name ?? 'UMUM') }}</td>
                        <td>{{ strtoupper($reg->eventCategory?->acara_name ?? '-') }}</td>
                        <td class="text-right">Rp {{ number_format($fee, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td>Tim/ Club</td>
                <td>: {{ strtoupper($club?->name ?? 'KSC') }}</td>
            </tr>
            <tr>
                <td>Nama Pelatih</td>
                <td>: {{ strtoupper($club?->coach_name ?? '-') }}</td>
            </tr>
            <tr>
                <td>Nomor Pelatih</td>
                <td>: {{ $club?->contact ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding-top: 15px;">Tagihan Individu</td>
                <td style="padding-top: 15px;">: Rp <span style="display:inline-block; margin-left: 5px;">{{ number_format($totalTagihan, 0, ',', '.') }}</span></td>
            </tr>
            <tr>
                <td style="padding-top: 5px;">Total Tagihan Event Ini</td>
                <td style="padding-top: 5px; font-size: 13px;">: Rp <span style="display:inline-block; margin-left: 5px;">{{ number_format($totalTagihan, 0, ',', '.') }}</span></td>
            </tr>
        </table>
        
        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>
