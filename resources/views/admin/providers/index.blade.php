@extends('layouts.admin')

@section('title', 'AI Providers Directory')
@section('page_title', 'AI Providers & Inference Hosts')

@section('content')
<div class="space-y-6">
    <!-- Header Filter & Search -->
    <div class="glass-card p-4 rounded-2xl border border-slate-800/80 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-white">Global AI Providers & Hosts</h2>
            <p class="text-xs text-slate-400">Directory of 80+ foundational creators and inference hosting clouds</p>
        </div>

        <form method="GET" action="{{ route('admin.providers.index') }}" class="flex items-center gap-2">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search providers..."
                       class="pl-9 pr-3 py-2 rounded-xl bg-slate-900 border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none w-64">
                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <button type="submit" class="py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors">
                Search
            </button>
            @if($search)
                <a href="{{ route('admin.providers.index') }}" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Providers Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse($providers as $provider)
            <div class="glass-card p-5 rounded-2xl border border-slate-800/80 hover:border-indigo-500/30 transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500/20 to-violet-500/20 border border-indigo-500/30 flex items-center justify-center font-bold text-indigo-400 text-base shadow-sm">
                            {{ strtoupper(substr($provider->name, 0, 1)) }}
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-slate-900 border border-slate-800 text-[11px] font-mono font-medium text-slate-300">
                            {{ $provider->models_count }} model{{ $provider->models_count != 1 ? 's' : '' }}
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-white group-hover:text-indigo-300 transition-colors">{{ $provider->name }}</h3>
                    <div class="text-[11px] font-mono text-slate-500 mt-0.5 truncate">{{ $provider->slug }}</div>

                    @if($provider->website)
                        <div class="mt-2">
                            <a href="{{ $provider->website }}" target="_blank" rel="noopener noreferrer" 
                               class="text-[11px] text-indigo-400/80 hover:text-indigo-300 flex items-center gap-1 truncate">
                                <span class="truncate">{{ str_replace(['https://', 'http://', 'www.'], '', $provider->website) }}</span>
                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                        </div>
                    @endif
                </div>

                <div class="mt-5 pt-3 border-t border-slate-800/80 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Catalog items</span>
                    <a href="{{ route('admin.models.index', ['provider_id' => $provider->id]) }}" 
                       class="px-2.5 py-1 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-500/30 text-indigo-300 text-xs font-semibold transition-colors flex items-center gap-1">
                        <span>View Models</span>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-slate-400 glass-card rounded-2xl">
                No providers match the search query "{{ $search }}".
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($providers->hasPages())
        <div class="p-4 glass-card rounded-2xl border border-slate-800/80">
            {{ $providers->links() }}
        </div>
    @endif
</div>
@endsection
