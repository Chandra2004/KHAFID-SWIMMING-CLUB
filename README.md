# 🏊‍♂️ Sistem Manajemen Kolam Renang (Web Management System)

Aplikasi Web Manajemen Kolam Renang modern yang dibangun menggunakan TALL Stack (Tailwind CSS, Alpine.js/Livewire, Laravel). Sistem ini dirancang untuk mempermudah pengelolaan operasional kolam renang, mulai dari informasi publik (homepage), manajemen tiket, laporan keuangan, hingga hak akses pegawai.

## 🚀 Fitur Utama

- **Tiga Tampilan Terpisah (Multi-Layout System)**:
  - 🏠 **Homepage / Landing Page**: Halaman informatif untuk publik mengenai fasilitas, harga tiket, dan jadwal operasional kolam renang.
  - 🔐 **Authentication**: Sistem login dan registrasi yang aman.
  - 📊 **Dashboard Manajemen**: Panel admin interaktif untuk mengelola data operasional (pengunjung, tiket, dan laporan).
- **Manajemen Hak Akses (Role & Permission)**: Menggunakan `spatie/laravel-permission` untuk membagi akses antara Admin, Kasir, atau Manajemen.
- **Laporan Keuangan & Operasional**:
  - 📄 **Export to PDF**: Fitur cetak tiket dan laporan ke format PDF (didukung oleh `barryvdh/laravel-dompdf`).
  - 📈 **Export to Excel**: Rekapitulasi data pengunjung dan keuangan ke spreadsheet (didukung oleh `maatwebsite/excel`).
- **Antarmuka Dinamis & Cepat**: Menggunakan **Livewire 4** & **Volt** untuk interaksi *Single Page Application (SPA)* tanpa *reload* halaman, dipadukan dengan desain responsif dari **Tailwind CSS v4**.

## 🛠️ Teknologi yang Digunakan (Tech Stack)

- **Backend**: Laravel 12 (PHP ^8.2)
- **Frontend / UI**: Livewire v4, Livewire Volt, Tailwind CSS v4, Blade Lucide Icons
- **Database**: MySQL / SQLite / PostgreSQL (Tergantung konfigurasi `.env`)
- **Asset Bundler**: Vite

## 📂 Struktur Layout

Sistem UI aplikasi ini dipecah ke dalam beberapa layout khusus agar rapi dan mudah di-maintain:
- `layout-homepage`: Khusus untuk halaman depan/publik (termasuk navbar dan footer publik).
- `layout-auth`: Khusus untuk halaman Login, Register, Lupa Password.
- `layout-dashboard`: Khusus untuk panel admin (termasuk sidebar/navbar admin).
- `layout-partials`: Komponen UI yang dapat digunakan berulang (seperti *notification*, konfigurasi *meta tags* SEO, dan halaman *coming soon*).

## 💻 Instalasi (Cara Menjalankan di Lokal)

1. **Clone repositori ini**
   ```bash
   git clone https://github.com/username-anda/web-management-kolam-renang.git
   cd web-management-kolam-renang
   ```

2. **Jalankan script setup otomatis**
   Proyek ini sudah dilengkapi dengan *script* instalasi instan di dalam `composer.json`. Jalankan perintah berikut:
   ```bash
   composer run setup
   ```
   *(Script ini akan menginstall vendor PHP, meng-copy file `.env`, membuat application key, menjalankan migrasi database, dan mem-build asset frontend secara otomatis).*

3. **Jalankan Development Server**
   ```bash
   composer run dev
   ```
   Aplikasi sekarang dapat diakses melalui `http://localhost:8000`
