<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>@yield('title', 'Admin Dashboard') - Arabic LLM Intelligence</title>

    <!-- Theme Detection (Anti-FOUC) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || localStorage.getItem('admin_theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            } else if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            } else {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            }
        })();
    </script>

    <!-- Google Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
    </style>
</head>
<body class="h-full bg-slate-950 font-sans text-slate-200 selection:bg-indigo-500 selection:text-white" 
      x-data="{ 
          sidebarOpen: false, 
          syncModal: false, 
          syncing: false, 
          syncResult: null,
          isDark: document.documentElement.classList.contains('dark'),
          toggleTheme() {
              if (document.documentElement.classList.contains('dark')) {
                  document.documentElement.classList.remove('dark');
                  document.documentElement.classList.add('light');
                  localStorage.setItem('theme', 'light');
                  localStorage.setItem('admin_theme', 'light');
                  this.isDark = false;
              } else {
                  document.documentElement.classList.add('dark');
                  document.documentElement.classList.remove('light');
                  localStorage.setItem('theme', 'dark');
                  localStorage.setItem('admin_theme', 'dark');
                  this.isDark = true;
              }
          }
      }">

    <div class="min-h-full flex">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" x-cloak 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-40 bg-slate-950/80 backdrop-blur-sm lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-72 glass-sidebar flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0">
            
            <!-- Brand Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800/80">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-sky-400 flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-white tracking-wide text-base leading-tight">AI Intelligence</div>
                        <div class="text-xs font-semibold text-indigo-400 tracking-wider uppercase">Admin Portal</div>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <div class="px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Intelligence</div>
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 shadow-inner shadow-indigo-500/10' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                    </svg>
                    <span>Overview & Analytics</span>
                </a>

                <a href="{{ route('admin.models.index') }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.models.*') ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 shadow-inner shadow-indigo-500/10' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.models.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                        <span>AI Models & Pricing</span>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-800 text-slate-300 border border-slate-700/60">
                        {{ \App\Models\AiModel::count() }}
                    </span>
                </a>

                <a href="{{ route('admin.providers.index') }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.providers.*') ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 shadow-inner shadow-indigo-500/10' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.providers.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                        </svg>
                        <span>Providers Directory</span>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-800 text-slate-300 border border-slate-700/60">
                        {{ \App\Models\Provider::count() }}
                    </span>
                </a>

                <a href="{{ route('admin.benchmarks.arabic') }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.benchmarks.arabic*') ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 shadow-inner shadow-indigo-500/10' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.benchmarks.arabic*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.942A4.5 4.5 0 0115.918 17H8.082a4.5 4.5 0 01-2.312-.758L4.2 15.3m15.6 0A2.25 2.25 0 0021 13.385V12.75a2.25 2.25 0 00-2.25-2.25h-.54M4.2 15.3A2.25 2.25 0 013 13.385V12.75a2.25 2.25 0 012.25-2.25h.54" />
                        </svg>
                        <span>Arabic Benchmark Lab</span>
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        Lab
                    </span>
                </a>

                <div class="pt-6 px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Research & Blog</div>

                <a href="{{ route('admin.blog.articles.index') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.blog.articles.*') ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 shadow-inner shadow-indigo-500/10' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.blog.articles.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        <span>Blog Articles</span>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-800 text-slate-300 border border-slate-700/60">
                        {{ \App\Models\BlogArticle::count() }}
                    </span>
                </a>

                <a href="{{ route('admin.blog.categories.index') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.blog.categories.*') ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 shadow-inner shadow-indigo-500/10' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.blog.categories.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                        <span>Blog Categories</span>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-800 text-slate-300 border border-slate-700/60">
                        {{ \App\Models\BlogCategory::count() }}
                    </span>
                </a>

                <div class="pt-6 px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Platform Administration</div>

                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 shadow-inner shadow-indigo-500/10' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        <span>User Management</span>
                    </div>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-800 text-slate-300 border border-slate-700/60">
                        {{ \App\Models\User::count() }}
                    </span>
                </a>
            </nav>

            <!-- User Footer & Logout -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-900/40">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-sm shadow-md">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="truncate">
                            <div class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" title="Logout" 
                                class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Body Wrapper -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            <header class="sticky top-0 z-30 glass-header px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-400 hover:text-white p-2 rounded-lg bg-slate-900 border border-slate-800">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-white flex items-center gap-2">
                            @yield('page_title', 'Dashboard')
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Theme Mode Toggle -->
                    <button @click="toggleTheme()" 
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white/90 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer"
                            :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                            aria-label="Toggle theme mode">
                        <!-- Sun Icon (Dark Mode active, click for Light) -->
                        <span x-show="isDark" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-400 transition-transform duration-300 hover:rotate-45" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                            <span class="hidden sm:inline text-xs font-medium text-slate-300">Light</span>
                        </span>
                        <!-- Moon Icon (Light Mode active, click for Dark) -->
                        <span x-show="!isDark" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-600 transition-transform duration-300 hover:-rotate-12" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                            <span class="hidden sm:inline text-xs font-medium text-slate-700">Dark</span>
                        </span>
                    </button>

                    <!-- Sync Trigger Button -->
                    <button @click="
                                syncing = true; 
                                syncModal = true; 
                                syncResult = null;
                                fetch('{{ route('admin.sync') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    syncing = false;
                                    syncResult = data;
                                })
                                .catch(err => {
                                    syncing = false;
                                    syncResult = { success: false, message: err.message };
                                });
                            "
                            :disabled="syncing"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 border border-indigo-400/30 shadow-lg shadow-indigo-500/20 transition-all duration-200 disabled:opacity-50">
                        <svg :class="syncing ? 'animate-spin' : ''" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="syncing ? 'Syncing...' : 'Sync Catalog Now'"></span>
                    </button>
                </div>
            </header>

            <!-- Alerts Banner -->
            <div class="px-6 pt-5">
                @if (session('success'))
                    <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-200 text-sm shadow-lg shadow-emerald-950/50">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-200 text-sm shadow-lg shadow-rose-950/50">
                        <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-200 text-sm shadow-lg shadow-rose-950/50">
                        <div class="font-semibold mb-1">Please fix the following issues:</div>
                        <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-300">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="flex-1 px-6 pb-12">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Sync Result Modal -->
    <div x-show="syncModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="sync-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity" @click="syncModal = false"></div>
            
            <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform glass-card rounded-2xl shadow-2xl border border-slate-700/60">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                            <svg :class="syncing ? 'animate-spin' : ''" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white" id="sync-modal-title">Catalog & Pricing Synchronization</h3>
                            <p class="text-xs text-slate-400">OpenRouter & LiteLLM Pipeline</p>
                        </div>
                    </div>
                    <button @click="syncModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="py-6">
                    <template x-if="syncing">
                        <div class="text-center py-6">
                            <div class="inline-block w-12 h-12 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
                            <div class="mt-4 font-semibold text-white">Fetching live models & cross-referencing prices...</div>
                            <p class="text-xs text-slate-400 mt-1">Connecting to OpenRouter endpoints and LiteLLM data specs...</p>
                        </div>
                    </template>

                    <template x-if="!syncing && syncResult">
                        <div>
                            <div :class="syncResult.success ? 'bg-emerald-950/40 border-emerald-500/30 text-emerald-300' : 'bg-rose-950/40 border-rose-500/30 text-rose-300'"
                                 class="p-4 rounded-xl border text-sm mb-5 flex items-start gap-3">
                                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span x-text="syncResult.message"></span>
                            </div>

                            <template x-if="syncResult.stats">
                                <div class="grid grid-cols-2 gap-3 text-xs">
                                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800">
                                        <div class="text-slate-400 font-medium">Providers Synced</div>
                                        <div class="text-lg font-bold text-white mt-1" x-text="syncResult.stats.providers"></div>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800">
                                        <div class="text-slate-400 font-medium">Models Synced</div>
                                        <div class="text-lg font-bold text-indigo-400 mt-1" x-text="syncResult.stats.models"></div>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800">
                                        <div class="text-slate-400 font-medium">Specs Recorded</div>
                                        <div class="text-lg font-bold text-emerald-400 mt-1" x-text="syncResult.stats.specs"></div>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800">
                                        <div class="text-slate-400 font-medium">Multi-Provider Offerings</div>
                                        <div class="text-lg font-bold text-purple-400 mt-1" x-text="syncResult.stats.offerings"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end">
                    <button @click="syncModal = false; if(!syncing && syncResult && syncResult.success) window.location.reload();" 
                            type="button" 
                            class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-white transition-colors">
                        Done & Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
