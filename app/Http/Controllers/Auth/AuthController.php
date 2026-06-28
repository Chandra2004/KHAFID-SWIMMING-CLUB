<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\ResetToken;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function forgotPassword()
    {
        return view('auth.forgot-password', [
            'title' => 'Lupa Kata Sandi - Khafid Swimming Club'
        ]);
    }

    public function forgotPasswordProcess(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        // Check for recent token (within 1 minute) to prevent spam
        $recentToken = ResetToken::where('user_uid', $user->uid)
            ->where('created_at', '>', Carbon::now()->subMinute())
            ->first();

        if ($recentToken) {
            $secondsRemaining = ceil(60 - Carbon::now()->diffInSeconds($recentToken->created_at));
            
            // Pastikan tidak negatif jika ada selisih milidetik
            $secondsRemaining = $secondsRemaining < 0 ? 0 : $secondsRemaining;

            return back()->with('notification', [
                'status' => 'warning',
                'message' => "Token sudah terkirim. Mohon tunggu {$secondsRemaining} detik untuk mencoba lagi.",
                'duration' => 5000
            ]);
        }

        $token = Str::upper(Str::random(8)); // 8 characters code
        $validUntil = Carbon::now()->addMinutes(5);

        // Delete old tokens (that are older than 1 minute)
        ResetToken::where('user_uid', $user->uid)->delete();

        // Create new token
        ResetToken::create([
            'uid' => Str::uuid(),
            'user_uid' => $user->uid,
            'email' => $user->email,
            'token' => $token,
            'valid_until' => $validUntil
        ]);

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($token, $user->uid, $validUntil));

            return back()->with('notification', [
                'status' => 'success',
                'message' => 'Instruksi reset password telah dikirim ke email Anda.',
                'duration' => 5000
            ]);
        } catch (\Exception $e) {
            return back()->with('notification', [
                'status' => 'error',
                'message' => 'Gagal mengirim email. Silakan coba lagi nanti.',
                'duration' => 5000
            ]);
        }
    }

    public function resetPassword($uid)
    {
        $user = User::where('uid', $uid)->firstOrFail();
        $token = request()->get('token');

        return view('auth.reset-password', [
            'title' => 'Atur Ulang Kata Sandi - Khafid Swimming Club',
            'data' => [
                'uid' => $uid,
                'email' => $user->email,
                'token' => $token
            ]
        ]);
    }

    public function resetPasswordProcess(Request $request, $uid)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8',
            'password_confirm' => 'required|same:password'
        ]);

        $resetToken = ResetToken::where('user_uid', $uid)
            ->where('token', $request->token)
            ->where('valid_until', '>', Carbon::now())
            ->first();

        if (!$resetToken) {
            return back()->with('notification', [
                'status' => 'error',
                'message' => 'Token tidak valid atau sudah kadaluarsa.',
                'duration' => 5000
            ]);
        }

        $user = User::where('uid', $uid)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        $resetToken->delete();

        return redirect('/login')->with('notification', [
            'status' => 'success',
            'message' => 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru Anda.',
            'duration' => 5000
        ]);
    }

    public function login()
    {
        $data = [
            'title' => 'Khafid Swimming Club (KSC) - Official Website | Masuk',
        ];

        return view('auth.login', $data);
    }

    public function loginProcess(LoginRequest $request)
    {
        $loginId = $request->input('email');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $fieldType = filter_var($loginId, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Cari user terlebih dahulu untuk cek status aktif
        $user = User::where($fieldType, $loginId)->first();

        if ($user && !$user->is_active) {
            return back()->with('notification', [
                'status' => 'error',
                'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi administrator.',
                'duration' => 5000
            ])->onlyInput('email');
        }

        if (Auth::attempt([$fieldType => $loginId, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            return redirect('/dashboard')->with('notification', [
                'status' => 'success',
                'message' => 'Selamat datang ' . Auth::user()->username . ' !',
                'duration' => 3000
            ]);
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    public function register()
    {
        $data = [
            'title' => 'Khafid Swimming Club (KSC) - Official Website | Daftar',
        ];

        return view('auth.register', $data);
    }

    public function registerProcess(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->assignRole('atlet');

        return redirect('/login')->with('notification', [
            'status' => 'success',
            'message' => 'Pendaftaran berhasil sebagai Atlet! Silakan masuk.',
            'duration' => 5000
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('notification', [
            'status' => 'success',
            'message' => 'Anda telah berhasil keluar.',
            'duration' => 3000
        ]);
    }
}
