#!/bin/bash

# Mendapatkan lokasi script
BASE_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo "=================================================="
echo "      LARAVEL STORAGE CLEANER (TOTAL RESET)"
echo "=================================================="
echo ""

echo "[1/7] Membersihkan Cache Framework..."
php "$BASE_DIR/artisan" optimize:clear

echo ""
echo "[2/7] Menghapus File Log..."
rm -f "$BASE_DIR/storage/logs/"*.log

echo ""
echo "[3/7] Menghapus File Temporary di storage/app..."
rm -f "$BASE_DIR/storage/app/"*.jpg
rm -f "$BASE_DIR/storage/app/"*.png
rm -f "$BASE_DIR/storage/app/"*.json
rm -f "$BASE_DIR/storage/app/"*.docx
rm -f "$BASE_DIR/storage/app/"*.pdf
rm -f "$BASE_DIR/storage/app/"*.pkt

# Hapus folder sampah di storage/app/ KECUALI yang penting
PROTECTED="uploads livewire-tmp private public"
for dir in "$BASE_DIR/storage/app/"*/; do
    dirname=$(basename "$dir")
    if ! echo "$PROTECTED" | grep -qw "$dirname"; then
        echo "  Hapus folder sampah: $dirname"
        rm -rf "$dir"
    fi
done

echo ""
echo "[4/7] Menghapus Sampah Livewire..."
rm -rf "$BASE_DIR/storage/app/livewire-tmp/"*
rm -rf "$BASE_DIR/storage/app/private/livewire-tmp/"*

echo ""
echo "[5/7] Membersihkan isi storage/app/private (kecuali .gitignore & livewire-tmp)..."
if [ -d "$BASE_DIR/storage/app/private" ]; then
    find "$BASE_DIR/storage/app/private" -mindepth 1 -maxdepth 1 ! -name '.gitignore' ! -name 'livewire-tmp' -exec rm -rf {} +
fi

echo ""
echo "[6/7] Membersihkan isi storage/app/public (HATI-HATI!)..."
if [ -d "$BASE_DIR/storage/app/public" ]; then
    find "$BASE_DIR/storage/app/public" -mindepth 1 ! -name '.gitignore' -delete
fi

echo ""
echo "[7/7] Membersihkan Session Lama..."
rm -f "$BASE_DIR/storage/framework/sessions/"*

echo ""
echo "=================================================="
echo "  [!] Folder AMAN (tidak dihapus):"
echo "      - storage/app/uploads/ (KTP, Akta, KK)"
echo "=================================================="
echo "      PEMBERSIHAN SELESAI! STORAGE RAMPING."
echo "=================================================="
