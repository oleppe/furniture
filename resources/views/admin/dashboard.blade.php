@extends('layouts.admin')

@section('title', 'Admin Overview')
@section('page_title', 'Intelligence & System Overview')

@section('content')
<div class="space-y-8">
    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: AI Models -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 relative overflow-hidden group hover:border-indigo-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">AI Models Synced</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-3">
                <div class="text-3xl font-extrabold text-white tracking-tight">{{ number_format($totalModels) }}</div>
                <div class="text-xs font-medium text-emerald-400 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $activeModels }} active
                </div>
            </div>
            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
                <span>Featured: <strong class="text-slate-200">{{ $featuredModels }}</strong></span>
                <a href="{{ route('admin.models.index') }}" class="text-indigo-400 hover:text-indigo-300 font-medium">Browse &rarr;</a>
            </div>
        </div>

        <!-- Card 2: Providers -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 relative overflow-hidden group hover:border-violet-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">AI Providers</span>
                <div class="w-9 h-9 rounded-xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center text-violet-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-3">
                <div class="text-3xl font-extrabold text-white tracking-tight">{{ number_format($totalProviders) }}</div>
                <div class="text-xs font-medium text-slate-400">Global & Fast Hosts</div>
            </div>
            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
                <span>Top: <strong class="text-slate-200">{{ $topProviders->first()?->name ?? 'OpenAI' }}</strong></span>
                <a href="{{ route('admin.providers.index') }}" class="text-violet-400 hover:text-violet-300 font-medium">Directory &rarr;</a>
            </div>
        </div>

        <!-- Card 3: Offerings -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 relative overflow-hidden group hover:border-sky-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Hosting Offerings</span>
                <div class="w-9 h-9 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-3">
                <div class="text-3xl font-extrabold text-white tracking-tight">{{ number_format($totalOfferings) }}</div>
                <div class="text-xs font-medium text-sky-400">Priced Endpoints</div>
            </div>
            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
                <span>Across clouds & gateways</span>
                <span class="text-emerald-400 font-medium">Live sync</span>
            </div>
        </div>

        <!-- Card 4: Users -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 relative overflow-hidden group hover:border-emerald-500/40 transition-all duration-300">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Platform Accounts</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-3">
                <div class="text-3xl font-extrabold text-white tracking-tight">{{ number_format($totalUsers) }}</div>
                <div class="text-xs font-medium text-purple-400">{{ $adminCount }} admin{{ $adminCount > 1 ? 's' : '' }}</div>
            </div>
            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between">
                <span>Role Protected</span>
                <a href="{{ route('admin.users.index') }}" class="text-emerald-400 hover:text-emerald-300 font-medium">Manage &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800/80 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </div>
            <div>
                <div class="text-sm font-bold text-white">Quick Administrative Actions</div>
                <div class="text-xs text-slate-400">Instant shortcuts to manage users, inspect catalog pricing, or trigger data ingestion.</div>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.models.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700/60 transition-colors">
                Inspect AI Models
            </a>
            <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700/60 transition-colors">
                Add New User
            </a>
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
                        .then(data => { syncing = false; syncResult = data; })
                        .catch(err => { syncing = false; syncResult = { success: false, message: err.message }; });
                    "
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 transition-colors">
                Run Pricing Sync
            </button>
        </div>
    </div>

    <!-- Two-Column Grid: Recent Models & Top Providers -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Synced Models (2 Cols) -->
        <div class="lg:col-span-2 glass-card rounded-2xl border border-slate-800/80 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-base font-bold text-white">Recently Synced AI Models</h2>
                    <p class="text-xs text-slate-400">Latest additions to the canonical catalog with baseline pricing</p>
                </div>
                <a href="{{ route('admin.models.index') }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">View All {{ $totalModels }} &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800/80 pb-2">
                            <th class="pb-3 font-semibold">Model Name</th>
                            <th class="pb-3 font-semibold">Provider</th>
                            <th class="pb-3 font-semibold">Context Window</th>
                            <th class="pb-3 font-semibold">Input / 1M</th>
                            <th class="pb-3 font-semibold">Output / 1M</th>
                            <th class="pb-3 font-semibold text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @foreach ($recentModels as $model)
                            <tr class="group hover:bg-slate-900/40 transition-colors">
                                <td class="py-3 font-semibold text-slate-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        @if($model->is_featured)
                                            <span class="text-amber-400" title="Featured Model">★</span>
                                        @endif
                                        <span>{{ $model->name }}</span>
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-500">{{ $model->slug }}</div>
                                </td>
                                <td class="py-3 text-slate-300">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-900 border border-slate-800 text-[11px] font-medium text-slate-300">
                                        {{ $model->provider?->name ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td class="py-3 text-slate-300 font-mono">
                                    @if($model->latestSpec?->context_window)
                                        {{ number_format($model->latestSpec->context_window / 1000, 0) }}k
                                    @else
                                        <span class="text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="py-3 text-emerald-400 font-mono">
                                    @if($model->latestSpec?->input_cost_per_1m !== null)
                                        ${{ number_format($model->latestSpec->input_cost_per_1m, 2) }}
                                    @else
                                        <span class="text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="py-3 text-sky-400 font-mono">
                                    @if($model->latestSpec?->output_cost_per_1m !== null)
                                        ${{ number_format($model->latestSpec->output_cost_per_1m, 2) }}
                                    @else
                                        <span class="text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $model->is_active ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/30' : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
                                        {{ $model->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Providers & Lowest Price Spotter (1 Col) -->
        <div class="space-y-6">
            <!-- Top Providers -->
            <div class="glass-card rounded-2xl border border-slate-800/80 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-white">Top Providers by Models</h3>
                    <a href="{{ route('admin.providers.index') }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">All &rarr;</a>
                </div>
                <div class="space-y-3">
                    @foreach($topProviders as $provider)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-900/60 border border-slate-800/80">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center font-bold text-indigo-400 text-xs">
                                    {{ strtoupper(substr($provider->name, 0, 1)) }}
                                </div>
                                <span class="text-xs font-semibold text-slate-900 dark:text-white">{{ $provider->name }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 text-[11px] font-mono font-medium">
                                {{ $provider->models_count }} models
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Cheapest Hosting Spotter -->
            <div class="glass-card rounded-2xl border border-slate-800/80 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-white">Ultra-Low Cost Inference</h3>
                    <span class="text-[10px] font-semibold text-emerald-400 bg-emerald-950/60 px-2 py-0.5 rounded-full border border-emerald-500/30">Top Deals</span>
                </div>
                <div class="space-y-2.5">
                    @foreach($cheapestOfferings as $offering)
                        <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-center justify-between">
                            <div class="truncate mr-2">
                                <div class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ $offering->aiModel?->name ?? 'Model' }}</div>
                                <div class="text-[11px] text-slate-400 truncate">Via {{ $offering->provider?->name ?? 'Host' }} ({{ $offering->host_type }})</div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-xs font-mono font-bold text-emerald-400">${{ number_format($offering->input_cost_per_1m, 3) }}/1M</div>
                                <div class="text-[10px] font-mono text-slate-500">out: ${{ number_format($offering->output_cost_per_1m, 3) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
