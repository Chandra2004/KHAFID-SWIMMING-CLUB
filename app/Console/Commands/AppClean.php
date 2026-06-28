<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class AppClean extends Command
{
    protected $signature = 'app:clean {--force : Paksa hapus tanpa konfirmasi}';
    protected $description = 'Membersihkan semua file temporary, log, dan storage untuk menghemat ruang';

    public function handle()
    {
        $this->info('==================================================');
        $this->info('      LARAVEL STORAGE CLEANER (ARTISAN MODE)');
        $this->info('==================================================');

        // 1. Optimize Clear
        $this->comment('[1/6] Membersihkan Cache Framework...');
        Artisan::call('optimize:clear');
        $this->info('Cache dibersihkan.');

        // 2. Logs
        $this->comment('[2/6] Menghapus File Log...');
        $logs = File::glob(storage_path('logs/*.log'));
        foreach ($logs as $log) {
            File::delete($log);
        }
        $this->info(count($logs) . ' file log dihapus.');

        // 3. Temporary Files in storage/app (JANGAN hapus folder uploads!)
        $this->comment('[3/6] Menghapus File Temporary di storage/app...');
        $tempCount = 0;
        $extensions = ['jpg', 'png', 'json', 'docx', 'pdf', 'pkt'];
        foreach ($extensions as $ext) {
            $files = File::glob(storage_path('app/*.' . $ext));
            foreach ($files as $file) {
                File::delete($file);
                $tempCount++;
            }
        }

        // Hapus folder-folder sampah di storage/app/ KECUALI yang penting
        $protectedFolders = ['uploads', 'livewire-tmp', 'private', 'public'];
        $appDirs = File::directories(storage_path('app'));
        foreach ($appDirs as $dir) {
            $dirName = basename($dir);
            if (!in_array($dirName, $protectedFolders)) {
                File::deleteDirectory($dir);
                $tempCount++;
                $this->line("  Hapus folder sampah: {$dirName}");
            }
        }
        $this->info($tempCount . ' item temporary dibersihkan.');

        // 4. Livewire Trash
        $this->comment('[4/6] Menghapus Sampah Livewire...');
        if (File::isDirectory(storage_path('app/livewire-tmp'))) {
            File::cleanDirectory(storage_path('app/livewire-tmp'));
        }
        if (File::isDirectory(storage_path('app/private/livewire-tmp'))) {
            File::cleanDirectory(storage_path('app/private/livewire-tmp'));
        }
        $this->info('Sampah Livewire dibersihkan.');

        // 5. Public Storage (Optional Warning)
        $this->comment('[5/6] Membersihkan Public Storage...');
        if ($this->option('force') || $this->confirm('Apakah Anda yakin ingin menghapus SEMUA file di public storage (termasuk foto galeri)?')) {
            $this->cleanDirectoryExcludeGitignore(storage_path('app/public'));
            $this->info('Public storage dibersihkan.');
        } else {
            $this->warn('Pembersihan public storage dilewati.');
        }

        // 6. Sessions
        $this->comment('[6/6] Membersihkan Session...');
        File::cleanDirectory(storage_path('framework/sessions'));
        $this->info('Session dibersihkan.');

        $this->info('==================================================');
        $this->info('      PEMBERSIHAN SELESAI! STORAGE RAMPING.');
        $this->info('==================================================');
    }

    private function cleanDirectoryExcludeGitignore($path)
    {
        if (!File::isDirectory($path)) return;

        $files = File::allFiles($path);
        foreach ($files as $file) {
            if ($file->getFilename() !== '.gitignore') {
                File::delete($file->getRealPath());
            }
        }

        $directories = File::directories($path);
        foreach ($directories as $directory) {
            File::deleteDirectory($directory);
        }
    }
}
