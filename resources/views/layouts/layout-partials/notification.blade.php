<div id="toast-template" class="hidden">
    {{-- Pre-render Lucide Icons for JS use --}}
    <div id="icon-success-template"><x-lucide-check-circle class="w-6 h-6" /></div>
    <div id="icon-error-template"><x-lucide-x-circle class="w-6 h-6" /></div>
    <div id="icon-warning-template"><x-lucide-alert-triangle class="w-6 h-6" /></div>
    <div id="icon-info-template"><x-lucide-info class="w-6 h-6" /></div>

    <div class="toast-item flex items-center w-full max-w-sm p-4 mb-3 text-gray-700 bg-white border border-slate-200 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] transition-all duration-500 ease-[cubic-bezier(0.23,1,0.32,1)] transform scale-90 opacity-0 translate-y-2 select-none"
        role="alert" style="pointer-events: auto;">
        <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-xl">
            <div class="icon-container"></div>
        </div>
        <div class="ms-3 text-sm font-semibold flex-grow"></div>
        <button type="button" class="ms-4 -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-50 inline-flex items-center justify-center h-8 w-8 btn-close">
            <span class="sr-only">Close</span>
            <x-lucide-x class="w-4 h-4" />
        </button>
    </div>
</div>

<script>
    (function() {
        const showToast = (status, message, duration = 5000, invoiceUrl = null) => {
            let container = document.getElementById('tf-notifications');
            if (!container) {
                container = document.createElement('div');
                container.id = 'tf-notifications';
                container.className = "fixed top-6 right-6 z-[9999] flex flex-col items-end w-full max-w-xs sm:max-w-sm pointer-events-none";
                document.body.appendChild(container);
            }

            const styles = {
                'error':   { bg: 'bg-red-50', text: 'text-red-600', border: 'border-red-200', icon: document.getElementById('icon-error-template').innerHTML },
                'success': { bg: 'bg-emerald-50', text: 'text-emerald-600', border: 'border-emerald-200', icon: document.getElementById('icon-success-template').innerHTML },
                'warning': { bg: 'bg-amber-50', text: 'text-amber-600', border: 'border-amber-200', icon: document.getElementById('icon-warning-template').innerHTML },
                'info':    { bg: 'bg-blue-50', text: 'text-blue-600', border: 'border-blue-200', icon: document.getElementById('icon-info-template').innerHTML }
            }[status] || { bg: 'bg-blue-50', text: 'text-blue-600', border: 'border-blue-200', icon: '' };

            const template = document.querySelector('#toast-template .toast-item');
            const newToast = template.cloneNode(true);

            newToast.className = `toast-item flex items-center w-full max-w-sm p-4 mb-3 text-gray-700 bg-white border ${styles.border} rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] transition-all duration-500 ease-[cubic-bezier(0.23,1,0.32,1)] transform scale-90 opacity-0 translate-y-2 select-none`;
            newToast.querySelector('.flex-shrink-0').className = `flex-shrink-0 flex items-center justify-center w-10 h-10 ${styles.bg} ${styles.text} rounded-xl`;
            newToast.querySelector('.icon-container').innerHTML = styles.icon;
            newToast.querySelector('.ms-3').innerText = message;

            if (invoiceUrl) {
                const btnHtml = `<a href="${invoiceUrl}" target="_blank" class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg> UNDUH INVOICE</a>`;
                newToast.querySelector('.ms-3').insertAdjacentHTML('beforeend', '<br>' + btnHtml);
                if (duration === 5000) duration = 20000; // Extend duration if it is default
            }


            container.prepend(newToast);
            
            requestAnimationFrame(() => {
                setTimeout(() => {
                    newToast.classList.remove('scale-90', 'opacity-0', 'translate-y-2');
                    newToast.classList.add('scale-100', 'opacity-100', 'translate-y-0');
                }, 10);
            });

            const removeToast = () => {
                newToast.classList.add('scale-95', 'opacity-0', 'translate-x-10');
                newToast.style.marginBottom = `-${newToast.offsetHeight}px`;
                newToast.addEventListener('transitionend', () => newToast.remove(), { once: true });
            };

            let timer = setTimeout(removeToast, duration);
            newToast.querySelector('.btn-close').onclick = () => { clearTimeout(timer); removeToast(); };
            newToast.onmouseenter = () => clearTimeout(timer);
            newToast.onmouseleave = () => timer = setTimeout(removeToast, 2000);
        };

        // Jika ada Session PHP (Refresh Halaman)
        @if (session('notification'))
            @php $n = session('notification'); @endphp
            showToast("{{ $n['status'] ?? 'success' }}", "{{ $n['message'] }}");
        @endif

        // Jika ada Event Livewire (Tanpa Refresh)
        window.addEventListener('notification', event => {
            const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
            showToast(data.status, data.message, data.duration, data.invoice_url);
        });
    })();
</script>
