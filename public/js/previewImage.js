 /**
 * previewImage.js
 * Script untuk menangani preview gambar secara client-side menggunakan Vanilla JS (FileReader).
 * Lebih ringan dibandingkan menggunakan temporaryUrl() dari Livewire.
 */

/**
 * Preview Single Image
 * @param {HTMLInputElement} inputElement - Elemen <input type="file">
 * @param {string} previewImageId - ID dari tag <img> yang akan menampilkan gambar
 * @param {string} placeholderId - (Opsional) ID dari elemen placeholder (misal icon SVG/teks) yang disembunyikan saat gambar muncul
 */
function previewSingleImage(inputElement, previewImageId, placeholderId = null) {
    const previewImg = document.getElementById(previewImageId);
    let placeholder = null;

    if (placeholderId) {
        placeholder = document.getElementById(placeholderId);
    }

    if (inputElement.files && inputElement.files[0]) {
        const file = inputElement.files[0];

        // Validasi ukuran file (Opsional, misal maks 5MB)
        if (file.size > 5242880) {
            alert('Ukuran file terlalu besar! Maksimal 5MB.');
            inputElement.value = ''; // Reset input
            return;
        }

        const reader = new FileReader();

        reader.onload = function(e) {
            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
                previewImg.style.display = 'block'; // Fallback jika tidak pakai class hidden tailwind

                if (placeholder) {
                    placeholder.classList.add('hidden');
                    placeholder.style.display = 'none';
                }
            }
        }

        reader.readAsDataURL(file);
    } else {
        // Jika dialog file di-cancel (kosong)
        if (previewImg) {
            previewImg.src = '';
            previewImg.classList.add('hidden');
            previewImg.style.display = 'none';

            if (placeholder) {
                placeholder.classList.remove('hidden');
                placeholder.style.display = '';
            }
        }
    }
}

/**
 * Preview Multiple Images
 * @param {HTMLInputElement} inputElement - Elemen <input type="file" multiple>
 * @param {string} previewContainerId - ID dari div/container yang akan menampung banyak gambar
 */
function previewMultipleImages(inputElement, previewContainerId) {
    const container = document.getElementById(previewContainerId);

    if (!container) return;

    // Kosongkan isi container sebelum merender ulang gambar yang dipilih
    container.innerHTML = '';

    if (inputElement.files && inputElement.files.length > 0) {
        container.classList.remove('hidden');

        Array.from(inputElement.files).forEach((file, index) => {
            // Validasi ukuran per file maks 5MB
            if (file.size > 5242880) {
                alert(`File "${file.name}" terlalu besar! Maksimal 5MB.`);
                return; // Lanjut ke iterasi selanjutnya
            }

            const reader = new FileReader();

            reader.onload = function(e) {
                // Buat elemen div wrapper untuk setiap gambar (styling menggunakan Tailwind)
                const wrapper = document.createElement('div');
                wrapper.className = 'relative w-24 h-24 sm:w-32 sm:h-32 rounded-xl overflow-hidden border-2 border-slate-200 shadow-sm group';

                // Buat elemen img
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-full h-full object-cover transition-transform group-hover:scale-110';

                wrapper.appendChild(img);
                container.appendChild(wrapper);
            }

            reader.readAsDataURL(file);
        });
    } else {
        container.classList.add('hidden');
    }
}

/**
 * Read file as Base64 and pass it to a callback
 * @param {HTMLInputElement} inputElement
 * @param {Function} callback
 */
function readAndSetBase64(inputElement, callback) {
    if (inputElement.files && inputElement.files[0]) {
        const file = inputElement.files[0];
        // Maksimal 5MB (5242880 bytes)
        if (file.size > 5242880) {
            callback(null);
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            callback(e.target.result);
        };
        reader.readAsDataURL(file);
    } else {
        callback(null);
    }
}

/**
 * Read multiple files as Base64 array and pass them to a callback
 * @param {HTMLInputElement} inputElement
 * @param {Function} callback
 */
function readMultipleAndSetBase64(inputElement, callback) {
    if (inputElement.files && inputElement.files.length > 0) {
        let base64Array = [];
        let count = 0;
        const total = inputElement.files.length;
        Array.from(inputElement.files).forEach(file => {
            if (file.size > 5242880) {
                count++;
                if (count === total) callback(base64Array);
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                base64Array.push(e.target.result);
                count++;
                if (count === total) {
                    callback(base64Array);
                }
            };
            reader.readAsDataURL(file);
        });
    } else {
        callback([]);
    }
}
