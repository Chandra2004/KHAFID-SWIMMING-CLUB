<table>
    @foreach($allEventResults as $eventResult)
        @php
            $event = $eventResult['event'];
            $globalData = $eventResult['globalData'];
        @endphp
        <thead>
            <tr>
                <th colspan="6" style="font-weight: bold; font-size: 16px; text-align: center;">{{ strtoupper($event['name'] ?? $event['nama_event']) }}</th>
            </tr>
            <tr>
                <th colspan="6" style="text-align: center; font-size: 11px;">{{ $event['location'] ?? $event['lokasi_event'] }} | {{ !empty($event['tanggal_mulai']) ? \Carbon\Carbon::parse($event['tanggal_mulai'])->isoFormat('D MMMM YYYY') : '' }}</th>
            </tr>
            <tr>
                <th colspan="6" style="font-weight: bold; font-size: 14px; text-align: center;">BUKU HASIL LOMBA (OFFICIAL RESULTS)</th>
            </tr>
            <tr><th colspan="6"></th></tr>
        </thead>

        @foreach($globalData as $item)
            <tbody>
                <tr style="background-color: #f1f5f9;">
                    <th colspan="1" style="font-weight: bold; border: 1.5px solid #000; text-align: left;">ACARA #{{ $item['acara']['acara_number'] ?? $item['acara']['nomor_acara'] }}</th>
                    <th colspan="4" style="font-weight: bold; border: 1.5px solid #000; text-align: center;">{{ strtoupper($item['acara']['acara_name'] ?? $item['acara']['nama_acara']) }}</th>
                    <th colspan="1" style="font-weight: bold; border: 1.5px solid #000; text-align: right;">{{ $item['acara']['main_requirement'] }}</th>
                </tr>

                <tr style="background-color: #ffffff;">
                    <th style="font-weight: bold; border: 1px solid #000; text-align: center; width: 80px;">POS</th>
                    <th style="font-weight: bold; border: 1px solid #000; width: 350px;">NAMA LENGKAP ATLET</th>
                    <th style="font-weight: bold; border: 1px solid #000; text-align: center; width: 100px;">LAHIR</th>
                    <th style="font-weight: bold; border: 1px solid #000; width: 250px;">KLUB/ASAL SEKOLAH</th>
                    <th style="font-weight: bold; border: 1px solid #000; text-align: center; width: 150px;">WAKTU AKHIR</th>
                    <th style="font-weight: bold; border: 1px solid #000; text-align: center; width: 100px;">STATUS</th>
                </tr>

                @forelse($item['results'] as $index => $res)
                    @php
                        $isFinish = $res['status'] === 'FINISH';
                        $bgColor = '#ffffff';
                        if($isFinish && $index == 0) $bgColor = '#fef3c7'; // Gold
                        if($isFinish && $index == 1) $bgColor = '#f1f5f9'; // Silver
                        if($isFinish && $index == 2) $bgColor = '#fff7ed'; // Bronze
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000; text-align: center; background-color: {{ $bgColor }}; font-weight: bold;">{{ $isFinish ? ($index + 1) : '-' }}</td>
                        <td style="border: 1px solid #000; background-color: {{ $bgColor }};">{{ strtoupper($res['full_name']) }}</td>
                        <td style="border: 1px solid #000; text-align: center; background-color: {{ $bgColor }};">
                            {{ $res['birth_date'] instanceof \Carbon\Carbon ? $res['birth_date']->format('Y') : ( !empty($res['birth_date']) ? date('Y', strtotime($res['birth_date'])) : '-' ) }}
                        </td>
                        <td style="border: 1px solid #000; background-color: {{ $bgColor }};">{{ strtoupper($res['club_name']) }}</td>
                        <td style="border: 1px solid #000; text-align: center; font-weight: bold; background-color: {{ $bgColor }}; font-family: 'Courier New';">
                            {{ $res['final_time'] ?? '-' }}
                        </td>
                        <td style="border: 1px solid #000; text-align: center; background-color: {{ $bgColor }}; font-size: 9px;">
                            {{ $res['status'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="border: 1px solid #000; text-align: center; padding: 10px; color: #888;">Data hasil belum tersedia</td>
                    </tr>
                @endforelse
                <tr><td colspan="6"></td></tr>
            </tbody>
        @endforeach
        <tr><td colspan="6" style="height: 30px;"></td></tr>
    @endforeach
</table>
