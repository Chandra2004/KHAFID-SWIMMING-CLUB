<footer class="bg-white border-t border-slate-200 pt-16 pb-8">
    <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-4 gap-12 mb-12">
            <div class="col-span-1 md:col-span-1">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ url('/assets/ico/icon-bar.png') }}" class="w-[80px]">
                </a>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Membangun generasi juara melalui olahraga renang yang disiplin, sportif, dan menyenangkan.
                </p>
                <div class="flex gap-4">
                    <a href="https://www.instagram.com/khafidswimmingclub?igsh=NGNpYWwzNHA4bWs="
                        target="_blank"
                        class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-[#A855F7] hover:text-white transition-all duration-300">
                        <x-lucide-instagram class="w-5 h-5" />
                    </a>
                    <a href="https://www.tiktok.com/@khafidswimmingclub1?_r=1&_t=ZS-95k2GUVW5qu"
                        target="_blank"
                        class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-black hover:text-white transition-all duration-300">
                        <x-lucide-music-2 class="w-5 h-5" />
                    </a>
                    <a href="https://youtube.com/@khafidswimmingclub?si=MR4g0Vtf8K-RA2WN"
                        target="_blank"
                        class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-[#FF0000] hover:text-white transition-all duration-300">
                        <x-lucide-youtube class="w-5 h-5" />
                    </a>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 mb-6">Tautan Cepat</h4>
                <ul class="space-y-3 text-sm text-slate-500">
                    <li><a href="{{ url('/about-us') }}" class="hover:text-ksc-blue transition">Profil Klub</a></li>
                    <li><a href="{{ url('/coaches') }}" class="hover:text-ksc-blue transition">Tim Pelatih</a></li>
                    <li><a href="{{ url('/events') }}" class="hover:text-ksc-blue transition">Jadwal & Biaya</a></li>
                    <li><a href="{{ url('/galleries') }}" class="hover:text-ksc-blue transition">Galeri</a></li>
                    <li><a href="{{ url('/facilities') }}" class="hover:text-ksc-blue transition">Fasilitas</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 mb-6">Hubungi Kami</h4>
                <ul class="space-y-4 text-sm text-slate-500">
                    <li class="flex items-start gap-3">
                        <x-lucide-map-pin class="w-5 h-5 text-ksc-blue shrink-0" />
                        <span>Jl. Bypass Krian No.KM.30 Sidomukti, Kraton, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur
                            61262</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <x-lucide-phone class="w-5 h-5 text-ksc-blue shrink-0" />
                        <span>+62 857-4500-0468</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <x-lucide-mail class="w-5 h-5 text-ksc-blue shrink-0" />
                        <span>khafid.swimmingclub16@gmail.com</span>
                    </li>
                </ul>
            </div>
        </div>

        <div
            class="border-t border-slate-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-slate-500">
            <p>&copy; 2026 Khafid Swimming Club. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-slate-900">Privacy Policy</a>
                <a href="#" class="hover:text-slate-900">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi ikon Lucide
        lucide.createIcons();

        // --- Logika Style Navbar ---
        const navbar = document.getElementById('navbar');
        if (navbar) {
            const handleNavbarStyle = () => {
                // Halaman kontak memiliki navbar solid sejak awal
                if (window.location.pathname === '/contact') {
                    navbar.classList.add('bg-ksc-white', 'shadow-lg');
                    navbar.classList.remove('bg-white/95', 'py-2');
                } else {
                    // Halaman lain memiliki efek transparan saat scroll
                    if (window.scrollY > 50) {
                        navbar.classList.add('bg-ksc-white', 'shadow-lg');
                        navbar.classList.remove('bg-white/95', 'py-2');
                    } else {
                        navbar.classList.remove('bg-ksc-white', 'shadow-lg');
                        navbar.classList.add('bg-white/95', 'py-2');
                    }
                }
            };
            // Atur style awal saat halaman dimuat
            handleNavbarStyle();
            // Perbarui style saat scroll
            window.addEventListener('scroll', handleNavbarStyle);
        }

        // --- Logika Toggle Menu Mobile ---
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const closeMenuBtn = document.getElementById('closeMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileMenuBtn && closeMenuBtn && mobileMenu) {
            const toggleMenu = () => {
                mobileMenu.classList.toggle('translate-x-full');
                // Mencegah scroll pada body saat menu terbuka
                document.body.style.overflow = mobileMenu.classList.contains('translate-x-full') ? 'auto' :
                    'hidden';
            };
            mobileMenuBtn.addEventListener('click', toggleMenu);
            closeMenuBtn.addEventListener('click', toggleMenu);
        }

        // --- Logika Animasi Reveal saat Scroll ---
        const revealElements = document.querySelectorAll(".reveal");
        if (revealElements.length > 0) {
            const reveal = () => {
                const windowHeight = window.innerHeight;
                const elementVisible = 100; // Jarak dari bawah viewport untuk memicu animasi
                revealElements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    if (elementTop < windowHeight - elementVisible) {
                        element.classList.add("active");
                    }
                });
            };
            window.addEventListener("scroll", reveal);
            // Panggil sekali saat load untuk elemen yang sudah terlihat
            reveal();
        }

        // --- Inisialisasi Swiper Slider ---
        if (typeof Swiper !== 'undefined' && document.querySelector(".coachSwiper")) {
            new Swiper(".coachSwiper", {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 40,
                    },
                },
            });
        }

        // Initialize Swiper for Coaches Tabs
        const swiperCoachEl = document.querySelector('.swiper-coach');
        if (typeof Swiper !== 'undefined' && swiperCoachEl) {
            const coachSwiper = new Swiper('.swiper-coach', {
                slidesPerView: 1,
                spaceBetween: 30,
                autoHeight: true,
                speed: 600,
                allowTouchMove: false,
                on: {
                    slideChange: function() {
                        updateActiveButton(this.activeIndex);
                    }
                }
            });

            // Function to go to specific category slide
            window.goToSlide = function(index) {
                coachSwiper.slideTo(index);
            }
        }

        // Initialize Swiper for Event Tabs
        const swiperEventEl = document.querySelector('.swiper-event');
        if (typeof Swiper !== 'undefined' && swiperEventEl) {
            const eventSwiper = new Swiper('.swiper-event', {
                slidesPerView: 1,
                spaceBetween: 30,
                autoHeight: true,
                speed: 600,
                allowTouchMove: false,
                on: {
                    slideChange: function() {
                        updateActiveEventButton(this.activeIndex);
                    }
                }
            });

            // Function to go to specific event slide
            window.goToEventSlide = function(index) {
                eventSwiper.slideTo(index);
            }
        }

        // Update Button UI
        function updateActiveButton(activeIndex) {
            const buttons = document.querySelectorAll('#filter-bar .filter-btn');
            buttons.forEach((btn, idx) => {
                if (idx === activeIndex) {
                    btn.classList.add('bg-ksc-blue', 'text-white', 'shadow-lg', 'shadow-ksc-blue/20');
                    btn.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                } else {
                    btn.classList.remove('bg-ksc-blue', 'text-white', 'shadow-lg',
                        'shadow-ksc-blue/20');
                    btn.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                }
            });
        }
    });
</script>
