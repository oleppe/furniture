@extends('layouts.admin')

@section('title', $isEditing ? 'Edit Research Article' : 'New Research Article')
@section('page_title', $isEditing ? 'Edit Article: '.$article->title : 'Compose New Research Article')

@php
    $blocks = $article->blocks ?? [];
    $initialPayload = [
        'title' => old('title', $article->title ?? ''),
        'slug' => old('slug', $article->slug ?? ''),
        'hero_metrics' => $article->hero_metrics ?: [['value' => '', 'label' => '', 'tone' => 'efficiency']],
        'takeaways' => $article->takeaways ?: [['num' => '01', 'title' => '', 'body' => '']],
        'sections' => collect($article->sections ?: [['id' => 'context', 'title' => '1. مقدمة الدراسة والسياق', 'paragraphs' => ['']]])
            ->map(fn (array $section): array => [
                'id' => $section['id'] ?? '',
                'title' => $section['title'] ?? '',
                'paragraphs_text' => is_array($section['paragraphs'] ?? null)
                    ? implode("\n\n", $section['paragraphs'])
                    : (string) ($section['paragraphs'] ?? ''),
            ])->values()->all(),
        'comparison_rows' => $article->comparison_rows ?: [],
        'charts' => $blocks['charts'] ?? [],
        'tables' => $blocks['tables'] ?? [],
        'quoted_models' => $blocks['quoted_models'] ?? [],
        'catalog' => $catalogModels,
    ];
@endphp

@section('content')
<div x-data="articleComposer(@js($initialPayload))" class="space-y-6">

    <!-- Top Back & Preview Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.blog.articles.index') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white text-xs font-semibold">
            &larr; Back to Articles
        </a>

        <div class="flex items-center gap-2">
            @if ($isEditing)
                <a href="{{ route('blog.show', $article->slug) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-indigo-300 text-xs font-semibold">
                    Open Public Article
                </a>
            @endif
        </div>
    </div>

    <!-- Section Switcher Pills -->
    <div class="glass-card p-2 rounded-2xl border border-slate-800/80 flex flex-wrap items-center gap-1.5 sticky top-20 z-20">
        <button type="button" @click="panel = 'basics'" :class="panel === 'basics' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
            1. Basics & Summary
        </button>
        <button type="button" @click="panel = 'sections'" :class="panel === 'sections' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
            2. Sections & Inline Quotes (<span x-text="sections.length"></span>)
        </button>
        <button type="button" @click="panel = 'quotes'" :class="panel === 'quotes' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
            3. Quote Models (<span x-text="quotedModels.length"></span>)
        </button>
        <button type="button" @click="panel = 'charts'" :class="panel === 'charts' ? 'bg-cyan-600 text-white' : 'text-slate-400 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
            4. Chart Builder (<span x-text="charts.length"></span>)
        </button>
        <button type="button" @click="panel = 'tables'" :class="panel === 'tables' ? 'bg-amber-600 text-white' : 'text-slate-400 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
            5. Table Builder (<span x-text="comparisonRows.length + ' rows / ' + tables.length + ' custom'"></span>)
        </button>
        <button type="button" @click="panel = 'publish'" :class="panel === 'publish' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
            6. Code & Publish
        </button>
    </div>

    <form method="POST"
          action="{{ $isEditing ? route('admin.blog.articles.update', $article) : route('admin.blog.articles.store') }}"
          @submit="syncHiddenInputs()"
          class="space-y-6">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <!-- Serialized Structured Payloads -->
        <input type="hidden" name="hero_metrics_json" x-ref="heroMetricsInput">
        <input type="hidden" name="takeaways_json" x-ref="takeawaysInput">
        <input type="hidden" name="sections_json" x-ref="sectionsInput">
        <input type="hidden" name="comparison_rows_json" x-ref="comparisonRowsInput">
        <input type="hidden" name="charts_json" x-ref="chartsInput">
        <input type="hidden" name="tables_json" x-ref="tablesInput">
        <input type="hidden" name="quoted_models_json" x-ref="quotedModelsInput">

        <!-- ==================== PANEL 1: BASICS & EXECUTIVE SUMMARY ==================== -->
        <div x-show="panel === 'basics'" class="space-y-6">
            <div class="glass-card p-6 rounded-2xl border border-slate-800/80 space-y-5">
                <h2 class="text-base font-bold text-white border-b border-slate-800 pb-3">Article Metadata & Positioning</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Article Title (Arabic / Display)</label>
                        <input type="text" name="title" x-model="title" required dir="auto"
                               placeholder="مثال: تحليل كفاءة التوكن العربي في النماذج الرائدة"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">URL Slug</label>
                        <div class="flex gap-2">
                            <input type="text" name="slug" x-model="slug" placeholder="auto-generated-if-empty"
                                   class="flex-1 px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-indigo-300 font-mono text-xs">
                            <button type="button" @click="generateSlug()" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs text-slate-200 font-semibold">Auto</button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Report ID</label>
                        <input type="text" name="report_id" value="{{ old('report_id', $article->report_id ?? 'EL-TR-'.now()->format('Y-md')) }}"
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Blog Category</label>
                        <select name="blog_category_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                            <option value="">— Uncategorized —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('blog_category_id', $article->blog_category_id) == $cat->id)>{{ $cat->name }} ({{ $cat->slug }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Sidebar Spotlight AI Model</label>
                        <select name="spotlight_model_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                            <option value="">— Auto-select top ranked model —</option>
                            @foreach ($catalogModels as $m)
                                <option value="{{ $m['id'] }}" @selected(old('spotlight_model_id', $article->spotlight_model_id) == $m['id'])>
                                    {{ $m['name'] }} ({{ $m['provider'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Executive Excerpt / Lead Paragraph</label>
                        <textarea name="excerpt" rows="3" required dir="auto"
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">{{ old('excerpt', $article->excerpt) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Key Metric Pill Value</label>
                        <input type="text" name="key_metric_value" value="{{ old('key_metric_value', $article->key_metric_value) }}" placeholder="e.g. 1.28 T/Word"
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-emerald-300 font-mono text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Key Metric Pill Label</label>
                        <input type="text" name="key_metric_label" value="{{ old('key_metric_label', $article->key_metric_label) }}" placeholder="e.g. أفضل كفاءة مسجلة" dir="auto"
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Author Name</label>
                        <input type="text" name="author_name" value="{{ old('author_name', $article->author_name) }}" dir="auto"
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Read Time</label>
                        <input type="text" name="read_time" value="{{ old('read_time', $article->read_time) }}" dir="auto"
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                    </div>
                </div>
            </div>

            <!-- Hero Metrics Builder -->
            <div class="glass-card p-6 rounded-2xl border border-slate-800/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white">Featured Metric Highlights (up to 3)</h3>
                        <p class="text-xs text-slate-400">Displayed on the featured study card in the blog index.</p>
                    </div>
                    <button type="button" @click="addHeroMetric()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs text-white font-semibold">+ Add Metric</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <template x-for="(metric, idx) in heroMetrics" :key="idx">
                        <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-[11px] font-mono text-indigo-400" x-text="'Metric #' + (idx + 1)"></span>
                                <button type="button" @click="heroMetrics.splice(idx, 1)" class="text-rose-400 text-xs hover:underline">Remove</button>
                            </div>
                            <input type="text" x-model="metric.value" placeholder="Value (e.g. -34.8%)" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs font-mono">
                            <input type="text" x-model="metric.label" placeholder="Label (Arabic)" dir="auto" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs">
                        </div>
                    </template>
                </div>
            </div>

            <!-- Executive Takeaways Builder -->
            <div class="glass-card p-6 rounded-2xl border border-slate-800/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white">Executive Summary Takeaways</h3>
                        <p class="text-xs text-slate-400">Concise takeaway cards shown at the top of the article.</p>
                    </div>
                    <button type="button" @click="addTakeaway()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs text-white font-semibold">+ Add Takeaway</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <template x-for="(item, idx) in takeaways" :key="idx">
                        <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-2">
                            <div class="flex justify-between items-center">
                                <input type="text" x-model="item.num" class="w-12 px-2 py-1 rounded bg-slate-950 border border-slate-800 text-indigo-400 text-xs font-mono">
                                <button type="button" @click="takeaways.splice(idx, 1)" class="text-rose-400 text-xs hover:underline">Remove</button>
                            </div>
                            <input type="text" x-model="item.title" placeholder="Takeaway Title" dir="auto" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs">
                            <textarea rows="3" x-model="item.body" placeholder="Takeaway explanation..." dir="auto" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-200 text-xs"></textarea>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ==================== PANEL 2: ARTICLE SECTIONS & INLINE QUOTE INSERTER ==================== -->
        <div x-show="panel === 'sections'" x-cloak class="space-y-5">
            <!-- Quick Inline Model Quote Toolbar -->
            <div class="glass-card p-4 rounded-2xl border border-emerald-500/30 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-emerald-300">Inline Model Quote Shortcode Inserter</h3>
                        <p class="text-xs text-slate-400">Select a model and click Insert to embed a live quoted model card directly inside a paragraph using <code class="text-emerald-300">[model:slug|quote="..."]</code>.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                    <select x-model="inlineModelSlug" class="px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                        <option value="">Choose AI Model...</option>
                        <template x-for="m in catalog" :key="m.id">
                            <option :value="m.slug" x-text="m.name + ' (' + m.provider + ')'"></option>
                        </template>
                    </select>
                    <input type="text" x-model="inlineQuoteText" placeholder="Optional quote / note about this model..." dir="auto"
                           class="md:col-span-2 px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                    <div class="flex gap-2">
                        <select x-model.number="activeSectionIdx" class="px-2 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                            <template x-for="(sec, idx) in sections" :key="idx">
                                <option :value="idx" x-text="'Sec #' + (idx + 1)"></option>
                            </template>
                        </select>
                        <button type="button" @click="insertInlineModelQuote()"
                                class="flex-1 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold cursor-pointer">
                            Insert Shortcode
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dynamic Sections List -->
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-white">Article Body Sections</h2>
                <button type="button" @click="addSection()" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold cursor-pointer">+ Add Section</button>
            </div>

            <div class="space-y-4">
                <template x-for="(section, idx) in sections" :key="idx">
                    <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-mono text-indigo-400" x-text="'Section #' + (idx + 1)"></span>
                            <button type="button" @click="sections.splice(idx, 1)" class="text-rose-400 text-xs hover:underline">Remove Section</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs text-slate-400 mb-1">Section Heading</label>
                                <input type="text" x-model="section.title" dir="auto" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">Anchor ID (TOC)</label>
                                <input type="text" x-model="section.id" placeholder="e.g. benchmarks" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-indigo-300 font-mono text-xs">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Paragraphs (separate paragraphs with a blank line; supports [model:slug|quote="..."])</label>
                            <textarea rows="5" x-model="section.paragraphs_text" dir="auto"
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-slate-200 text-sm leading-relaxed"></textarea>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ==================== PANEL 3: MODEL QUOTES BUILDER ==================== -->
        <div x-show="panel === 'quotes'" x-cloak class="space-y-5">
            <div class="glass-card p-5 rounded-2xl border border-emerald-500/30 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-white">Quote & Embed AI Models inside the Article</h2>
                        <p class="text-xs text-slate-400">Attach structured Model Quote cards that display live Arabic score, token compression, cost, and an analyst verdict with a direct link to the model page.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-300 mb-1">Select AI Model from Catalog</label>
                        <select x-model="selectedQuoteModelId" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                            <option value="">Choose an AI Model...</option>
                            <template x-for="m in catalog" :key="m.id">
                                <option :value="m.id" x-text="m.name + ' — ' + m.provider + (m.arabic_score ? ' (' + m.arabic_score + '/156)' : '')"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Placement</label>
                        <select x-model="selectedQuotePlacement" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                            <option value="after_section_1">After Section 1</option>
                            <option value="after_section_2">After Section 2</option>
                            <option value="after_section_3">After Section 3</option>
                            <option value="showcase">Dedicated Models Showcase</option>
                        </select>
                    </div>
                    <button type="button" @click="addQuotedModel()"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold cursor-pointer">
                        + Add Model Quote
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(qm, idx) in quotedModels" :key="idx">
                        <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-950/70 border border-emerald-500/30 text-emerald-300 font-bold text-xs" x-text="qm.name"></span>
                                    <span class="text-xs font-mono text-slate-400" x-text="qm.slug"></span>
                                </div>
                                <button type="button" @click="quotedModels.splice(idx, 1)" class="text-rose-400 text-xs hover:underline">Remove Quote</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">Verdict / Highlight Badge</label>
                                    <input type="text" x-model="qm.badge" dir="auto" placeholder="e.g. الأعلى كفاءة في التوكن"
                                           class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">Provider Name</label>
                                    <input type="text" x-model="qm.provider" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-300 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">Article Placement</label>
                                    <select x-model="qm.placement" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs">
                                        <option value="after_section_1">After Section 1</option>
                                        <option value="after_section_2">After Section 2</option>
                                        <option value="after_section_3">After Section 3</option>
                                        <option value="showcase">Dedicated Models Showcase</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-400 mb-1">Analyst Quote / Takeaway for this Model</label>
                                <textarea rows="2" x-model="qm.quote" dir="auto" placeholder="اكتب اقتباساً أو ملاحظة هندسية تشرح سبب الاستشهاد بهذا النموذج في المقال..."
                                          class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs"></textarea>
                            </div>
                        </div>
                    </template>

                    <div x-show="quotedModels.length === 0" class="text-center py-6 text-xs text-slate-500">
                        No quoted models added yet. Pick a model above to quote it with live metrics in your article.
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== PANEL 4: CHART BUILDER ==================== -->
        <div x-show="panel === 'charts'" x-cloak class="space-y-5">
            <div class="glass-card p-5 rounded-2xl border border-cyan-500/30 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-white">Interactive Bar Chart Builder</h2>
                        <p class="text-xs text-slate-400">Build comparison charts manually or auto-import metrics directly from live AI models in your database.</p>
                    </div>
                    <button type="button" @click="addChart()"
                            class="px-3.5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold cursor-pointer">
                        + Add New Chart
                    </button>
                </div>

                <div class="space-y-6">
                    <template x-for="(chart, cIdx) in charts" :key="cIdx">
                        <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono text-cyan-400" x-text="'Chart #' + (cIdx + 1)"></span>
                                <button type="button" @click="charts.splice(cIdx, 1)" class="text-rose-400 text-xs hover:underline">Remove Chart</button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">Chart Title</label>
                                    <input type="text" x-model="chart.title" dir="auto" placeholder="مقارنة معامل التقطيع العربي" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">Subtitle / Note</label>
                                    <input type="text" x-model="chart.subtitle" dir="auto" placeholder="القيمة الأقل تعني توفيراً أكبر" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-300 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">Unit</label>
                                    <input type="text" x-model="chart.unit" placeholder="T/Word, %, $, t/s" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-cyan-300 font-mono text-xs">
                                </div>
                            </div>

                            <!-- 1-Click Auto-Import Model Bar -->
                            <div class="flex flex-wrap items-center gap-2 p-3 rounded-lg bg-slate-950/80 border border-slate-800">
                                <span class="text-[11px] font-semibold text-cyan-300">Auto-add Bar from Model:</span>
                                <select x-model="chartImportModelId" class="px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                    <option value="">Select Model...</option>
                                    <template x-for="m in catalog" :key="m.id">
                                        <option :value="m.id" x-text="m.name"></option>
                                    </template>
                                </select>
                                <select x-model="chartImportMetric" class="px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                    <option value="arabic_score">Arabic Score (/156)</option>
                                    <option value="tokens_per_word">Tokens / Word</option>
                                    <option value="tps">Speed (TPS)</option>
                                    <option value="input_price">Input Price ($/1M)</option>
                                </select>
                                <button type="button" @click="importBarIntoChart(cIdx)" class="px-3 py-1.5 rounded-lg bg-cyan-600/20 border border-cyan-500/40 text-cyan-300 hover:bg-cyan-600/30 text-xs font-semibold cursor-pointer">
                                    Import Bar
                                </button>
                                <button type="button" @click="addManualBar(cIdx)" class="ms-auto px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold cursor-pointer">
                                    + Manual Bar
                                </button>
                            </div>

                            <!-- Bars List -->
                            <div class="space-y-2">
                                <template x-for="(bar, bIdx) in chart.bars" :key="bIdx">
                                    <div class="grid grid-cols-12 gap-2 items-center">
                                        <input type="text" x-model="bar.label" placeholder="Model / Label" class="col-span-4 px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs">
                                        <input type="number" step="0.01" x-model.number="bar.value" placeholder="Value" class="col-span-2 px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-cyan-300 font-mono text-xs">
                                        <input type="text" x-model="bar.display_value" placeholder="Display" class="col-span-2 px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-300 font-mono text-xs">
                                        <select x-model="bar.color" class="col-span-2 px-2 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs">
                                            <option value="primary">Cyan</option>
                                            <option value="emerald">Emerald</option>
                                            <option value="amber">Amber</option>
                                            <option value="indigo">Indigo</option>
                                            <option value="rose">Rose</option>
                                        </select>
                                        <label class="col-span-1 flex items-center justify-center gap-1 text-[11px] text-slate-300">
                                            <input type="checkbox" x-model="bar.highlight" class="rounded bg-slate-950 border-slate-700">
                                            Top
                                        </label>
                                        <button type="button" @click="chart.bars.splice(bIdx, 1)" class="col-span-1 text-rose-400 text-xs text-right">Del</button>
                                    </div>
                                </template>
                            </div>

                            <!-- Live Visual Preview -->
                            <div x-show="chart.bars.length > 0" class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                                <div class="text-[11px] font-semibold text-slate-400">Live Preview</div>
                                <template x-for="(bar, bIdx) in chart.bars" :key="'prev-' + bIdx">
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-slate-200" x-text="bar.label"></span>
                                            <span class="font-mono text-cyan-300" x-text="(bar.display_value || bar.value) + ' ' + (chart.unit || '')"></span>
                                        </div>
                                        <div class="w-full h-2 rounded-full bg-slate-900 overflow-hidden">
                                            <div class="h-full rounded-full bg-cyan-500 transition-all" :style="'width: ' + barPercent(chart.bars, bar.value) + '%'"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ==================== PANEL 5: TABLE BUILDER ==================== -->
        <div x-show="panel === 'tables'" x-cloak class="space-y-6">
            <!-- Benchmark Comparison Table -->
            <div class="glass-card p-5 rounded-2xl border border-amber-500/30 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-white">Standard Model Comparison Table</h2>
                        <p class="text-xs text-slate-400">Shown in the Benchmark Results section of the article. Import live AI models in one click or enter rows manually.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 p-3 rounded-xl bg-slate-900/70 border border-slate-800">
                    <select x-model="tableImportModelId" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs min-w-[220px]">
                        <option value="">Import Row from Live Model...</option>
                        <template x-for="m in catalog" :key="m.id">
                            <option :value="m.id" x-text="m.name + ' (' + m.provider + ')'"></option>
                        </template>
                    </select>
                    <button type="button" @click="importComparisonRow()" class="px-3.5 py-2 rounded-xl bg-amber-600/20 border border-amber-500/40 text-amber-300 hover:bg-amber-600/30 text-xs font-semibold cursor-pointer">
                        + Import Model Row
                    </button>
                    <button type="button" @click="addComparisonRow()" class="ms-auto px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold cursor-pointer">
                        + Add Blank Row
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(row, rIdx) in comparisonRows" :key="rIdx">
                        <div class="grid grid-cols-12 gap-2 items-center p-2.5 rounded-xl bg-slate-900/60 border border-slate-800">
                            <input type="text" x-model="row.model" placeholder="Model Name" class="col-span-3 px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs">
                            <input type="text" x-model="row.tpw" placeholder="T/Word" class="col-span-2 px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-emerald-300 font-mono text-xs">
                            <input type="text" x-model="row.arabic_score" placeholder="148 / 156" class="col-span-2 px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white font-mono text-xs">
                            <input type="text" x-model="row.cost_usd" placeholder="$1.45" class="col-span-2 px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-amber-300 font-mono text-xs">
                            <input type="text" x-model="row.cost_sar" placeholder="5.44 ر.س" dir="auto" class="col-span-2 px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-300 text-xs">
                            <div class="col-span-1 flex items-center justify-end gap-2">
                                <input type="checkbox" x-model="row.highlight" title="Highlight Best" class="rounded bg-slate-950 border-slate-700">
                                <button type="button" @click="comparisonRows.splice(rIdx, 1)" class="text-rose-400 text-xs">Del</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Custom Multi-Column Tables Builder -->
            <div class="glass-card p-5 rounded-2xl border border-slate-800/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-white">Custom Flexible Tables</h3>
                        <p class="text-xs text-slate-400">Create any additional structured data tables with custom headers and rows.</p>
                    </div>
                    <button type="button" @click="addCustomTable()" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold cursor-pointer">
                        + Add Custom Table
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(tbl, tIdx) in tables" :key="tIdx">
                        <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <input type="text" x-model="tbl.title" dir="auto" placeholder="Table Title" class="flex-1 px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs font-bold">
                                <button type="button" @click="addCustomColumn(tIdx)" class="px-2.5 py-1.5 rounded-lg bg-slate-800 text-xs text-slate-200">+ Col</button>
                                <button type="button" @click="addCustomRow(tIdx)" class="px-2.5 py-1.5 rounded-lg bg-slate-800 text-xs text-slate-200">+ Row</button>
                                <button type="button" @click="tables.splice(tIdx, 1)" class="text-rose-400 text-xs">Remove</button>
                            </div>

                            <!-- Headers -->
                            <div class="grid gap-2" :style="'grid-template-columns: repeat(' + tbl.headers.length + ', minmax(0, 1fr))'">
                                <template x-for="(hdr, hIdx) in tbl.headers" :key="hIdx">
                                    <input type="text" x-model="tbl.headers[hIdx]" dir="auto" placeholder="Header" class="px-2.5 py-1.5 rounded-lg bg-slate-950 border border-indigo-500/30 text-indigo-300 text-xs font-semibold">
                                </template>
                            </div>

                            <!-- Rows -->
                            <div class="space-y-1.5">
                                <template x-for="(row, rIdx) in tbl.rows" :key="rIdx">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 grid gap-2" :style="'grid-template-columns: repeat(' + tbl.headers.length + ', minmax(0, 1fr))'">
                                            <template x-for="(hdr, cIdx) in tbl.headers" :key="cIdx">
                                                <input type="text" x-model="tbl.rows[rIdx][cIdx]" dir="auto" placeholder="Cell" class="px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs">
                                            </template>
                                        </div>
                                        <button type="button" @click="tbl.rows.splice(rIdx, 1)" class="text-rose-400 text-xs">Del</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ==================== PANEL 6: CODE SNIPPET & PUBLISH ==================== -->
        <div x-show="panel === 'publish'" x-cloak class="glass-card p-6 rounded-2xl border border-slate-800/80 space-y-5">
            <h2 class="text-base font-bold text-white border-b border-slate-800 pb-3">Code Probe & Publishing Settings</h2>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Technical Code Snippet (Optional)</label>
                <textarea name="code_snippet" rows="6" dir="ltr" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-emerald-300 font-mono text-xs">{{ old('code_snippet', $article->code_snippet) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Publication Date</label>
                    <input type="date" name="published_at" value="{{ old('published_at', $article->published_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                           class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                </div>
                <label class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-900/70 border border-slate-800 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $article->is_published)) class="rounded bg-slate-950 border-slate-700 text-emerald-600">
                    <div>
                        <div class="text-xs font-semibold text-white">Published</div>
                        <div class="text-[11px] text-slate-400">Visible on /blog and /sitemap.xml</div>
                    </div>
                </label>
                <label class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-900/70 border border-slate-800 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $article->is_featured)) class="rounded bg-slate-950 border-slate-700 text-indigo-600">
                    <div>
                        <div class="text-xs font-semibold text-white">Featured Study</div>
                        <div class="text-[11px] text-slate-400">Pin to the spotlight hero on /blog</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Sticky Save Footer -->
        <div class="glass-card p-4 rounded-2xl border border-slate-800/80 flex items-center justify-between gap-4">
            <div class="text-xs text-slate-400">
                Ready to save? All charts, tables, and quoted models are serialized automatically.
            </div>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 cursor-pointer">
                {{ $isEditing ? 'Save Changes' : 'Publish Research Article' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function articleComposer(initial) {
        return {
            panel: 'basics',
            title: initial.title || '',
            slug: initial.slug || '',
            heroMetrics: initial.hero_metrics || [],
            takeaways: initial.takeaways || [],
            sections: initial.sections || [],
            comparisonRows: initial.comparison_rows || [],
            charts: initial.charts || [],
            tables: initial.tables || [],
            quotedModels: initial.quoted_models || [],
            catalog: initial.catalog || [],
            activeSectionIdx: 0,
            inlineModelSlug: '',
            inlineQuoteText: '',
            selectedQuoteModelId: '',
            selectedQuotePlacement: 'after_section_1',
            chartImportModelId: '',
            chartImportMetric: 'arabic_score',
            tableImportModelId: '',

            generateSlug() {
                const raw = (this.title || '').trim().toLowerCase();
                this.slug = raw
                    .replace(/[^\p{L}\p{N}\s-]/gu, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '') || ('article-' + Date.now());
            },

            addHeroMetric() {
                if (this.heroMetrics.length < 3) {
                    this.heroMetrics.push({ value: '', label: '', tone: 'primary' });
                }
            },

            addTakeaway() {
                const num = String(this.takeaways.length + 1).padStart(2, '0');
                this.takeaways.push({ num, title: '', body: '' });
            },

            addSection() {
                const n = this.sections.length + 1;
                this.sections.push({ id: 'section-' + n, title: n + '. قسم جديد', paragraphs_text: '' });
                this.activeSectionIdx = this.sections.length - 1;
            },

            insertInlineModelQuote() {
                if (!this.inlineModelSlug || !this.sections[this.activeSectionIdx]) {
                    return;
                }
                const cleanQuote = (this.inlineQuoteText || '').replace(/"/g, "'").trim();
                const shortcode = cleanQuote
                    ? `[model:${this.inlineModelSlug}|quote="${cleanQuote}"]`
                    : `[model:${this.inlineModelSlug}]`;

                const current = this.sections[this.activeSectionIdx].paragraphs_text || '';
                this.sections[this.activeSectionIdx].paragraphs_text = current
                    ? current.trimEnd() + '\n\n' + shortcode
                    : shortcode;
                this.inlineQuoteText = '';
            },

            addQuotedModel() {
                const model = this.catalog.find((m) => String(m.id) === String(this.selectedQuoteModelId));
                if (!model) {
                    return;
                }
                this.quotedModels.push({
                    model_id: model.id,
                    slug: model.slug,
                    name: model.name,
                    provider: model.provider,
                    badge: 'نموذج مرجعي في الدراسة',
                    quote: '',
                    placement: this.selectedQuotePlacement,
                });
                this.selectedQuoteModelId = '';
            },

            addChart() {
                this.charts.push({
                    title: 'مقارنة أداء النماذج',
                    subtitle: 'بيانات حية من مختبر إدراك Lab',
                    unit: 'pts',
                    bars: [],
                });
            },

            addManualBar(chartIndex) {
                this.charts[chartIndex].bars.push({
                    label: '',
                    value: 0,
                    display_value: '',
                    color: 'primary',
                    highlight: false,
                });
            },

            importBarIntoChart(chartIndex) {
                const model = this.catalog.find((m) => String(m.id) === String(this.chartImportModelId));
                if (!model) {
                    return;
                }
                const metric = this.chartImportMetric;
                let value = 0;
                let display = '0';

                if (metric === 'arabic_score') {
                    value = Number(model.arabic_score ?? 0);
                    display = String(value);
                } else if (metric === 'tokens_per_word') {
                    value = Number(model.tokens_per_word ?? 0);
                    display = value.toFixed(2);
                } else if (metric === 'tps') {
                    value = Number(model.tps ?? 0);
                    display = value.toFixed(0);
                } else if (metric === 'input_price') {
                    value = Number(model.input_price ?? 0);
                    display = '$' + value.toFixed(2);
                }

                this.charts[chartIndex].bars.push({
                    label: model.name,
                    value,
                    display_value: display,
                    color: 'primary',
                    highlight: false,
                });
            },

            barPercent(bars, val) {
                const max = Math.max(...bars.map((b) => Number(b.value) || 0), 1);
                return Math.min(100, Math.max(4, Math.round(((Number(val) || 0) / max) * 100)));
            },

            addComparisonRow() {
                this.comparisonRows.push({
                    model: '',
                    tpw: '',
                    arabic_score: '',
                    cost_usd: '',
                    cost_sar: '',
                    highlight: false,
                });
            },

            importComparisonRow() {
                const model = this.catalog.find((m) => String(m.id) === String(this.tableImportModelId));
                if (!model) {
                    return;
                }
                this.comparisonRows.push({
                    model: model.name,
                    tpw: model.tokens_per_word !== null ? Number(model.tokens_per_word).toFixed(2) : '—',
                    arabic_score: model.arabic_score !== null ? `${model.arabic_score} / 156` : '—',
                    cost_usd: model.cost_1m_words_usd !== null ? `$${Number(model.cost_1m_words_usd).toFixed(2)}` : '—',
                    cost_sar: model.cost_1m_words_sar !== null ? `${Number(model.cost_1m_words_sar).toFixed(2)} ر.س` : '—',
                    highlight: false,
                });
                this.tableImportModelId = '';
            },

            addCustomTable() {
                this.tables.push({
                    title: 'جدول مقارنة مخصص',
                    headers: ['البند', 'القيمة', 'الملاحظة'],
                    rows: [['', '', '']],
                });
            },

            addCustomColumn(tableIdx) {
                this.tables[tableIdx].headers.push('عمود جديد');
                this.tables[tableIdx].rows.forEach((row) => row.push(''));
            },

            addCustomRow(tableIdx) {
                const len = this.tables[tableIdx].headers.length;
                this.tables[tableIdx].rows.push(Array(len).fill(''));
            },

            syncHiddenInputs() {
                const normalizedSections = this.sections.map((sec) => ({
                    id: sec.id,
                    title: sec.title,
                    paragraphs: (sec.paragraphs_text || '')
                        .split(/\n{2,}/)
                        .map((p) => p.trim())
                        .filter(Boolean),
                }));

                this.$refs.heroMetricsInput.value = JSON.stringify(this.heroMetrics);
                this.$refs.takeawaysInput.value = JSON.stringify(this.takeaways);
                this.$refs.sectionsInput.value = JSON.stringify(normalizedSections);
                this.$refs.comparisonRowsInput.value = JSON.stringify(this.comparisonRows);
                this.$refs.chartsInput.value = JSON.stringify(this.charts);
                this.$refs.tablesInput.value = JSON.stringify(this.tables);
                this.$refs.quotedModelsInput.value = JSON.stringify(this.quotedModels);
            },
        };
    }
</script>
@endpush

