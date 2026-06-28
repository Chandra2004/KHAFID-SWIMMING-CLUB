{{-- Variabel yang tersedia: $data (array), $ip_address (string) --}}
<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notifikasi Kontak Masuk - KSC Admin</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
        
        <!-- Schema.org markup for Google -->
        <script type="application/ld+json">
        {
          "@@context": "http://schema.org",
          "@@type": "EmailMessage",
          "potentialAction": {
            "@@type": "ViewAction",
            "name": "Balas Pesan",
            "target": "mailto:{{ $data['email'] }}?subject=Re%3A%20{{ urlencode($data['subjek']) }}"
          },
          "description": "Lihat dan balas pesan yang dikirim melalui formulir kontak."
        }
        </script>
        
    </head>

    <body style="margin: 0; padding: 0; background-color: #0f172a; font-family: 'Poppins', Helvetica, Arial, sans-serif; color: #ffffff;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a; padding: 40px 0;">
            <tr>
                <td align="center">

                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #1e293b; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 2.5rem; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">

                        <tr>
                            <td align="center" style="padding: 50px 40px; background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); position: relative;">
                                <div style="background-color: #ffffff; padding: 12px; border-radius: 24px; display: inline-block; margin-bottom: 20px; box-shadow: 0 10px 15px rgba(0,0,0,0.2);">
                                    <img src="{{ $message->embed(public_path('/assets/ico/icon-bar.png')) }}" alt="KSC Logo" width="70" style="display: block; border: 0;">
                                </div>
                                <h1 style="font-size: 24px; font-weight: 700; margin: 0; color: #ffffff; letter-spacing: -0.025em;">Pesan Baru Diterima</h1>
                                <p style="color: rgba(255, 255, 255, 0.7); font-size: 15px; margin-top: 8px; font-weight: 400;">Ada pertanyaan baru dari pengunjung website.</p>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 40px;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="padding-bottom: 30px;">
                                            <div style="background-color: rgba(255, 255, 255, 0.03); border-radius: 20px; padding: 20px; border: 1px solid rgba(255, 255, 255, 0.05);">
                                                <label style="color: #f59e0b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 8px;">Detail Pengirim</label>
                                                <div style="color: #ffffff; font-size: 18px; font-weight: 600;">{{ $data['nama_lengkap'] }}</div>
                                                <div style="color: #94a3b8; font-size: 14px; margin-top: 2px;">{{ $data['email'] }}</div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding-bottom: 35px;">
                                            <label style="color: #94a3b8; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 12px; padding-left: 5px;">Perihal: <span style="color: #ffffff; text-transform: none;">{{ $data['subjek'] }}</span></label>
                                            <div style="background-color: rgba(255, 255, 255, 0.05); border-left: 4px solid #f59e0b; border-radius: 4px 16px 16px 4px; padding: 25px; min-height: 100px;">
                                                <p style="color: #cbd5e1; font-size: 15px; line-height: 1.8; margin: 0; font-style: italic;">
                                                    "{!! nl2br(e($data['pesan'])) !!}"
                                                </p>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center">
                                            <a href="mailto:{{ $data['email'] }}?subject=Re: {{ urlencode($data['subjek']) }}" style="background-color: #f59e0b; color: #0f172a; padding: 18px 45px; border-radius: 20px; text-decoration: none; font-weight: 700; display: inline-block; font-size: 15px; transition: all 0.3s ease; box-shadow: 0 10px 20px rgba(245, 158, 11, 0.15);">
                                                Balas Pesan via Email
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td align="center" style="padding: 25px; border-top: 1px solid rgba(255, 255, 255, 0.05); background-color: rgba(0, 0, 0, 0.15);">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="center" style="color: #64748b; font-size: 12px; line-height: 1.5;">
                                            Diterima pada: <strong>{{ date('d M Y, H:i') }}</strong><br>
                                            ID Pelacakan IP: <span style="color: #475569;">{{ $ip_address }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <p style="margin-top: 30px; color: #475569; font-size: 12px; letter-spacing: 0.05em;">
                    &copy; {{ date('Y') }} <strong>KHAFID SWIMMING CLUB</strong>. Admin Notification System.
                    </p>
                </td>
            </tr>
        </table>
    </body>

</html>
