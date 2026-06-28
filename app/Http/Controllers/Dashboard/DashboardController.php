<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\FinanceAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Pengeluaran Saya (Self)
        $total_self = Payment::whereHas('registration', function ($q) use ($user) {
            $q->where('user_uid', $user->uid);
        })->where('status', 'paid')->sum('amount');

        // 2. Pendapatan Lomba Terbanyak (Berdasarkan satu kategori lomba yang paling tinggi pendapatannya)
        $total_lomba = Payment::where('payments.status', 'paid')
            ->join('registrations', 'payments.registration_uid', '=', 'registrations.uid')
            ->selectRaw('SUM(payments.amount) as total_income')
            ->groupBy('registrations.event_category_uid')
            ->orderByDesc('total_income')
            ->first()?->total_income ?? 0;

        // 3. Pendapatan Event Terbesar (Berdasarkan satu event yang paling tinggi pendapatannya)
        $total_event = Payment::where('payments.status', 'paid')
            ->join('registrations', 'payments.registration_uid', '=', 'registrations.uid')
            ->join('event_categories', 'registrations.event_category_uid', '=', 'event_categories.uid')
            ->selectRaw('SUM(payments.amount) as total_income')
            ->groupBy('event_categories.event_uid')
            ->orderByDesc('total_income')
            ->first()?->total_income ?? 0;

        // 4. Total Seluruh Pendapatan (Akumulasi semua pembayaran lunas)
        $total_all = Payment::where('status', 'paid')->sum('amount');

        // 5. Riwayat Lomba (Hanya yang sudah dikonfirmasi)
        $history_self = \App\Models\Registration::where('user_uid', $user->uid)->where('status', 'confirmed')->count();
        $history_all = \App\Models\Registration::where('status', 'confirmed')->count();

        // 6. Pesan/Notifikasi (Belum dibaca)
        $message_self = \App\Models\Notification::where('user_uid', $user->uid)->where('is_read', false)->count();
        $message_all = \App\Models\Notification::where('is_read', false)->count();

        // 7. Manajemen Akses & User
        $total_users = \App\Models\User::count();
        $total_roles = \Spatie\Permission\Models\Role::count();
        $total_permissions = \Spatie\Permission\Models\Permission::count();

        // Get all roles with their user counts dynamically
        $roles_data = \Spatie\Permission\Models\Role::withCount('users')->get();

        $data = [
            'title' => 'Dashboard ' . ucfirst($user->username) . ' | Khafid Swimming Club (KSC) - Official Website',
            'total_self' => $total_self,
            'total_lomba' => $total_lomba,
            'total_event' => $total_event,
            'total_all' => $total_all,
            'history_self' => $history_self,
            'history_all' => $history_all,
            'message_self' => $message_self,
            'message_all' => $message_all,
            'total_users' => $total_users,
            'total_roles' => $total_roles,
            'total_permissions' => $total_permissions,
            'roles_data' => $roles_data,
        ];

        return view('dashboard.index', $data);
    }
}
