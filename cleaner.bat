@echo off
setlocal
set "BASE_DIR=%~dp0"

echo ==================================================
echo       LARAVEL STORAGE CLEANER (TOTAL RESET)
echo ==================================================
echo.

echo [1/7] Membersihkan Cache Framework...
php "%BASE_DIR%artisan" optimize:clear

echo.
echo [2/7] Menghapus File Log...
del /q /f "%BASE_DIR%storage\logs\*.log" >nul 2>&1

echo.
echo [3/7] Menghapus File Temporary di storage/app...
del /q /f "%BASE_DIR%storage\app\*.jpg" >nul 2>&1
del /q /f "%BASE_DIR%storage\app\*.png" >nul 2>&1
del /q /f "%BASE_DIR%storage\app\*.json" >nul 2>&1
del /q /f "%BASE_DIR%storage\app\*.docx" >nul 2>&1
del /q /f "%BASE_DIR%storage\app\*.pdf" >nul 2>&1
del /q /f "%BASE_DIR%storage\app\*.pkt" >nul 2>&1

REM Hapus folder sampah di storage/app/ KECUALI yang penting
for /d %%d in ("%BASE_DIR%storage\app\*") do (
    if /i not "%%~nxd"=="uploads" (
    if /i not "%%~nxd"=="livewire-tmp" (
    if /i not "%%~nxd"=="private" (
    if /i not "%%~nxd"=="public" (
        echo   Hapus folder sampah: %%~nxd
        rd /s /q "%%d" >nul 2>&1
    ))))
)

echo.
echo [4/7] Menghapus Sampah Livewire...
if exist "%BASE_DIR%storage\app\livewire-tmp" (
    del /q /s /f "%BASE_DIR%storage\app\livewire-tmp\*.*" >nul 2>&1
    for /d %%x in ("%BASE_DIR%storage\app\livewire-tmp\*") do rd /s /q "%%x" >nul 2>&1
)
if exist "%BASE_DIR%storage\app\private\livewire-tmp" (
    del /q /s /f "%BASE_DIR%storage\app\private\livewire-tmp\*.*" >nul 2>&1
    for /d %%x in ("%BASE_DIR%storage\app\private\livewire-tmp\*") do rd /s /q "%%x" >nul 2>&1
)

echo.
echo [5/7] Membersihkan isi storage/app/private (kecuali .gitignore dan livewire-tmp)...
if exist "%BASE_DIR%storage\app\private" (
    pushd "%BASE_DIR%storage\app\private"
    for /f "delims=" %%i in ('dir /b /a-d 2^>nul ^| findstr /v /i ".gitignore"') do del /f /q "%%i" >nul 2>&1
    for /d %%i in (*) do (
        if /i not "%%i"=="livewire-tmp" rd /s /q "%%i" >nul 2>&1
    )
    popd
)

echo.
echo [6/7] Membersihkan isi storage/app/public (HATI-HATI: Foto Galeri akan terhapus!)...
if exist "%BASE_DIR%storage\app\public" (
    pushd "%BASE_DIR%storage\app\public"
    for /f "delims=" %%i in ('dir /b /a-d 2^>nul ^| findstr /v /i ".gitignore"') do del /f /q "%%i" >nul 2>&1
    for /d %%i in (*) do rd /s /q "%%i" >nul 2>&1
    popd
)

echo.
echo [7/7] Membersihkan Session Lama...
del /q /f "%BASE_DIR%storage\framework\sessions\*.*" >nul 2>&1

echo.
echo ==================================================
echo   [!] Folder AMAN (tidak dihapus):
echo       - storage\app\uploads\ (KTP, Akta, KK)
echo ==================================================
echo       PEMBERSIHAN SELESAI! STORAGE RAMPING.
echo ==================================================
pause
