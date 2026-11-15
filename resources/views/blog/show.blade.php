@extends('layouts.public')

@php
    $pageUrl = route('blog.show', $post['slug']);

    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'TechArticle',
        '@id' => $pageUrl.'#article',
        'headline' => $post['title'],
        'description' => $post['excerpt'],
        'datePublished' => $post['published_at'],
        'dateModified' => $post['published_at'],
        'inLanguage' => 'ar',
        'url' => $pageUrl,
        'author' => ['@type' => 'Organization', 'name' => $post['author']],
        'publisher' => ['@id' => route('home').'#organization'],
        'isPartOf' => ['@id' => route('blog.index').'#blog'],
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'الرئيسية', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'المدونة والأبحاث', 'item' => route('blog.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post['category_label'], 'item' => $pageUrl],
        ],
    ];
@endphp

@section('title', $post['title'].' | '.config('seo.site_name'))
@section('meta_description', Str::limit($post['excerpt'], 155))
@section('canonical', $pageUrl)

@push('head')
    <script type="application/ld+json">
        @json($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
    <script type="application/ld+json">
        @json($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    </script>
@endpush

@section('content')
<div x-data="blogArticle()" @scroll.window="updateProgress()">

    <!-- Reading Progress Bar -->
    <div class="fixed top-0 inset-x-0 z-[60] h-[3px] bg-transparent pointer-events-none">
        <div class="h-full bg-primary transition-[width] duration-75" :style="'width: ' + progress + '%'"></div>
    </div>

    <!-- Breadcrumb -->
    <div class="w-full px-gutter md:px-gutter-desktop pt-space-md">
        <div class="max-w-7xl mx-auto">
            <nav class="flex flex-wrap items-center gap-space-xs font-body-sm text-body-sm text-text-tertiary" aria-label="مسار التنقل">
                <a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="text-text-tertiary/50" aria-hidden="true">/</span>
                <a href="{{ route('blog.index') }}" class="hover:text-primary transition-colors">المدونة والأبحاث</a>
                <span class="text-text-tertiary/50" aria-hidden="true">/</span>
                <span class="text-text-primary truncate max-w-[260px] sm:max-w-none" aria-current="page">{{ $post['category_label'] }}</span>
            </nav>
        </div>
    </div>

    <!-- Article Header & Executive Summary -->
    <header class="w-full px-gutter md:px-gutter-desktop pt-space-md pb-space-lg">
        <div class="max-w-7xl mx-auto space-y-space-lg">
            <div class="max-w-4xl space-y-space-md">
                <div class="flex flex-wrap items-center gap-space-sm">
                    <span class="px-space-sm py-0.5 rounded-full bg-primary/15 text-primary font-body-sm text-body-sm">{{ $post['category_label'] }}</span>
                    <span class="px-space-sm py-0.5 rounded bg-surface-panel border border-border-subtle font-label-code text-label-code text-text-tertiary" dir="ltr">{{ $post['report_id'] }}</span>
                    <span class="font-label-code text-label-code text-text-tertiary">{{ $post['read_time'] }}</span>
                    <span class="text-text-tertiary">•</span>
                    <time datetime="{{ $post['published_at'] }}" class="font-label-code text-label-code text-text-tertiary">{{ $post['published_label'] }}</time>
                </div>

                <h1 class="font-display-hero-mobile md:font-display-hero text-display-hero-mobile md:text-display-hero text-text-primary leading-tight">
                    {{ $post['title'] }}
                </h1>

                <p class="font-body-lg text-body-lg text-text-secondary leading-relaxed">
                    {{ $post['excerpt'] }}
                </p>

                <div class="flex flex-wrap items-center justify-between gap-space-sm pt-space-xs">
                    <div class="flex items-center gap-space-sm">
                        <div class="w-10 h-10 rounded-full bg-primary/15 border border-primary/30 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">science</span>
                        </div>
                        <div>
                            <div class="font-body-sm text-body-sm text-text-primary font-semibold">{{ $post['author'] }}</div>
                            <div class="font-label-code text-label-code text-text-tertiary">{{ $post['author_role'] }}</div>
                        </div>
                    </div>

                    <button type="button"
                            @click="copyLink()"
                            class="inline-flex items-center gap-2 px-space-md py-space-xs rounded-xl bg-surface-panel border border-border-subtle hover:border-primary/50 text-text-secondary hover:text-text-primary font-body-sm text-body-sm transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]" x-text="copiedLink ? 'check' : 'share'"></span>
                        <span x-text="copiedLink ? 'تم نسخ رابط البحث' : 'مشاركة البحث'"></span>
                    </button>
                </div>
            </div>

            <!-- Executive Summary Card -->
            @if (! empty($post['takeaways']))
                <div class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-lg space-y-space-md">
                    <div class="flex items-center justify-between gap-space-sm">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-primary" aria-hidden="true">summarize</span>
                            <h2 class="font-headline-sm text-headline-sm text-text-primary">ملخص تنفيذي للمطورين وقادة التقنية</h2>
                        </div>
                        <span class="hidden sm:inline font-label-code text-label-code text-secondary">{{ $post['key_metric_value'] }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
                        @foreach ($post['takeaways'] as $takeaway)
                            <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-md space-y-space-xs">
                                <div class="flex items-center gap-2 font-label-code text-label-code text-primary">
                                    <span>{{ $takeaway['num'] }}</span>
                                    <span>/</span>
                                    <span>{{ $takeaway['title'] }}</span>
                                </div>
                                <p class="font-body-sm text-body-sm text-text-secondary leading-relaxed">
                                    {{ $takeaway['body'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </header>

    <!-- Main 12-Col Reading Layout -->
    <section class="w-full px-gutter md:px-gutter-desktop pb-space-xl">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">

            <!-- Article Body -->
            <article class="lg:col-span-8 space-y-space-lg">
                @php
                    $quotedByPlacement = collect($post['quoted_models'] ?? [])->groupBy('placement');
                    $charts = $post['charts'] ?? [];
                    $customTables = $post['tables'] ?? [];
                @endphp

                @foreach ($post['sections'] as $sectionIndex => $section)
                    <section id="{{ $section['id'] }}" class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-lg space-y-space-md scroll-mt-32">
                        <h2 class="font-headline-md text-headline-md text-text-primary">{{ $section['title'] }}</h2>

                        @foreach ($section['rendered_items'] ?? [] as $item)
                            @if ($item['type'] === 'model_quote')
                                @include('blog.partials.quoted-model-card', ['quote' => $item['card']])
                            @else
                                <p class="font-body-md text-body-md text-text-secondary leading-loose">{{ $item['text'] }}</p>
                            @endif
                        @endforeach

                        @if ($sectionIndex === 0 && $post['slug'] === 'arabic-token-efficiency-analysis')
                            <!-- Visual Arabic Morphological Tokenization Comparison -->
                            <div class="bg-surface-panel border border-border-subtle rounded-xl p-space-md space-y-space-sm">
                                <div class="flex items-center justify-between font-label-code text-label-code text-text-tertiary">
                                    <span>مثال حي على التقطيع الصرفي العربي: «وباستثناءاتهم»</span>
                                    <span dir="ltr">Tokenizer Probe</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-sm">
                                    <div class="bg-surface-base border border-border-subtle rounded-lg p-space-sm space-y-2">
                                        <div class="flex items-center justify-between font-body-sm text-body-sm">
                                            <span class="text-text-secondary">مقطّع تقليدي غير محسّن</span>
                                            <span class="font-label-code text-metric-latency" dir="ltr">6 Tokens</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5" dir="rtl">
                                            @foreach (['وبـ', 'ا', 'ستـ', 'ثنا', 'ءاتـ', 'هم'] as $chip)
                                                <span class="px-2 py-0.5 rounded bg-metric-latency/15 border border-metric-latency/30 text-text-primary font-body-sm text-body-sm">{{ $chip }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="bg-surface-base border border-border-subtle rounded-lg p-space-sm space-y-2">
                                        <div class="flex items-center justify-between font-body-sm text-body-sm">
                                            <span class="text-text-secondary">مقطّع حديث عالي الكفاءة</span>
                                            <span class="font-label-code text-metric-efficiency" dir="ltr">2 Tokens</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5" dir="rtl">
                                            @foreach (['وباستثناءات', 'هم'] as $chip)
                                                <span class="px-2 py-0.5 rounded bg-metric-efficiency/15 border border-metric-efficiency/30 text-text-primary font-body-sm text-body-sm">{{ $chip }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Render quoted models explicitly assigned after section 1, 2, or 3 --}}
                        @foreach ($quotedByPlacement->get('after_section_'.($sectionIndex + 1), []) as $quotedItem)
                            @include('blog.partials.quoted-model-card', ['quote' => $quotedItem])
                        @endforeach

                        {{-- Section 2 Empirical Comparison Table --}}
                        @if ($sectionIndex === 1 && ! empty($post['comparison_rows']))
                            <div class="overflow-x-auto bg-surface-panel border border-border-subtle rounded-xl">
                                <table class="w-full text-right border-collapse">
                                    <thead>
                                        <tr class="border-b border-border-subtle font-label-code text-label-code text-text-tertiary">
                                            <th scope="col" class="py-space-sm px-space-md font-normal">النموذج / المنهجية</th>
                                            <th scope="col" class="py-space-sm px-space-md font-normal">معامل التقطيع</th>
                                            <th scope="col" class="py-space-sm px-space-md font-normal">الاختبار العربي</th>
                                            <th scope="col" class="py-space-sm px-space-md font-normal">تكلفة 1M كلمة (USD)</th>
                                            <th scope="col" class="py-space-sm px-space-md font-normal">بالريال السعودي</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-subtle/60 font-metric-cell text-metric-cell">
                                        @foreach ($post['comparison_rows'] as $row)
                                            <tr class="{{ ! empty($row['highlight']) ? 'bg-primary/5' : '' }}">
                                                <td class="py-space-sm px-space-md font-body-sm text-body-sm text-text-primary font-semibold">
                                                    {{ $row['model'] }}
                                                    @if (! empty($row['highlight']))
                                                        <span class="mr-1 px-1.5 py-0.5 rounded bg-secondary/15 text-secondary font-label-code text-label-code">الأفضل</span>
                                                    @endif
                                                </td>
                                                <td class="py-space-sm px-space-md text-metric-efficiency" dir="ltr">{{ $row['tpw'] }} T/W</td>
                                                <td class="py-space-sm px-space-md text-text-primary" dir="ltr">{{ $row['arabic_score'] }}</td>
                                                <td class="py-space-sm px-space-md text-metric-cost" dir="ltr">{{ $row['cost_usd'] }}</td>
                                                <td class="py-space-sm px-space-md text-text-secondary">{{ $row['cost_sar'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        {{-- Section 3 Code Snippet --}}
                        @if ($sectionIndex === 2 && ! empty($post['code_snippet']))
                            <div class="bg-surface-base border border-border-subtle rounded-xl overflow-hidden">
                                <div class="flex items-center justify-between px-space-md py-space-xs bg-surface-panel border-b border-border-subtle">
                                    <span class="font-label-code text-label-code text-text-tertiary">Measurement Snippet</span>
                                    <button type="button"
                                            @click="copyCode(@js($post['code_snippet']))"
                                            class="inline-flex items-center gap-1 font-label-code text-label-code text-primary hover:underline cursor-pointer">
                                        <span class="material-symbols-outlined text-[14px]" x-text="copiedCode ? 'check' : 'content_copy'"></span>
                                        <span x-text="copiedCode ? 'تم النسخ' : 'نسخ الكود'"></span>
                                    </button>
                                </div>
                                <pre class="p-space-md overflow-x-auto text-left" dir="ltr"><code class="font-metric-cell text-metric-cell text-text-secondary whitespace-pre">{{ $post['code_snippet'] }}</code></pre>
                            </div>
                        @endif
                    </section>
                @endforeach

                {{-- Comparison Table fallback if article has fewer than 2 sections --}}
                @if (count($post['sections'] ?? []) < 2 && ! empty($post['comparison_rows']))
                    <section class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-lg space-y-space-md">
                        <h2 class="font-headline-md text-headline-md text-text-primary">جدول مقارنة النماذج</h2>
                        <div class="overflow-x-auto bg-surface-panel border border-border-subtle rounded-xl">
                            <table class="w-full text-right border-collapse">
                                <thead>
                                    <tr class="border-b border-border-subtle font-label-code text-label-code text-text-tertiary">
                                        <th scope="col" class="py-space-sm px-space-md font-normal">النموذج / المنهجية</th>
                                        <th scope="col" class="py-space-sm px-space-md font-normal">معامل التقطيع</th>
                                        <th scope="col" class="py-space-sm px-space-md font-normal">الاختبار العربي</th>
                                        <th scope="col" class="py-space-sm px-space-md font-normal">تكلفة 1M كلمة (USD)</th>
                                        <th scope="col" class="py-space-sm px-space-md font-normal">بالريال السعودي</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border-subtle/60 font-metric-cell text-metric-cell">
                                    @foreach ($post['comparison_rows'] as $row)
                                        <tr class="{{ ! empty($row['highlight']) ? 'bg-primary/5' : '' }}">
                                            <td class="py-space-sm px-space-md font-body-sm text-body-sm text-text-primary font-semibold">{{ $row['model'] }}</td>
                                            <td class="py-space-sm px-space-md text-metric-efficiency" dir="ltr">{{ $row['tpw'] }} T/W</td>
                                            <td class="py-space-sm px-space-md text-text-primary" dir="ltr">{{ $row['arabic_score'] }}</td>
                                            <td class="py-space-sm px-space-md text-metric-cost" dir="ltr">{{ $row['cost_usd'] }}</td>
                                            <td class="py-space-sm px-space-md text-text-secondary">{{ $row['cost_sar'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                {{-- Interactive Visual Bar Charts --}}
                @foreach ($charts as $chart)
                    @php
                        $bars = $chart['bars'] ?? [];
                        $maxVal = max(1, collect($bars)->max(fn (array $b): float => (float) ($b['value'] ?? 0)) ?: 1);
                    @endphp
                    <section class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-lg space-y-space-md">
                        <div class="flex flex-wrap items-start justify-between gap-space-xs">
                            <div>
                                <h2 class="font-headline-md text-headline-md text-text-primary">{{ $chart['title'] }}</h2>
                                @if (! empty($chart['subtitle']))
                                    <p class="font-body-sm text-body-sm text-text-tertiary mt-0.5">{{ $chart['subtitle'] }}</p>
                                @endif
                            </div>
                            @if (! empty($chart['unit']))
                                <span class="px-space-sm py-0.5 rounded bg-surface-panel border border-border-subtle font-label-code text-label-code text-primary" dir="ltr">{{ $chart['unit'] }}</span>
                            @endif
                        </div>

                        <div class="space-y-space-sm">
                            @foreach ($bars as $bar)
                                @php
                                    $pct = min(100, max(5, round((((float) ($bar['value'] ?? 0)) / $maxVal) * 100, 1)));
                                    $barBg = match ($bar['color'] ?? 'primary') {
                                        'emerald' => 'bg-metric-efficiency',
                                        'amber' => 'bg-metric-cost',
                                        'rose' => 'bg-metric-latency',
                                        'indigo' => 'bg-tertiary',
                                        default => 'bg-primary',
                                    };
                                @endphp
                                <div class="bg-surface-panel border {{ ! empty($bar['highlight']) ? 'border-primary/40' : 'border-border-subtle' }} rounded-xl p-space-sm space-y-1.5">
                                    <div class="flex items-center justify-between gap-space-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="font-body-sm text-body-sm text-text-primary font-semibold">{{ $bar['label'] }}</span>
                                            @if (! empty($bar['highlight']))
                                                <span class="px-1.5 py-0.5 rounded bg-secondary/15 text-secondary font-label-code text-label-code">الأفضل</span>
                                            @endif
                                        </div>
                                        <span class="font-metric-cell text-metric-cell text-text-primary" dir="ltr">
                                            {{ $bar['display_value'] ?? $bar['value'] }} {{ $chart['unit'] ?? '' }}
                                        </span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-surface-base overflow-hidden">
                                        <div class="h-full rounded-full {{ $barBg }}" style="width: {{ $pct }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                {{-- Custom Multi-Column Tables --}}
                @foreach ($customTables as $table)
                    <section class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-lg space-y-space-md">
                        <h2 class="font-headline-md text-headline-md text-text-primary">{{ $table['title'] }}</h2>
                        <div class="overflow-x-auto bg-surface-panel border border-border-subtle rounded-xl">
                            <table class="w-full text-right border-collapse">
                                @if (! empty($table['headers']))
                                    <thead>
                                        <tr class="border-b border-border-subtle font-label-code text-label-code text-text-tertiary">
                                            @foreach ($table['headers'] as $header)
                                                <th scope="col" class="py-space-sm px-space-md font-normal">{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                @endif
                                <tbody class="divide-y divide-border-subtle/60 font-body-sm text-body-sm text-text-secondary">
                                    @foreach ($table['rows'] ?? [] as $row)
                                        <tr>
                                            @foreach ($row as $cellIdx => $cell)
                                                <td class="py-space-sm px-space-md {{ $cellIdx === 0 ? 'text-text-primary font-semibold' : '' }}">{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach

                {{-- Quoted Models Showcase (any quotes marked as showcase or after_section_>count) --}}
                @if ($quotedByPlacement->get('showcase', collect())->isNotEmpty())
                    <section class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-lg space-y-space-sm">
                        <h2 class="font-headline-md text-headline-md text-text-primary">النماذج المستشهد بها في الدراسة</h2>
                        @foreach ($quotedByPlacement->get('showcase', []) as $quotedItem)
                            @include('blog.partials.quoted-model-card', ['quote' => $quotedItem])
                        @endforeach
                    </section>
                @endif

                {{-- Code Snippet fallback if fewer than 3 sections --}}
                @if (count($post['sections'] ?? []) < 3 && ! empty($post['code_snippet']))
                    <section class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-lg space-y-space-md">
                        <h2 class="font-headline-md text-headline-md text-text-primary">مثال برمجي للقياس</h2>
                        <div class="bg-surface-base border border-border-subtle rounded-xl overflow-hidden">
                            <div class="flex items-center justify-between px-space-md py-space-xs bg-surface-panel border-b border-border-subtle">
                                <span class="font-label-code text-label-code text-text-tertiary">Measurement Snippet</span>
                                <button type="button"
                                        @click="copyCode(@js($post['code_snippet']))"
                                        class="inline-flex items-center gap-1 font-label-code text-label-code text-primary hover:underline cursor-pointer">
                                    <span class="material-symbols-outlined text-[14px]" x-text="copiedCode ? 'check' : 'content_copy'"></span>
                                    <span x-text="copiedCode ? 'تم النسخ' : 'نسخ الكود'"></span>
                                </button>
                            </div>
                            <pre class="p-space-md overflow-x-auto text-left" dir="ltr"><code class="font-metric-cell text-metric-cell text-text-secondary whitespace-pre">{{ $post['code_snippet'] }}</code></pre>
                        </div>
                    </section>
                @endif

                <!-- Focused Plugin & Doc AI Action Banner -->
                <div class="bg-surface-subtle border border-primary/30 rounded-2xl p-space-lg flex flex-col sm:flex-row sm:items-center justify-between gap-space-md">
                    <div class="space-y-1">
                        <div class="font-label-code text-label-code text-primary">إضافة وخدمات إدراك Lab</div>
                        <h3 class="font-headline-sm text-headline-sm text-text-primary">هل تريد تطبيق هذه التوصيات تلقائياً في مشاريعك؟</h3>
                        <p class="font-body-sm text-body-sm text-text-secondary">
                            استخدم حاسبة التكلفة التفاعلية أو قارن النماذج مباشرة في لوحة المتصدرين لاختيار الأنسب لمشاريعك.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-space-xs shrink-0">
                        <a href="{{ route('home') }}#calculator-widget" class="px-space-md py-space-sm rounded-xl bg-primary-container text-on-primary-container font-body-sm text-body-sm font-semibold hover:opacity-90 transition-opacity whitespace-nowrap">
                            جرب الحاسبة الآن
                        </a>
                        <a href="{{ route('home') }}#leaderboard-section" class="px-space-md py-space-sm rounded-xl bg-surface-elevated border border-border-subtle text-text-primary hover:border-primary/50 font-body-sm text-body-sm whitespace-nowrap transition-colors">
                            لوحة المتصدرين
                        </a>
                    </div>
                </div>
            </article>

            <!-- Sticky Sidebar: TOC + Model Spotlight + Services/Plugin Card -->
            <aside class="lg:col-span-4 space-y-space-md lg:sticky lg:top-32">

                <!-- 1. Table of Contents -->
                <div class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-md space-y-space-sm">
                    <h2 class="font-headline-sm text-headline-sm text-text-primary">محتويات التقرير</h2>
                    <nav class="space-y-space-xs" aria-label="فهرس المقال">
                        @foreach ($post['sections'] as $section)
                            <a href="#{{ $section['id'] }}"
                               class="block py-1 px-space-sm rounded-lg font-body-sm text-body-sm text-text-secondary hover:text-primary hover:bg-surface-panel transition-colors">
                                {{ $section['title'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <!-- 2. Featured Model Spotlight -->
                @if ($spotlightModel)
                    <div class="bg-surface-subtle border border-border-subtle rounded-2xl p-space-md space-y-space-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-label-code text-label-code text-secondary">MODEL SPOTLIGHT</span>
                            <span class="material-symbols-outlined text-[18px] text-secondary" aria-hidden="true">verified</span>
                        </div>
                        <div>
                            <h3 class="font-headline-sm text-headline-sm text-text-primary">{{ $spotlightModel['name'] }}</h3>
                            <p class="font-label-code text-label-code text-text-tertiary">{{ $spotlightModel['provider'] }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-space-xs">
                            <div class="bg-surface-panel border border-border-subtle rounded-lg p-space-xs">
                                <div class="font-body-sm text-body-sm text-text-tertiary">الاختبار العربي</div>
                                <div class="font-metric-cell text-metric-cell text-text-primary" dir="ltr">
                                    {{ $spotlightModel['score'] !== null ? $spotlightModel['score'].'/'.\App\Services\ModelRankingService::ARABIC_SCORE_MAX : '—' }}
                                </div>
                            </div>
                            <div class="bg-surface-panel border border-border-subtle rounded-lg p-space-xs">
                                <div class="font-body-sm text-body-sm text-text-tertiary">إدخال / 1M</div>
                                <div class="font-metric-cell text-metric-cell text-metric-cost" dir="ltr">
                                    {{ $spotlightModel['in_price'] !== null ? '$'.number_format($spotlightModel['in_price'], 2) : '—' }}
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-space-xs pt-1">
                            <a href="{{ route('models.show', $spotlightModel['slug']) }}"
                               class="w-full inline-flex items-center justify-center gap-1.5 py-space-sm rounded-xl bg-primary-container text-on-primary-container font-body-sm text-body-sm font-semibold hover:opacity-90 transition-opacity">
                                عرض ملف النموذج الكامل
                                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                            </a>
                            <a href="{{ route('home') }}#calculator-widget"
                               class="w-full inline-flex items-center justify-center gap-1.5 py-space-xs rounded-xl bg-surface-panel border border-border-subtle text-text-secondary hover:text-text-primary font-body-sm text-body-sm transition-colors">
                                مقارنة التكلفة في الحاسبة
                            </a>
                        </div>
                    </div>
                @endif

                <!-- 3. Plugin & Services Spotlight -->
                <div class="bg-surface-subtle border border-primary/30 rounded-2xl p-space-md space-y-space-sm">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded bg-primary/15 text-primary font-label-code text-label-code">PLUGIN & SERVICES</span>
                        <span class="material-symbols-outlined text-[18px] text-primary" aria-hidden="true">extension</span>
                    </div>
                    <h2 class="font-headline-sm text-headline-sm text-text-primary">ركز على الإنتاج مع خدمات إدراك Lab</h2>
                    <ul class="space-y-space-xs font-body-sm text-body-sm text-text-secondary">
                        @foreach ($services as $service)
                            <li class="flex items-start gap-2">
                                <span class="material-symbols-outlined text-[16px] text-secondary mt-0.5 shrink-0" aria-hidden="true">{{ $service['icon'] }}</span>
                                <a href="{{ route('home').$service['anchor'] }}" class="hover:text-primary transition-colors">{{ $service['title'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>

        <!-- Related Articles -->
        @if (count($relatedPosts))
            <div class="max-w-7xl mx-auto mt-space-xl pt-space-lg border-t border-border-subtle space-y-space-md">
                <div class="flex items-center justify-between">
                    <h2 class="font-headline-md text-headline-md text-text-primary">أبحاث وتقارير ذات صلة</h2>
                    <a href="{{ route('blog.index') }}" class="font-body-sm text-body-sm text-primary hover:underline flex items-center gap-1">
                        كل الأبحاث
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md">
                    @foreach ($relatedPosts as $related)
                        <a href="{{ route('blog.show', $related['slug']) }}"
                           class="bg-surface-subtle border border-border-subtle hover:border-primary/40 rounded-xl p-space-md space-y-space-xs transition-colors group">
                            <div class="flex items-center justify-between font-label-code text-label-code">
                                <span class="text-primary">{{ $related['category_label'] }}</span>
                                <span class="text-secondary" dir="ltr">{{ $related['key_metric_value'] }}</span>
                            </div>
                            <h3 class="font-headline-sm text-headline-sm text-text-primary group-hover:text-primary transition-colors leading-snug">
                                {{ $related['title'] }}
                            </h3>
                            <div class="font-label-code text-label-code text-text-tertiary pt-1">{{ $related['read_time'] }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <!-- Mobile Sticky Bottom Action Bar -->
    <div class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-surface-subtle/95 backdrop-blur-lg border-t border-border-subtle px-gutter py-space-xs flex items-center justify-between gap-space-xs">
        <a href="{{ route('home') }}#calculator-widget" class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 rounded-lg bg-primary-container text-on-primary-container font-body-sm text-body-sm font-semibold">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">calculate</span>
            حاسبة التكلفة
        </a>
        <button type="button" @click="copyLink()" class="px-3 py-2 rounded-lg bg-surface-panel border border-border-subtle text-text-secondary font-body-sm text-body-sm flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]" x-text="copiedLink ? 'check' : 'link'"></span>
            <span x-text="copiedLink ? 'نُسخ' : 'رابط'"></span>
        </button>
        <a href="{{ route('blog.index') }}" class="px-3 py-2 rounded-lg bg-surface-panel border border-border-subtle text-text-secondary font-body-sm text-body-sm">
            الأبحاث
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function blogArticle() {
        return {
            progress: 0,
            copiedLink: false,
            copiedCode: false,

            updateProgress() {
                const doc = document.documentElement;
                const scrollable = doc.scrollHeight - doc.clientHeight;
                this.progress = scrollable > 0 ? Math.min(100, Math.max(0, (window.scrollY / scrollable) * 100)) : 0;
            },

            copyLink() {
                navigator.clipboard?.writeText(window.location.href).then(() => {
                    this.copiedLink = true;
                    setTimeout(() => { this.copiedLink = false; }, 2000);
                });
            },

            copyCode(snippet) {
                navigator.clipboard?.writeText(snippet).then(() => {
                    this.copiedCode = true;
                    setTimeout(() => { this.copiedCode = false; }, 2000);
                });
            },
        };
    }
</script>
@endpush

