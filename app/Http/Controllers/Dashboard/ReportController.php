<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Services\ReportExportService;
use App\Exports\RegistrationsExport;
use App\Exports\BukuAcaraExport;
use App\Exports\BukuHasilExport;
use App\Exports\PendaftaranKlubExport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Display the report center page.
     */
    public function index()
    {
        $user = auth()->user();
        $userRole = $user->getRoleNames()->first();
        $eventUid = request('event_uid', 'all');

        // Get all events for filter dropdown
        $events = Event::orderBy('start_date', 'desc')->get()->map(function ($event) {
            return [
                'uid'        => $event->uid,
                'nama_event' => $event->name,
            ];
        });

        // Preview data filtered by event if provided
        $previewQuery = Registration::with(['user.profile', 'eventCategory.event'])
            ->latest();

        if ($eventUid !== 'all') {
            $previewQuery->whereHas('eventCategory', function ($q) use ($eventUid) {
                $q->where('event_uid', $eventUid);
            });
        }

        $previewData = $previewQuery->take(10)
            ->get()
            ->map(function ($reg) {
                return [
                    'created_at'         => $reg->created_at,
                    'nama_event'         => $reg->eventCategory?->event?->name ?? '-',
                    'nama_lengkap'       => $reg->user?->profile?->full_name ?? $reg->user?->username ?? '-',
                    'nomor_pendaftaran'  => $reg->registration_number ?? '-',
                    'status_pendaftaran' => $reg->status ?? 'pending',
                ];
            });

        // Stats
        $totalAnggota = \App\Models\User::count();
        $totalEvent = Event::count();
        $totalLomba = \App\Models\EventCategory::count();
        
        $regStats = [
            'total' => Registration::count(),
            'pending' => Registration::where('status', 'pending')->count(),
            'confirmed' => Registration::where('status', 'confirmed')->count(),
            'cancelled' => Registration::where('status', 'cancelled')->count(),
            'rejected' => Registration::where('status', 'rejected')->count(),
        ];

        $totalRevenue = Registration::where('status', 'confirmed')
            ->whereHas('eventCategory')
            ->with('eventCategory')
            ->get()
            ->sum(function($reg) {
                return $reg->eventCategory->registration_fee ?? 0;
            });

        return view('dashboard.report.index', [
            'user' => [
                'nama_role' => $userRole,
            ],
            'events'      => $events,
            'previewData' => $previewData,
            'totalAnggota' => $totalAnggota,
            'totalEvent'  => $totalEvent,
            'totalLomba'  => $totalLomba,
            'regStats'    => $regStats,
            'totalRevenue' => $totalRevenue,
            'filters'       => request()->all(),
            'selectedEvent' => $eventUid,
        ]);
    }

    /**
     * Export Buku Acara PDF.
     */
    public function exportBukuAcara(string $eventUid)
    {
        $this->authorize('report-data.export');
        $format = request('format', 'pdf');

        if ($format === 'excel') {
            $eventName = $eventUid === 'all' ? 'SEMUA EVENT' : (Event::where('uid', $eventUid)->value('name') ?? 'EVENT');
            $filename = "BUKU ACARA_" . strtoupper($eventName) . "_" . date('d-m-Y') . ".xlsx";
            return Excel::download(new BukuAcaraExport($eventUid), $filename);
        }

        $pdf = ReportExportService::generateBukuAcara($eventUid);
        $eventName = $eventUid === 'all' ? 'SEMUA EVENT' : (Event::where('uid', $eventUid)->value('name') ?? 'EVENT');
        $filename = "BUKU ACARA_" . strtoupper($eventName) . "_" . date('d-m-Y') . ".pdf";

        return $pdf->stream($filename);
    }

    /**
     * Export Buku Hasil Lomba PDF.
     */
    public function exportBukuHasil(string $eventUid)
    {
        $this->authorize('report-data.export');
        $format = request('format', 'pdf');

        if ($format === 'excel') {
            $eventName = $eventUid === 'all' ? 'SEMUA EVENT' : (Event::where('uid', $eventUid)->value('name') ?? 'EVENT');
            $filename = "BUKU HASIL_" . strtoupper($eventName) . "_" . date('d-m-Y') . ".xlsx";
            return Excel::download(new BukuHasilExport($eventUid), $filename);
        }

        $pdf = ReportExportService::generateBukuHasil($eventUid);
        $eventName = $eventUid === 'all' ? 'SEMUA EVENT' : (Event::where('uid', $eventUid)->value('name') ?? 'EVENT');
        $filename = "BUKU HASIL_" . strtoupper($eventName) . "_" . date('d-m-Y') . ".pdf";

        return $pdf->stream($filename);
    }

    /**
     * Export Data Pendaftaran PDF.
     */
    public function exportPendaftaran(Request $request)
    {
        $this->authorize('report-data.export');

        $eventUid = $request->get('event_uid', 'all');
        $format = $request->get('format', 'pdf');

        if ($format === 'excel') {
            return $this->exportDataPendaftaranExcel($eventUid);
        }

        $pdf = ReportExportService::generateDataPendaftaran($eventUid !== 'all' ? $eventUid : null);

        $eventName = 'SEMUA EVENT';
        if ($eventUid && $eventUid !== 'all') {
            $event = Event::where('uid', $eventUid)->first();
            $eventName = $event ? $event->name : 'EVENT';
        }
        
        $filename = "DATA PENDAFTARAN_" . strtoupper($eventName) . "_" . date('d-m-Y') . ".pdf";

        return $pdf->stream($filename);
    }

    /**
     * Export Data Pendaftaran to Excel (.xlsx).
     */
    private function exportDataPendaftaranExcel($eventUid)
    {
        $eventName = 'SEMUA EVENT';
        if ($eventUid && $eventUid !== 'all') {
            $event = Event::where('uid', $eventUid)->first();
            $eventName = $event ? $event->name : 'EVENT';
        }

        $filename = "DATA PENDAFTARAN_" . strtoupper($eventName) . "_" . date('d-m-Y') . ".xlsx";
        
        return Excel::download(new RegistrationsExport($eventUid), $filename);
    }

    /**
     * Export Data Pendaftaran Klub (Invoice)
     */
    public function exportPendaftaranKlub(Request $request)
    {
        $this->authorize('report-data.export');

        $eventUid = $request->get('event_uid', 'all');
        $format = $request->get('format', 'pdf');
        
        $user = auth()->user();
        $clubUid = $user->profile?->club_uid;
        // Kita izinkan admin untuk export walaupun tanpa club (bisa ditarik semua club)
        $isAdmin = $user->hasRole(['superadmin', 'admin', 'admin_pendaftaran']);

        if (!$clubUid && !$isAdmin) {
            return back()->with('error', 'Laporan ini khusus untuk pendaftar yang tergabung dalam sebuah klub.');
        }

        if ($format === 'excel') {
            return $this->exportPendaftaranKlubExcel($eventUid, $clubUid);
        }

        $pdf = ReportExportService::generateDataPendaftaranKlub($eventUid !== 'all' ? $eventUid : null, $clubUid);

        $eventName = 'SEMUA EVENT';
        if ($eventUid && $eventUid !== 'all') {
            $event = Event::where('uid', $eventUid)->first();
            $eventName = $event ? $event->name : 'EVENT';
        }
        
        $filename = "DATA PENDAFTARAN KLUB_" . strtoupper($eventName) . "_" . date('d-m-Y') . ".pdf";

        return $pdf->stream($filename);
    }

    private function exportPendaftaranKlubExcel($eventUid, $clubUid)
    {
        $eventName = 'SEMUA EVENT';
        if ($eventUid && $eventUid !== 'all') {
            $event = Event::where('uid', $eventUid)->first();
            $eventName = $event ? $event->name : 'EVENT';
        }

        $filename = "DATA PENDAFTARAN KLUB_" . strtoupper($eventName) . "_" . date('d-m-Y') . ".xlsx";
        
        return Excel::download(new PendaftaranKlubExport($eventUid, $clubUid), $filename);
    }

    /**
     * Handle general export action from the report page.
     */
    public function exportProcess(Request $request)
    {
        $this->authorize('report-data.export');

        $type = $request->get('type', 'pendaftaran');
        $eventUid = $request->get('event_uid', 'all');

        switch ($type) {
            case 'buku_acara':
                return $this->exportBukuAcara($eventUid);

            case 'buku_hasil':
                return $this->exportBukuHasil($eventUid);

            case 'pendaftaran_klub':
                return $this->exportPendaftaranKlub($request);

            case 'pendaftaran':
            default:
                return $this->exportPendaftaran($request);
        }
    }

    /**
     * Print individual registration proof.
     */
    public function printBukti(string $uid)
    {
        $registration = Registration::where('uid', $uid)->firstOrFail();

        // Security check: User can only print their own or if they have permission
        if (auth()->id() !== $registration->user_uid && !auth()->user()->can('master-history-pendaftaran.detail')) {
            abort(403);
        }

        // Only confirmed registrations can be printed
        if ($registration->status !== 'confirmed') {
            return back()->with('error', 'Bukti pendaftaran hanya dapat diunduh jika status sudah terkonfirmasi.');
        }

        $pdf = ReportExportService::generateBuktiPendaftaran($uid);

        return $pdf->download('bukti-pendaftaran-' . $registration->registration_number . '.pdf');
    }
}
