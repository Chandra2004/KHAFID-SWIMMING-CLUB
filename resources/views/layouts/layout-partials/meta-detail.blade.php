
{{-- META UNTUK HALAMAN DETAIL (EVENT, PELATIH, BERITA) --}}
<meta name="description" content="{{ $meta_description ?? 'Khafid Swimming Club (KSC) - Official Website' }}">
<meta name="keywords" content="{{ $meta_keywords ?? 'Klub renang Sidoarjo, les renang Krian, privat renang, sekolah renang, Khafid Swimming Club, KSC, belajar renang, atlet renang, lomba renang, jawatimur' }}">
<meta name="author" content="Khafid Swimming Club">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ request()->url() }}">

<!-- Open Graph Meta Tags (untuk Facebook, LinkedIn, dll.) -->
<meta property="og:title" content="{{ $title ?? 'Detail - Khafid Swimming Club' }}">
<meta property="og:description" content="{{ $meta_description ?? 'Informasi detail mengenai ' . ($title ?? 'Khafid Swimming Club') }}">
<meta property="og:type" content="article">
<meta property="article:published_time" content="{{ $published_time ?? '' }}">
<meta property="article:author" content="Khafid Swimming Club">
<meta property="article:section" content="{{ $category ?? 'Informasi' }}">
<meta property="og:url" content="{{ request()->url() }}">
<meta property="og:site_name" content="Khafid Swimming Club">
<meta property="og:image" content="{{ $meta_image ?? (isset($event['banner_event']) ? url($event['banner_event']) : url('/assets/ico/icon-bar.png')) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="id_ID">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title ?? 'Detail - Khafid Swimming Club' }}">
<meta name="twitter:description" content="{{ $meta_description ?? 'Informasi detail mengenai '.($title ?? 'Khafid Swimming Club') }}">
<meta name="twitter:image" content="{{ $meta_image ?? (isset($event['banner_event']) ? url($event['banner_event']) : url('/assets/ico/icon-bar.png')) }}">
<meta name="twitter:site" content="@ksc_sidoarjo">
<meta name="twitter:creator" content="@ksc_sidoarjo">

<!-- Additional Meta Tags -->
{{-- <meta name="theme-color" content="#1e40af">
<meta name="msapplication-navbutton-color" content="#1e40af">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="google-site-verification" content="YOUR_GOOGLE_VERIFICATION_CODE"> --}}
