<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\AccessManagementController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\MasterController;
use App\Http\Controllers\Dashboard\ReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

//HOMEPAGE
Route::get('/', [\App\Http\Controllers\HomepageController::class, 'homepage'])->name('homepage');
Route::get('/events', [\App\Http\Controllers\HomepageController::class, 'events'])->name('events');
Route::get('/events/page/{page}', [\App\Http\Controllers\HomepageController::class, 'events']);
Route::get('/events/search/{keyword}', [\App\Http\Controllers\HomepageController::class, 'events']);
Route::get('/events/search/{keyword}/page/{page}', [\App\Http\Controllers\HomepageController::class, 'events']);
Route::get('/detail-event/{slug}/{uid}', [\App\Http\Controllers\HomepageController::class, 'eventDetail'])->name('event-detail');
Route::get('/galleries', [\App\Http\Controllers\HomepageController::class, 'gallery'])->name('gallery');
Route::get('/facilities', [\App\Http\Controllers\HomepageController::class, 'facilities'])->name('facilities');
Route::get('/coaches', [\App\Http\Controllers\HomepageController::class, 'coaches'])->name('coaches');
Route::get('/contact', [\App\Http\Controllers\HomepageController::class, 'contact'])->name('contact');
Route::get('/about-us', [\App\Http\Controllers\HomepageController::class, 'aboutUs'])->name('about-us');
Route::post('/contact/process', [\App\Http\Controllers\HomepageController::class, 'contactProcess'])->name('contact.process');
Route::get('/coming-soon', function () {
    return view('layouts.layout-partials.coming-soon', [
        'title' => 'Coming Soon | Khafid Swimming Club'
    ]);
})->name('coming-soon');

// AUTH
Route::middleware('guest')->group(function() {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login/process', [AuthController::class, 'loginProcess']);
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register/process', [AuthController::class, 'registerProcess']);

    // Password Reset
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password/process', [AuthController::class, 'forgotPasswordProcess'])->name('password.email');
    Route::get('/reset-password/{uid}', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password/{uid}/process', [AuthController::class, 'resetPasswordProcess'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('can:dashboard.view');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Secure Document View
    Route::get('/document/view/{type}/{filename}', [\App\Http\Controllers\Dashboard\SecureDocumentController::class, 'show'])->name('document.view');

    // Access Management
    Route::prefix('dashboard')->group(function () {
        // User Management
        Route::get('/management-user', function () {
            return view('dashboard.access.user');
        })->name('management-user.index')->middleware('can:users.view');

        // Role Management
        Route::get('/management-role', [AccessManagementController::class, 'role'])->name('master.role')->middleware('can:roles.view');

        // Permission Management
        Route::get('/management-permission', [AccessManagementController::class, 'permission'])->name('master.permission')->middleware('can:permissions.view');

        // Club Management
        Route::get('/management-club', function () {
            return view('dashboard.access.club');
        })->name('management-club.index')->middleware('can:clubs.view');

        // Profile Management
        Route::get('/notifications', function () {
            return view('dashboard.account.notifications');
        })->name('notifications.index');

        Route::get('/my-profile', [ProfileController::class, 'myProfile'])->name('dashboard.my-profile');
        Route::get('/my-profile/edit/process', [ProfileController::class, 'myProfileEditProcess'])->name('dashboard.my-profile.edit');
        Route::get('/result-event', [MasterController::class, 'resultEvent'])->name('dashboard.result-event');
        Route::get('/result-event/{event_uid}', [MasterController::class, 'resultEventDetail'])->name('dashboard.result-event.detail');
        Route::get('/history-pendaftaran', [MasterController::class, 'historyPendaftaran'])->name('dashboard.history-pendaftaran')->middleware('permission:master-history-pendaftaran.view|master-history-pendaftaran.view.self|master-history-pendaftaran.view.all');
        Route::get('/pendaftaran/print-bukti/{uid}', [ReportController::class, 'printBukti'])->name('dashboard.pendaftaran.print-bukti');

        // Master Management
        Route::prefix('master')->group(function () {
            Route::get('/style', [MasterController::class, 'style'])->name('master.style')->middleware('can:master-gaya.view');
            Route::get('/finance', [MasterController::class, 'finance'])->name('master.finance')->middleware('can:master-keuangan.view');
            Route::get('/gallery', [MasterController::class, 'gallery'])->name('master.gallery')->middleware('can:master-galeri.view');
            Route::get('/parameter', [MasterController::class, 'parameter'])->name('master.parameter')->middleware('can:master-parameter.view');
            Route::get('/event', [MasterController::class, 'event'])->name('master.event')->middleware('can:master-event.view');
            Route::get('/lomba', [MasterController::class, 'lombaIndex'])->name('master.lomba')->middleware('can:master-lomba.view');
            Route::get('/event/{uid}/lomba', [MasterController::class, 'lomba'])->name('master.event.lomba')->middleware('can:master-event.view');
            Route::get('/pendaftaran', [MasterController::class, 'pendaftaran'])->name('master.pendaftaran');
        });

        // Setting Management
        Route::prefix('setting')->group(function () {
            Route::get('/document', [\App\Http\Controllers\Dashboard\SettingController::class, 'document'])->name('setting.document')->middleware('can:setting-document.view');
        });

        // Report Management
        Route::prefix('report')->middleware('can:report-data.view')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('report.index');
            Route::get('/export/buku-acara/{event_uid}', [ReportController::class, 'exportBukuAcara'])->name('report.export.buku-acara');
            Route::get('/export/buku-hasil/{event_uid}', [ReportController::class, 'exportBukuHasil'])->name('report.export.buku-hasil');
            Route::get('/export/pendaftaran', [ReportController::class, 'exportPendaftaran'])->name('report.export.pendaftaran');
            Route::get('/export/process', [ReportController::class, 'exportProcess'])->name('report.export.process');
        });
    });
});
Route::get('invoice/{invoice}/{invoice_number?}', [InvoiceController::class, 'download'])->name('invoice.download');
