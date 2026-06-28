<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class WebartisanAuth
{
    /**
     * Handle an incoming request.
     *
     * Protects Webartisan routes with a password key.
     * Uses encrypted cookies instead of sessions so it works
     * even when the database is empty (no sessions table).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $passwordHash = config('webartisan.password');
        $prefix = config('webartisan.route_prefix', 'webartisan');

        // 1. Handle Logout/Reset Command
        if ($request->isMethod('POST') && $request->has('command')) {
            $cmd = trim(strtolower($request->input('command')));
            if (in_array($cmd, ['reset', 'exit', 'quit', 'logout'])) {
                return response()->json(['output' => 'AKSES DIKUNCI. Sesi telah dihapus.'])
                                 ->withoutCookie('wa_tokens');
            }
        }

        // 2. Proteksi Dasar
        if (empty($passwordHash)) {
            abort(403, 'Webartisan SECURITY ERROR: Password belum dikonfigurasi.');
        }

        // 3. Ambil Token dari Cookie (Bukan Session)
        $currentToken = $request->query('token') ?? $request->input('token');
        $activeTokensJson = $request->cookie('wa_tokens');
        $activeTokens = $activeTokensJson ? json_decode($activeTokensJson, true) : [];

        // Buat Fingerprint (IP + Browser)
        $fingerprint = md5($request->ip() . $request->userAgent() . config('app.key'));

        // Logika Validasi:
        $isValid = false;

        // A. Jika ada token spesifik di URL/Input, validasi itu dulu
        if ($currentToken && isset($activeTokens[$currentToken]) && is_array($activeTokens[$currentToken])) {
            $tokenData = $activeTokens[$currentToken];
            if (isset($tokenData['fp']) && $tokenData['fp'] === $fingerprint && (time() - $tokenData['time'] < 3600)) {
                $isValid = true;
            }
        } 
        // B. Jika ini adalah POST (perintah terminal) dan tidak ada token di body, 
        //    cek apakah ada token apapun di cookie yang cocok dengan fingerprint ini
        elseif ($request->isMethod('POST')) {
            foreach ($activeTokens as $t => $data) {
                if (is_array($data) && isset($data['fp']) && $data['fp'] === $fingerprint && (time() - $data['time'] < 3600)) {
                    $isValid = true;
                    break;
                }
            }
        }

        if ($isValid) {
            return $next($request);
        }

        // 4. Proses Login (Jika ada input password)
        if ($request->has('key')) {
            if (Hash::check($request->query('key'), $passwordHash)) {
                $newToken = bin2hex(random_bytes(16));
                $activeTokens[$newToken] = [
                    'time' => time(),
                    'fp' => $fingerprint
                ];
                
                // Simpan token ke dalam Cookie (berlaku 60 menit)
                $cookie = cookie('wa_tokens', json_encode($activeTokens), 60);
                return redirect()->to(url($prefix . '?token=' . $newToken))->withCookie($cookie);
            } else {
                return $this->showLoginForm($request, 'Password Salah!');
            }
        }

        // 5. Tampilkan Form Login jika semua gagal
        return $this->showLoginForm($request, $request->query('token') ? 'Sesi berakhir.' : null);
    }

    protected function showLoginForm(Request $request, $errorMessage = null): Response
    {
        $prefix = config('webartisan.route_prefix', 'webartisan');
        $error = $errorMessage ? '<div class="error">'. $errorMessage .'</div>' : '';
        $token = $request->query('token') ?? $request->input('token');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webartisan - Akses Terkunci</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f0f1a;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #e0e0e0;
        }
        .container {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }
        .icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 24px;
        }
        h1 {
            text-align: center;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #fff;
        }
        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #888;
            margin-bottom: 32px;
        }
        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #aaa;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input[type="password"]:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
        }
        button:hover { opacity: 0.9; }
        button:active { transform: scale(0.98); }
        .footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <h1>Webartisan</h1>
        <p class="subtitle">Masukkan password untuk mengakses terminal</p>
        {$error}
        <form method="GET" action="/{$prefix}">
            <label for="password">Password</label>
            <input type="password" id="password" name="key" placeholder="••••••••" autofocus required>
            <button type="submit">Buka Akses</button>
        </form>
        <p class="footer">Akses dilindungi • Webartisan</p>
    </div>
</body>
</html>
HTML;

        return response($html, 401);
    }
}
