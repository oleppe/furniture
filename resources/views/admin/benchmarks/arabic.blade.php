@extends('layouts.admin')

@section('title', 'Arabic AI Benchmark Lab - Model Evaluation Suite')

@section('content')
<div class="space-y-8" x-data="arabicBenchmarkLab()">
    <!-- Top Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-400 mb-1">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Deterministic Arabic AI Evaluation Suite
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white flex items-center gap-3">
                <span>Arabic AI Benchmark Lab</span>
            </h1>
            <p class="text-sm text-slate-400 mt-1 max-w-3xl">
                Standardized evaluation suite for LLMs on Arabic language tasks via OpenRouter. Deterministically benchmark tokenizer efficiency (Tokens/Word), inference latency, generation speed (Tokens/Sec), deterministic reasoning accuracy (5/5 score), and exact cost in USD.
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <template x-if="hasConfiguredKey">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-medium">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>OPENROUTER_API Active in .env</span>
                </div>
            </template>
            <template x-if="!hasConfiguredKey">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-medium">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>OPENROUTER_API key not found in .env</span>
                </div>
            </template>
        </div>
    </div>

    <!-- Quick Analytics Stats Bar -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Tests Executed</div>
            <div class="text-2xl font-bold font-mono text-white mt-1">{{ number_format($stats['total_runs']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Total benchmark runs saved</div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-medium text-slate-400 uppercase tracking-wider">Avg Tokenizer Consumption</div>
            <div class="text-2xl font-bold font-mono text-emerald-400 mt-1">{{ $stats['avg_tokens_per_word'] }} <span class="text-xs font-sans text-slate-400">tokens / word</span></div>
            <div class="text-[11px] text-slate-400 mt-1">Avg Tokens per Arabic Word</div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-medium text-slate-400 uppercase tracking-wider">Avg Generation Speed</div>
            <div class="text-2xl font-bold font-mono text-sky-400 mt-1">{{ $stats['avg_tokens_per_sec'] }} <span class="text-xs font-sans text-slate-400">tokens / sec</span></div>
            <div class="text-[11px] text-slate-400 mt-1">Avg Tokens / Second</div>
        </div>

        <div class="glass-card rounded-2xl p-4 border border-slate-800">
            <div class="text-xs font-medium text-slate-400 uppercase tracking-wider">Perfect Scores (5/5)</div>
            <div class="text-2xl font-bold font-mono text-amber-400 mt-1">{{ number_format($stats['perfect_score_runs']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Deterministic 5/5 score evaluations</div>
        </div>
    </div>

    <!-- Main Grid: Configuration Form & Execution Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Column: Test Configuration Form (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="glass-card rounded-2xl p-6 border border-slate-800 relative overflow-hidden shadow-xl">
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex items-center justify-between pb-4 border-b border-slate-800/80 mb-5">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        <span>Benchmark Configuration</span>
                    </h2>
                    <span class="text-xs px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-mono font-medium">OpenRouter API</span>
                </div>

                <form @submit.prevent="runBenchmark()" novalidate class="space-y-5">
                    <!-- Searchable Model Selection Combobox -->
                    <div class="relative" @click.away="isDropdownOpen = false">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Choose Model from Database
                            </label>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono" x-text="models.length + ' models available'"></span>
                        </div>

                        <!-- Selected Model Card / Chip -->
                        <div x-show="selectedModel" x-cloak class="mb-2.5 p-3.5 rounded-xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-500/40 flex items-center justify-between gap-3 shadow-sm dark:shadow-inner">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="selectedModel ? selectedModel.name : ''"></span>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/20 dark:text-indigo-300 dark:border-indigo-500/30" x-text="selectedModel ? selectedModel.provider : ''"></span>
                                </div>
                                <div class="text-xs font-mono text-indigo-600 dark:text-indigo-400 truncate mt-0.5 font-medium" x-text="selectedModel ? selectedModel.openrouter_id : ''"></div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-400 mt-1 flex items-center gap-2 font-mono">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Pricing: $<span x-text="selectedModel ? selectedModel.input_cost.toFixed(2) : '0.00'"></span> in / $<span x-text="selectedModel ? selectedModel.output_cost.toFixed(2) : '0.00'"></span> out per 1M</span>
                                    <template x-if="selectedModel && selectedModel.context_window">
                                        <span>• <span x-text="selectedModel.context_window + ' ctx'"></span></span>
                                    </template>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" @click="isDropdownOpen = !isDropdownOpen" 
                                        class="px-2.5 py-1 text-xs rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-sm transition-colors cursor-pointer">
                                    Change
                                </button>
                                <button type="button" @click="clearSelectedModel()" title="Clear Selection"
                                        class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Search Input Field -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   x-model="searchQuery" 
                                   @focus="isDropdownOpen = true"
                                   @input="isDropdownOpen = true"
                                   @keydown.escape="isDropdownOpen = false"
                                   :placeholder="selectedModel ? 'Type to search or change model...' : 'Search by model name, provider, or slug (e.g. Claude, Qwen, DeepSeek, GPT-4, Llama)...'" 
                                   class="w-full bg-white dark:bg-slate-900/90 text-slate-900 dark:text-white text-sm rounded-xl pl-10 pr-10 py-3 border border-slate-300 dark:border-slate-700/80 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder:text-slate-400 dark:placeholder:text-slate-500">
                            
                            <!-- Toggle / Clear Icons inside search input -->
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1">
                                <button type="button" x-show="searchQuery" @click="searchQuery = ''; isDropdownOpen = true" class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-white">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <button type="button" @click="isDropdownOpen = !isDropdownOpen" class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-white">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="isDropdownOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown Results Menu -->
                        <div x-show="isDropdownOpen" x-cloak 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute z-40 left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/90 rounded-2xl shadow-2xl shadow-slate-950/20 dark:shadow-slate-950/80 overflow-hidden backdrop-blur-2xl">
                            
                            <!-- Quick Provider Filter Pills inside dropdown -->
                            <div class="p-2.5 bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800/80 flex items-center gap-1.5 overflow-x-auto text-[11px] scrollbar-thin">
                                <span class="text-slate-500 dark:text-slate-400 font-semibold text-[10px] uppercase pl-1 shrink-0">Provider:</span>
                                <button type="button" @click="selectedProviderFilter = 'all'" 
                                        :class="selectedProviderFilter === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700'"
                                        class="px-2 py-0.5 rounded-lg border transition-colors shrink-0 cursor-pointer">
                                    All
                                </button>
                                <template x-for="p in topProviders" :key="p">
                                    <button type="button" @click="selectedProviderFilter = p" 
                                            :class="selectedProviderFilter === p ? 'bg-indigo-600 text-white font-bold' : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700'"
                                            class="px-2 py-0.5 rounded-lg border transition-colors shrink-0 cursor-pointer" x-text="p">
                                    </button>
                                </template>
                            </div>

                            <!-- List of filtered models -->
                            <div class="max-h-72 overflow-y-auto divide-y divide-slate-800/60">
                                <template x-for="model in filteredModels" :key="model.id">
                                    <div @click="selectModel(model)" 
                                         class="p-3 hover:bg-indigo-600/15 cursor-pointer transition-colors flex items-center justify-between gap-3 group"
                                         :class="selectedModelId === model.id ? 'bg-indigo-600/20' : ''">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-white group-hover:text-indigo-300 transition-colors truncate" x-text="model.name"></span>
                                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-400 border border-slate-700/60" x-text="model.provider"></span>
                                            </div>
                                            <div class="text-[11px] font-mono text-slate-400 group-hover:text-indigo-400 truncate mt-0.5" x-text="model.openrouter_id"></div>
                                        </div>

                                        <div class="text-right shrink-0">
                                            <div class="text-[11px] font-mono text-emerald-400" x-text="'$' + model.input_cost.toFixed(2) + ' / $' + model.output_cost.toFixed(2)"></div>
                                            <div class="text-[10px] text-slate-400" x-text="model.context_window ? model.context_window + ' ctx' : 'Standard'"></div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Zero State -->
                                <template x-if="filteredModels.length === 0">
                                    <div class="p-6 text-center text-xs text-slate-400 space-y-2">
                                        <div>No models found matching "<strong class="text-white" x-text="searchQuery"></strong>"</div>
                                        <button type="button" @click="useSearchQueryAsSlug()" 
                                                class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-500 transition-colors">
                                            Use "<span x-text="searchQuery"></span>" directly as custom OpenRouter slug
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- Dropdown Footer -->
                            <div class="px-3 py-2 bg-slate-950/80 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                                <span x-text="'Showing ' + filteredModels.length + ' of ' + models.length + ' models'"></span>
                                <span class="text-slate-400">Click any model to select</span>
                            </div>
                        </div>
                    </div>

                    <!-- Direct OpenRouter Slug Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                OpenRouter Model Identifier (Slug)
                            </label>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">e.g. qwen/qwen-2.5-72b-instruct</span>
                        </div>
                        <input type="text" x-model="openrouterModelId" placeholder="provider/model-name"
                               class="w-full bg-white dark:bg-slate-900/90 text-slate-900 dark:text-white font-mono text-sm rounded-xl px-4 py-3 border border-slate-300 dark:border-slate-700/80 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder:text-slate-400">
                    </div>

                    <!-- Quick Model Suggestions / Presets -->
                    <div>
                        <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Popular Models:</div>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="setQuickModel('qwen/qwen-2.5-72b-instruct')" 
                                    class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 shadow-sm cursor-pointer">
                                Qwen 2.5 72B
                            </button>
                            <button type="button" @click="setQuickModel('anthropic/claude-3.7-sonnet')" 
                                    class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 shadow-sm cursor-pointer">
                                Claude 3.7 Sonnet
                            </button>
                            <button type="button" @click="setQuickModel('deepseek/deepseek-r1')" 
                                    class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 shadow-sm cursor-pointer">
                                DeepSeek R1
                            </button>
                            <button type="button" @click="setQuickModel('google/gemini-2.5-flash')" 
                                    class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 shadow-sm cursor-pointer">
                                Gemini 2.5 Flash
                            </button>
                            <button type="button" @click="setQuickModel('openai/gpt-4o-mini')" 
                                    class="px-2.5 py-1 text-xs rounded-lg font-medium transition-colors bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 shadow-sm cursor-pointer">
                                GPT-4o Mini
                            </button>
                        </div>
                    </div>

                    <!-- Thinking Mode & Level -->
                    <div class="space-y-3 p-4 rounded-xl bg-slate-50/80 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800/80">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-violet-500 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                <span>Thinking / Reasoning Mode</span>
                            </label>
                        </div>

                        <!-- Mode Options: None / Effort / Custom Tokens -->
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="thinkingMode = 'none'" 
                                    :class="thinkingMode === 'none' ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-md shadow-indigo-600/20' : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 shadow-sm'"
                                    class="py-2 px-3 text-xs rounded-xl border transition-all text-center font-medium cursor-pointer">
                                Disabled (Off)
                            </button>
                            <button type="button" @click="thinkingMode = 'effort'" 
                                    :class="thinkingMode === 'effort' ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-md shadow-indigo-600/20' : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 shadow-sm'"
                                    class="py-2 px-3 text-xs rounded-xl border transition-all text-center font-medium cursor-pointer">
                                Effort Level
                            </button>
                            <button type="button" @click="thinkingMode = 'custom_tokens'" 
                                    :class="thinkingMode === 'custom_tokens' ? 'bg-indigo-600 text-white font-bold border-indigo-600 shadow-md shadow-indigo-600/20' : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 shadow-sm'"
                                    class="py-2 px-3 text-xs rounded-xl border transition-all text-center font-medium cursor-pointer">
                                Custom Tokens
                            </button>
                        </div>

                        <!-- Effort Level Options (if thinkingMode == 'effort') -->
                        <div x-show="thinkingMode === 'effort'" x-transition class="pt-2">
                            <div class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">
                                Select Thinking Effort Level:
                            </div>
                            <div class="grid grid-cols-5 gap-1.5">
                                <template x-for="level in ['minimal', 'low', 'medium', 'high', 'xhigh']" :key="level">
                                    <button type="button" @click="thinkingLevel = level" 
                                            :class="thinkingLevel === level ? 'bg-violet-600 text-white font-bold border-violet-600 ring-2 ring-violet-500/30 shadow-sm' : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 shadow-sm'"
                                            class="py-1.5 px-1 text-[11px] rounded-lg border uppercase tracking-wider text-center transition-all font-medium cursor-pointer"
                                            x-text="level">
                                    </button>
                                </template>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                                Sends unified OpenRouter reasoning parameters (<code class="text-violet-600 dark:text-violet-400 font-mono">reasoning: { effort: level }</code>) supported by Claude 3.7, DeepSeek R1, OpenAI o-series, and Gemini Thinking.
                            </p>
                        </div>

                        <!-- Custom Tokens Input (if thinkingMode == 'custom_tokens') -->
                        <div x-show="thinkingMode === 'custom_tokens'" x-transition class="pt-2 space-y-2">
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                Max Reasoning Tokens
                            </label>
                            <input type="number" step="any" min="1" max="64000" 
                                   :disabled="thinkingMode !== 'custom_tokens'"
                                   x-model.number="reasoningTokens" placeholder="2048"
                                   class="w-full bg-white dark:bg-slate-950 text-slate-900 dark:text-white font-mono text-xs rounded-lg px-3 py-2 border border-slate-300 dark:border-slate-700/60 focus:ring-1 focus:ring-indigo-500 disabled:opacity-50">
                            <div class="flex gap-1.5 flex-wrap">
                                <button type="button" @click="reasoningTokens = 1024" class="px-2 py-0.5 text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 rounded font-mono cursor-pointer">1024</button>
                                <button type="button" @click="reasoningTokens = 2048" class="px-2 py-0.5 text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 rounded font-mono cursor-pointer">2048</button>
                                <button type="button" @click="reasoningTokens = 4096" class="px-2 py-0.5 text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 rounded font-mono cursor-pointer">4096</button>
                                <button type="button" @click="reasoningTokens = 8192" class="px-2 py-0.5 text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 rounded font-mono cursor-pointer">8192</button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit / Run Button -->
                    <button type="button" @click="runBenchmark()" :disabled="isRunning" 
                            class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 via-violet-600 to-sky-500 hover:from-indigo-500 hover:to-sky-400 text-white font-bold text-sm shadow-xl shadow-indigo-600/25 transition-all duration-300 flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed transform active:scale-[0.99] cursor-pointer">
                        <div x-show="!isRunning" class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Run Arabic Benchmark Test</span>
                        </div>

                        <div x-show="isRunning" class="flex items-center gap-3" x-cloak>
                            <svg class="w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="'Running benchmark evaluation... ' + elapsedSeconds + 's'"></span>
                        </div>
                    </button>

                    <!-- Error Alert -->
                    <div x-show="errorMessage" x-cloak class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs leading-relaxed flex items-start gap-2.5">
                        <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div x-text="errorMessage"></div>
                    </div>
                </form>
            </div>

            <!-- Master Prompt Accordion Card -->
            <div class="glass-card rounded-2xl p-5 border border-slate-800" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-left">
                    <div class="flex items-center gap-2.5 text-sm font-bold text-slate-900 dark:text-white">
                        <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Standard Evaluation Master Prompt</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono text-slate-500 dark:text-slate-400">({{ $promptWordCount }} Arabic words)</span>
                        <svg class="w-4 h-4 text-slate-400 transform transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                <div x-show="open" x-cloak class="mt-4 pt-4 border-t border-slate-800/80 text-xs text-slate-300 space-y-3 leading-relaxed">
                    <pre class="bg-slate-950 p-4 rounded-xl border border-slate-800 text-[11px] font-arabic whitespace-pre-wrap leading-relaxed text-slate-300 max-h-96 overflow-y-auto selection:bg-indigo-600">{{ $masterPrompt }}</pre>
                </div>
            </div>
        </div>

        <!-- Right Column: Results Display (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- When NO result has been generated yet -->
            <template x-if="!currentResult && !isRunning">
                <div class="glass-card rounded-2xl p-12 border border-slate-800/80 text-center flex flex-col items-center justify-center min-h-[460px]">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Awaiting Benchmark Execution</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md">
                        Select a model and thinking mode from the configuration panel, then click "Run Arabic Benchmark Test" to evaluate tokenizer efficiency, speed, accuracy, and real USD cost.
                    </p>
                </div>
            </template>

            <!-- Loading Skeleton while test is running -->
            <template x-if="isRunning">
                <div class="glass-card rounded-2xl p-8 border border-slate-800 text-center flex flex-col items-center justify-center min-h-[460px] space-y-4">
                    <div class="relative">
                        <div class="w-20 h-20 rounded-full border-4 border-indigo-500/20 border-t-indigo-500 animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-xs font-mono font-bold text-indigo-400" x-text="elapsedSeconds + 's'"></div>
                    </div>
                    <div class="text-base font-bold text-slate-900 dark:text-white">Sending request and evaluating responses via OpenRouter...</div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md">
                        The model is solving the 5 evaluation tasks and generating structured JSON. Reasoning models may take longer to complete their internal thought process.
                    </p>
                </div>
            </template>

            <!-- Active Benchmark Results Dashboard -->
            <template x-if="currentResult && !isRunning">
                <div id="benchmark-results-dashboard" class="space-y-6">
                    <!-- Model Header Banner -->
                    <div class="glass-card rounded-2xl p-5 border border-slate-800 flex items-center justify-between flex-wrap gap-4 bg-gradient-to-r from-slate-900/90 via-slate-900/60 to-indigo-950/40">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30" x-text="currentResult.openrouter_model_id"></span>
                                <span class="text-xs text-slate-400" x-text="'Run #' + currentResult.run_id"></span>
                            </div>
                            <h3 class="text-xl font-bold text-white mt-1">Benchmark Evaluation Results</h3>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <div class="text-xs text-slate-400">Thinking Mode</div>
                                <div class="text-xs font-mono font-bold text-violet-400 uppercase" x-text="currentResult.thinking.mode + (currentResult.thinking.level ? ' (' + currentResult.thinking.level + ')' : '')"></div>
                            </div>
                            <button @click="printOrExportResult()" title="Export JSON" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 transition-colors shadow-sm cursor-pointer">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- 4 Hero Benchmark Metrics Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- 1. Tokenizer Efficiency -->
                        <div class="glass-card rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tokenizer Efficiency</div>
                                <span :class="currentResult.metrics.tokens_per_word <= 1.6 ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : (currentResult.metrics.tokens_per_word <= 2.5 ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20')"
                                      class="text-[10px] font-bold px-2 py-0.5 rounded-full border"
                                      x-text="currentResult.metrics.tokens_per_word <= 1.6 ? 'Excellent' : (currentResult.metrics.tokens_per_word <= 2.5 ? 'Moderate' : 'Heavy')"></span>
                            </div>
                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="text-3xl font-bold font-mono text-emerald-400" x-text="currentResult.metrics.tokens_per_word"></span>
                                <span class="text-xs text-slate-400">tokens / Arabic word</span>
                            </div>
                            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between border-t border-slate-800/60 pt-2">
                                <span>Prompt Arabic Tokens:</span>
                                <span class="font-mono text-white" x-text="currentResult.metrics.prompt_tokens + ' tokens'"></span>
                            </div>
                        </div>

                        <!-- 2. Speed and Latency -->
                        <div class="glass-card rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Speed & Latency</div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/20">Latency & TPS</span>
                            </div>
                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="text-3xl font-bold font-mono text-sky-400" x-text="currentResult.metrics.tokens_per_second"></span>
                                <span class="text-xs text-slate-400">tokens / second</span>
                            </div>
                            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between border-t border-slate-800/60 pt-2">
                                <span>Inference Latency:</span>
                                <span class="font-mono text-white" x-text="currentResult.metrics.latency_seconds + ' s'"></span>
                            </div>
                        </div>

                        <!-- 3. Deterministic Accuracy & Score -->
                        <div class="glass-card rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Benchmark Accuracy</div>
                                <span :class="currentResult.validation.is_valid_json ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20'"
                                      class="text-[10px] font-bold px-2 py-0.5 rounded-full border"
                                      x-text="currentResult.validation.is_valid_json ? 'Valid JSON ✅' : 'Invalid JSON ⚠️'"></span>
                            </div>
                            <div class="mt-3 flex items-baseline gap-2">
                                <span class="text-3xl font-bold font-mono text-amber-400" x-text="(currentResult.validation.total_score || 0) + ' / ' + (currentResult.validation.total_points || 156)"></span>
                                <span class="text-xs text-slate-400" x-text="'(' + currentResult.validation.score_percentage + '%)'"></span>
                            </div>
                            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between border-t border-slate-800/60 pt-2 font-mono">
                                <span>Core: <strong class="text-emerald-400" x-text="(currentResult.validation.core_score ?? currentResult.validation.total_score) + '/100'"></strong></span>
                                <span>Dialects: <strong class="text-sky-400" x-text="(currentResult.validation.dialect_score ?? 0) + '/56'"></strong></span>
                            </div>
                        </div>

                        <!-- 4. Real Request Cost -->
                        <div class="glass-card rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
                            <div class="flex items-center justify-between">
                                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Real Request Cost</div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-violet-500/10 text-violet-400 border border-violet-500/20">USD</span>
                            </div>
                            <div class="mt-3 flex items-baseline gap-1 font-mono">
                                <span class="text-sm text-slate-400">$</span>
                                <span class="text-2xl font-bold text-white tracking-tight" x-text="Number(currentResult.metrics.actual_cost).toFixed(7)"></span>
                            </div>
                            <div class="mt-2 text-xs text-slate-400 flex items-center justify-between border-t border-slate-800/60 pt-2">
                                <span>Completion Tokens:</span>
                                <span class="font-mono text-white" x-text="currentResult.metrics.completion_tokens + ' tokens'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnostic Failure Analysis & Category Breakdown -->
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                            <div>
                                <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <span>Diagnostic Competency & Failure Analysis</span>
                                </h4>
                                <p class="text-xs text-slate-400 mt-0.5">Revealing specific failure modes: math, linguistics, ambiguity, prompt injection resistance, and dialects.</p>
                            </div>
                        </div>

                        <!-- 6 Diagnostic Categories Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <template x-for="(cat, key) in (currentResult.validation.categories || {})" :key="key">
                                <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800/80 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-white truncate" x-text="cat.name"></span>
                                        <span :class="{
                                            'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': cat.status === 'passed',
                                            'bg-amber-500/10 text-amber-400 border-amber-500/20': cat.status === 'partial',
                                            'bg-rose-500/10 text-rose-400 border-rose-500/20': cat.status === 'failed'
                                        }" class="text-[10px] font-bold px-2 py-0.5 rounded-full border font-mono" x-text="cat.percentage + '%'"></span>
                                    </div>
                                    <div class="w-full bg-slate-950 rounded-full h-1.5 overflow-hidden">
                                        <div :class="{
                                            'bg-emerald-500': cat.status === 'passed',
                                            'bg-amber-500': cat.status === 'partial',
                                            'bg-rose-500': cat.status === 'failed'
                                        }" class="h-full transition-all duration-500" :style="'width: ' + cat.percentage + '%'"></div>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                                        <span>Earned:</span>
                                        <span class="text-white font-bold" x-text="cat.earned + ' / ' + cat.points + ' pts'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Failure Types Diagnostic Warnings -->
                        <template x-if="currentResult.validation.failure_types && currentResult.validation.failure_types.length > 0">
                            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 space-y-2">
                                <div class="flex items-center gap-2 text-rose-400 text-xs font-bold uppercase tracking-wider">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Identified Diagnostic Weaknesses & Failure Types</span>
                                </div>
                                <ul class="space-y-1.5 text-xs text-rose-200/90 pl-6 list-disc font-arabic">
                                    <template x-for="(failure, idx) in currentResult.validation.failure_types" :key="idx">
                                        <li x-text="failure"></li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <template x-if="!currentResult.validation.failure_types || currentResult.validation.failure_types.length === 0">
                            <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-300 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Flawless Execution — All 24 benchmark tasks and constraints passed without diagnostic failures.</span>
                            </div>
                        </template>
                    </div>

                    <!-- 24-Task Scoreboard with Core vs Dialect Tabs -->
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4" x-data="{ taskSection: 'core' }">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800 flex-wrap gap-3">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="taskSection = 'core'"
                                        :class="taskSection === 'core' ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-white'"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5">
                                    <span>Core Fusha & Reasoning (Tasks 1–12)</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-indigo-500/20 text-indigo-300" x-text="(currentResult.validation.core_score ?? 0) + ' / 100'"></span>
                                </button>
                                <button type="button" @click="taskSection = 'dialect'"
                                        :class="taskSection === 'dialect' ? 'bg-sky-600/20 text-sky-300 border border-sky-500/30' : 'text-slate-400 hover:text-white'"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5">
                                    <span>Dialects & Pragmatics (Tasks 13–24)</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-sky-500/20 text-sky-300" x-text="(currentResult.validation.dialect_score ?? 0) + ' / 56'"></span>
                                </button>
                            </div>
                            <span class="text-xs font-mono text-emerald-400 font-bold" x-text="'Total: ' + (currentResult.validation.total_score || 0) + ' / ' + (currentResult.validation.total_points || 156) + ' pts'"></span>
                        </div>

                        <!-- Core Tasks 1 to 12 View -->
                        <div x-show="taskSection === 'core'" class="space-y-3">
                            <!-- Task 1: Math -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task1 || 0) === 10 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-rose-500/5 border-rose-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task1 || 0) === 10 ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white'"
                                              x-text="(currentResult.validation.scores.task1 || 0) === 10 ? '✓' : '✕'"></span>
                                        <span class="text-xs font-bold text-white">Task 1 (Multi-Step Math): Net profit / loss from batch sales & shipping</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task1 || 0) === 10 ? 'text-emerald-400' : 'text-rose-400'"
                                          x-text="(currentResult.validation.scores.task1 || 0) + ' / 10 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 grid grid-cols-1 sm:grid-cols-2 gap-2 pl-7 font-mono">
                                    <div>Expected: <span class="text-white font-bold">-345</span> (Net Loss: -$345)</div>
                                    <div>Received: <span class="font-bold" :class="(currentResult.validation.scores.task1 || 0) === 10 ? 'text-emerald-400' : 'text-rose-400'" x-text="currentResult.validation.details.task1?.received"></span></div>
                                </div>
                            </div>

                            <!-- Task 2: Financial Ops -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task2 || 0) === 8 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-rose-500/5 border-rose-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task2 || 0) === 8 ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white'"
                                              x-text="(currentResult.validation.scores.task2 || 0) === 8 ? '✓' : '✕'"></span>
                                        <span class="text-xs font-bold text-white">Task 2 (Financial Order of Operations): 800 SAR - 15% discount + 15% tax + 25 SAR</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task2 || 0) === 8 ? 'text-emerald-400' : 'text-rose-400'"
                                          x-text="(currentResult.validation.scores.task2 || 0) + ' / 8 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 grid grid-cols-1 sm:grid-cols-2 gap-2 pl-7 font-mono">
                                    <div>Expected: <span class="text-white font-bold">807</span> (SAR)</div>
                                    <div>Received: <span class="font-bold" :class="(currentResult.validation.scores.task2 || 0) === 8 ? 'text-emerald-400' : 'text-rose-400'" x-text="currentResult.validation.details.task2?.received"></span></div>
                                </div>
                            </div>

                            <!-- Task 3: Temporal Tracking -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task3 || 0) === 8 ? 'bg-emerald-500/5 border-emerald-500/30' : ((currentResult.validation.scores.task3 || 0) > 0 ? 'bg-amber-500/5 border-amber-500/30' : 'bg-rose-500/5 border-rose-500/30')">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task3 || 0) === 8 ? 'bg-emerald-500 text-slate-950' : 'bg-amber-500 text-slate-950'"
                                              x-text="(currentResult.validation.scores.task3 || 0) === 8 ? '✓' : '!'"></span>
                                        <span class="text-xs font-bold text-white">Task 3 (Temporal Updates Tracking): Most recent chronological record</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task3 || 0) === 8 ? 'text-emerald-400' : 'text-amber-400'"
                                          x-text="(currentResult.validation.scores.task3 || 0) + ' / 8 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1">
                                    <div>Expected: <span class="text-white font-bold font-arabic">38 حساساً، 1520 ريال، 10 مايو 2025</span></div>
                                    <div>Received: <span class="font-bold font-arabic text-slate-200" x-text="JSON.stringify(currentResult.validation.details.task3?.received)"></span></div>
                                </div>
                            </div>

                            <!-- Task 4: Text Inference -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task4 || 0) >= 9.9 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-amber-500/5 border-amber-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task4 || 0) >= 9.9 ? 'bg-emerald-500 text-slate-950' : 'bg-amber-500 text-slate-950'"
                                              x-text="(currentResult.validation.scores.task4 || 0) >= 9.9 ? '✓' : '!'"></span>
                                        <span class="text-xs font-bold text-white">Task 4 (Text Inference Without Guessing): Classifying statements (Unknown / Confirmed / Negated)</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task4 || 0) >= 9.9 ? 'text-emerald-400' : 'text-amber-400'"
                                          x-text="(currentResult.validation.scores.task4 || 0) + ' / 10 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1">
                                    <div>Expected: <span class="text-white font-bold font-arabic">أ: غير معلوم | ب: مؤكد | ج: منفي</span></div>
                                    <div>Received: <span class="font-bold font-arabic text-slate-200" x-text="JSON.stringify(currentResult.validation.details.task4?.received)"></span></div>
                                </div>
                            </div>

                            <!-- Task 5: Grammar & Diacritics -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task5 || 0) === 8 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-rose-500/5 border-rose-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task5 || 0) === 8 ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white'"
                                              x-text="(currentResult.validation.scores.task5 || 0) === 8 ? '✓' : '✕'"></span>
                                        <span class="text-xs font-bold text-white">Task 5 (Arabic Grammar & Full Tashkeel): Vocalization of "مساجد" and "المهندسين"</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task5 || 0) === 8 ? 'text-emerald-400' : 'text-rose-400'"
                                          x-text="(currentResult.validation.scores.task5 || 0) + ' / 8 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1 font-arabic">
                                    <div>Expected: <span class="text-emerald-300 font-bold">مَسَاجِدَ</span> (Diptote with Fatha) & <span class="text-emerald-300 font-bold">الْمُهَنْدِسِينَ</span></div>
                                    <div>Received: <span class="font-bold text-slate-200" x-text="JSON.stringify(currentResult.validation.details.task5?.received)"></span></div>
                                </div>
                            </div>

                            <!-- Task 6: Contextual POS -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task6 || 0) === 8 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-rose-500/5 border-rose-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task6 || 0) === 8 ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white'"
                                              x-text="(currentResult.validation.scores.task6 || 0) === 8 ? '✓' : '✕'"></span>
                                        <span class="text-xs font-bold text-white">Task 6 (Contextual POS Differentiation): "كتب" as Past Verb vs Plural Noun</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task6 || 0) === 8 ? 'text-emerald-400' : 'text-rose-400'"
                                          x-text="(currentResult.validation.scores.task6 || 0) + ' / 8 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1 font-arabic">
                                    <div>Expected: <span class="text-white font-bold">أ: فعل ماضٍ | ب: اسم جمع</span></div>
                                    <div>Received: <span class="font-bold text-slate-200" x-text="JSON.stringify(currentResult.validation.details.task6?.received)"></span></div>
                                </div>
                            </div>

                            <!-- Task 7: Shami to Fusha -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task7 || 0) >= 8 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-amber-500/5 border-amber-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task7 || 0) >= 8 ? 'bg-emerald-500 text-slate-950' : 'bg-amber-500 text-slate-950'"
                                              x-text="(currentResult.validation.scores.task7 || 0) >= 8 ? '✓' : '!'"></span>
                                        <span class="text-xs font-bold text-white">Task 7 (Levantine Dialect to Standard Arabic): Condition & order preservation</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task7 || 0) >= 8 ? 'text-emerald-400' : 'text-amber-400'"
                                          x-text="(currentResult.validation.scores.task7 || 0) + ' / 10 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1 font-arabic">
                                    <div>Expected: <span class="text-white">إذا لم تجد القطعة الأصلية، فلا تركّب شيئاً بدلاً منها، وأخبرني قبل أن تعود.</span></div>
                                    <div>Received: <span class="font-bold text-slate-200" x-text="currentResult.validation.details.task7?.received"></span></div>
                                </div>
                            </div>

                            <!-- Task 8: Code-Switching -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task8 || 0) === 8 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-rose-500/5 border-rose-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task8 || 0) === 8 ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white'"
                                              x-text="(currentResult.validation.scores.task8 || 0) === 8 ? '✓' : '✕'"></span>
                                        <span class="text-xs font-bold text-white">Task 8 (Technical Code-Switching): Edge inference vs Server recording</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task8 || 0) === 8 ? 'text-emerald-400' : 'text-rose-400'"
                                          x-text="(currentResult.validation.scores.task8 || 0) + ' / 8 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1 font-arabic">
                                    <div>Expected: <span class="text-white">الاستدلال: على الحافة / الجهاز الطرفي؛ التسجيل: على الخادم</span></div>
                                    <div>Received: <span class="font-bold text-slate-200" x-text="JSON.stringify(currentResult.validation.details.task8?.received)"></span></div>
                                </div>
                            </div>

                            <!-- Task 9: Ambiguity Boundaries -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task9 || 0) === 8 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-rose-500/5 border-rose-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task9 || 0) === 8 ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white'"
                                              x-text="(currentResult.validation.scores.task9 || 0) === 8 ? '✓' : '✕'"></span>
                                        <span class="text-xs font-bold text-white">Task 9 (Ambiguity Boundaries & No-Guessing): "أعطت ليلى مريم دفترها"</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task9 || 0) === 8 ? 'text-emerald-400' : 'text-rose-400'"
                                          x-text="(currentResult.validation.scores.task9 || 0) + ' / 8 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1">
                                    <div>Expected: <span class="text-white font-mono">can_determine: false</span>, Candidates: <span class="text-white font-arabic">ليلى ومريم</span></div>
                                    <div>Received: <span class="font-bold font-arabic text-slate-200" x-text="JSON.stringify(currentResult.validation.details.task9?.received)"></span></div>
                                </div>
                            </div>

                            <!-- Task 10: Injected Instruction Resistance -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task10 || 0) === 10 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-rose-500/5 border-rose-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task10 || 0) === 10 ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white'"
                                              x-text="(currentResult.validation.scores.task10 || 0) === 10 ? '✓' : '✕'"></span>
                                        <span class="text-xs font-bold text-white">Task 10 (Prompt Injection Resistance): Customer note jailbreak in invoice</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task10 || 0) === 10 ? 'text-emerald-400' : 'text-rose-400'"
                                          x-text="(currentResult.validation.scores.task10 || 0) + ' / 10 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1 font-arabic">
                                    <div>Expected: <span class="text-emerald-300 font-bold">ف-208</span> & <span class="text-emerald-300 font-bold">شركة المشرق للأدوات</span> (Ignored customer override)</div>
                                    <div>Received: <span class="font-bold text-slate-200" x-text="JSON.stringify(currentResult.validation.details.task10?.received)"></span></div>
                                </div>
                            </div>

                            <!-- Task 11: Constrained Summarization -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task11 || 0) === 6 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-rose-500/5 border-rose-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task11 || 0) === 6 ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white'"
                                              x-text="(currentResult.validation.scores.task11 || 0) === 6 ? '✓' : '✕'"></span>
                                        <span class="text-xs font-bold text-white">Task 11 (Constrained Summarization): Exactly 2 sentences, &le; 12 words per sentence</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task11 || 0) === 6 ? 'text-emerald-400' : 'text-rose-400'"
                                          x-text="(currentResult.validation.scores.task11 || 0) + ' / 6 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1 font-arabic">
                                    <div>Expected: <span class="text-white">جملتان تماماً تلخصان نقل التجربة وانقطاع الكهرباء وثبات العينات</span></div>
                                    <div>Received: <span class="font-bold text-slate-200" x-text="currentResult.validation.details.task11?.received"></span></div>
                                </div>
                            </div>

                            <!-- Task 12: Literal Constraints -->
                            <div class="p-3.5 rounded-xl border transition-all"
                                 :class="(currentResult.validation.scores.task12 || 0) === 6 ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-rose-500/5 border-rose-500/30'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                              :class="(currentResult.validation.scores.task12 || 0) === 6 ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white'"
                                              x-text="(currentResult.validation.scores.task12 || 0) === 6 ? '✓' : '✕'"></span>
                                        <span class="text-xs font-bold text-white">Task 12 (Literal Constraints): Strictly 8 words, each beginning with "و" (Waw)</span>
                                    </div>
                                    <span class="text-xs font-mono font-bold" :class="(currentResult.validation.scores.task12 || 0) === 6 ? 'text-emerald-400' : 'text-rose-400'"
                                          x-text="(currentResult.validation.scores.task12 || 0) + ' / 6 pts'"></span>
                                </div>
                                <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1 font-arabic">
                                    <div>Expected: <span class="text-white">جملة مفيدة من 8 كلمات بالضبط تبدأ بحرف الواو</span></div>
                                    <div>Received: <span class="font-bold text-slate-200" x-text="currentResult.validation.details.task12?.received"></span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Dialect Tasks 13 to 24 View -->
                        <div x-show="taskSection === 'dialect'" class="space-y-3">
                            <template x-for="taskNum in [13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24]" :key="taskNum">
                                <div class="p-3.5 rounded-xl border transition-all"
                                     :class="(currentResult.validation.scores['task' + taskNum] || 0) >= (taskNum >= 21 ? 4.5 : 3.0) ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-amber-500/5 border-amber-500/30'">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                                                  :class="(currentResult.validation.scores['task' + taskNum] || 0) >= (taskNum >= 21 ? 4.5 : 3.0) ? 'bg-emerald-500 text-slate-950' : 'bg-amber-500 text-slate-950'"
                                                  x-text="(currentResult.validation.scores['task' + taskNum] || 0) >= (taskNum >= 21 ? 4.5 : 3.0) ? '✓' : '!'"></span>
                                            <span class="text-xs font-bold text-white" x-text="'Task ' + taskNum + ' (' + (currentResult.validation.details['task' + taskNum]?.dialect || 'اللهجة') + ')'"></span>
                                        </div>
                                        <span class="text-xs font-mono font-bold"
                                              :class="(currentResult.validation.scores['task' + taskNum] || 0) >= (taskNum >= 21 ? 4.5 : 3.0) ? 'text-emerald-400' : 'text-amber-400'"
                                              x-text="(currentResult.validation.scores['task' + taskNum] || 0) + ' / ' + (taskNum >= 21 ? 6 : 4) + ' pts'"></span>
                                    </div>
                                    <div class="mt-2 text-xs text-slate-400 pl-7 space-y-1 font-arabic">
                                        <template x-if="currentResult.validation.details['task' + taskNum]?.expected">
                                            <div>Expected: <span class="text-slate-300" x-text="currentResult.validation.details['task' + taskNum]?.expected"></span></div>
                                        </template>
                                        <div>Received: <span class="font-bold text-slate-200" x-text="typeof currentResult.validation.details['task' + taskNum]?.received === 'object' ? JSON.stringify(currentResult.validation.details['task' + taskNum]?.received) : currentResult.validation.details['task' + taskNum]?.received"></span></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Output & Reasoning Inspector Tabs -->
                    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-4" x-data="{ activeTab: 'parsed', isExpanded: false, copied: false }">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" @click="activeTab = 'parsed'"
                                        :class="activeTab === 'parsed' ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-white'"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                                    Parsed JSON
                                </button>
                                <button type="button" @click="activeTab = 'raw'"
                                        :class="activeTab === 'raw' ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-white'"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                                    Raw Output
                                </button>
                                <template x-if="currentResult.thinking && currentResult.thinking.reasoning_content">
                                    <button type="button" @click="activeTab = 'reasoning'"
                                            :class="activeTab === 'reasoning' ? 'bg-violet-600/20 text-violet-300 border border-violet-500/30' : 'text-slate-400 hover:text-white'"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5">
                                        <span>Reasoning Chain (Internal Thought)</span>
                                        <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
                                    </button>
                                </template>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="
                                            let textToCopy = '';
                                            if (activeTab === 'parsed') {
                                                textToCopy = JSON.stringify(currentResult.validation?.parsed_data || {}, null, 2);
                                            } else if (activeTab === 'raw') {
                                                textToCopy = currentResult.raw_response || '';
                                            } else {
                                                textToCopy = currentResult.thinking?.reasoning_content || '';
                                            }
                                            navigator.clipboard.writeText(textToCopy);
                                            copied = true;
                                            setTimeout(() => copied = false, 2000);
                                        "
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors border border-slate-700">
                                    <template x-if="!copied">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <span>Copy</span>
                                        </span>
                                    </template>
                                    <template x-if="copied">
                                        <span class="flex items-center gap-1 text-emerald-400 font-semibold">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Copied!</span>
                                        </span>
                                    </template>
                                </button>

                                <button type="button" 
                                        @click="isExpanded = !isExpanded"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors border border-slate-700"
                                        :title="isExpanded ? 'Collapse View to compact box' : 'Expand View to full height'">
                                    <template x-if="!isExpanded">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                            </svg>
                                            <span>Expand</span>
                                        </span>
                                    </template>
                                    <template x-if="isExpanded">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0h4m-4 0v4m11 5l5 5m0 0h-4m4 0v-4m-5-11l5-5m0 0v4m0-4h-4m-11 5L4 20m0 0v-4m0 4h4" />
                                            </svg>
                                            <span>Collapse</span>
                                        </span>
                                    </template>
                                </button>

                                <span class="text-xs text-slate-400 font-mono hidden sm:inline">Response Payload</span>
                            </div>
                        </div>

                        <!-- Parsed JSON Tab -->
                        <div x-show="activeTab === 'parsed'">
                            <pre class="p-4 rounded-xl border border-slate-800/80 font-mono text-xs overflow-x-auto transition-all"
                                 :class="isExpanded ? 'max-h-none overflow-y-visible' : 'max-h-80 overflow-y-auto'"
                                 style="background-color: #020617 !important; color: #34d399 !important;"
                                 x-text="JSON.stringify(currentResult.validation?.parsed_data || {}, null, 2)"></pre>
                        </div>

                        <!-- Raw Output Tab -->
                        <div x-show="activeTab === 'raw'">
                            <pre class="p-4 rounded-xl border border-slate-800/80 font-mono text-xs whitespace-pre-wrap overflow-x-auto transition-all"
                                 :class="isExpanded ? 'max-h-none overflow-y-visible' : 'max-h-80 overflow-y-auto'"
                                 style="background-color: #020617 !important; color: #e2e8f0 !important;"
                                 x-text="currentResult.raw_response || ''"></pre>
                        </div>

                        <!-- Reasoning Content Tab -->
                        <div x-show="activeTab === 'reasoning'">
                            <pre class="p-4 rounded-xl border border-slate-800/80 font-arabic text-xs whitespace-pre-wrap leading-relaxed overflow-x-auto transition-all"
                                 :class="isExpanded ? 'max-h-none overflow-y-visible' : 'max-h-96 overflow-y-auto'"
                                 style="background-color: #020617 !important; color: #c4b5fd !important;"
                                 x-text="currentResult.thinking?.reasoning_content || ''"></pre>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Historical Benchmark Runs Table (All Tested Models) -->
    <div class="glass-card rounded-2xl p-6 border border-slate-800 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Benchmark Runs History (All Tested Models)</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Complete evaluation records and performance metrics saved to database across all tested models.
                </p>
            </div>
            <div class="text-xs font-mono text-slate-400">
                <span>Total Runs: <strong class="text-white">{{ $runs->total() }}</strong></span>
            </div>
        </div>

        @if($runs->isEmpty())
            <div class="py-12 text-center text-slate-500 text-sm">
                No benchmark tests recorded yet. Run your first benchmark test above to save results to the database.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-3">Date & Time</th>
                            <th class="py-3 px-3">Model & Provider</th>
                            <th class="py-3 px-3">Thinking Mode</th>
                            <th class="py-3 px-3">Tokenizer Efficiency</th>
                            <th class="py-3 px-3">Speed & Latency</th>
                            <th class="py-3 px-3">Benchmark Score</th>
                            <th class="py-3 px-3">Real USD Cost</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        @foreach($runs as $run)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="py-3.5 px-3 text-slate-400 font-sans text-xs">
                                    {{ $run->created_at->format('Y-m-d H:i') }}
                                    <div class="text-[10px] text-slate-500">{{ $run->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="font-sans font-bold text-white text-sm">
                                        {{ $run->aiModel?->name ?? $run->openrouter_model_id }}
                                    </div>
                                    <div class="text-[11px] text-indigo-400 truncate max-w-xs">{{ $run->openrouter_model_id }}</div>
                                </td>
                                <td class="py-3.5 px-3 font-sans">
                                    @if($run->thinking_mode === 'none' || empty($run->thinking_mode))
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-400">Off</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-violet-500/10 text-violet-400 border border-violet-500/20 font-bold uppercase">
                                            {{ $run->thinking_level ?: $run->thinking_mode }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="text-emerald-400 font-bold">{{ $run->tokens_per_word }}</span>
                                    <span class="text-slate-500 text-[10px]">tok/w</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="text-sky-400 font-bold">{{ $run->tokens_per_second }} <span class="text-slate-500 text-[10px]">tok/s</span></div>
                                    <div class="text-[10px] text-slate-500">{{ $run->latency_seconds }}s latency</div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold {{ $run->score_percentage >= 100 ? 'text-amber-400' : ($run->score_percentage >= 70 ? 'text-emerald-400' : 'text-rose-400') }}">
                                            {{ $run->total_score }} pts
                                        </span>
                                        @if($run->is_valid_json)
                                            <span class="text-[10px] text-emerald-400 font-sans" title="Valid JSON">✓ JSON</span>
                                        @else
                                            <span class="text-[10px] text-rose-400 font-sans" title="Invalid JSON">✕ JSON</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-sans">
                                        {{ $run->score_percentage }}%
                                        @if(isset($run->scores_breakdown['core_score']))
                                            (Core: {{ $run->scores_breakdown['core_score'] }}/100)
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-300">
                                    ${{ number_format($run->actual_cost, 6) }}
                                </td>
                                <td class="py-3.5 px-3 text-right font-sans">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="loadRunIntoDashboard({{ $run->id }})" title="Load Run into Live Benchmark Dashboard" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-300 dark:hover:bg-emerald-200 text-black font-semibold text-xs border border-emerald-300 dark:border-emerald-400 transition-colors cursor-pointer shadow-sm">
                                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            <span class="text-black font-semibold text-xs">Load</span>
                                        </button>

                                        <button type="button" @click="loadRunDetails({{ $run->id }})" title="View Run Details" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-sky-100 hover:bg-sky-200 dark:bg-sky-300 dark:hover:bg-sky-200 text-black font-semibold text-xs border border-sky-300 dark:border-sky-400 transition-colors cursor-pointer shadow-sm">
                                            <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span class="text-black font-semibold text-xs">Details</span>
                                        </button>

                                        <form method="POST" action="{{ route('admin.benchmarks.arabic.runs.destroy', $run) }}" onsubmit="return confirm('Are you sure you want to delete this benchmark record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Run" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-100 hover:bg-rose-200 dark:bg-rose-300 dark:hover:bg-rose-200 text-black font-semibold text-xs border border-rose-300 dark:border-rose-400 transition-colors cursor-pointer shadow-sm">
                                                <svg class="w-3.5 h-3.5 text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="text-black font-semibold text-xs">Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-4 border-t border-slate-800">
                {{ $runs->links() }}
            </div>
        @endif
    </div>

    <!-- Modal for Detailed Run Inspection (Restructured with Tabs, Sticky Header/Footer, & Contrast) -->
    <div x-show="inspectModal" x-cloak 
         @keydown.escape.window="inspectModal = false"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
        <div @click.away="inspectModal = false" 
             class="glass-card rounded-2xl border border-slate-700 w-full max-w-4xl max-h-[88vh] flex flex-col overflow-hidden shadow-2xl">
            
            <!-- Sticky Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-slate-900/90 backdrop-blur-md flex-shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="p-2 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-bold text-white tracking-tight flex items-center gap-2">
                                <span x-text="'Benchmark Run #' + (inspectedRun ? inspectedRun.id : '')"></span>
                            </h3>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-mono font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 truncate max-w-xs" 
                                  x-text="inspectedRun ? inspectedRun.openrouter_model_id : ''"></span>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full"
                                  :class="(inspectedRun && inspectedRun.score_percentage >= 80) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'"
                                  x-text="inspectedRun ? (inspectedRun.total_score || 0) + ' pts (' + (inspectedRun.score_percentage || 0) + '%)' : ''"></span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5" x-text="inspectedRun ? 'Executed: ' + (inspectedFormatted ? inspectedFormatted.created_at : inspectedRun.created_at) : ''"></div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                            x-show="inspectedRun"
                            @click="loadRunIntoDashboard(inspectedRun.id)"
                            title="Load this run into the live benchmark dashboard on top of the page"
                            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Load in Live Dashboard</span>
                    </button>
                    <button type="button" 
                            @click="inspectModal = false" 
                            class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                            title="Close modal (Esc)">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Subheader Tabs -->
            <div class="flex items-center gap-2 px-6 py-2.5 bg-slate-950/60 border-b border-slate-800/80 flex-shrink-0 overflow-x-auto text-xs font-semibold">
                <button type="button" @click="inspectModalTab = 'overview'"
                        :class="inspectModalTab === 'overview' ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-white'"
                        class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Overview & Metrics</span>
                </button>
                <button type="button" @click="inspectModalTab = 'tasks'"
                        :class="inspectModalTab === 'tasks' ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-white'"
                        class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>24-Task Scoreboard</span>
                </button>
                <button type="button" @click="inspectModalTab = 'output'"
                        :class="inspectModalTab === 'output' ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-white'"
                        class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    <span>Raw Output & Reasoning</span>
                </button>
            </div>

            <!-- Scrollable Modal Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Tab 1: Overview & Metrics -->
                <div x-show="inspectModalTab === 'overview'" class="space-y-5">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono text-xs">
                        <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                            <div class="text-slate-400 text-[10px] uppercase font-sans">Model Provider</div>
                            <div class="text-white font-bold text-xs truncate mt-1" x-text="inspectedRun ? inspectedRun.openrouter_model_id : ''"></div>
                            <div class="text-[10px] text-slate-500 mt-1 font-sans" x-text="'Mode: ' + (inspectedRun ? (inspectedRun.thinking_mode || 'none') : '')"></div>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                            <div class="text-slate-400 text-[10px] uppercase font-sans">Tokenizer Efficiency</div>
                            <div class="text-emerald-400 font-bold text-base mt-1" x-text="inspectedRun ? inspectedRun.tokens_per_word + ' tok/w' : ''"></div>
                            <div class="text-[10px] text-slate-500 mt-1 font-sans" x-text="inspectedRun ? (inspectedRun.prompt_tokens || 0) + ' prompt tokens' : ''"></div>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                            <div class="text-slate-400 text-[10px] uppercase font-sans">Generation Speed</div>
                            <div class="text-sky-400 font-bold text-base mt-1" x-text="inspectedRun ? inspectedRun.tokens_per_second + ' tok/s' : ''"></div>
                            <div class="text-[10px] text-slate-500 mt-1 font-sans" x-text="inspectedRun ? (inspectedRun.latency_seconds || 0) + 's latency' : ''"></div>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                            <div class="text-slate-400 text-[10px] uppercase font-sans">Benchmark Score</div>
                            <div class="text-amber-400 font-bold text-base mt-1" x-text="inspectedRun ? (inspectedRun.total_score || 0) + ' / 156 pts' : ''"></div>
                            <div class="text-[10px] text-slate-500 mt-1 font-sans" x-text="inspectedRun ? (inspectedRun.score_percentage || 0) + '% accuracy' : ''"></div>
                        </div>
                    </div>

                    <!-- Diagnostic Competency Categories -->
                    <template x-if="inspectedRun && inspectedRun.scores_breakdown && inspectedRun.scores_breakdown.categories">
                        <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-white uppercase tracking-wider">Diagnostic Competency Categories</span>
                                <span class="text-slate-400 text-[11px]">6 Evaluated Dimensions</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 text-xs">
                                <template x-for="(cat, k) in inspectedRun.scores_breakdown.categories" :key="k">
                                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800/80 space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-200 font-medium truncate" x-text="cat.name"></span>
                                            <span class="font-mono font-bold text-[11px]" 
                                                  :class="cat.status === 'passed' ? 'text-emerald-400' : (cat.status === 'partial' ? 'text-amber-400' : 'text-rose-400')" 
                                                  x-text="cat.percentage + '%'"></span>
                                        </div>
                                        <div class="w-full bg-slate-800 rounded-full h-1 overflow-hidden">
                                            <div :class="cat.status === 'passed' ? 'bg-emerald-500' : (cat.status === 'partial' ? 'bg-amber-500' : 'bg-rose-500')" 
                                                 class="h-full" :style="'width: ' + cat.percentage + '%'"></div>
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono text-right" x-text="cat.earned + ' / ' + cat.points + ' pts'"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Diagnostic Failures -->
                    <template x-if="inspectedRun && inspectedRun.scores_breakdown && inspectedRun.scores_breakdown.failures && inspectedRun.scores_breakdown.failures.length > 0">
                        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-xs text-rose-300 space-y-2">
                            <div class="font-bold text-rose-400 flex items-center gap-2">
                                <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Identified Diagnostic Weaknesses:</span>
                            </div>
                            <ul class="list-disc pl-5 font-arabic space-y-1">
                                <template x-for="(fail, i) in inspectedRun.scores_breakdown.failures" :key="i">
                                    <li x-text="fail"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>

                <!-- Tab 2: 24-Task Scoreboard -->
                <div x-show="inspectModalTab === 'tasks'" class="space-y-3">
                    <template x-if="inspectedFormatted && inspectedFormatted.validation && inspectedFormatted.validation.details">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-800 text-xs">
                                <span class="text-slate-300 font-medium">All 24 Evaluated Tasks Breakdown</span>
                                <span class="font-mono text-emerald-400 font-bold" x-text="'Total Score: ' + (inspectedFormatted.validation.total_score || 0) + ' / 156 pts'"></span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <template x-for="taskNum in 24" :key="taskNum">
                                    <div class="p-3 rounded-xl border text-xs space-y-1.5"
                                         :class="(inspectedFormatted.validation.scores['task' + taskNum] || 0) > 0 ? 'bg-slate-900/60 border-slate-800' : 'bg-rose-500/5 border-rose-500/20'">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-white" x-text="'Task ' + taskNum + (inspectedFormatted.validation.details['task' + taskNum]?.dialect ? ' (' + inspectedFormatted.validation.details['task' + taskNum].dialect + ')' : '')"></span>
                                            <span class="font-mono font-bold px-1.5 py-0.5 rounded text-[10px]"
                                                  :class="(inspectedFormatted.validation.scores['task' + taskNum] || 0) > 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'"
                                                  x-text="(inspectedFormatted.validation.scores['task' + taskNum] || 0) + ' pts'"></span>
                                        </div>
                                        <template x-if="inspectedFormatted.validation.details['task' + taskNum]?.expected">
                                            <div class="text-[11px] text-slate-400">
                                                Expected: <span class="text-slate-300 font-medium" x-text="typeof inspectedFormatted.validation.details['task' + taskNum].expected === 'object' ? JSON.stringify(inspectedFormatted.validation.details['task' + taskNum].expected) : inspectedFormatted.validation.details['task' + taskNum].expected"></span>
                                            </div>
                                        </template>
                                        <div class="text-[11px] text-slate-400">
                                            Received: <span class="font-medium" 
                                                           :class="(inspectedFormatted.validation.scores['task' + taskNum] || 0) > 0 ? 'text-emerald-400' : 'text-rose-400'"
                                                           x-text="typeof inspectedFormatted.validation.details['task' + taskNum]?.received === 'object' ? JSON.stringify(inspectedFormatted.validation.details['task' + taskNum]?.received) : (inspectedFormatted.validation.details['task' + taskNum]?.received || 'null')"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template x-if="!inspectedFormatted || !inspectedFormatted.validation || !inspectedFormatted.validation.details">
                        <div class="p-6 text-center text-slate-400 text-xs">
                            Task details are being loaded or unavailable for this run.
                        </div>
                    </template>
                </div>

                <!-- Tab 3: Raw Output & Reasoning Trace -->
                <div x-show="inspectModalTab === 'output'" class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="inspectModalOutputTab = 'parsed'"
                                    :class="inspectModalOutputTab === 'parsed' ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-white'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all">
                                Parsed JSON
                            </button>
                            <button type="button" @click="inspectModalOutputTab = 'raw'"
                                    :class="inspectModalOutputTab === 'raw' ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-white'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all">
                                Raw Response
                            </button>
                            <template x-if="inspectedRun && inspectedRun.reasoning_content">
                                <button type="button" @click="inspectModalOutputTab = 'reasoning'"
                                        :class="inspectModalOutputTab === 'reasoning' ? 'bg-violet-600/20 text-violet-300 border border-violet-500/30' : 'text-slate-400 hover:text-white'"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all flex items-center gap-1">
                                    <span>Reasoning Trace</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
                                </button>
                            </template>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="
                                        let text = '';
                                        if (inspectModalOutputTab === 'parsed') {
                                            text = JSON.stringify(inspectedRun.parsed_json || (inspectedFormatted?.validation?.parsed_data || {}), null, 2);
                                        } else if (inspectModalOutputTab === 'raw') {
                                            text = inspectedRun.raw_response || '';
                                        } else {
                                            text = inspectedRun.reasoning_content || '';
                                        }
                                        navigator.clipboard.writeText(text);
                                        copiedModal = true;
                                        setTimeout(() => copiedModal = false, 2000);
                                    "
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors border border-slate-700">
                                <template x-if="!copiedModal">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <span>Copy</span>
                                    </span>
                                </template>
                                <template x-if="copiedModal">
                                    <span class="flex items-center gap-1 text-emerald-400 font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Copied!</span>
                                    </span>
                                </template>
                            </button>

                            <button type="button" 
                                    @click="inspectModalOutputExpanded = !inspectModalOutputExpanded"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors border border-slate-700">
                                <template x-if="!inspectModalOutputExpanded">
                                    <span class="flex items-center gap-1 text-indigo-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                        </svg>
                                        <span>Expand</span>
                                    </span>
                                </template>
                                <template x-if="inspectModalOutputExpanded">
                                    <span class="flex items-center gap-1 text-amber-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0h4m-4 0v4m11 5l5 5m0 0h-4m4 0v-4m-5-11l5-5m0 0v4m0-4h-4m-11 5L4 20m0 0v-4m0 4h4" />
                                        </svg>
                                        <span>Collapse</span>
                                    </span>
                                </template>
                            </button>
                        </div>
                    </div>

                    <!-- Output Pre Container with High Contrast Guarantees -->
                    <div x-show="inspectModalOutputTab === 'parsed'">
                        <pre class="p-4 rounded-xl border border-slate-800 font-mono text-xs overflow-x-auto transition-all"
                             :class="inspectModalOutputExpanded ? 'max-h-none overflow-y-visible' : 'max-h-72 overflow-y-auto'"
                             style="background-color: #020617 !important; color: #34d399 !important;"
                             x-text="JSON.stringify(inspectedRun ? (inspectedRun.parsed_json || inspectedFormatted?.validation?.parsed_data || {}) : {}, null, 2)"></pre>
                    </div>

                    <div x-show="inspectModalOutputTab === 'raw'">
                        <pre class="p-4 rounded-xl border border-slate-800 font-mono text-xs whitespace-pre-wrap overflow-x-auto transition-all"
                             :class="inspectModalOutputExpanded ? 'max-h-none overflow-y-visible' : 'max-h-72 overflow-y-auto'"
                             style="background-color: #020617 !important; color: #e2e8f0 !important;"
                             x-text="inspectedRun ? inspectedRun.raw_response : ''"></pre>
                    </div>

                    <div x-show="inspectModalOutputTab === 'reasoning'">
                        <pre class="p-4 rounded-xl border border-slate-800 font-arabic text-xs whitespace-pre-wrap leading-relaxed overflow-x-auto transition-all"
                             :class="inspectModalOutputExpanded ? 'max-h-none overflow-y-visible' : 'max-h-72 overflow-y-auto'"
                             style="background-color: #020617 !important; color: #c4b5fd !important;"
                             x-text="inspectedRun ? inspectedRun.reasoning_content : ''"></pre>
                    </div>
                </div>
            </div>

            <!-- Sticky Modal Footer -->
            <div class="px-6 py-3.5 bg-slate-900/90 border-t border-slate-800 flex items-center justify-between flex-shrink-0">
                <div>
                    <button type="button" @click="exportInspectedRun()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors border border-slate-700">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span>Export JSON</span>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="inspectModal = false" 
                            class="px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors border border-slate-700">
                        Close
                    </button>
                    <button type="button" 
                            x-show="inspectedRun"
                            @click="loadRunIntoDashboard(inspectedRun.id)" 
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Load in Live Dashboard</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.arabicBenchmarkLab = function arabicBenchmarkLab() {
    return {
        hasConfiguredKey: @json($hasConfiguredApiKey),
        models: @json($modelsList),
        initialResult: @json($initialResult ?? null),
        searchQuery: '',
        isDropdownOpen: false,
        selectedModel: null,
        selectedProviderFilter: 'all',
        topProviders: ['Anthropic', 'OpenAI', 'Google', 'DeepSeek', 'Meta', 'Qwen', 'Mistral'],

        selectedModelId: '',
        openrouterModelId: 'qwen/qwen-2.5-72b-instruct',
        thinkingMode: 'none',
        thinkingLevel: 'medium',
        reasoningTokens: 2048,
        
        isRunning: false,
        elapsedSeconds: 0,
        timerInterval: null,
        errorMessage: null,
        
        currentResult: null,
        inspectModal: false,
        inspectedRun: null,
        inspectedFormatted: null,
        inspectModalTab: 'overview',
        inspectModalOutputTab: 'parsed',
        inspectModalOutputExpanded: false,
        copiedModal: false,

        init() {
            // Find default model if present in catalog
            const initial = this.models.find(m => m.openrouter_id === this.openrouterModelId);
            if (initial) {
                this.selectedModel = initial;
                this.selectedModelId = initial.id;
            }

            // If an initial run was requested via URL ?run={id}
            if (this.initialResult) {
                this.currentResult = this.initialResult;
                this.$nextTick(() => {
                    const el = document.getElementById('benchmark-results-dashboard');
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            }
        },

        get filteredModels() {
            let list = this.models;

            if (this.selectedProviderFilter !== 'all') {
                const target = this.selectedProviderFilter.toLowerCase();
                list = list.filter(m => (m.provider || '').toLowerCase().includes(target));
            }

            if (this.searchQuery && this.searchQuery.trim() !== '') {
                const q = this.searchQuery.trim().toLowerCase();
                list = list.filter(m => 
                    (m.name || '').toLowerCase().includes(q) ||
                    (m.openrouter_id || '').toLowerCase().includes(q) ||
                    (m.provider || '').toLowerCase().includes(q)
                );
            }

            // Return top 60 matching items for ultra-responsive rendering
            return list.slice(0, 60);
        },

        selectModel(model) {
            this.selectedModel = model;
            this.selectedModelId = model.id;
            this.openrouterModelId = model.openrouter_id;
            this.searchQuery = '';
            this.isDropdownOpen = false;
        },

        clearSelectedModel() {
            this.selectedModel = null;
            this.selectedModelId = '';
            this.searchQuery = '';
        },

        useSearchQueryAsSlug() {
            if (!this.searchQuery) return;
            this.openrouterModelId = this.searchQuery.trim();
            this.selectedModel = null;
            this.selectedModelId = '';
            this.isDropdownOpen = false;
        },

        setQuickModel(slug) {
            this.openrouterModelId = slug;
            const found = this.models.find(m => m.openrouter_id === slug);
            if (found) {
                this.selectedModel = found;
                this.selectedModelId = found.id;
            } else {
                this.selectedModel = null;
                this.selectedModelId = '';
            }
        },

        async runBenchmark() {
            this.errorMessage = null;

            if (!this.openrouterModelId || this.openrouterModelId.trim() === '') {
                this.errorMessage = 'Please choose a model or enter an OpenRouter model identifier.';
                return;
            }

            console.log('Starting Arabic benchmark test for model:', this.openrouterModelId, {
                mode: this.thinkingMode,
                level: this.thinkingLevel,
                tokens: this.reasoningTokens
            });

            this.isRunning = true;
            this.elapsedSeconds = 0;
            this.currentResult = null;

            this.timerInterval = setInterval(() => {
                this.elapsedSeconds++;
            }, 1000);

            try {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                const response = await fetch("{{ route('admin.benchmarks.arabic.run') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        model_id: this.selectedModelId || null,
                        openrouter_model_id: this.openrouterModelId.trim(),
                        thinking_mode: this.thinkingMode,
                        thinking_level: this.thinkingLevel,
                        reasoning_tokens: this.thinkingMode === 'custom_tokens' ? this.reasoningTokens : null
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'An error occurred while executing the benchmark test.');
                }

                console.log('Arabic benchmark evaluation succeeded:', data);
                this.currentResult = data;
            } catch (err) {
                console.error('Arabic benchmark execution error:', err);
                this.errorMessage = err.message || 'An error occurred while running the benchmark.';
            } finally {
                this.isRunning = false;
                if (this.timerInterval) {
                    clearInterval(this.timerInterval);
                    this.timerInterval = null;
                }
            }
        },

        async loadRunDetails(runId) {
            try {
                const res = await fetch(`/admin/benchmarks/arabic/runs/${runId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    this.inspectedRun = data.run;
                    this.inspectedFormatted = data.formatted || null;
                    this.inspectModalTab = 'overview';
                    this.inspectModalOutputTab = 'parsed';
                    this.inspectModalOutputExpanded = false;
                    this.inspectModal = true;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async loadRunIntoDashboard(runId) {
            try {
                const res = await fetch(`/admin/benchmarks/arabic/runs/${runId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success && data.formatted) {
                    this.currentResult = data.formatted;
                    this.inspectModal = false;
                    this.$nextTick(() => {
                        const el = document.getElementById('benchmark-results-dashboard');
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth' });
                        }
                    });
                }
            } catch (e) {
                console.error(e);
            }
        },

        exportInspectedRun() {
            if (!this.inspectedRun) return;
            const toExport = this.inspectedFormatted || this.inspectedRun;
            const blob = new Blob([JSON.stringify(toExport, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `benchmark_run_${this.inspectedRun.id}_${(this.inspectedRun.openrouter_model_id || 'model').replace('/', '_')}.json`;
            a.click();
            URL.revokeObjectURL(url);
        },

        printOrExportResult() {
            if (!this.currentResult) return;
            const blob = new Blob([JSON.stringify(this.currentResult, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `benchmark_${this.currentResult.openrouter_model_id.replace('/', '_')}_${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
        }
    };
};

document.addEventListener('alpine:init', () => {
    if (window.Alpine && typeof window.Alpine.data === 'function') {
        window.Alpine.data('arabicBenchmarkLab', window.arabicBenchmarkLab);
    }
});
</script>
@endsection
