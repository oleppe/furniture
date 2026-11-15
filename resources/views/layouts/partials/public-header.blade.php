<!-- Main Navigation Header -->
<header class="fixed top-0 inset-x-0 z-50 bg-surface-subtle/95 backdrop-blur-xl border-b border-border-subtle">
    <div class="w-full px-gutter md:px-gutter-desktop h-[4.5rem] flex items-center justify-between gap-space-lg">

        <!-- Brand -->
        <a href="{{ route('home') }}" class="flex items-center gap-space-sm shrink-0 group" aria-label="إدراك Lab — الصفحة الرئيسية">
            <img src="{{ asset('images/idraak-lab-logo.png') }}" alt="إدراك Lab — لوحة المتصدرين العربية لنماذج الذكاء الاصطناعي" width="56" height="56" decoding="async" class="w-12 h-12 shrink-0 object-cover rounded-xl bg-[#0b1220] p-0.5 ring-1 ring-border-subtle group-hover:ring-primary/50 transition-all">
            <div class="leading-tight">
                <div class="font-headline-sm text-headline-sm text-text-primary">إدراك Lab</div>
                <div class="hidden sm:block font-label-code text-label-code text-text-tertiary">منصة الذكاء والمعايير العربية</div>
            </div>
        </a>

        @php
            $homePrefix = request()->routeIs('home') ? '' : route('home');
            $isHome = request()->routeIs('home');
            $isBlog = request()->routeIs('blog.*');
        @endphp

        <!-- Desktop Navigation -->
        <nav class="hidden xl:flex items-center gap-space-md font-body-md text-body-md">
            <a href="{{ $homePrefix }}#leaderboard-section" class="py-space-sm {{ $isHome ? 'text-primary border-b-2 border-primary' : 'text-text-secondary hover:text-text-primary border-b-2 border-transparent' }} transition-colors">المتصدرين</a>
            <a href="{{ $homePrefix }}#calculator-widget" class="py-space-sm text-text-secondary hover:text-text-primary border-b-2 border-transparent transition-colors">حاسبة التكاليف</a>
            <a href="{{ route('blog.index') }}" class="py-space-sm {{ $isBlog ? 'text-primary border-b-2 border-primary' : 'text-text-secondary hover:text-text-primary border-b-2 border-transparent' }} transition-colors">المدونة والأبحاث</a>
            <a href="{{ $homePrefix }}#methodology" class="py-space-sm text-text-secondary hover:text-text-primary border-b-2 border-transparent transition-colors">المنهجية</a>
            <a href="{{ $homePrefix }}#faq" class="py-space-sm text-text-secondary hover:text-text-primary border-b-2 border-transparent transition-colors">الأسئلة الشائعة</a>
        </nav>

        <!-- Actions -->
        <div class="flex items-center gap-space-sm">
            <button type="button" data-theme-toggle
                    title="تبديل الوضع الفاتح/الداكن"
                    aria-label="تبديل الوضع الفاتح والداكن"
                    class="flex items-center justify-center p-space-sm rounded-lg bg-surface-panel border border-border-subtle text-text-secondary hover:border-border-highlight hover:text-text-primary transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[18px] theme-icon-light">dark_mode</span>
                <span class="material-symbols-outlined text-[18px] theme-icon-dark">light_mode</span>
            </button>
            <button type="button"
                    x-data
                    @keydown.window="if (($event.metaKey || $event.ctrlKey) && $event.key.toLowerCase() === 'k') { $event.preventDefault(); const el = document.getElementById('leaderboard-search'); if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); el.focus(); } else { window.location.href = '{{ route('home') }}#leaderboard-section'; } }"
                    @click="const el = document.getElementById('leaderboard-search'); if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); el.focus(); } else { window.location.href = '{{ route('home') }}#leaderboard-section'; }"
                    class="hidden md:flex items-center gap-space-sm px-space-md py-space-sm rounded-lg bg-surface-panel border border-border-subtle text-text-tertiary hover:border-border-highlight hover:text-text-secondary transition-colors shrink-0 whitespace-nowrap cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">search</span>
                <span class="font-body-sm text-body-sm">بحث في النماذج...</span>
                <kbd class="font-label-code text-label-code px-space-xs py-0.5 rounded bg-surface-elevated border border-border-subtle text-text-tertiary">⌘K</kbd>
            </button>
            <a href="{{ route('admin.login') }}" class="hidden lg:flex items-center gap-space-xs px-space-md py-space-sm rounded-lg border border-border-subtle text-text-secondary hover:border-primary/50 hover:text-primary transition-colors font-body-sm text-body-sm shrink-0 whitespace-nowrap">
                <span class="material-symbols-outlined text-[18px]">key</span>
                إضافة API Key
            </a>
            <a href="{{ route('admin.login') }}" title="تسجيل الدخول" aria-label="تسجيل الدخول" class="flex items-center gap-space-xs px-space-md py-space-sm rounded-lg bg-primary-container text-on-primary-container hover:opacity-90 transition-opacity font-body-sm text-body-sm font-semibold shrink-0 whitespace-nowrap">
                <span class="material-symbols-outlined text-[18px]">person</span>
                <span class="hidden sm:inline">تسجيل الدخول</span>
            </a>
            <button type="button" @click="$dispatch('toggle-mobile-nav')" class="xl:hidden text-text-secondary hover:text-text-primary p-space-sm" aria-label="القائمة">
                <span class="material-symbols-outlined text-[24px]">menu</span>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <nav x-data x-on:toggle-mobile-nav.window="$el.classList.toggle('hidden')" class="hidden xl:hidden border-t border-border-subtle px-gutter md:px-gutter-desktop py-space-md space-y-space-xs font-body-md text-body-md">
        <a href="{{ $homePrefix }}#leaderboard-section" class="block py-space-sm {{ $isHome ? 'text-primary' : 'text-text-secondary hover:text-text-primary' }}">المتصدرين</a>
        <a href="{{ $homePrefix }}#calculator-widget" class="block py-space-sm text-text-secondary hover:text-text-primary">حاسبة التكاليف</a>
        <a href="{{ route('blog.index') }}" class="block py-space-sm {{ $isBlog ? 'text-primary' : 'text-text-secondary hover:text-text-primary' }}">المدونة والأبحاث</a>
        <a href="{{ $homePrefix }}#methodology" class="block py-space-sm text-text-secondary hover:text-text-primary">المنهجية</a>
        <a href="{{ $homePrefix }}#faq" class="block py-space-sm text-text-secondary hover:text-text-primary">الأسئلة الشائعة</a>
    </nav>
</header>
