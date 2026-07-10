/**
 * previewImage.js
 * AlpineJS Image Upload Preview Logic
 */

// Logika untuk Single Upload (Bisa Dipakai Berulang Kali di Blade)
function singleUpload(initialUrl = '') {
    return {
        imageUrl: initialUrl,
        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                // Maksimal 5MB
                if (file.size > 5242880) {
                    alert("Ukuran file terlalu besar! Maksimal 5MB.");
                    this.clearImage();
                    return;
                }
                
                // Jika sudah ada preview sebelumnya (baru dipilih), hapus dari memori
                if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
                    URL.revokeObjectURL(this.imageUrl);
                }
                
                this.imageUrl = URL.createObjectURL(file);
            }
        },
        clearImage() {
            this.imageUrl = '';
            // Reset semua input file di dalam scope komponen ini (mendukung 4 tombol input sekaligus)
            if (this.$el) {
                const inputs = this.$el.querySelectorAll('input[type="file"]');
                inputs.forEach(input => input.value = '');
            }
        }
    }
}

// Logika untuk Multi Upload (Gallery)
function multiUpload(initialUrls = []) {
    return {
        images: initialUrls,
        previewImages(event) {
            // Karena wire:model akan mengganti keseluruhan file array setiap kali input berubah,
            // kita harus membersihkan preview lama agar sinkron dengan state Livewire.
            this.clearAllImages();
            
            const files = Array.from(event.target.files);
            files.forEach(file => {
                if (file.size > 5242880) {
                    alert(`File "${file.name}" terlalu besar! Maksimal 5MB.`);
                    return;
                }
                const url = URL.createObjectURL(file);
                this.images.push(url);
            });
            // Reset input agar file yang sama bisa diupload ulang jika sudah dihapus
            event.target.value = ''; 
        },
        removeImage(index) {
            // Hapus URL object untuk membebaskan memori
            if (this.images[index].startsWith('blob:')) {
                URL.revokeObjectURL(this.images[index]);
            }
            this.images.splice(index, 1);
        },
        clearAllImages() {
            this.images.forEach(img => {
                if (img.startsWith('blob:')) {
                    URL.revokeObjectURL(img);
                }
            });
            this.images = [];
        }
    }
}
