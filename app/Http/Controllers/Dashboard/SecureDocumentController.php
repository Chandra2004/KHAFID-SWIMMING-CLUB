<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class SecureDocumentController extends Controller
{
    /**
     * Menampilkan dokumen sensitif dengan pengecekan permission.
     */
    public function show($type, $filename)
    {
        // 1. Cek Permission (Hanya yang punya izin 'documents.view-sensitive' yang boleh lewat)
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->can('documents.view-sensitive')) {
            abort(403, 'Anda tidak memiliki izin untuk melihat dokumen ini.');
        }

        // 2. Tentukan Folder berdasarkan Type
        $allowedTypes = [
            'ktp' => 'uploads/ktp_documents/',
            'akta' => 'uploads/akta_documents/',
            'kk' => 'uploads/kk_documents/',
        ];

        if (!array_key_exists($type, $allowedTypes)) {
            abort(404, 'Tipe dokumen tidak valid.');
        }

        // 3. Baca file dari folder STORAGE (bukan public - agar tidak bisa diakses via URL)
        $path = storage_path('app/' . $allowedTypes[$type] . $filename);

        // 3. Cek apakah file ada
        if (!File::exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // 4. Kirim file sebagai response (tanpa header download agar langsung tampil di browser)
        $file = File::get($path);
        $mimeType = File::mimeType($path);

        return Response::make($file, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }
}
