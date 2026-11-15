@extends('layouts.admin')

@section('title', 'AI Models & Pricing Matrix')
@section('page_title', 'AI Models & Multi-Provider Pricing Matrix')

@section('content')
<div x-data="{ 
    drawerOpen: false, 
    drawerLoading: false, 
    activeTab: 'overview',
    selectedModel: null, 
    spec: null,
    benchmarkGeneral: null,
    benchmarkArabic: null,
    speed: null,
    offerings: [],
    rawDb: null,
    copied: false,
    copyRawJson() {
        if (!this.rawDb) return;
        navigator.clipboard.writeText(JSON.stringify(this.rawDb, null, 2));
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
    },
    openDrawer(modelId) {
        this.drawerOpen = true;
        this.drawerLoading = true;
        this.activeTab = 'overview';
        this.selectedModel = null;
        this.spec = null;
        this.benchmarkGeneral = null;
        this.benchmarkArabic = null;
        this.speed = null;
        this.offerings = [];
        this.rawDb = null;
        this.copied = false;
        
        fetch(`/admin/models/${modelId}/offerings`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            this.selectedModel = data.model;
            this.spec = data.spec;
            this.benchmarkGeneral = data.benchmark_general;
            this.benchmarkArabic = data.benchmark_arabic;
            this.speed = data.speed;
            this.offerings = data.offerings;
            this.rawDb = data.raw_database_record;
            this.drawerLoading = false;
        })
        .catch(err => {
            console.error('Failed to load model details', err);
            this.drawerLoading = false;
        });
    }
}" class="space-y-6">

    <!-- Header Stats Banner -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total AI Models</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($stats['total']) }}</div>
            </div>
            <div class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
        </div>
        <div class="glass-card p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Active in Catalog</div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($stats['active']) }}</div>
            </div>
            <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>
        <div class="glass-card p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Featured Showcase</div>
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($stats['featured']) }}</div>
            </div>
            <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
            </div>
        </div>
        <div class="glass-card p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Open Source Weights</div>
                <div class="text-xl font-bold text-sky-600 dark:text-sky-400 mt-1">{{ number_format($stats['open_source']) }}</div>
            </div>
            <div class="p-2 rounded-lg bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls Form -->
    <div class="glass-card p-4 rounded-2xl border border-slate-200 dark:border-slate-800/80">
        <form method="GET" action="{{ route('admin.models.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <!-- Search Text -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-400 uppercase tracking-wider mb-1">Search Models</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by model, slug, or provider..."
                           class="w-full pl-9 pr-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700/80 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>

            <!-- Provider Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-400 uppercase tracking-wider mb-1">Provider</label>
                <select name="provider_id" class="w-full py-2 px-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700/80 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">All Providers</option>
                    @foreach ($providers as $prov)
                        <option value="{{ $prov->id }}" {{ $providerId == $prov->id ? 'selected' : '' }}>{{ $prov->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Open Source Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-400 uppercase tracking-wider mb-1">Architecture Type</label>
                <select name="open_source" class="w-full py-2 px-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700/80 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">All Types</option>
                    <option value="1" {{ $openSource === '1' ? 'selected' : '' }}>Open Source</option>
                    <option value="0" {{ $openSource === '0' ? 'selected' : '' }}>Proprietary (Closed)</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-400 uppercase tracking-wider mb-1">Status</label>
                <select name="is_active" class="w-full py-2 px-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700/80 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">All States</option>
                    <option value="1" {{ $isActive === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ $isActive === '0' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 shadow-md shadow-indigo-600/20">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.models.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Models Table Card -->
    <div class="glass-card rounded-2xl border border-slate-200 dark:border-slate-800/80 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100/90 dark:bg-slate-900/80 text-slate-700 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3.5 px-4 font-semibold">Model (Click to Inspect)</th>
                        <th class="py-3.5 px-4 font-semibold">Provider</th>
                        <th class="py-3.5 px-4 font-semibold">Context / Max Out</th>
                        <th class="py-3.5 px-4 font-semibold">Baseline Cost / 1M</th>
                        <th class="py-3.5 px-4 font-semibold">Multi-Host Offerings</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Featured</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Active</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @forelse ($models as $model)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors group">
                            <!-- Model Name (CLICKABLE TO OPEN DRAWER) -->
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                <button type="button" @click="openDrawer({{ $model->id }})" class="text-left w-full group/item focus:outline-none">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white group-hover/item:text-indigo-600 dark:group-hover/item:text-indigo-400 group-hover/item:underline transition-colors flex items-center gap-1.5 cursor-pointer">
                                            {{ $model->name }}
                                            <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover/item:text-indigo-600 dark:group-hover/item:text-indigo-400 transition-all opacity-70 group-hover/item:opacity-100 group-hover/item:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </span>
                                        @if($model->is_open_source)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-100 dark:bg-emerald-950/70 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-400">Open</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-0.5 group-hover/item:text-slate-700 dark:group-hover/item:text-slate-300">{{ $model->slug }}</div>
                                </button>
                            </td>

                            <!-- Provider -->
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.models.index', ['provider_id' => $model->provider_id]) }}" 
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs transition-colors">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    <span>{{ $model->provider?->name ?? 'Unknown' }}</span>
                                </a>
                            </td>

                            <!-- Context Window & Output -->
                            <td class="py-3 px-4 font-mono text-slate-700 dark:text-slate-300">
                                @if($model->latestSpec?->context_window)
                                    <div><strong class="text-indigo-600 dark:text-indigo-300 font-bold">{{ number_format($model->latestSpec->context_window / 1000, 0) }}k</strong> ctx</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ number_format($model->latestSpec->max_output_tokens / 1000, 0) }}k max out</div>
                                @else
                                    <span class="text-slate-400 dark:text-slate-600">N/A</span>
                                @endif
                            </td>

                            <!-- Baseline Rates -->
                            <td class="py-3 px-4 font-mono">
                                @if($model->latestSpec?->input_cost_per_1m !== null)
                                    <div class="text-emerald-600 dark:text-emerald-400 font-semibold">${{ number_format($model->latestSpec->input_cost_per_1m, 2) }} <span class="text-[10px] text-slate-500">in</span></div>
                                    <div class="text-sky-600 dark:text-sky-400 font-medium">${{ number_format($model->latestSpec->output_cost_per_1m, 2) }} <span class="text-[10px] text-slate-500">out</span></div>
                                @else
                                    <span class="text-slate-400 dark:text-slate-600">N/A</span>
                                @endif
                            </td>

                            <!-- Offerings Pill -->
                            <td class="py-3 px-4">
                                @if($model->offerings_count > 0)
                                    <button @click="openDrawer({{ $model->id }})" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-300 font-semibold text-[11px] transition-colors">
                                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                        <span>{{ $model->offerings_count }} Host{{ $model->offerings_count > 1 ? 's' : '' }}</span>
                                    </button>
                                @else
                                    <button @click="openDrawer({{ $model->id }})" class="text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 text-[11px] underline">
                                        Direct Only
                                    </button>
                                @endif
                            </td>

                            <!-- Featured Toggle -->
                            <td class="py-3 px-4 text-center">
                                <form method="POST" action="{{ route('admin.models.toggle-featured', $model) }}">
                                    @csrf
                                    <button type="submit" 
                                            title="Toggle Featured"
                                            class="p-1 rounded-lg transition-colors {{ $model->is_featured ? 'text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-400/10 hover:bg-amber-100 dark:hover:bg-amber-400/20' : 'text-slate-300 dark:text-slate-600 hover:text-slate-500 dark:hover:text-slate-400' }}">
                                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </button>
                                </form>
                            </td>

                            <!-- Active Toggle -->
                            <td class="py-3 px-4 text-center">
                                <form method="POST" action="{{ route('admin.models.toggle-active', $model) }}">
                                    @csrf
                                    <button type="submit" 
                                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $model->is_active ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-800' }}">
                                        <span class="sr-only">Toggle Active</span>
                                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $model->is_active ? 'translate-x-4' : 'translate-x-0' }}"></span>
                                    </button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right">
                                <button @click="openDrawer({{ $model->id }})" 
                                        class="px-2.5 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600/40 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-300 font-medium text-xs transition-colors inline-flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Inspect All</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-500 dark:text-slate-400">
                                No AI models match the specified search or filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($models->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/80 bg-slate-900/40">
                {{ $models->links() }}
            </div>
        @endif
    </div>

    <!-- Enhanced Slide-Over Drawer Container (MAX-W-4XL HIGH DENSITY) -->
    <div x-show="drawerOpen" x-cloak 
         class="fixed inset-0 z-50 overflow-hidden" 
         aria-labelledby="drawer-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Backdrop -->
            <div x-show="drawerOpen" 
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" 
                 @click="drawerOpen = false"></div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div x-show="drawerOpen" 
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="pointer-events-auto w-screen max-w-4xl glass-sidebar border-l border-slate-800 shadow-2xl flex flex-col">
                    
                    <!-- Drawer Header -->
                    <div class="px-6 py-5 border-b border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white" id="drawer-title" x-text="selectedModel ? selectedModel.name : 'Model Inspector'"></h3>
                                    <span x-show="selectedModel && selectedModel.is_open_source" class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-950/80 border border-emerald-500/30 text-emerald-300">Open Source</span>
                                    <span x-show="selectedModel && selectedModel.is_featured" class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-950/80 border border-amber-500/30 text-amber-300">★ Featured</span>
                                </div>
                                <div class="text-xs text-slate-400 font-mono" x-text="selectedModel ? selectedModel.slug : 'Complete Database Specification'"></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Direct Link to Dedicated Full Page View -->
                            <template x-if="selectedModel && selectedModel.show_url">
                                <a :href="selectedModel.show_url" 
                                   class="px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700/80 hover:bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold transition-colors inline-flex items-center gap-1.5">
                                    <span>Full Page Profile</span>
                                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                            </template>

                            <button @click="drawerOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Drawer Tabs Navigation -->
                    <div class="px-6 border-b border-slate-800 bg-slate-950/40 flex items-center gap-2 overflow-x-auto">
                        <button @click="activeTab = 'overview'" 
                                :class="activeTab === 'overview' ? 'text-indigo-400 border-indigo-500 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900/50'"
                                class="px-3.5 py-2.5 text-xs font-semibold rounded-t-xl border-b-2 transition-all flex items-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Overview & Specs</span>
                        </button>

                        <button @click="activeTab = 'pricing'" 
                                :class="activeTab === 'pricing' ? 'text-indigo-400 border-indigo-500 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900/50'"
                                class="px-3.5 py-2.5 text-xs font-semibold rounded-t-xl border-b-2 transition-all flex items-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Pricing & Caching</span>
                        </button>

                        <button @click="activeTab = 'benchmarks'" 
                                :class="activeTab === 'benchmarks' ? 'text-indigo-400 border-indigo-500 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900/50'"
                                class="px-3.5 py-2.5 text-xs font-semibold rounded-t-xl border-b-2 transition-all flex items-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            <span>Benchmarks & Arabic</span>
                        </button>

                        <button @click="activeTab = 'offerings'" 
                                :class="activeTab === 'offerings' ? 'text-indigo-400 border-indigo-500 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900/50'"
                                class="px-3.5 py-2.5 text-xs font-semibold rounded-t-xl border-b-2 transition-all flex items-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            <span>Hosting Endpoints (<span x-text="offerings.length"></span>)</span>
                        </button>

                        <button @click="activeTab = 'raw_db'" 
                                :class="activeTab === 'raw_db' ? 'text-indigo-400 border-indigo-500 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900/50'"
                                class="px-3.5 py-2.5 text-xs font-semibold rounded-t-xl border-b-2 transition-all flex items-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zM9 9h6M9 13h6M9 17h3" /></svg>
                            <span>All DB Properties (JSON)</span>
                        </button>
                    </div>

                    <!-- Drawer Content Body -->
                    <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                        <!-- Loading State -->
                        <template x-if="drawerLoading">
                            <div class="text-center py-24">
                                <div class="inline-block w-10 h-10 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
                                <div class="mt-3 text-xs text-slate-400">Loading comprehensive database specifications...</div>
                            </div>
                        </template>

                        <!-- Loaded State -->
                        <template x-if="!drawerLoading && selectedModel">
                            <div class="space-y-6">
                                
                                <!-- ================= TAB 1: OVERVIEW & SPECS ================= -->
                                <div x-show="activeTab === 'overview'" class="space-y-5">
                                    <!-- Extended Description -->
                                    <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 space-y-2">
                                        <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Model Summary & Architecture Context</div>
                                        <p class="text-xs text-slate-300 leading-relaxed" x-text="selectedModel.description || 'No extended description provided.'"></p>
                                    </div>

                                    <!-- Core Identifiers Grid -->
                                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-3">
                                        <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                                            <span>Registry Identifiers & Lifecycle</span>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs font-mono">
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] font-sans text-slate-500 uppercase font-semibold">OpenRouter ID</div>
                                                <div class="text-slate-200 mt-1 break-all" x-text="selectedModel.openrouter_id || 'N/A'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] font-sans text-slate-500 uppercase font-semibold">Canonical Slug</div>
                                                <div class="text-slate-200 mt-1 break-all" x-text="selectedModel.canonical_slug || selectedModel.slug"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] font-sans text-slate-500 uppercase font-semibold">Hugging Face ID</div>
                                                <div class="text-slate-200 mt-1 break-all" x-text="selectedModel.hugging_face_id || 'None'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] font-sans text-slate-500 uppercase font-semibold">LiteLLM ID</div>
                                                <div class="text-slate-200 mt-1 break-all" x-text="selectedModel.litellm_id || 'N/A'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] font-sans text-slate-500 uppercase font-semibold">Release Date</div>
                                                <div class="text-slate-200 font-sans mt-1" x-text="selectedModel.release_date_formatted || selectedModel.release_date || 'N/A'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] font-sans text-slate-500 uppercase font-semibold">Knowledge Cutoff</div>
                                                <div class="text-slate-200 font-sans mt-1" x-text="selectedModel.knowledge_cutoff || 'Not Specified'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] font-sans text-slate-500 uppercase font-semibold">Expiration / Sunset</div>
                                                <div class="text-slate-200 font-sans mt-1" x-text="selectedModel.expiration_date || 'Active Standard'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] font-sans text-slate-500 uppercase font-semibold">Developer / Creator</div>
                                                <div class="text-indigo-400 font-sans font-bold mt-1" x-text="selectedModel.provider || 'Unknown'"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Architecture & Modalities -->
                                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-3">
                                        <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                            <span>Architecture, Modalities & Tokenizer</span>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Modality Pipeline</div>
                                                <div class="font-mono text-indigo-300 font-bold mt-1" x-text="selectedModel.architecture || 'text->text'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Tokenizer Model</div>
                                                <div class="font-mono text-slate-200 mt-1" x-text="selectedModel.architecture_details?.tokenizer || 'Standard'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Instruct Format</div>
                                                <div class="font-mono text-slate-200 mt-1" x-text="selectedModel.architecture_details?.instruct_type || 'Direct / ChatML'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Moderation Filter</div>
                                                <div class="mt-1">
                                                    <span x-show="spec && spec.is_moderated" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-950 text-rose-300 border border-rose-500/30">Enforced</span>
                                                    <span x-show="!spec || !spec.is_moderated" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-950 text-emerald-300 border border-emerald-500/30">Uncensored / Raw</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modality Pills -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                            <div class="p-2.5 rounded-lg bg-slate-950/40 border border-slate-800/60 space-y-1.5">
                                                <div class="text-[10px] text-slate-400 font-semibold uppercase">Supported Inputs</div>
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="mod in (selectedModel.architecture_details?.input_modalities || ['text'])" :key="mod">
                                                        <span class="px-2 py-0.5 rounded bg-indigo-950/80 border border-indigo-500/30 text-indigo-300 text-[11px] font-mono capitalize" x-text="mod"></span>
                                                    </template>
                                                </div>
                                            </div>

                                            <div class="p-2.5 rounded-lg bg-slate-950/40 border border-slate-800/60 space-y-1.5">
                                                <div class="text-[10px] text-slate-400 font-semibold uppercase">Generated Outputs</div>
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="mod in (selectedModel.architecture_details?.output_modalities || ['text'])" :key="mod">
                                                        <span class="px-2 py-0.5 rounded bg-sky-950/80 border border-sky-500/30 text-sky-300 text-[11px] font-mono capitalize" x-text="mod"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reasoning Process Configuration -->
                                    <template x-if="selectedModel.reasoning_spec">
                                        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-3">
                                            <div class="text-[11px] font-bold text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                <span>Reasoning Engine & Thinking Process Parameters</span>
                                            </div>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                                <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800">
                                                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Mandatory</div>
                                                    <div class="font-bold text-white mt-0.5" x-text="selectedModel.reasoning_spec.mandatory ? 'Enforced' : 'Optional'"></div>
                                                </div>
                                                <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800">
                                                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Default Enabled</div>
                                                    <div class="font-bold text-white mt-0.5" x-text="selectedModel.reasoning_spec.default_enabled ? 'True' : 'False'"></div>
                                                </div>
                                                <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800">
                                                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Default Effort</div>
                                                    <div class="font-mono text-amber-300 font-bold uppercase mt-0.5" x-text="selectedModel.reasoning_spec.default_effort || 'Normal'"></div>
                                                </div>
                                                <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800">
                                                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Token Budget</div>
                                                    <div class="font-bold text-white mt-0.5" x-text="selectedModel.reasoning_spec.supports_max_tokens ? 'Supported' : 'Uncapped'"></div>
                                                </div>
                                            </div>
                                            <template x-if="selectedModel.reasoning_spec.supported_efforts">
                                                <div class="text-xs pt-1 flex items-center gap-2">
                                                    <span class="text-slate-400 text-[11px]">Supported Effort Tiers:</span>
                                                    <div class="flex flex-wrap gap-1">
                                                        <template x-for="eff in selectedModel.reasoning_spec.supported_efforts" :key="eff">
                                                            <span class="px-2 py-0.5 rounded bg-amber-950/80 border border-amber-500/30 text-amber-300 font-mono text-[10px] uppercase" x-text="eff"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Model Capabilities Feature Flags -->
                                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-3">
                                        <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span>Feature Capabilities Matrix</span>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 text-xs">
                                            <template x-for="(has, cap) in (selectedModel.capabilities || {})" :key="cap">
                                                <div class="p-2 rounded-lg border flex items-center gap-2"
                                                     :class="has ? 'bg-emerald-950/30 border-emerald-500/30 text-emerald-300' : 'bg-slate-950/40 border-slate-800 text-slate-500'">
                                                    <svg x-show="has" class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                    <svg x-show="!has" class="w-3.5 h-3.5 text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    <span class="truncate font-mono text-[11px]" x-text="cap.replace('supports_', '').replace(/_/g, ' ')"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Accepted Parameters -->
                                    <template x-if="selectedModel.supported_parameters && selectedModel.supported_parameters.length">
                                        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-2">
                                            <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">
                                                Accepted API Parameters (<span x-text="selectedModel.supported_parameters.length"></span>)
                                            </div>
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="p in selectedModel.supported_parameters" :key="p">
                                                    <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 font-mono text-[10px] text-slate-300" x-text="p"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- ================= TAB 2: PRICING & CACHING ================= -->
                                <div x-show="activeTab === 'pricing'" class="space-y-5">
                                    <!-- High Level Cost Grid -->
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                        <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                                            <div class="text-[10px] text-slate-500 uppercase font-semibold">Standard In / 1M</div>
                                            <div class="text-xl font-mono font-bold text-emerald-400 mt-1" x-text="spec && spec.input_cost_per_1m !== null ? '$' + spec.input_cost_per_1m.toFixed(3) : 'Free'"></div>
                                            <div class="text-[10px] text-slate-500 mt-0.5">Base prompt token rate</div>
                                        </div>

                                        <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                                            <div class="text-[10px] text-slate-500 uppercase font-semibold">Standard Out / 1M</div>
                                            <div class="text-xl font-mono font-bold text-sky-400 mt-1" x-text="spec && spec.output_cost_per_1m !== null ? '$' + spec.output_cost_per_1m.toFixed(3) : 'Free'"></div>
                                            <div class="text-[10px] text-slate-500 mt-0.5">Completion generation rate</div>
                                        </div>

                                        <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                                            <div class="text-[10px] text-slate-500 uppercase font-semibold">Context Window</div>
                                            <div class="text-xl font-mono font-bold text-indigo-300 mt-1" x-text="spec && spec.context_window ? (spec.context_window / 1000).toLocaleString() + 'k' : '-'"></div>
                                            <div class="text-[10px] text-slate-500 mt-0.5">Total capacity tokens</div>
                                        </div>

                                        <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800">
                                            <div class="text-[10px] text-slate-500 uppercase font-semibold">Max Completion Out</div>
                                            <div class="text-xl font-mono font-bold text-violet-300 mt-1" x-text="spec && spec.max_output_tokens ? (spec.max_output_tokens / 1000).toLocaleString() + 'k' : '-'"></div>
                                            <div class="text-[10px] text-slate-500 mt-0.5">Per-request limit</div>
                                        </div>
                                    </div>

                                    <!-- Prompt Caching Rates -->
                                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-3">
                                        <div class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                            <span>Prompt Caching Architecture Rates (Per 1 Million Tokens)</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                            <div class="p-3 rounded-lg bg-slate-950/60 border border-slate-800">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Cache Read Rate</div>
                                                <div class="text-lg font-mono font-bold text-emerald-400 mt-1" x-text="spec && spec.input_cache_read_cost_per_1m !== null ? '$' + spec.input_cache_read_cost_per_1m.toFixed(3) : 'Not Enabled'"></div>
                                                <div class="text-[10px] text-slate-500 mt-0.5">Discount on cached token hits</div>
                                            </div>

                                            <div class="p-3 rounded-lg bg-slate-950/60 border border-slate-800">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Cache Write Rate (Initial)</div>
                                                <div class="text-lg font-mono font-bold text-slate-200 mt-1" x-text="spec && spec.input_cache_write_cost_per_1m !== null ? '$' + spec.input_cache_write_cost_per_1m.toFixed(3) : 'Standard In Rate'"></div>
                                                <div class="text-[10px] text-slate-500 mt-0.5">Cost to store prefix in memory</div>
                                            </div>

                                            <div class="p-3 rounded-lg bg-slate-950/60 border border-slate-800">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Cache Write (1-Hour Retention)</div>
                                                <div class="text-lg font-mono font-bold text-slate-200 mt-1" x-text="spec && spec.input_cache_write_1h_cost_per_1m !== null ? '$' + spec.input_cache_write_1h_cost_per_1m.toFixed(3) : 'N/A'"></div>
                                                <div class="text-[10px] text-slate-500 mt-0.5">Extended 1hr TTL caching</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Multimodal & Grounding Rates -->
                                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-3">
                                        <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span>Multimodal, Grounding & Reasoning Surcharges</span>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 text-xs">
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Internal Reasoning / 1M</div>
                                                <div class="font-mono font-bold text-white mt-1" x-text="spec && spec.internal_reasoning_cost_per_1m !== null ? '$' + spec.internal_reasoning_cost_per_1m.toFixed(3) : 'Included'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Web Search / Query</div>
                                                <div class="font-mono font-bold text-white mt-1" x-text="spec && spec.web_search_cost !== null ? '$' + spec.web_search_cost.toFixed(4) : 'N/A'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Image In / Image</div>
                                                <div class="font-mono font-bold text-white mt-1" x-text="spec && spec.image_input_cost !== null ? '$' + spec.image_input_cost.toFixed(4) : 'N/A'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Image Out / Image</div>
                                                <div class="font-mono font-bold text-white mt-1" x-text="spec && spec.image_output_cost !== null ? '$' + spec.image_output_cost.toFixed(4) : 'N/A'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Audio In / 1M</div>
                                                <div class="font-mono font-bold text-white mt-1" x-text="spec && spec.audio_input_cost_per_1m !== null ? '$' + spec.audio_input_cost_per_1m.toFixed(2) : 'N/A'"></div>
                                            </div>
                                            <div class="p-2.5 rounded-lg bg-slate-950/60 border border-slate-800">
                                                <div class="text-[10px] text-slate-500 uppercase font-semibold">Audio Out / 1M</div>
                                                <div class="font-mono font-bold text-white mt-1" x-text="spec && spec.audio_output_cost_per_1m !== null ? '$' + spec.audio_output_cost_per_1m.toFixed(2) : 'N/A'"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tiered Overrides -->
                                    <template x-if="spec && spec.pricing_overrides && spec.pricing_overrides.length">
                                        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-2">
                                            <div class="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Volume Discount Tier Thresholds</div>
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-xs text-left">
                                                    <thead>
                                                        <tr class="text-slate-400 border-b border-slate-800">
                                                            <th class="py-1.5 px-3">Min Tokens</th>
                                                            <th class="py-1.5 px-3">Discounted Prompt Price</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-800/80 font-mono">
                                                        <template x-for="ov in spec.pricing_overrides" :key="ov.min_prompt_tokens">
                                                            <tr>
                                                                <td class="py-1.5 px-3 text-slate-300" x-text="(ov.min_prompt_tokens || 0).toLocaleString() + '+'"></td>
                                                                <td class="py-1.5 px-3 text-emerald-400 font-bold" x-text="'$' + ((ov.prompt || 0) * 1000000).toFixed(3) + ' / 1M'"></td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- ================= TAB 3: BENCHMARKS & ARABIC ================= -->
                                <div x-show="activeTab === 'benchmarks'" class="space-y-5">
                                    <!-- Arabic LLM Benchmark Box -->
                                    <div class="p-5 rounded-2xl border border-indigo-500/30 bg-gradient-to-br from-indigo-950/20 via-slate-900/90 to-slate-900/60 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Arabic Tokenization & Cost Efficiency Metrics</h4>
                                            </div>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-950 border border-indigo-500/40 text-indigo-300">MENA Operational Benchmark</span>
                                        </div>

                                        <template x-if="benchmarkArabic">
                                            <div class="space-y-3">
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                    <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800">
                                                        <div class="text-[10px] text-slate-400 font-semibold uppercase">Arabic Token Density</div>
                                                        <div class="text-xl font-mono font-bold text-emerald-400 mt-1" x-text="benchmarkArabic.arabic_tokens_per_1k_words.toLocaleString() + ' tok'"></div>
                                                        <div class="text-[10px] text-slate-500 mt-0.5">Tokens required per 1,000 Arabic words</div>
                                                    </div>

                                                    <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800">
                                                        <div class="text-[10px] text-slate-400 font-semibold uppercase">Token Inflation Ratio</div>
                                                        <div class="text-xl font-mono font-bold text-amber-400 mt-1" x-text="benchmarkArabic.token_inflation_ratio.toFixed(2) + 'x'"></div>
                                                        <div class="text-[10px] text-slate-500 mt-0.5">Overhead multiplier vs English baseline</div>
                                                    </div>

                                                    <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800">
                                                        <div class="text-[10px] text-slate-400 font-semibold uppercase">Effective Arabic Cost</div>
                                                        <div class="text-xl font-mono font-bold text-sky-400 mt-1" x-text="'$' + benchmarkArabic.effective_arabic_cost_per_1k_words.toFixed(4)"></div>
                                                        <div class="text-[10px] text-slate-500 mt-0.5">Real cost per 1,000 Arabic words</div>
                                                    </div>
                                                </div>

                                                <div class="p-3 rounded-xl bg-indigo-950/30 border border-indigo-500/20 text-xs text-indigo-200 leading-relaxed">
                                                    💡 <strong>Insight:</strong> This model exhibits a <strong x-text="benchmarkArabic.token_inflation_ratio.toFixed(2) + 'x'"></strong> inflation multiplier on Arabic text. Tokenizer efficiency directly impacts inference latency and API expenditure for Arabic conversational interfaces.
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="!benchmarkArabic">
                                            <div class="text-center py-6 text-xs text-slate-500 bg-slate-950/40 rounded-xl border border-slate-800">
                                                No localized Arabic efficiency metrics registered yet for this model.
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Artificial Analysis General Intelligence -->
                                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-3">
                                        <div class="text-[11px] font-bold text-sky-400 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                            <span>Artificial Analysis Intelligence & Coding Indices</span>
                                        </div>

                                        <template x-if="benchmarkGeneral">
                                            <div class="space-y-3">
                                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                                    <div class="p-3 rounded-lg bg-slate-950/60 border border-slate-800 text-center">
                                                        <div class="text-[10px] text-slate-500 uppercase font-semibold">General Intelligence</div>
                                                        <div class="text-xl font-bold font-mono text-indigo-400 mt-1" x-text="benchmarkGeneral.intelligence_index !== null ? benchmarkGeneral.intelligence_index.toFixed(1) : '-'"></div>
                                                    </div>

                                                    <div class="p-3 rounded-lg bg-slate-950/60 border border-slate-800 text-center">
                                                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Coding Index</div>
                                                        <div class="text-xl font-bold font-mono text-emerald-400 mt-1" x-text="benchmarkGeneral.coding_index !== null ? benchmarkGeneral.coding_index.toFixed(1) : '-'"></div>
                                                    </div>

                                                    <div class="p-3 rounded-lg bg-slate-950/60 border border-slate-800 text-center">
                                                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Agentic Work Index</div>
                                                        <div class="text-xl font-bold font-mono text-purple-400 mt-1" x-text="benchmarkGeneral.agentic_index !== null ? benchmarkGeneral.agentic_index.toFixed(1) : '-'"></div>
                                                    </div>

                                                    <div class="p-3 rounded-lg bg-slate-950/60 border border-slate-800 text-center">
                                                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Chatbot Arena Elo</div>
                                                        <div class="text-xl font-bold font-mono text-amber-400 mt-1" x-text="benchmarkGeneral.elo_rating !== null ? Math.round(benchmarkGeneral.elo_rating) : '-'"></div>
                                                    </div>
                                                </div>

                                                <!-- Academic Benchmarks -->
                                                <div class="grid grid-cols-3 gap-2.5 pt-1 text-xs">
                                                    <div class="p-2.5 rounded-lg bg-slate-950/40 border border-slate-800/80">
                                                        <div class="text-[10px] text-slate-500 uppercase">MMLU Score</div>
                                                        <div class="text-sm font-bold text-white mt-0.5" x-text="benchmarkGeneral.mmlu_score !== null ? benchmarkGeneral.mmlu_score.toFixed(1) + '%' : 'N/A'"></div>
                                                    </div>
                                                    <div class="p-2.5 rounded-lg bg-slate-950/40 border border-slate-800/80">
                                                        <div class="text-[10px] text-slate-500 uppercase">Code Eval (HumanEval)</div>
                                                        <div class="text-sm font-bold text-white mt-0.5" x-text="benchmarkGeneral.code_eval_score !== null ? benchmarkGeneral.code_eval_score.toFixed(1) + '%' : 'N/A'"></div>
                                                    </div>
                                                    <div class="p-2.5 rounded-lg bg-slate-950/40 border border-slate-800/80">
                                                        <div class="text-[10px] text-slate-500 uppercase">GSM8K Math Score</div>
                                                        <div class="text-sm font-bold text-white mt-0.5" x-text="benchmarkGeneral.math_score !== null ? benchmarkGeneral.math_score.toFixed(1) + '%' : 'N/A'"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="!benchmarkGeneral">
                                            <div class="text-center py-6 text-xs text-slate-500 bg-slate-950/40 rounded-xl border border-slate-800">
                                                No artificial analysis evaluation benchmark scores logged for this model yet.
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- ================= TAB 4: HOSTING OFFERINGS ================= -->
                                <div x-show="activeTab === 'offerings'" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                                <span>Hosting Endpoints & Rates Matrix</span>
                                                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-[11px] font-mono text-slate-300" x-text="offerings.length"></span>
                                            </h4>
                                            <div class="text-[11px] text-slate-500">Sorted by lowest input price per million tokens</div>
                                        </div>
                                    </div>

                                    <template x-if="offerings.length === 0">
                                        <div class="text-center py-10 text-xs text-slate-500 bg-slate-900/40 rounded-xl border border-slate-800">
                                            No external third-party inference offerings registered for this model yet.
                                        </div>
                                    </template>

                                    <div class="space-y-3">
                                        <template x-for="off in offerings" :key="off.id">
                                            <div class="p-3.5 rounded-xl bg-slate-900/70 border border-slate-800 hover:border-slate-700 transition-colors">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-bold text-white" x-text="off.provider_name"></span>
                                                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 font-mono text-slate-400 uppercase" x-text="off.host_type"></span>
                                                        <span x-show="off.is_cheapest" class="text-[10px] px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 border border-emerald-500/30 font-semibold">Cheapest</span>
                                                        <span x-show="off.is_official_direct" class="text-[10px] px-2 py-0.5 rounded bg-purple-950 text-purple-300 border border-purple-500/30 font-semibold">Official Direct</span>
                                                    </div>
                                                    <div class="text-xs font-mono font-bold text-emerald-400" x-text="'$' + off.input_cost_per_1m.toFixed(3) + ' / 1M'"></div>
                                                </div>

                                                <div class="mt-3 grid grid-cols-4 gap-2 text-[11px] font-mono">
                                                    <div class="p-2 rounded-lg bg-slate-950/60">
                                                        <div class="text-[10px] text-slate-500">Input / 1M</div>
                                                        <div class="text-emerald-400 font-semibold" x-text="'$' + off.input_cost_per_1m.toFixed(3)"></div>
                                                        <div x-show="off.batch_input_cost_per_1m > 0" class="text-[10px] text-slate-500" x-text="'batch $' + off.batch_input_cost_per_1m.toFixed(3)"></div>
                                                    </div>
                                                    <div class="p-2 rounded-lg bg-slate-950/60">
                                                        <div class="text-[10px] text-slate-500">Output / 1M</div>
                                                        <div class="text-sky-400 font-semibold" x-text="'$' + off.output_cost_per_1m.toFixed(3)"></div>
                                                        <div x-show="off.batch_output_cost_per_1m > 0" class="text-[10px] text-slate-500" x-text="'batch $' + off.batch_output_cost_per_1m.toFixed(3)"></div>
                                                    </div>
                                                    <div class="p-2 rounded-lg bg-slate-950/60">
                                                        <div class="text-[10px] text-slate-500">Cache Read</div>
                                                        <div class="text-slate-300" x-text="off.cache_read_cost_per_1m > 0 ? '$' + off.cache_read_cost_per_1m.toFixed(3) : '-'"></div>
                                                    </div>
                                                    <div class="p-2 rounded-lg bg-slate-950/60">
                                                        <div class="text-[10px] text-slate-500">Latency / TPS</div>
                                                        <div class="text-violet-300" x-text="(off.ttft_ms > 0 ? off.ttft_ms + 'ms' : '-') + ' / ' + (off.tokens_per_sec > 0 ? off.tokens_per_sec + ' tps' : '-')"></div>
                                                    </div>
                                                </div>

                                                <div class="mt-2 text-[10px] font-mono text-slate-500 truncate" x-text="'Endpoint: ' + off.endpoint_key"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- ================= TAB 5: RAW DATABASE RECORD (JSON) ================= -->
                                <div x-show="activeTab === 'raw_db'" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Live Database Properties & Schema Fields</h4>
                                            <div class="text-[11px] text-slate-500">Complete raw attributes from tables: <code>models</code>, <code>model_specs</code>, <code>benchmarks_general</code>, <code>benchmarks_arabic</code>, and <code>speed_telemetry</code></div>
                                        </div>
                                        <button @click="copyRawJson()" class="px-3 py-1.5 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/40 border border-indigo-500/30 text-indigo-300 text-xs font-semibold transition-colors flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                            <span x-text="copied ? 'Copied to Clipboard!' : 'Copy Complete JSON'"></span>
                                        </button>
                                    </div>

                                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800/80 overflow-x-auto max-h-[500px] overflow-y-auto">
                                        <pre class="font-mono text-xs text-indigo-200/90 whitespace-pre leading-relaxed" x-text="JSON.stringify(rawDb, null, 2)"></pre>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>

                    <!-- Drawer Footer -->
                    <div class="p-4 border-t border-slate-800 bg-slate-900/60 flex items-center justify-between">
                        <template x-if="selectedModel && selectedModel.show_url">
                            <a :href="selectedModel.show_url" class="text-xs text-indigo-400 hover:underline flex items-center gap-1">
                                <span>Open Full Screen Profile Page</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </a>
                        </template>
                        <template x-if="!selectedModel || !selectedModel.show_url">
                            <span></span>
                        </template>

                        <button @click="drawerOpen = false" class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-white transition-colors">
                            Close Inspector
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
