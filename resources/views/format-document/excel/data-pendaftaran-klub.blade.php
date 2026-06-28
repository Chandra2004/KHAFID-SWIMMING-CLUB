<table>
    @forelse($clubData as $idx => $data)
        <tr>
            <td colspan="8" style="text-align: center; font-weight: bold; font-size: 16px;">
                @if (isset($eventData))
                    {{ strtoupper($eventData['nama_event']) }}
                @else
                    LIST PENDAFTARAN KLUB
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: center; font-size: 12px;">
                @if (isset($eventData))
                    {{ strtoupper($eventData['lokasi_event']) }} | {{ $eventData['tanggal_mulai'] ? \Carbon\Carbon::parse($eventData['tanggal_mulai'])->isoFormat('D MMMM YYYY') : '' }}
                @else
                    Laporan Rekapitulasi Pendaftaran Global
                @endif
            </td>
        </tr>
        
        <tr><td colspan="8"></td></tr>
        
        <tr>
            <td colspan="8" style="font-weight: bold; font-size: 14px;">Individu</td>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">No</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Nama Lengkap</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Lahir</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Jenis Kelamin</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Tim/ Club</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Kategori</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Tipe Gaya</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Prestasi</th>
        </tr>
        
        @forelse($data['registrations'] as $index => $reg)
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000;">{{ strtoupper($reg['nama_lengkap']) }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ !empty($reg['tanggal_lahir']) ? date('Y', strtotime($reg['tanggal_lahir'])) : '-' }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ strtolower($reg['jenis_kelamin']) === 'female' ? 'Perempuan' : 'Laki-laki' }}</td>
                <td style="border: 1px solid #000;">{{ strtoupper($reg['klub_renang']) }}</td>
                <td style="border: 1px solid #000;">{{ strtoupper($reg['kategori']) }}</td>
                <td style="border: 1px solid #000;">{{ strtoupper($reg['tipe_gaya']) }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $reg['prestasi'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="border: 1px solid #000; text-align: center;">Belum ada pendaftaran untuk klub ini.</td>
            </tr>
        @endforelse
        
        <tr><td colspan="8"></td></tr>
        
        <tr>
            <td colspan="2" style="font-weight: bold;">Tim/ Club</td>
            <td colspan="6">: {{ strtoupper($data['club_name']) }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Nama Pelatih</td>
            <td colspan="6">: {{ strtoupper($data['coach_name']) }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Nomor Pelatih</td>
            <td colspan="6">: {{ $data['coach_phone'] }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Tagihan Individu</td>
            <td colspan="6">: {{ $data['total_tagihan'] }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Total Tagihan</td>
            <td colspan="6">: {{ $data['total_tagihan'] }}</td>
        </tr>
        
        <tr><td colspan="8"></td></tr>
        <tr><td colspan="8"></td></tr>
    @empty
        <tr>
            <td colspan="8" style="text-align: center;">Tidak ada data pendaftaran.</td>
        </tr>
    @endforelse
</table>
