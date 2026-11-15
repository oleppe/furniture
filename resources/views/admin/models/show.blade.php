@extends('layouts.admin')

@section('title', $model->name . ' - AI Model Profile')
@section('page_title', $model->name)

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'overview', copied: false, copyJson() { navigator.clipboard.writeText(document.getElementById('raw-json-data').innerText); this.copied = true; setTimeout(() => this.copied = false, 2000); } }">
    <!-- Breadcrumbs & Navigation -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Admin</a>
            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('admin.models.index') }}" class="hover:text-white transition-colors">AI Models & Pricing</a>
            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-indigo-400 font-medium">{{ $model->name }}</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.models.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-slate-300 text-xs font-semibold transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Catalog</span>
            </a>
            
            <form method="POST" action="{{ route('admin.models.toggle-active', $model) }}">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-colors {{ $model->is_active ? 'bg-emerald-950/40 border-emerald-500/30 text-emerald-300 hover:bg-emerald-900/60' : 'bg-rose-950/40 border-rose-500/30 text-rose-300 hover:bg-rose-900/60' }}">
                    {{ $model->is_active ? 'Active' : 'Inactive' }} (Toggle)
                </button>
            </form>

            <form method="POST" action="{{ route('admin.models.toggle-featured', $model) }}">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-colors {{ $model->is_featured ? 'bg-amber-950/40 border-amber-500/30 text-amber-300 hover:bg-amber-900/60' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white' }}">
                    {{ $model->is_featured ? '★ Featured' : '☆ Not Featured' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Header Hero Profile Card -->
    <div class="glass-card p-6 rounded-2xl border border-slate-800/80 shadow-xl space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-sky-400 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-indigo-600/30 shrink-0">
                    {{ substr($model->name, 0, 2) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $model->name }}</h1>
                        @if($model->is_open_source)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-950/80 border border-emerald-500/30 text-emerald-400">Open Source</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-800 border border-slate-700 text-slate-300">Proprietary</span>
                        @endif

                        @if($model->is_featured)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-950/80 border border-amber-500/30 text-amber-400">★ Featured</span>
                        @endif

                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $model->is_active ? 'bg-emerald-950/80 border border-emerald-500/30 text-emerald-400' : 'bg-rose-950/80 border border-rose-500/30 text-rose-400' }}">
                            {{ $model->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400 mt-1 font-mono">
                        <span>Slug: <strong class="text-slate-200">{{ $model->slug }}</strong></span>
                        @if($model->canonical_slug)
                            <span>Canonical: <strong class="text-slate-300">{{ $model->canonical_slug }}</strong></span>
                        @endif
                        <span>Provider: 
                            <a href="{{ route('admin.models.index', ['provider_id' => $model->provider_id]) }}" class="text-indigo-400 hover:underline">
                                {{ $model->provider?->name ?? 'Unknown' }}
                            </a>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Metrics Header -->
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 rounded-xl bg-slate-900/90 border border-slate-800 text-center">
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Context Window</div>
                    <div class="text-lg font-mono font-bold text-indigo-300">
                        {{ $model->latestSpec?->context_window ? number_format($model->latestSpec->context_window / 1000, 0) . 'k' : 'N/A' }}
                    </div>
                </div>
                <div class="px-4 py-2 rounded-xl bg-slate-900/90 border border-slate-800 text-center">
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Prompt / 1M</div>
                    <div class="text-lg font-mono font-bold text-emerald-400">
                        {{ $model->latestSpec?->input_cost_per_1m !== null ? '$' . number_format($model->latestSpec->input_cost_per_1m, 2) : 'N/A' }}
                    </div>
                </div>
                <div class="px-4 py-2 rounded-xl bg-slate-900/90 border border-slate-800 text-center">
                    <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Completion / 1M</div>
                    <div class="text-lg font-mono font-bold text-sky-400">
                        {{ $model->latestSpec?->output_cost_per_1m !== null ? '$' . number_format($model->latestSpec->output_cost_per_1m, 2) : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        @if($model->description)
            <p class="text-xs text-slate-300 leading-relaxed pt-2 border-t border-slate-800/80">
                {{ $model->description }}
            </p>
        @endif
    </div>

    <!-- Tabbed Navigation Bar -->
    <div class="flex items-center gap-1 border-b border-slate-800 overflow-x-auto pb-1">
        <button @click="activeTab = 'overview'" 
                :class="activeTab === 'overview' ? 'text-indigo-400 border-indigo-500 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900'"
                class="px-4 py-2 text-xs font-semibold rounded-t-xl border-b-2 transition-all flex items-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Overview & Specifications</span>
        </button>

        <button @click="activeTab = 'pricing'" 
                :class="activeTab === 'pricing' ? 'text-indigo-400 border-indigo-500 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900'"
                class="px-4 py-2 text-xs font-semibold rounded-t-xl border-b-2 transition-all flex items-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Pricing & Caching Matrix</span>
        </button>

        <button @click="activeTab = 'benchmarks'" 
                :class="activeTab === 'benchmarks' ? 'text-indigo-400 border-indigo-500 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900'"
                class="px-4 py-2 text-xs font-semibold rounded-t-xl border-b-2 transition-all flex items-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            <span>Benchmarks & Arabic Metrics</span>
        </button>

        <button @click="activeTab = 'offerings'" 
                :class="activeTab === 'offerings' ? 'text-indigo-400 border-indigo-500 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900'"
                class="px-4 py-2 text-xs font-semibold rounded-t-xl border-b-2 transition-all flex items-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            <span>Hosting Offerings ({{ $model->offerings->count() }})</span>
        </button>

        <button @click="activeTab = 'raw_db'" 
                :class="activeTab === 'raw_db' ? 'text-indigo-400 border-indigo-500 bg-indigo-500/10' : 'text-slate-400 hover:text-slate-200 border-transparent hover:bg-slate-900'"
                class="px-4 py-2 text-xs font-semibold rounded-t-xl border-b-2 transition-all flex items-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zM9 9h6M9 13h6M9 17h3" /></svg>
            <span>All Database Properties (Raw JSON)</span>
        </button>
    </div>

    <!-- TAB 1: OVERVIEW & SPECIFICATIONS -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        <!-- Identification & External IDs Grid -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                <span>Core Identifiers & Source Registry References</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">OpenRouter ID</div>
                    <div class="font-mono text-white mt-1 break-all">{{ $model->openrouter_id ?? 'N/A' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">LiteLLM Identifier</div>
                    <div class="font-mono text-white mt-1 break-all">{{ $model->litellm_id ?? 'N/A' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Hugging Face ID</div>
                    <div class="font-mono text-white mt-1 break-all">{{ $model->hugging_face_id ?? 'Not Hosted on HF' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Release Date</div>
                    <div class="text-white mt-1 font-medium">{{ $model->release_date?->format('F d, Y') ?? 'N/A' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Knowledge Cutoff</div>
                    <div class="text-white mt-1 font-medium">{{ $model->knowledge_cutoff ?? 'Unspecified' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Retirement / Expiration</div>
                    <div class="text-white mt-1 font-medium">{{ $model->expiration_date ?? 'No Planned Sunset' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Provider Website</div>
                    <div class="mt-1">
                        @if($model->provider?->website)
                            <a href="{{ $model->provider->website }}" target="_blank" rel="noopener noreferrer" class="text-indigo-400 hover:underline flex items-center gap-1">
                                <span>{{ $model->provider->website }}</span>
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                        @else
                            <span class="text-slate-500">N/A</span>
                        @endif
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">API Details Link</div>
                    <div class="mt-1">
                        @if($model->openrouter_details_url)
                            <span class="font-mono text-slate-300 text-[11px] break-all">{{ $model->openrouter_details_url }}</span>
                        @else
                            <span class="text-slate-500">N/A</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Architecture, Modalities & Tokenizer -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                <span>Architecture, Modalities & Tokenizer Specifications</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Modality Pipeline</div>
                    <div class="font-mono text-indigo-300 font-semibold mt-1">{{ $model->architecture ?? 'text->text' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Tokenizer Architecture</div>
                    <div class="font-mono text-slate-200 mt-1">{{ $model->architecture_details['tokenizer'] ?? 'Standard' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Instruct / Prompt Format</div>
                    <div class="font-mono text-slate-200 mt-1">{{ $model->architecture_details['instruct_type'] ?? 'Standard Chat' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Content Moderation</div>
                    <div class="mt-1">
                        @if($model->latestSpec?->is_moderated)
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-950/80 border border-rose-500/30 text-rose-300">Provider Moderated</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-950/80 border border-emerald-500/30 text-emerald-300">Unfiltered / Raw</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Modality Chips -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 space-y-2">
                    <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Input Modalities Supported</div>
                    <div class="flex flex-wrap gap-1.5">
                        @php $inputs = $model->architecture_details['input_modalities'] ?? ['text']; @endphp
                        @foreach($inputs as $inMod)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-950/80 border border-indigo-500/30 text-indigo-300 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                <span class="capitalize">{{ $inMod }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 space-y-2">
                    <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Output Modalities Generated</div>
                    <div class="flex flex-wrap gap-1.5">
                        @php $outputs = $model->architecture_details['output_modalities'] ?? ['text']; @endphp
                        @foreach($outputs as $outMod)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-sky-950/80 border border-sky-500/30 text-sky-300 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                                <span class="capitalize">{{ $outMod }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Reasoning Specifications -->
        @if(!empty($model->reasoning_spec))
            <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <span>Reasoning Engine & Thinking Process Parameters</span>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Mandatory Reasoning</div>
                        <div class="font-bold text-white mt-1">{{ !empty($model->reasoning_spec['mandatory']) ? 'Yes (Enforced)' : 'No (Optional)' }}</div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Default Enabled</div>
                        <div class="font-bold text-white mt-1">{{ !empty($model->reasoning_spec['default_enabled']) ? 'Enabled' : 'Disabled' }}</div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Default Effort</div>
                        <div class="font-mono text-amber-300 font-bold uppercase mt-1">{{ $model->reasoning_spec['default_effort'] ?? 'Standard' }}</div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Token Budget Support</div>
                        <div class="font-bold text-white mt-1">{{ !empty($model->reasoning_spec['supports_max_tokens']) ? 'Supported' : 'Uncapped' }}</div>
                    </div>
                </div>

                @if(!empty($model->reasoning_spec['supported_efforts']))
                    <div class="text-xs">
                        <span class="text-slate-400 mr-2">Supported Effort Levels:</span>
                        <div class="inline-flex flex-wrap gap-1.5 mt-1">
                            @foreach($model->reasoning_spec['supported_efforts'] as $effort)
                                <span class="px-2 py-0.5 rounded bg-amber-950/60 border border-amber-500/30 text-amber-300 font-mono text-[11px] uppercase">{{ $effort }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Capabilities Matrix (15 Feature Flags) -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>Supported Capabilities & Native Feature Flags</span>
            </h3>

            @php
                $caps = $model->capabilities ?? [];
                $capList = [
                    'supports_vision' => 'Vision / Image Input',
                    'supports_reasoning' => 'Deep Reasoning',
                    'supports_prompt_caching' => 'Prompt Caching',
                    'supports_function_calling' => 'Function Calling',
                    'supports_tool_choice' => 'Explicit Tool Choice',
                    'supports_response_schema' => 'Structured Outputs (JSON)',
                    'supports_web_search' => 'Live Web Search',
                    'supports_audio_input' => 'Audio Input',
                    'supports_audio_output' => 'Audio Generation',
                    'supports_computer_use' => 'OS Computer Use',
                    'supports_pdf_input' => 'Native PDF Processing',
                    'supports_system_messages' => 'System Instruction Prompting',
                    'supports_native_streaming' => 'Native Token Streaming',
                    'supports_parallel_function_calling' => 'Parallel Tools',
                    'supports_prompt_cache_breakpoint' => 'Cache Breakpoints',
                ];
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 text-xs">
                @foreach($capList as $key => $label)
                    @php $hasCap = !empty($caps[$key]); @endphp
                    <div class="p-2.5 rounded-xl border flex items-center gap-2 {{ $hasCap ? 'bg-emerald-950/30 border-emerald-500/30 text-emerald-300' : 'bg-slate-900/30 border-slate-800/80 text-slate-500' }}">
                        @if($hasCap)
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        @else
                            <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        @endif
                        <span class="font-medium truncate">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Supported Parameters -->
        @if(!empty($model->supported_parameters))
            <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                    <span>Accepted Generation Parameters ({{ count($model->supported_parameters) }})</span>
                </h3>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($model->supported_parameters as $param)
                        <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 font-mono text-[11px] text-slate-300">
                            {{ $param }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- TAB 2: PRICING & CACHING MATRIX -->
    <div x-show="activeTab === 'pricing'" class="space-y-6">
        @php $spec = $model->latestSpec; @endphp
        <!-- Baseline & Capacity -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card p-4 rounded-xl border border-slate-800">
                <div class="text-xs text-slate-400 font-medium">Standard Prompt In / 1M</div>
                <div class="text-2xl font-bold font-mono text-emerald-400 mt-1">
                    {{ $spec?->input_cost_per_1m !== null ? '$' . number_format($spec->input_cost_per_1m, 3) : 'Free / N/A' }}
                </div>
                <div class="text-[10px] text-slate-500 mt-1">Base cost for un-cached input tokens</div>
            </div>

            <div class="glass-card p-4 rounded-xl border border-slate-800">
                <div class="text-xs text-slate-400 font-medium">Standard Completion Out / 1M</div>
                <div class="text-2xl font-bold font-mono text-sky-400 mt-1">
                    {{ $spec?->output_cost_per_1m !== null ? '$' . number_format($spec->output_cost_per_1m, 3) : 'Free / N/A' }}
                </div>
                <div class="text-[10px] text-slate-500 mt-1">Cost for output generation tokens</div>
            </div>

            <div class="glass-card p-4 rounded-xl border border-slate-800">
                <div class="text-xs text-slate-400 font-medium">Maximum Context Window</div>
                <div class="text-2xl font-bold font-mono text-indigo-300 mt-1">
                    {{ $spec?->context_window ? number_format($spec->context_window) : 'N/A' }}
                </div>
                <div class="text-[10px] text-slate-500 mt-1">Combined input + output context limit</div>
            </div>

            <div class="glass-card p-4 rounded-xl border border-slate-800">
                <div class="text-xs text-slate-400 font-medium">Max Output Per Request</div>
                <div class="text-2xl font-bold font-mono text-violet-300 mt-1">
                    {{ $spec?->max_output_tokens ? number_format($spec->max_output_tokens) : 'N/A' }}
                </div>
                <div class="text-[10px] text-slate-500 mt-1">Max tokens returned in a single completion</div>
            </div>
        </div>

        <!-- Prompt Caching Hierarchy -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <span>Prompt Caching Rates (Per 1 Million Tokens)</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Prompt Cache Read</div>
                    <div class="text-lg font-mono font-bold text-emerald-400 mt-1">
                        {{ $spec?->input_cache_read_cost_per_1m !== null ? '$' . number_format($spec->input_cache_read_cost_per_1m, 3) : 'Not Supported' }}
                    </div>
                    <div class="text-[10px] text-slate-500 mt-1">Discounted rate when context hits cache</div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Prompt Cache Write (Initial)</div>
                    <div class="text-lg font-mono font-bold text-slate-200 mt-1">
                        {{ $spec?->input_cache_write_cost_per_1m !== null ? '$' . number_format($spec->input_cache_write_cost_per_1m, 3) : 'Default In Rate' }}
                    </div>
                    <div class="text-[10px] text-slate-500 mt-1">Cost to prime and write to cache</div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Cache Write (1-Hour Retention)</div>
                    <div class="text-lg font-mono font-bold text-slate-200 mt-1">
                        {{ $spec?->input_cache_write_1h_cost_per_1m !== null ? '$' . number_format($spec->input_cache_write_1h_cost_per_1m, 3) : 'N/A' }}
                    </div>
                    <div class="text-[10px] text-slate-500 mt-1">Extended 1-hour TTL cache write rate</div>
                </div>
            </div>
        </div>

        <!-- Multimodal & Specialized Surcharges -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>Multimodal, Reasoning & Grounding Surcharges</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Internal Reasoning / 1M</div>
                    <div class="font-mono font-bold text-white mt-1">{{ $spec?->internal_reasoning_cost_per_1m !== null ? '$' . number_format($spec->internal_reasoning_cost_per_1m, 3) : 'Standard' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Web Search / Query</div>
                    <div class="font-mono font-bold text-white mt-1">{{ $spec?->web_search_cost !== null ? '$' . number_format($spec->web_search_cost, 4) : 'N/A' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Image In / Image</div>
                    <div class="font-mono font-bold text-white mt-1">{{ $spec?->image_input_cost !== null ? '$' . number_format($spec->image_input_cost, 4) : 'N/A' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Image Out / Image</div>
                    <div class="font-mono font-bold text-white mt-1">{{ $spec?->image_output_cost !== null ? '$' . number_format($spec->image_output_cost, 4) : 'N/A' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Audio In / 1M</div>
                    <div class="font-mono font-bold text-white mt-1">{{ $spec?->audio_input_cost_per_1m !== null ? '$' . number_format($spec->audio_input_cost_per_1m, 2) : 'N/A' }}</div>
                </div>

                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Audio Out / 1M</div>
                    <div class="font-mono font-bold text-white mt-1">{{ $spec?->audio_output_cost_per_1m !== null ? '$' . number_format($spec->audio_output_cost_per_1m, 2) : 'N/A' }}</div>
                </div>
            </div>
        </div>

        @if(!empty($spec?->pricing_overrides))
            <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tiered Volume Pricing Overrides</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800">
                                <th class="py-2 px-3">Min Prompt Tokens</th>
                                <th class="py-2 px-3">Discounted Prompt Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach($spec->pricing_overrides as $override)
                                <tr>
                                    <td class="py-2 px-3 font-mono text-slate-200">{{ number_format($override['min_prompt_tokens'] ?? 0) }}+</td>
                                    <td class="py-2 px-3 font-mono text-emerald-400 font-bold">${{ number_format(($override['prompt'] ?? 0) * 1000000, 3) }} / 1M</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- TAB 3: BENCHMARKS & ARABIC EFFICIENCY -->
    <div x-show="activeTab === 'benchmarks'" class="space-y-6">
        @php 
            $benchGen = $model->benchmarkGeneral;
            $benchAra = $model->benchmarkArabic;
        @endphp

        <!-- Arabic LLM Optimization Benchmark -->
        <div class="glass-card p-6 rounded-2xl border border-indigo-500/30 bg-gradient-to-br from-indigo-950/20 via-slate-900/80 to-slate-900/60 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
                    <h3 class="text-sm font-bold text-white tracking-wide">Arabic Tokenization & Cost Efficiency Metrics</h3>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-950 border border-indigo-500/40 text-indigo-300">
                    Saudi / MENA Benchmark Index
                </span>
            </div>

            @if($benchAra)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl bg-slate-900/90 border border-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Arabic Token Density</div>
                        <div class="text-2xl font-mono font-bold text-emerald-400 mt-1">
                            {{ number_format($benchAra->arabic_tokens_per_1k_words, 0) }}
                        </div>
                        <div class="text-[10px] text-slate-500 mt-1">Tokens required to represent 1,000 Arabic words</div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-900/90 border border-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Token Inflation Ratio</div>
                        <div class="text-2xl font-mono font-bold text-amber-400 mt-1">
                            {{ number_format($benchAra->token_inflation_ratio, 2) }}x
                        </div>
                        <div class="text-[10px] text-slate-500 mt-1">Multiplier compared to English token density (~1.0x baseline)</div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-900/90 border border-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Effective Arabic Cost / 1k Words</div>
                        <div class="text-2xl font-mono font-bold text-sky-400 mt-1">
                            ${{ number_format($benchAra->effective_arabic_cost_per_1k_words, 4) }}
                        </div>
                        <div class="text-[10px] text-slate-500 mt-1">Real operational expenditure for 1,000 processed Arabic words</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-indigo-950/30 border border-indigo-500/20 text-xs text-indigo-200 leading-relaxed">
                    💡 <strong>Architecture Analysis:</strong> Models with lower token inflation ratios provide significantly faster throughput and cost up to <strong>{{ number_format($benchAra->token_inflation_ratio, 1) }}x less</strong> when parsing Arabic corporate documents and chat queries.
                </div>
            @else
                <div class="p-6 text-center text-xs text-slate-500 bg-slate-900/40 rounded-xl border border-slate-800">
                    No Arabic tokenization evaluation recorded for this model yet.
                </div>
            @endif
        </div>

        <!-- Artificial Analysis Intelligence Indices -->
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                <span>Artificial Analysis Intelligence & Coding Indices</span>
            </h3>

            @if($benchGen)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-center">
                        <div class="text-[10px] text-slate-500 uppercase font-semibold">General Intelligence</div>
                        <div class="text-xl font-bold font-mono text-indigo-400 mt-1">{{ $benchGen->intelligence_index ? number_format($benchGen->intelligence_index, 1) : '-' }}</div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-center">
                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Coding Index</div>
                        <div class="text-xl font-bold font-mono text-emerald-400 mt-1">{{ $benchGen->coding_index ? number_format($benchGen->coding_index, 1) : '-' }}</div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-center">
                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Agentic Work Index</div>
                        <div class="text-xl font-bold font-mono text-purple-400 mt-1">{{ $benchGen->agentic_index ? number_format($benchGen->agentic_index, 1) : '-' }}</div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-center">
                        <div class="text-[10px] text-slate-500 uppercase font-semibold">Chatbot Arena Elo</div>
                        <div class="text-xl font-bold font-mono text-amber-400 mt-1">{{ $benchGen->elo_rating ? number_format($benchGen->elo_rating, 0) : '-' }}</div>
                    </div>
                </div>

                <!-- Academic Benchmarks -->
                <div class="grid grid-cols-3 gap-3 pt-3 border-t border-slate-800 text-xs">
                    <div class="p-3 rounded-xl bg-slate-900/40 border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase">MMLU Score</div>
                        <div class="text-base font-bold text-white mt-1">{{ $benchGen->mmlu_score ? number_format($benchGen->mmlu_score, 1) . '%' : 'N/A' }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/40 border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase">Code Eval (HumanEval)</div>
                        <div class="text-base font-bold text-white mt-1">{{ $benchGen->code_eval_score ? number_format($benchGen->code_eval_score, 1) . '%' : 'N/A' }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/40 border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase">GSM8K Math Score</div>
                        <div class="text-base font-bold text-white mt-1">{{ $benchGen->math_score ? number_format($benchGen->math_score, 1) . '%' : 'N/A' }}</div>
                    </div>
                </div>
            @else
                <div class="p-6 text-center text-xs text-slate-500 bg-slate-900/40 rounded-xl border border-slate-800">
                    No general benchmark index recorded for this model yet.
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 4: HOSTING OFFERINGS & ENDPOINTS -->
    <div x-show="activeTab === 'offerings'" class="space-y-6">
        <div class="glass-card rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl">
            <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white">Multi-Host Infrastructure Endpoints</h3>
                    <div class="text-xs text-slate-400">Comparing real-time API latency, throughput, and pricing across hosting providers</div>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-slate-800 text-xs font-mono text-slate-300">
                    {{ $model->offerings->count() }} Endpoints Available
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-900/80 text-slate-400 border-b border-slate-800">
                            <th class="py-3 px-4 font-semibold">Hosting Provider</th>
                            <th class="py-3 px-4 font-semibold">Host Type</th>
                            <th class="py-3 px-4 font-semibold">Region / Endpoint</th>
                            <th class="py-3 px-4 font-semibold font-mono">Input / 1M</th>
                            <th class="py-3 px-4 font-semibold font-mono">Output / 1M</th>
                            <th class="py-3 px-4 font-semibold font-mono">Cache Read</th>
                            <th class="py-3 px-4 font-semibold font-mono">Latency / TPS</th>
                            <th class="py-3 px-4 font-semibold text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        @forelse($model->offerings->sortBy('input_cost_per_1m') as $offering)
                            <tr class="hover:bg-slate-900/50 transition-colors">
                                <td class="py-3 px-4 font-sans font-bold text-white">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $offering->provider?->name ?? 'Unknown' }}</span>
                                        @if($offering->is_official_direct)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-950 text-purple-300 border border-purple-500/30">Official</span>
                                        @endif
                                        @if($offering->is_cheapest)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-950 text-emerald-300 border border-emerald-500/30">Cheapest</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-sans text-slate-400 uppercase text-[11px]">
                                    {{ $offering->host_type }}
                                </td>
                                <td class="py-3 px-4 text-slate-300 truncate max-w-xs text-[11px]">
                                    <div>{{ $offering->endpoint_key }}</div>
                                    @if($offering->region)
                                        <div class="text-[10px] text-slate-500">{{ $offering->region }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-emerald-400 font-bold">
                                    ${{ number_format($offering->input_cost_per_1m, 3) }}
                                    @if($offering->batch_input_cost_per_1m !== null)
                                        <div class="text-[10px] font-normal text-slate-500">batch ${{ number_format($offering->batch_input_cost_per_1m, 3) }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sky-400">
                                    ${{ number_format($offering->output_cost_per_1m, 3) }}
                                    @if($offering->batch_output_cost_per_1m !== null)
                                        <div class="text-[10px] font-normal text-slate-500">batch ${{ number_format($offering->batch_output_cost_per_1m, 3) }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-300">
                                    {{ $offering->cache_read_input_token_cost_per_1m > 0 ? '$' . number_format($offering->cache_read_input_token_cost_per_1m, 3) : '-' }}
                                </td>
                                <td class="py-3 px-4 text-violet-300">
                                    {{ ($offering->ttft_ms > 0 ? $offering->ttft_ms . 'ms' : '-') . ' / ' . ($offering->tokens_per_sec > 0 ? $offering->tokens_per_sec . ' tps' : '-') }}
                                </td>
                                <td class="py-3 px-4 text-right font-sans">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-950/70 border border-emerald-500/30 text-emerald-400">Active</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 font-sans text-slate-500">
                                    No dedicated host endpoints configured for this model yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 5: ALL DATABASE PROPERTIES (RAW JSON) -->
    <div x-show="activeTab === 'raw_db'" class="space-y-4">
        <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Raw Database Rows & Schemas</h3>
                    <div class="text-xs text-slate-500">All fields and columns saved in database tables: <code>models</code>, <code>model_specs</code>, <code>benchmarks_general</code>, <code>benchmarks_arabic</code>, and <code>speed_telemetry</code></div>
                </div>
                <button @click="copyJson()" class="px-3 py-1.5 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/40 border border-indigo-500/30 text-indigo-300 text-xs font-semibold transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                    <span x-text="copied ? 'Copied to Clipboard!' : 'Copy Complete JSON'"></span>
                </button>
            </div>

            @php
                $dump = [
                    'model' => $model->toArray(),
                    'latest_spec' => $model->latestSpec?->toArray(),
                    'benchmark_general' => $model->benchmarkGeneral?->toArray(),
                    'benchmark_arabic' => $model->benchmarkArabic?->toArray(),
                    'latest_speed' => $model->latestSpeed?->toArray(),
                ];
            @endphp

            <div class="relative bg-slate-950 p-4 rounded-xl border border-slate-800/80 overflow-x-auto max-h-[600px] overflow-y-auto">
                <pre id="raw-json-data" class="font-mono text-xs text-indigo-200/90 whitespace-pre leading-relaxed">{{ json_encode($dump, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
