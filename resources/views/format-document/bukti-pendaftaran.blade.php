<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Pendaftaran - {{ isset($registration->registration_number) ? $registration->registration_number : '' }}</title>
    <style>
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
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 900;
            src: url('https://fonts.gstatic.com/s/montserrat/v25/JTURjIg1_i6t8kCHKm4df_8Pdzv3qPNy.ttf') format('truetype');
        }

        @page {
            margin: 0;
            size: A4 portrait;
        }

        body {
            font-family: 'Montserrat', Helvetica, Arial, sans-serif;
            line-height: 1.4;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .container {
            padding: 0;
            position: relative;
        }

        /* HEADER BLUE BAR */
        .top-gradient {
            height: 15px;
            background: linear-gradient(to right, #1e3a8a, #3b82f6);
            width: 100%;
        }

        .header-wrapper {
            padding: 25px 50px 10px 50px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .logo-box {
            width: 80px;
        }

        .logo-img {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }

        .header-text {
            text-align: center;
            padding: 0 20px;
        }

        .header-text h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: -1px;
        }

        .header-text p {
            margin: 4px 0 0;
            font-size: 10px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* MAIN CONTENT */
        .content-wrapper {
            padding: 0 50px;
        }

        .card-main {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            position: relative;
        }

        .card-top {
            background: #f8fafc;
            padding: 20px 40px;
            border-bottom: 1px dotted #e2e8f0;
            display: table;
            width: 100%;
            box-sizing: border-box;
        }

        .reg-info {
            display: table-cell;
            vertical-align: middle;
        }

        .reg-label {
            font-size: 9px;
            font-weight: 900;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 4px;
        }

        .reg-number {
            font-size: 28px;
            font-weight: 900;
            color: #2563eb;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .stamp-box {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .stamp-badge {
            display: inline-block;
            border: 3px solid #10b981;
            color: #10b981;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            border-radius: 12px;
            transform: rotate(-5deg);
        }

        .card-body {
            padding: 25px 40px;
        }

        /* MAIN LAYOUT TABLE */
        .layout-table {
            width: 100%;
            margin-bottom: 25px;
        }

        .info-table {
            width: 100%;
        }

        .info-cell {
            padding: 10px 10px 10px 0;
            vertical-align: top;
        }

        .field-label {
            font-size: 9px;
            font-weight: 900;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 6px;
        }

        .field-value {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        /* AVATAR / PROFILE PHOTO */
        .profile-photo-cell {
            vertical-align: top;
            text-align: right;
            width: 120px;
            padding-top: 10px;
        }

        .profile-photo-container {
            width: 120px;
            height: 120px;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            overflow: hidden;
            background-color: #f1f5f9;
            display: inline-block;
        }

        .profile-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* EVENT BOX */
        .event-card {
            background: #0f172a;
            border-radius: 24px;
            padding: 25px 30px;
            color: #ffffff;
            margin-top: 5px;
        }

        .event-tag {
            font-size: 9px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .event-name {
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            line-height: 1.2;
        }

        .event-cat {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 25px;
        }

        .schedule-grid {
            width: 100%;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 15px;
        }

        .schedule-item {
            text-align: center;
        }

        .schedule-label {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .schedule-val {
            font-size: 32px;
            font-weight: 900;
            color: #ffffff;
        }

        /* LINKS */
        .links-container {
            margin-top: 15px;
            background: #f0fdf4;
            border: 1px solid #dcfce7;
            border-radius: 20px;
            padding: 15px 20px;
        }

        .link-block {
            margin-bottom: 15px;
        }

        .link-block:last-child { margin-bottom: 0; }

        .link-title {
            font-size: 9px;
            font-weight: 900;
            color: #166534;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .link-text {
            font-size: 11px;
            color: #15803d;
            font-weight: 600;
            word-break: break-all;
        }

        /* FOOTER */
        .footer-wrapper {
            margin-top: 25px;
            text-align: center;
            padding: 0 50px 20px 50px;
        }

        .footer-line {
            height: 1px;
            background: #f1f5f9;
            width: 100%;
            margin-bottom: 20px;
        }

        .footer-text {
            font-size: 10px;
            color: #94a3b8;
            line-height: 1.6;
            font-weight: 500;
        }

        .footer-text strong {
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-gradient"></div>

        <div class="header-wrapper">
            <table class="header-table">
                <tr>
                    <td class="logo-box">
                        @if(!empty($logoSrcLeft))
                            <img src="{{ $logoSrcLeft }}" class="logo-img">
                        @endif
                    </td>
                    <td class="header-text">
                        <h1>Bukti Pendaftaran Peserta</h1>
                        <p>
                            @if(isset($registration->eventCategory->event->name))
                                {{ $registration->eventCategory->event->name }}
                            @endif
                        </p>
                        <p style="font-size: 9px; color: #94a3b8; letter-spacing: 0.5px; text-transform: none;">
                            @if(isset($registration->eventCategory->event->location))
                                {{ $registration->eventCategory->event->location }}
                            @endif
                        </p>
                    </td>
                    <td class="logo-box" style="text-align: right;">
                        @if(!empty($logoSrcRight))
                            <img src="{{ $logoSrcRight }}" class="logo-img">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="content-wrapper">
            <div class="card-main">
                <div class="card-top">
                    <div class="reg-info">
                        <div class="reg-label">Nomor Registrasi</div>
                        <h2 class="reg-number">{{ isset($registration->registration_number) ? $registration->registration_number : '' }}</h2>
                    </div>
                    <div class="stamp-box">
                        <div class="stamp-badge">TERVERIFIKASI</div>
                    </div>
                </div>

                <div class="card-body">
                    <table class="layout-table">
                        <tr>
                            <td style="vertical-align: top;">
                                <table class="info-table">
                                    <tr>
                                        <td class="info-cell" colspan="2">
                                            <div class="field-label">Nama Lengkap Atlet</div>
                                            <div class="field-value" style="font-size: 20px;">
                                                @if(isset($registration->user->profile->full_name))
                                                    {{ strtoupper($registration->user->profile->full_name) }}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-cell" width="55%">
                                            <div class="field-label">Klub/Asal Sekolah</div>
                                            <div class="field-value">
                                                @if(isset($registration->user->profile->club->name))
                                                    {{ strtoupper($registration->user->profile->club->name) }}
                                                @else
                                                    INDEPENDENT
                                                @endif
                                            </div>
                                        </td>
                                        <td class="info-cell" width="45%">
                                            <div class="field-label">Tanggal Pendaftaran</div>
                                            <div class="field-value">
                                                @if(isset($registration->created_at))
                                                    {{ $registration->created_at->format('d F Y') }}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-cell" colspan="2">
                                            <div class="field-label">Jenis Kelamin</div>
                                            <div class="field-value">
                                                @if(isset($registration->user->profile->gender) && $registration->user->profile->gender === 'male')
                                                    LAKI-LAKI
                                                @else
                                                    PEREMPUAN
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            <td class="profile-photo-cell">
                                <div class="profile-photo-container">
                                    @php
                                        $avatarData = null;
                                        if (isset($registration->user->profile->profile_picture) && !empty($registration->user->profile->profile_picture)) {
                                            $avatar = $registration->user->profile->profile_picture;
                                            if (str_starts_with($avatar, 'http')) {
                                                $avatarData = $avatar;
                                            } else {
                                                $path = public_path(ltrim($avatar, '/'));
                                                if (file_exists($path)) {
                                                    $avatarData = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
                                                }
                                            }
                                        }
                                    @endphp

                                    @if($avatarData)
                                        <img src="{{ $avatarData }}" class="profile-photo-img" alt="Foto Profil">
                                    @else
                                        <div class="profile-photo-img" style="background: #cbd5e1; display:flex; align-items:center; justify-content:center; height:100%; color:#64748b; font-size:10px; font-weight:bold; text-align:center; padding:10px; box-sizing:border-box;">NO FOTO</div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="event-card">
                        <div class="event-tag">Detail Perlombaan</div>
                        <h3 class="event-name">
                            @if(isset($registration->eventCategory->acara_name))
                                {{ $registration->eventCategory->acara_name }}
                            @endif
                        </h3>
                        <div class="event-cat">
                            Kategori: {{ (isset($registration->eventCategory->main_requirement) && $registration->eventCategory->main_requirement) ? $registration->eventCategory->main_requirement : 'Umum' }}
                        </div>

                        @if(isset($registration->schedule) && !empty($registration->schedule))
                            <table class="schedule-grid">
                                <tr>
                                    <td class="schedule-item" width="50%">
                                        <div class="schedule-label">Seri (Heat)</div>
                                        <div class="schedule-val">{{ $registration->schedule->heat_number }}</div>
                                    </td>
                                    <td class="schedule-item" width="50%">
                                        <div class="schedule-label">Lintasan (Lane)</div>
                                        <div class="schedule-val">{{ $registration->schedule->lane_number }}</div>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </div>

                    @php
                        $eventGroupLink = isset($registration->eventCategory->event->group_link) ? $registration->eventCategory->event->group_link : null;
                        $categoryGroupLink = isset($registration->eventCategory->group_link) ? $registration->eventCategory->group_link : null;
                    @endphp

                    @if($eventGroupLink || $categoryGroupLink)
                        <div class="links-container">
                            @if($eventGroupLink)
                                <div class="link-block">
                                    <div class="link-title">Grup Koordinasi Event Utama:</div>
                                    <div class="link-text">{{ $eventGroupLink }}</div>
                                </div>
                            @endif

                            @if($categoryGroupLink)
                                <div class="link-block">
                                    <div class="link-title">Grup Teknis Nomor Lomba:</div>
                                    <div class="link-text">{{ $categoryGroupLink }}</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="footer-wrapper">
            <div class="footer-line"></div>
            <p class="footer-text">
                Bukti ini sah dan diterbitkan secara elektronik oleh sistem manajemen <strong>Khafid Swimming Club</strong>.<br>
                Silakan tunjukkan bukti ini (digital/cetak) kepada panitia saat daftar ulang.
            </p>
        </div>
    </div>
</body>
</html>
