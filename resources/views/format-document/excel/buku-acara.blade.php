<table>
    @foreach($globalData as $item)
        <thead>
            <tr>
                <th colspan="6" style="font-weight: bold; font-size: 14px; text-align: center;">{{ strtoupper($item['event']['nama_event']) }}</th>
            </tr>
            <tr>
                <th colspan="6" style="text-align: center;">{{ $item['event']['lokasi_event'] }}</th>
            </tr>
            <tr>
                <th colspan="6" style="font-weight: bold; text-align: center;">BUKU ACARA (PROGRAM BOOK)</th>
            </tr>
            <tr><th colspan="6"></th></tr>
        </thead>

        @foreach($item['dataAcara'] as $acara)
            <tbody>
                <tr style="background-color: #f3f4f6;">
                    <th colspan="1" style="font-weight: bold; border: 1px solid #000;">ACARA #{{ $acara['nomor_acara'] }}</th>
                    <th colspan="4" style="font-weight: bold; border: 1px solid #000; text-align: center;">{{ strtoupper($acara['nama_acara']) }}</th>
                    <th colspan="1" style="font-weight: bold; border: 1px solid #000; text-align: right;">{{ $acara['main_requirement'] }}</th>
                </tr>

                @foreach($acara['seri'] as $seriNum => $athletes)
                    <tr>
                        <th colspan="6" style="font-weight: bold; padding-top: 10px;">SERI {{ $seriNum }}</th>
                    </tr>
                    <tr style="background-color: #ffffff;">
                        <th style="font-weight: bold; border: 1px solid #000; text-align: center;">LINT</th>
                        <th style="font-weight: bold; border: 1px solid #000;">NAMA LENGKAP ATLET</th>
                        <th style="font-weight: bold; border: 1px solid #000; text-align: center;">LAHIR</th>
                        <th style="font-weight: bold; border: 1px solid #000;">KLUB/ASAL SEKOLAH</th>
                        <th style="font-weight: bold; border: 1px solid #000; text-align: center;">ENTRY TIME</th>
                        <th style="font-weight: bold; border: 1px solid #000; text-align: center;">HASIL AKHIR</th>
                    </tr>
                    @foreach($athletes as $at)
                        <tr>
                            <td style="border: 1px solid #000; text-align: center;">{{ $at['nomor_lintasan'] }}</td>
                            <td style="border: 1px solid #000;">{{ strtoupper($at['nama_lengkap']) }} ({{ $at['registration_number'] }})</td>
                            <td style="border: 1px solid #000; text-align: center;">{{ $at['tanggal_lahir'] }}</td>
                            <td style="border: 1px solid #000;">{{ strtoupper($at['klub_renang']) }}</td>
                            <td style="border: 1px solid #000; text-align: center;">{{ $at['prestasi'] }}</td>
                            <td style="border: 1px solid #000;"></td>
                        </tr>
                    @endforeach
                    <tr><td colspan="6"></td></tr>
                @endforeach
            </tbody>
            <tr><td colspan="6"></td></tr>
        @endforeach
        <tr><td colspan="6" style="height: 30px;"></td></tr>
    @endforeach
</table>
