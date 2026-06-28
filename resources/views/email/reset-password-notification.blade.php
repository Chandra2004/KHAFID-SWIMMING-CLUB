<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - KSC</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; background-color: #0f172a; font-family: 'Poppins', Helvetica, Arial, sans-serif; color: #ffffff;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a; padding: 40px 0;">
        <tr>
            <td align="center">
                
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 550px; background-color: #1e293b; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 2.5rem; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
                    
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px;">
                            <div style="background-color: #ffffff; padding: 10px; border-radius: 20px; display: inline-block; margin-bottom: 20px;">
                                <img src="{{ $message->embed(public_path('/assets/ico/icon-bar.png')) }}" alt="KSC Logo" width="60" style="display: block; border: 0;">
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 40px 40px 40px; text-align: center;">
                            <h1 style="font-size: 24px; font-weight: 700; margin: 0; color: #ffffff;">Atur Ulang Kata Sandi</h1>
                            <p style="color: #94a3b8; font-size: 15px; margin-top: 15px; line-height: 1.6;">
                                Kami menerima permintaan untuk meriset kata sandi akun <strong> Khafid Swimming Club</strong> Anda. Gunakan kode di bawah ini atau klik tombol untuk melanjutkan.
                            </p>

                            <div style="margin: 30px 0; background-color: rgba(30, 64, 175, 0.2); border: 2px dashed #1e40af; border-radius: 20px; padding: 25px;">
                                <span style="color: #f59e0b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; display: block; margin-bottom: 10px;">Kode Keamanan</span>
                                <div style="font-size: 36px; font-weight: 700; color: #ffffff; letter-spacing: 8px; margin-bottom: 10px;">
                                    {{ $token }}
                                </div>
                                <div style="display: inline-block; padding: 4px 12px; background-color: rgba(245, 158, 11, 0.1); border-radius: 8px;">
                                    <span style="color: #f59e0b; font-size: 11px; font-weight: 600;">
                                        Valid sampai: {{ \Carbon\Carbon::parse($valid_until)->format('H:i') }} WIB
                                    </span>
                                </div>
                            </div>

                            <a href="{{ url('/reset-password/'.$uid.'?token='.$token) }}" style="background-color: #1e40af; color: #ffffff; padding: 18px 35px; border-radius: 15px; text-decoration: none; font-weight: 600; display: inline-block; font-size: 15px; box-shadow: 0 10px 20px rgba(30, 64, 175, 0.3);">
                                Reset Password Sekarang
                            </a>

                            <p style="color: #64748b; font-size: 13px; margin-top: 30px;">
                                Jika Anda tidak meminta ini, abaikan saja email ini. Keamanan akun Anda adalah prioritas kami.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 25px; border-top: 1px solid rgba(255, 255, 255, 0.05); background-color: rgba(0, 0, 0, 0.15);">
                            <p style="color: #475569; font-size: 11px; margin: 0;">
                                Pesan ini dikirim secara otomatis. Jangan membalas email ini.<br>
                                &copy; {{ date('Y') }} Khafid Swimming Club
                            </p>
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 20px; text-align: center;">
                    <a href="#" style="color: #334155; font-size: 12px; text-decoration: none;">Pusat Bantuan</a>
                    <span style="color: #334155; margin: 0 10px;">•</span>
                    <a href="#" style="color: #334155; font-size: 12px; text-decoration: none;">Keamanan Akun</a>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
