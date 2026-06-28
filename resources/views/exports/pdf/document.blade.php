<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @if(($document->font_family ?? '') === 'Montserrat')
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    @elseif(($document->font_family ?? '') === 'Roboto')
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: '{{ $document->font_family ?? 'Arial' }}', sans-serif; 
            font-size: {{ $document->font_size ?? 10 }}pt; 
            color: #1a1a1a; 
        }
        .page { padding: 1cm; }
        .header { display: flex; align-items: center; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; margin-bottom: 20px; }
        .header-logo { width: 70px; }
        .header-logo img { max-width: 60px; max-height: 60px; }
        .header-center { flex: 1; text-align: center; }
        .header-center h1 { font-size: 16pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header-center p { font-size: 10pt; margin-top: 2px; }
        .body-title { text-align: center; font-weight: bold; text-transform: uppercase; text-decoration: underline; margin-bottom: 15px; font-size: 11pt; }
        .content-block { margin-bottom: 15px; font-size: 10pt; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 4px 8px; text-align: left; font-size: {{ $layout['table']['font_size'] ?? '10' }}pt; }
        @if($layout['table']['show_border'] ?? true)
        th, td { border: 1px solid #333; }
        @endif
        th { background: #e8e8e8; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        @if($layout['table']['zebra'] ?? true)
        tr:nth-child(even) { background: #f5f5f5; }
        @endif
        .footer { position: fixed; bottom: 1cm; left: 1cm; right: 1cm; border-top: 1px solid #ccc; padding-top: 5px; font-size: 8pt; color: #888; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <div class="page">
        @if($layout['header']['show'] ?? true)
        <table style="border: none; margin-bottom: 20px; border-bottom: 2px solid #1a1a1a; padding-bottom: 10px;">
            <tr style="border: none;">
                <td style="border: none; width: 80px; vertical-align: middle;">
                    @if(($layout['header']['show_logo_left'] ?? true) && $document->logo_left)
                        <img src="{{ public_path('storage/' . $document->logo_left) }}" style="max-width:60px;max-height:60px;">
                    @endif
                </td>
                <td style="border: none; text-align: center; vertical-align: middle;">
                    <div style="font-size:16pt;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">{{ $document->title ?? 'DOKUMEN' }}</div>
                    <div style="font-size:10pt;margin-top:2px;">{{ $document->description ?? '' }}</div>
                </td>
                <td style="border: none; width: 80px; text-align: right; vertical-align: middle;">
                    @if(($layout['header']['show_logo_right'] ?? true) && $document->logo_right)
                        <img src="{{ public_path('storage/' . $document->logo_right) }}" style="max-width:60px;max-height:60px;">
                    @endif
                </td>
            </tr>
        </table>
        @endif

        @if($document->content)
            <div class="content-block">{!! $document->content !!}</div>
        @endif

        @if(count($columns) > 0)
            <div class="body-title">{{ strtoupper($document->type ?? 'LAPORAN') }}</div>
            <table>
                <thead>
                    <tr>
                        @foreach($columns as $col)
                            <th>{{ $col['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            @foreach($columns as $col)
                                <td>{{ $row[$col['key']] ?? '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($layout['footer']['show'] ?? true)
            <div class="footer">
                <span>{{ $layout['footer']['text'] ?? '' }}</span>
                <span>Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
            </div>
        @endif
    </div>
</body>
</html>
