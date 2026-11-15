<!DOCTYPE html>
<html lang="en" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Admin Login - Arabic LLM Intelligence</title>

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

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-slate-950 font-sans flex items-center justify-center p-4 relative overflow-hidden"
      x-data="{
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

    <!-- Theme Toggle Floating Button -->
    <div class="fixed top-5 right-5 z-20">
        <button @click="toggleTheme()" 
                type="button" 
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white/90 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40 cursor-pointer backdrop-blur-md"
                :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <span x-show="isDark" class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                <span class="text-xs font-medium text-slate-300">Light</span>
            </span>
            <span x-show="!isDark" class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
                <span class="text-xs font-medium text-slate-700">Dark</span>
            </span>
        </button>
    </div>
    <!-- Ambient Background Glows -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-violet-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-sky-500/10 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-sky-400 items-center justify-center shadow-xl shadow-indigo-500/30 mb-4 ring-1 ring-white/20">
                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">AI Intelligence Portal</h1>
            <p class="text-sm text-slate-400 mt-1">Arabic LLM Intelligence & Multi-Provider Pricing</p>
        </div>

        <!-- Glass Card -->
        <div class="glass-card rounded-3xl p-8 shadow-2xl border border-slate-800/80 backdrop-blur-2xl">
            @if (session('info'))
                <div class="mb-5 p-3.5 rounded-xl bg-sky-950/50 border border-sky-500/30 text-sky-200 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-200 text-xs">
                    <div class="font-semibold mb-1">Authentication Error</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Administrator Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email', 'ap.sy.uk@gmail.com') }}" required autofocus
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                               placeholder="admin@example.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <input id="password" type="password" name="password" required
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                               placeholder="••••••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-slate-400 hover:text-slate-300">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                        <span>Remember session</span>
                    </label>
                    <span class="text-indigo-400/80 hover:text-indigo-300 transition-colors">Protected Admin Area</span>
                </div>

                <button type="submit" 
                        class="w-full py-3 px-4 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 border border-indigo-400/30 shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                    <span>Sign In to Admin Panel</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </button>
            </form>
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Arabic LLM Intelligence Platform. Built with Laravel & Tailwind CSS.
        </div>
    </div>
</body>
</html>
