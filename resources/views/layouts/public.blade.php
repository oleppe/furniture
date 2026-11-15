<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Theme Detection (Anti-FOUC): dark is the default, light is opt-in -->
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('theme') || localStorage.getItem('admin_theme');
                var mode = saved === 'light' ? 'light' : 'dark';
                document.documentElement.classList.toggle('light', mode === 'light');
                document.documentElement.classList.toggle('dark', mode === 'dark');
            } catch (error) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @php
        $siteName = config('seo.site_name');
        $siteTagline = config('seo.tagline');

        $pageTitle = trim($__env->yieldContent('title'));
        $pageTitle = $pageTitle !== '' ? $pageTitle : $siteName.' — '.$siteTagline;

        $pageDescription = trim($__env->yieldContent('meta_description'));
        $pageDescription = $pageDescription !== '' ? $pageDescription : 'المرجع العربي الأول لمقارنة سرعة، ذكاء، وتكاليف نماذج الذكاء الاصطناعي — رصد لحظي لكفاءة تقطيع الحروف العربية وزمن الاستجابة وتكلفة المعالجة.';

        $pageCanonical = trim($__env->yieldContent('canonical'));
        $pageCanonical = $pageCanonical !== '' ? $pageCanonical : url()->current();

        $pageRobots = trim($__env->yieldContent('meta_robots'));
        $pageRobots = $pageRobots !== '' ? $pageRobots : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        $pageImagePath = trim($__env->yieldContent('og_image'));
        $pageImagePath = $pageImagePath !== '' ? $pageImagePath : config('seo.social_image.path');
        $pageImage = str_starts_with($pageImagePath, 'http') ? $pageImagePath : asset($pageImagePath);
        $pageImageType = trim($__env->yieldContent('og_image_type'));
        $pageImageType = $pageImageType !== '' ? $pageImageType : config('seo.social_image.type');
        $pageImageWidth = trim($__env->yieldContent('og_image_width'));
        $pageImageWidth = $pageImageWidth !== '' ? $pageImageWidth : config('seo.social_image.width');
        $pageImageHeight = trim($__env->yieldContent('og_image_height'));
        $pageImageHeight = $pageImageHeight !== '' ? $pageImageHeight : config('seo.social_image.height');
        $pageImageAlt = config('seo.social_image.alt');
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="robots" content="{{ $pageRobots }}">
    <meta name="author" content="{{ $siteName }}">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="format-detection" content="telephone=no">
    <link rel="canonical" href="{{ $pageCanonical }}">

@if (config('seo.verification.google'))
    <meta name="google-site-verification" content="{{ config('seo.verification.google') }}">
@endif
@if (config('seo.verification.bing'))
    <meta name="msvalidate.01" content="{{ config('seo.verification.bing') }}">
@endif
@if (config('seo.verification.yandex'))
    <meta name="yandex-verification" content="{{ config('seo.verification.yandex') }}">
@endif

    <!-- Language targeting (single-language Arabic site) -->
    <link rel="alternate" hreflang="ar" href="{{ $pageCanonical }}">
    <link rel="alternate" hreflang="x-default" href="{{ $pageCanonical }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="ar_AR">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $pageCanonical }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:image:secure_url" content="{{ $pageImage }}">
    <meta property="og:image:type" content="{{ $pageImageType }}">
    <meta property="og:image:width" content="{{ $pageImageWidth }}">
    <meta property="og:image:height" content="{{ $pageImageHeight }}">
    <meta property="og:image:alt" content="{{ $pageImageAlt }}">
@hasSection('meta_updated_at')
    <meta property="og:updated_time" content="{{ trim($__env->yieldContent('meta_updated_at')) }}">
@endif

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
@if (config('seo.twitter.site'))
    <meta name="twitter:site" content="{{ config('seo.twitter.site') }}">
@endif
@if (config('seo.twitter.creator'))
    <meta name="twitter:creator" content="{{ config('seo.twitter.creator') }}">
@endif
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $pageImage }}">
    <meta name="twitter:image:alt" content="{{ $pageImageAlt }}">

    <meta name="theme-color" content="#06090e" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="color-scheme" content="dark light">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/idraak-lab-logo1.jpg') }}">

    @include('layouts.partials.public-schema')

    <!-- Google Fonts: IBM Plex Sans Arabic, JetBrains Mono & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #06090e; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            user-select: none;
            vertical-align: middle;
        }
    </style>
    @stack('head')
</head>
<body class="h-full bg-surface text-text-primary font-body-md antialiased selection:bg-primary-container/30 selection:text-text-primary">

    @include('layouts.partials.public-header')

    <main class="pt-[4.5rem]">
        @yield('content')
    </main>

    @include('layouts.partials.public-footer')

    @stack('scripts')
</body>
</html>
